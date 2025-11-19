<?php

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use InvalidArgumentException;

final readonly class QueryCriteria
{
    private string $paramName;

    /**
     * @param string $field
     * @param QueryOperatorEnum $operator
     * @param mixed|ValueObjectInterface $value
     */
    public function __construct(
        public string            $field,
        public mixed             $value,
        public QueryOperatorEnum $operator = QueryOperatorEnum::EQUALS,
    )
    {
        $this->validate();

        $this->paramName = str_replace(['.'], '_', $this->field);
    }

    /**
     * @return void
     */
    private function validate(): void
    {
        if (empty($this->field)) {
            throw new InvalidArgumentException('Field cannot be empty');
        }

        if (str_contains($this->field, '`')
            || str_contains($this->field, '(')
            || str_contains($this->field, ')')
            || str_contains($this->field, ':')
            || str_contains($this->field, ';')
            || str_contains($this->field, '"')
            || str_contains($this->field, "'")
        ) {
            throw new InvalidArgumentException('Field cannot contain special characters');
        }

        switch ($this->operator) {
            case QueryOperatorEnum::IN:
            case QueryOperatorEnum::NOT_IN:
                if (!is_array($this->value)) {
                    throw new InvalidArgumentException('Value must be an array');
                }
                break;
            case QueryOperatorEnum::LESS_THAN:
            case QueryOperatorEnum::LESS_THAN_OR_EQUALS:
            case QueryOperatorEnum::GREATER_THAN:
            case QueryOperatorEnum::GREATER_THAN_OR_EQUALS:
            case QueryOperatorEnum::EQUALS:
            case QueryOperatorEnum::NOT_EQUALS:
            case QueryOperatorEnum::LIKE:
            case QueryOperatorEnum::NOT_LIKE:
                break;
        }
    }

    /**
     * @return string
     */
    public function compile(): string
    {
        $paramName = $this->getParamName();

        switch ($this->operator) {
            case QueryOperatorEnum::EQUALS:
                return "`$this->field` = :$paramName";
            case QueryOperatorEnum::NOT_EQUALS:
                return "`$this->field` != :$paramName";
            case QueryOperatorEnum::LESS_THAN:
                return "`$this->field` < :$paramName";
            case QueryOperatorEnum::LESS_THAN_OR_EQUALS:
                return "`$this->field` <= :$paramName";
            case QueryOperatorEnum::GREATER_THAN:
                return "`$this->field` > :$paramName";
            case QueryOperatorEnum::GREATER_THAN_OR_EQUALS:
                return "`$this->field` >= :$paramName";
            case QueryOperatorEnum::LIKE:
                return "`$this->field` LIKE :$paramName";
            case QueryOperatorEnum::NOT_LIKE:
                return "`$this->field` NOT LIKE :$paramName";
            case QueryOperatorEnum::IN:
                $paramNames = array_keys($this->getParameters());

                return "`$this->field` IN (:" . implode(', :', $paramNames) . ")";
            case QueryOperatorEnum::NOT_IN:
                $paramNames = array_keys($this->getParameters());

                return "`$this->field` NOT IN (:" . implode(', :', $paramNames) . ")";
        }
        throw new InvalidArgumentException('Invalid operator');
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        $paramName = $this->getParamName();

        if ($this->operator === QueryOperatorEnum::IN || $this->operator === QueryOperatorEnum::NOT_IN) {
            $values = array_values($this->value);
            $parameters = [];
            for ($i = 0; $i < count($values); $i++) {
                $value = $values[$i] instanceof ValueObjectInterface
                    ? $values[$i]->toRaw()
                    : $values[$i];

                $parameters[$paramName . '_' . $i] = (string)$value;
            }

            return $parameters;
        }

        $value = $this->value instanceof ValueObjectInterface
            ? $this->value->toRaw()
            : $this->value;

        return [
            $paramName => (string)$value,
        ];
    }

    /**
     * @return string
     */
    private function getParamName(): string
    {
        return $this->paramName;
    }
}