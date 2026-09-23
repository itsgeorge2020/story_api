<?php

namespace App\Traits;

trait EnumTrait
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getByCase(string $case): ?self
    {
        return self::tryFrom($case);
    }

    public static function getAllAsArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->getLabel();
        }
        return $result;
    }
}
