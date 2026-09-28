<?php

namespace App\Telegram\Formatters;

class TelegramTextFormatter
{
    private const MAX_CHUNK_LENGTH = 4000;

    public function format(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = preg_replace('/\%\%([^*]+)\%\%/u', '<i>$1</i>', $text);
        $text = preg_replace('/\*\*([^*]+)\*\*/u', '<b>$1</b>', $text);

        return trim($text);
    }

    public function splitIntoChunks(string $text, int $maxLength = self::MAX_CHUNK_LENGTH): array
    {
        $paragraphs = explode("\n\n", $text);
        $chunks = [];
        $buffer = '';

        foreach ($paragraphs as $paragraph) {
            $candidate = $buffer === '' ? $paragraph : $buffer . "\n\n" . $paragraph;

            if (mb_strlen($candidate) > $maxLength) {
                if ($buffer !== '') {
                    $chunks[] = $buffer;
                }
                $buffer = $paragraph;
            } else {
                $buffer = $candidate;
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return $chunks;
    }
}
