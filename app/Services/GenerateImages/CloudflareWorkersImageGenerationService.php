<?php

namespace App\Services\GenerateImages;

use App\Contracts\ImageGenerationServiceInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareWorkersImageGenerationService implements ImageGenerationServiceInterface
{
    private const API_URL = 'https://api.cloudflare.com/client/v4/accounts/';
    private const GEN_MODEL_URL_PART = '/ai/run/@cf/black-forest-labs/flux-1-schnell';

    private readonly string $accountId;
    private readonly string $apiToken;

    public function __construct( ) {
        $this->accountId = config('services.cloudflare.accountId');
        $this->apiToken = config('services.cloudflare.apiToken');
    }

    /**
     * @throws ConnectionException
     */
    public function generateStream(string $prompt, int $steps = 4)
    {
        $response = Http::withToken($this->apiToken)
            ->timeout(60)
            ->post(
                self::API_URL . $this->accountId . self::GEN_MODEL_URL_PART,
                [
                    'prompt' => $prompt,
                    'steps'  => $steps,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Image generation failed: ' . $response->body()
            );
        }

        $base64 = $response->json('result.image');

        if (empty($base64)) {
            throw new RuntimeException('Return empty image');
        }

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, base64_decode($base64));
        rewind($stream);

        return $stream;
    }
}
