<?php

declare(strict_types=1);

namespace Inquisition\Core\Infrastructure\Support;

final readonly class StringHelper
{
    private function __construct() {}

    public static function camelCaseToSnakeCase(string $input): string
    {
        if (empty($input)) {
            return $input;
        }

        $result = '';
        $length = strlen($input);

        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];

            if ($char >= 'A' && $char <= 'Z') {
                if ($i > 0) {
                    $result .= '_';
                }
                $result .= chr(ord($char) + 32);
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

}
