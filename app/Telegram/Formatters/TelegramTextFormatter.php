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
        return $this->balanceTags($this->rawChunks($text, $maxLength));
    }

    public function splitForPhoto(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return ['caption' => '', 'messages' => []];
        }

        $captionChunks = $this->rawChunks($text, self::MAX_CAPTION_LENGTH);
        $caption = array_shift($captionChunks);
        $rest = trim(implode("\n\n", $captionChunks));

        $messages = $rest === '' ? [] : $this->rawChunks($rest, self::MAX_CHUNK_LENGTH);

        $balanced = $this->balanceTags(array_merge([$caption], $messages));
        $caption = array_shift($balanced);

        return [
            'caption'  => $caption,
            'messages' => $balanced,
        ];
    }

    private function rawChunks(string $text, int $maxLength): array
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

    private function balanceTags(array $chunks): array
    {
        $balanced = [];
        $carry = [];

        foreach ($chunks as $chunk) {
            foreach ($carry as $tag) {
                $chunk = "<$tag>" . $chunk;
            }

            $stack = [];

            if (preg_match_all('/<(\/?)([bi])>/', $chunk, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    if ($match[1] === '') {
                        $stack[] = $match[2];
                    } else {
                        array_pop($stack);
                    }
                }
            }

            foreach (array_reverse($stack) as $tag) {
                $chunk .= "</$tag>";
            }

            $balanced[] = $chunk;
            $carry = $stack;
        }

        return $balanced;
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
