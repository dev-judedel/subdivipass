<?php

namespace App\Services;

use App\Models\Gate;
use App\Models\Pass;
use App\Models\ValidationAttempt;
use App\Models\WorkerPass;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    public function __construct(
        private QRService $qrService,
        private CurfewService $curfewService
    ) {
    }

    public function validate(array $context): array
    {
        /** @var Gate $gate */
        $gate = $context['gate'];
        /** @var User $guard */
        $guard = $context['guard'];

        $method = $context['method'];
        $code = $context['code'];
        $scanType = $context['scan_type'] ?? 'entry';
        $deviceId = $context['device_id'] ?? null;
        $wasOffline = (bool) ($context['was_offline'] ?? false);
        $ip = $context['ip'] ?? null;

        $this->enforceRateLimit($guard->id, $method, $code);

        [$pass, $payloadMeta] = $this->resolvePass($method, $code);

        $outcome = $this->evaluate($pass, $gate);

        $attempt = ValidationAttempt::create([
            'pass_id' => $pass?->id,
            'gate_id' => $gate->id,
            'guard_id' => $guard->id,
            'method' => $method,
            'input_code' => Str::limit($code, 190),
            'status' => $outcome['status'],
            'message' => $outcome['message'],
            'meta' => [
                'scan_type' => $scanType,
                'device_id' => $deviceId,
                'payload' => $payloadMeta,
            ],
            'ip_address' => $ip,
            'was_offline' => $wasOffline,
        ]);

        $outcome['attempt_id'] = $attempt->id;
        $outcome['pass'] = $pass;
        $outcome['scan_data'] = [
            'payload' => $payloadMeta,
            'code_fragment' => Str::limit($code, 24),
        ];

        if ($outcome['status'] === 'success') {
            $this->rememberScan($pass, $gate);
        }

        return $outcome;
    }

    protected function resolvePass(string $method, string $code): array
    {
        $meta = [];
        $pass = null;

        if ($method === 'qr') {
            [$meta, $pass] = $this->resolveFromQr($code);
        } elseif ($method === 'pin') {
            $pass = Pass::where('pin', $code)->first();
        } elseif ($method === 'pass_number') {
            $pass = Pass::where('pass_number', $code)->first();
        } else {
            $pass = Pass::where('uuid', $code)->orWhere('pass_number', $code)->first();
        }

        return [$pass, $meta];
    }

    protected function resolveFromQr(string $payload): array
    {
        $decoded = $this->decodeQrPayload($payload);

        if (!$this->qrService->verifySignature($decoded)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid QR signature.',
            ]);
        }

        $pass = null;
        $worker = null;

        // Check if this is a worker QR code
        if (isset($decoded['type']) && $decoded['type'] === 'worker' && isset($decoded['worker_id'])) {
            $worker = WorkerPass::with('pass')->find($decoded['worker_id']);
            if ($worker) {
                $pass = $worker->pass;
                $decoded['scanned_worker'] = $worker;  // Add worker to metadata
            }
        } elseif (isset($decoded['pass_id'])) {
            // Regular pass QR code
            $pass = Pass::where('uuid', $decoded['pass_id'])->first();
        }

        return [$decoded, $pass];
    }

    protected function decodeQrPayload(string $payload): array
    {
        $stringPayload = $this->attemptDecodeToString($payload);

        $data = json_decode($stringPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid QR data.',
            ]);
        }

        if (isset($data['ciphertext'], $data['iv'])) {
            $data = $this->decryptPayload($data);
        }

        return $data;
    }

    protected function attemptDecodeToString(string $payload): string
    {
        $trimmed = trim($payload);

        if (Str::startsWith($trimmed, '{') && Str::endsWith($trimmed, '}')) {
            return $trimmed;
        }

        $decoded = base64_decode($trimmed, true);
        if ($decoded !== false) {
            return $decoded;
        }

        return $trimmed;
    }

    protected function decryptPayload(array $encrypted): array
    {
        $ciphertext = base64_decode($encrypted['ciphertext'], true);
        $iv = base64_decode($encrypted['iv'], true);
        $tag = isset($encrypted['tag']) ? base64_decode($encrypted['tag'], true) : null;

        if ($ciphertext === false || $iv === false) {
            throw ValidationException::withMessages([
                'code' => 'Invalid QR payload.',
            ]);
        }

        $key = $this->encryptionKey();
        $plain = $tag
            ? openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag)
            : openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        if ($plain === false) {
            throw ValidationException::withMessages([
                'code' => 'Unable to decrypt QR payload.',
            ]);
        }

        $decoded = json_decode($plain, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw ValidationException::withMessages([
                'code' => 'Invalid QR payload.',
            ]);
        }

        return $decoded;
    }

    protected function encryptionKey(): string
    {
        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        return hash('sha256', $key, true);
    }

    protected function enforceRateLimit(int $guardId, string $method, string $code): void
    {
        $globalKey = "guard-scan:{$guardId}";
        $this->hitRateLimiter($globalKey, 30, 60);

        if ($method === 'pin') {
            $pinKey = "guard-pin:{$guardId}:{$code}";
            $this->hitRateLimiter($pinKey, 3, 15 * 60);
        }
    }

    protected function hitRateLimiter(string $key, int $maxAttempts, int $decaySeconds): void
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function evaluate(?Pass $pass, Gate $gate): array
    {
        if (!$pass) {
            return [
                'status' => 'error',
                'message' => 'Pass not found. Please verify the code or PIN.',
            ];
        }

        if ($pass->isBlacklisted()) {
            return [
                'status' => 'error',
                'message' => 'Pass is blacklisted. Escalate to supervisor.',
            ];
        }

        if ($pass->isRevoked()) {
            return [
                'status' => 'error',
                'message' => 'This pass has been revoked.',
            ];
        }

        if ($pass->isExpired()) {
            return [
                'status' => 'error',
                'message' => 'Pass is expired.',
            ];
        }

        if (!$pass->isApproved()) {
            return [
                'status' => 'error',
                'message' => 'Pass is not approved or active yet.',
            ];
        }

        if (!$pass->isValid()) {
            return [
                'status' => 'error',
                'message' => 'Pass is outside its validity window.',
            ];
        }

        // Check curfew and time restrictions
        $curfewValidation = $this->curfewService->validatePassEntry($pass);
        if (!$curfewValidation['valid']) {
            return [
                'status' => 'error',
                'message' => $curfewValidation['message'],
                'code' => $curfewValidation['code'],
            ];
        }

        if ($pass->subdivision_id !== $gate->subdivision_id) {
            return [
                'status' => 'warning',
                'message' => 'Pass belongs to a different subdivision.',
            ];
        }

        if ($this->isDuplicateScan($pass, $gate)) {
            return [
                'status' => 'warning',
                'message' => 'Duplicate scan detected in the last few seconds.',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Pass validated successfully.',
        ];
    }

    protected function isDuplicateScan(Pass $pass, Gate $gate): bool
    {
        $key = "pass-duplicate:{$pass->id}:{$gate->id}";

        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, now()->addSeconds(15));

        return false;
    }

    protected function rememberScan(Pass $pass, Gate $gate): void
    {
        $key = "pass-duplicate:{$pass->id}:{$gate->id}";
        Cache::put($key, true, now()->addSeconds(15));
    }

}
