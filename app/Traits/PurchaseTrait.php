<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;
use Google\Client;
use Google\Service\AndroidPublisher;

trait PurchaseTrait
{

    protected $androidPublisherService;
    protected $mode;
    protected $apple_shared_secret;
    protected $google_package_name;
    protected $apple_key_id;
    protected $apple_issuer_id;
    protected $apple_bundle_id;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(base_path(config('services.google_play.credentials')));
        $client->addScope(AndroidPublisher::ANDROIDPUBLISHER);
        $this->androidPublisherService = new AndroidPublisher($client);

        $this->google_package_name = config('services.google_play.package_name');
        $this->mode = config('services.apple.in_app_env', 'sandbox');
        $this->apple_shared_secret = config('services.apple.shared_secret');
        $this->apple_key_id = config('services.apple.key_id');
        $this->apple_issuer_id = config('services.apple.issuer_id');
        $this->apple_bundle_id = config('services.package.name');
    }

    public function verifyAndroidPurchase($purchaseToken, $productId)
    {
        $packageName = $this->google_package_name; // e.g. com.myapp
        $status = false;
        try {
            $result = $this->androidPublisherService->purchases_products->get(
                $packageName,
                $productId,
                $purchaseToken
            );

            // Check purchase state (0 = purchased, 1 = canceled, etc.)
            if ($result->purchaseState == 0) {
                $status = true;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage() . ' - ' . $e->getLine();
            Log::error("Android purchase verification failed: " . $error);
        }

        return ['status' => $status];
    }
    
    public function verifyIosPurchase($receiptData)
    {
        // Check if receipt data is present
        if (empty($receiptData)) {
            Log::warning('iOS receipt data missing');
            return ['status' => false];
        }

        // Select the correct endpoint
        $url = $this->mode === 'sandbox'
            ? 'https://sandbox.itunes.apple.com/verifyReceipt'
            : 'https://buy.itunes.apple.com/verifyReceipt';

        // Payload for Apple
        $payload = [
            'receipt-data' => $receiptData, // base64 receipt from device
            'password' => $this->apple_shared_secret, // app shared secret
            'exclude-old-transactions' => true
        ];

        try {
            $response = Http::timeout(10)->post($url, $payload);

            if (!$response->successful()) {
                Log::warning('Apple receipt verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['status' => false];
            }elseif($response->status() == 200){
                return ['status' => true];
            }
        } catch (\Exception $e) {
            Log::error('iOS receipt verification exception', ['message' => $e->getMessage()]);
            return ['status' => false];
        }

        return ['status' => false];
    }

    public function generateAppleJwt()
    {
        $path = storage_path('app/apple/AuthKey_' . $this->apple_key_id . '.p8'); // your .p8 file
        $privateKey = file_get_contents($path);

        $time = time();
        $payload = [
            'iss' => $this->apple_issuer_id,
            'iat' => $time,
            'exp' => $time + 1800, // 30 min
            'aud' => 'appstoreconnect-v1',
            'bid' => $this->apple_bundle_id,
        ];

        $headers = [
            'alg' => 'ES256',
            'kid' => $this->apple_key_id,
            'typ' => 'JWT',
        ];

        return JWT::encode($payload, $privateKey, 'ES256', $this->apple_key_id, $headers);
    }

    public function jwtDecodeString(string $jwt): array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid JWT format.');
        }

        [$header, $payload, $signature] = $parts;

        return [
            'header'    => json_decode($this->base64UrlDecode($header), true),
            'payload'   => json_decode($this->base64UrlDecode($payload), true),
            'signature' => $signature,
        ];
    }

    public function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function decodeIosTransaction($jwsToken)
    {
        $parts = explode('.', $jwsToken);
        if (count($parts) !== 3) {
            return null;
        }
        return json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
    }
}
