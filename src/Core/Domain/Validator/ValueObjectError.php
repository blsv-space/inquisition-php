<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Validator;

class ValueObjectError
{
    public string $field;
    public string $message;
}
