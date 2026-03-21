<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation\Rule;

use Inquisition\Core\Application\Validation\RuleInterface;
use InvalidArgumentException;

final readonly class FileExtensionRule implements RuleInterface
{
    public function __construct(
        private array $allowedExtensions,
    ) {
        if (empty($this->allowedExtensions)) {
            throw new InvalidArgumentException('Allowed extensions cannot be empty');
        }
    }

    #[\Override]
    public function passes(mixed $value, array $data = []): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (!is_array($value) || !isset($value['name'])) {
            return false;
        }

        $filename = $value['name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, array_map('strtolower', $this->allowedExtensions));
    }

    #[\Override]
    public function message(): string
    {
        $extensions = implode(', ', $this->allowedExtensions);
        return sprintf('File must have one of the following extensions: %s', $extensions);
    }

    #[\Override]
    public function getName(): string
    {
        return 'file_extension';
    }
}
