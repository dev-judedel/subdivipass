<?php

namespace App\Services;

use App\Models\Pass;
use App\Models\WorkerPass;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QRService
{
    /**
     * Generate QR code for a pass.
     *
     * @param Pass $pass
     * @return string Path to the generated QR code
     */
    public function generateQRCode(Pass $pass): string
    {
        // Create QR code data
        $qrData = $this->prepareQRData($pass);

        // Generate signature
        $signature = $this->generateSignature($qrData);

        // Add signature to data
        $qrData['signature'] = $signature;

        // Convert to JSON
        $jsonData = json_encode($qrData);

        // Generate QR code image
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($jsonData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(400)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->labelText($pass->pass_number)
            ->labelAlignment(LabelAlignment::Center)
            ->build();

        // Save QR code to storage
        $fileName = $this->generateFileName($pass);
        $path = "qrcodes/{$pass->subdivision_id}/{$fileName}";

        Storage::disk('public')->put($path, $result->getString());

        return $path;
    }

    /**
     * Prepare QR code data.
     *
     * @param Pass $pass
     * @return array
     */
    protected function prepareQRData(Pass $pass): array
    {
        return [
            'pass_id' => $pass->uuid,
            'pass_number' => $pass->pass_number,
            'subdivision_id' => $pass->subdivision_id,
            'subdivision_code' => $pass->subdivision->code,
            'type' => $pass->type->slug,
            'visitor_name' => $pass->visitor_name,
            'valid_from' => $pass->valid_from->toIso8601String(),
            'valid_to' => $pass->valid_to->toIso8601String(),
            'pin' => $pass->pin,
            'created_at' => $pass->created_at->toIso8601String(),
        ];
    }

    /**
     * Generate HMAC-SHA256 signature for QR data.
     *
     * @param array $data
     * @return string
     */
    protected function generateSignature(array $data): string
    {
        $key = config('app.key');
        $dataString = json_encode($data);

        return hash_hmac('sha256', $dataString, $key);
    }

    /**
     * Verify QR code signature.
     *
     * @param array $qrData
     * @return bool
     */
    public function verifySignature(array $qrData): bool
    {
        if (!isset($qrData['signature'])) {
            return false;
        }

        $providedSignature = $qrData['signature'];
        unset($qrData['signature']);

        $calculatedSignature = $this->generateSignature($qrData);

        return hash_equals($calculatedSignature, $providedSignature);
    }

    /**
     * Generate file name for QR code.
     *
     * @param Pass $pass
     * @return string
     */
    protected function generateFileName(Pass $pass): string
    {
        return sprintf(
            '%s_%s.png',
            $pass->uuid,
            now()->format('YmdHis')
        );
    }

    /**
     * Decode and verify QR code data.
     *
     * @param string $qrDataJson
     * @return array|null
     */
    public function decodeAndVerify(string $qrDataJson): ?array
    {
        try {
            $qrData = json_decode($qrDataJson, true);

            if (!$this->verifySignature($qrData)) {
                return null;
            }

            return $qrData;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Regenerate QR code for a pass.
     *
     * @param Pass $pass
     * @return string
     */
    public function regenerateQRCode(Pass $pass): string
    {
        // Delete old QR code if exists
        if ($pass->qr_code_path) {
            Storage::disk('public')->delete($pass->qr_code_path);
        }

        // Generate new QR code
        return $this->generateQRCode($pass);
    }

    /**
     * Generate QR code for a worker pass.
     *
     * @param WorkerPass $worker
     * @return string Path to the generated QR code
     */
    public function generateWorkerQRCode(WorkerPass $worker): string
    {
        // Create QR code data for worker
        $qrData = $this->prepareWorkerQRData($worker);

        // Generate signature
        $signature = $this->generateWorkerSignature($worker);

        // Add signature to data
        $qrData['signature'] = $signature;

        // Convert to JSON
        $jsonData = json_encode($qrData);

        // Generate QR code image
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($jsonData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(400)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->labelText($worker->worker_name)
            ->labelAlignment(LabelAlignment::Center)
            ->build();

        // Save QR code to storage
        $fileName = $this->generateWorkerFileName($worker);
        $path = "qrcodes/workers/{$worker->pass->subdivision_id}/{$fileName}";

        Storage::disk('public')->put($path, $result->getString());

        return $path;
    }

    /**
     * Prepare QR code data for worker.
     *
     * @param WorkerPass $worker
     * @return array
     */
    protected function prepareWorkerQRData(WorkerPass $worker): array
    {
        $pass = $worker->pass;

        return [
            'type' => 'worker',
            'worker_id' => $worker->id,
            'pass_id' => $pass->uuid,
            'pass_number' => $pass->pass_number,
            'subdivision_id' => $pass->subdivision_id,
            'subdivision_code' => $pass->subdivision->code,
            'worker_name' => $worker->worker_name,
            'worker_position' => $worker->worker_position,
            'worker_id_number' => $worker->worker_id_number,
            'valid_from' => $pass->valid_from->toIso8601String(),
            'valid_to' => $pass->valid_to->toIso8601String(),
            'created_at' => $worker->created_at->toIso8601String(),
        ];
    }

    /**
     * Generate HMAC-SHA256 signature for worker QR data.
     *
     * @param WorkerPass $worker
     * @return string
     */
    public function generateWorkerSignature(WorkerPass $worker): string
    {
        $data = $this->prepareWorkerQRData($worker);
        return $this->generateSignature($data);
    }

    /**
     * Generate file name for worker QR code.
     *
     * @param WorkerPass $worker
     * @return string
     */
    protected function generateWorkerFileName(WorkerPass $worker): string
    {
        return sprintf(
            'worker_%s_%s.png',
            $worker->id,
            now()->format('YmdHis')
        );
    }

    /**
     * Verify worker QR code signature.
     *
     * @param array $qrData
     * @return bool
     */
    public function verifyWorkerSignature(array $qrData): bool
    {
        if (!isset($qrData['signature']) || !isset($qrData['type']) || $qrData['type'] !== 'worker') {
            return false;
        }

        return $this->verifySignature($qrData);
    }
}
