<?php

namespace App\Services;

class DeepLinkService
{
    protected $androidPackage;
    protected $appleAppId;

    public function constructor()
    {
        $appleJson = json_decode(file_get_contents(public_path('.well-known/apple-app-site-association.json')), true);
        $this->appleAppId = $appleJson['applinks']['details'][0]['appID'] ?? null;

        // Android JSON read
        $androidJson = json_decode(file_get_contents(public_path('.well-known/assetlinks.json')), true);
        $this->androidPackage = $androidJson[0]['target']['package_name'] ?? null;
    }

    public function createDeepLink($type, $preamps)
    {
        $baseUrl = config('app.url') . "/{$type}?{$preamps}";

        return [
            'type' => $type,
            'universal_link' => $baseUrl,
            'android_fallback' => route('redirect-url', ['type' => 'android']),
            'ios_fallback' => route('redirect-url', ['type' => 'apple']),
        ];
    }

    public function redirectFunctionApple()
    {
        return "https://apps.apple.com/app/id=" . $this->appleAppId;
    }

    public function redirectFunctionGoogle()
    {
        return "https://play.google.com/store/apps/details?id=" . $this->androidPackage;
    }
}
