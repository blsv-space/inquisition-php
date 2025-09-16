<?php

namespace Inquisition\Core\Domain\Validator;

class StubValueObjectValidator extends AbstractValueObjectValidator
    implements ValueObjectValidatorInterface
{

    protected function doValidate(mixed $data): void
    {
    }
}