<?php

declare(strict_types=1);

namespace Inquisition\Core\Application\Validation;

use Inquisition\Core\Application\Validation\Exception\ValidationException;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;

class HttpRequestValidator implements ValidatorInterface
{
    /**
     * @var array<string, RuleInterface[]>
     */
    private array $rules = [];

    /**
     * @var array<string>
     */
    public protected(set) array $errors = [] {
        get {
            return $this->errors;
        }
    }

    /**
     * @throws ValidationException
     */
    #[\Override]
    public function validate(mixed $data): bool
    {
        if (!$data instanceof RequestInterface) {
            throw new ValidationException('HttpRequestValidator can only validate RequestInterface instances');
        }

        $this->clearErrors();
        $this->validateRequest($data);

        if ($this->hasErrors()) {
            throw new ValidationException('Request validation failed: ' . implode(', ', $this->errors));
        }

        return true;
    }

    #[\Override]
    public function addRule(string $field, RuleInterface $rule): self
    {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }

        $this->rules[$field][] = $rule;

        return $this;
    }

    #[\Override]
    public function addRules(array $rules): self
    {
        foreach ($rules as $field => $rule) {
            if (is_array($rule)) {
                foreach ($rule as $singleRule) {
                    $this->addRule($field, $singleRule);
                }
            } else {
                $this->addRule($field, $rule);
            }
        }

        return $this;
    }

    #[\Override]
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }

    #[\Override]
    public function hasErrors(): bool
    {
        return count($this->errors) !== 0;
    }

    /**
     * Validate HTTP request against defined rules.
     *
     */
    private function validateRequest(RequestInterface $request): void
    {
        $data = $request->getAllParameters();

        $errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if (!$rule->passes($value, $data)) {
                    $errors[] = sprintf('%s: %s', $field, $rule->message());
                }
            }
        }

        $this->errors = $errors;
    }
}
