<?php

namespace App\Services;

use App\Contracts\TextRequestServiceInterface;
use App\Exceptions\DeepSeekException;
use App\Exceptions\GenerateException;
use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;
use Throwable;

class DeepSeekTextRequestService implements TextRequestServiceInterface
{
    /**
     * @throws DeepSeekException
     * @throws GenerateException
     */
    public function send(string $prompt): string
    {
        try {
            $response = DeepSeekClient::build(config('services.deepseek.key'))
                ->withModel(Models::V4_FLASH->value)
                ->setTemperature(1.0)
                ->setMaxTokens(6000)
                ->setResponseFormat('json_object')
                ->query($prompt)
                ->run();
        } catch (Throwable $exception) {
            throw new DeepSeekException('Ошибка при обращении к DeepSeek API: ' . $exception->getMessage());
        }

        // Приводим ответ к массиву — на случай, если клиент вернул строку
        $payload = $this->normalizeResponse($response);

        // Ошибка самого DeepSeek API (Insufficient Balance и т.п.)
        if (isset($payload['error'])) {
            $message = $payload['error']['message'] ?? 'Неизвестная ошибка DeepSeek';
            throw new DeepSeekException('DeepSeek API: ' . $message);
        }

        // Контракт: status / code / message
        if (($payload['status'] ?? null) !== 'ok') {
            $code = $payload['code'] ?? 0;
            $message = $payload['message'] ?? 'Неизвестная ошибка генерации';
            throw new GenerateException("Ошибка генерации (код {$code}): {$message}");
        }

        $story = $payload['message'] ?? null;

        if (!is_string($story) || trim($story) === '') {
            throw new GenerateException('DeepSeek вернул пустой текст сказки');
        }

        return $story;
    }

    /**
     * Приводит любой вариант ответа (строка / массив / объект) к массиву.
     */
    private function normalizeResponse(mixed $response): array
    {
        // Строка — пытаемся распарсить как JSON
        if (is_string($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Может быть вложенный content: {"choices":[{"message":{"content":"{...}"}}]}
                if (isset($decoded['choices'][0]['message']['content'])) {
                    $inner = json_decode($decoded['choices'][0]['message']['content'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($inner)) {
                        return $inner;
                    }
                }
                return $decoded;
            }
            return ['message' => $response];
        }

        // Объект — приводим к массиву
        if (is_object($response)) {
            $response = json_decode(json_encode($response), true);
        }

        if (is_array($response)) {
            // Разворачиваем OpenAI-обёртку, если она есть
            if (isset($response['choices'][0]['message']['content'])) {
                $inner = json_decode($response['choices'][0]['message']['content'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($inner)) {
                    return $inner;
                }
            }
            return $response;
        }

        return ['message' => (string) $response];
    }
}
