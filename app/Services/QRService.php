<?php

namespace App\Services;

use App\Models\Pass;
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
}
