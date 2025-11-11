<?php
namespace App\Services;

use Google\Client;
use Google\Service\AndroidPublisher;

class GooglePlayValidator
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google/service-account.json'));
        $this->client->addScope(AndroidPublisher::ANDROIDPUBLISHER);
        $this->service = new AndroidPublisher($this->client);
    }

    /**
     * Validate the purchase from Play Store.
     */
    public function verifySubscription(string $packageName, string $subscriptionId, string $purchaseToken)
    {
        try {
            $response = $this->service->purchases_subscriptions->get(
                $packageName,
                $subscriptionId,
                $purchaseToken
            );

            return $response;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    protected static function generateAppStoreJWT()
    {
        $privateKeyPath = config('services.appstore.private_key');
        $keyId          = config('services.appstore.key');
        $issuerId       = config('services.appstore.issuer_id');

        if (!file_exists($privateKeyPath)) {
            return null;
        }

        $privateKey = file_get_contents($privateKeyPath);
        $now = time();
        $token = [
            'iss' => $issuerId,
            'iat' => $now,
            'exp' => $now + 1800,
            'aud' => 'appstoreconnect-v1',
            'bid' =>  config('services.subscription.package_name'),
        ];

        $headers = [
            'alg' => 'ES256',
            'kid' => '9VC79U759S',
            'typ' => 'JWT'
        ];

        return \Firebase\JWT\JWT::encode(
            $token,
            $privateKey,
            'ES256',
            $keyId,
            $headers
        );
    }

    public function getAndroidPublisherService(): AndroidPublisher
    {
        return $this->service;
    }

    public function cancelSubscription(string $packageName, string $subscriptionId, string $purchaseToken)
    {
        try {
            $response = $this->service->purchases_subscriptions->cancel(
                $packageName,
                $subscriptionId,
                $purchaseToken
            );

            return $response;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

}
