<?php

namespace App\Telegram\Formatters;

class TelegramTextFormatter
{
    private const MAX_CHUNK_LENGTH = 4000;
    private const MAX_CAPTION_LENGTH = 1024;

    public function format(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = preg_replace('/~([^~]+)~/u', '<i>$1</i>', $text);
        $text = preg_replace('/\^([^\^]+)\^/u', '<b>$1</b>', $text);

        return trim($text);
    }

    public function splitIntoChunks(string $text, int $maxLength = self::MAX_CHUNK_LENGTH): array
    {
        $chunks = [];
        $buffer = '';

        foreach (explode("\n\n", $text) as $paragraph) {
            foreach ($this->splitOversized($paragraph, $maxLength) as $piece) {
                $candidate = $buffer === '' ? $piece : $buffer . "\n\n" . $piece;

                if (mb_strlen($candidate) > $maxLength) {
                    if ($buffer !== '') {
                        $chunks[] = $buffer;
                    }
                    $buffer = $piece;
                } else {
                    $buffer = $candidate;
                }
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return $chunks;
    }

    public function splitForPhoto(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return ['caption' => '', 'messages' => []];
        }

        $captionChunks = $this->splitIntoChunks($text, self::MAX_CAPTION_LENGTH);
        $caption = array_shift($captionChunks);
        $rest = trim(implode("\n\n", $captionChunks));

        return [
            'caption'  => $caption,
            'messages' => $rest === '' ? [] : $this->splitIntoChunks($rest),
        ];
    }

    private function splitOversized(string $paragraph, int $maxLength): array
    {
        if (mb_strlen($paragraph) <= $maxLength) {
            return [$paragraph];
        }

        $pieces = [];
        $buffer = '';

        foreach (explode(' ', $paragraph) as $word) {
            $candidate = $buffer === '' ? $word : $buffer . ' ' . $word;

            if (mb_strlen($candidate) > $maxLength) {
                if ($buffer !== '') {
                    $pieces[] = $buffer;
                }
                $buffer = $word;
            } else {
                $buffer = $candidate;
            }
        }

        if ($buffer !== '') {
            $pieces[] = $buffer;
        }

        return $pieces;
    }
}
