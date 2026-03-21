<?php

declare(strict_types=1);

namespace Inquisition\Core\Domain\Validator;

class StubValueObjectValidator extends AbstractValueObjectValidator implements ValueObjectValidatorInterface
{
    #[\Override]
    protected function doValidate(mixed $data): void {}
}
