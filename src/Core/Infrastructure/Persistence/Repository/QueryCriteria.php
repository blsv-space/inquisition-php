<?php

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use Inquisition\Core\Domain\ValueObject\ValueObjectInterface;
use InvalidArgumentException;

final readonly class QueryCriteria
{
    private string $paramName;

    /**
     * @param string|ValueObjectInterface $field
     * @param QueryOperatorEnum $operator
     * @param mixed $value
     */
    public function __construct(
        public string|ValueObjectInterface $field,
        public QueryOperatorEnum           $operator,
        public mixed                       $value
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
        $field = $this->field instanceof ValueObjectInterface ? $this->field->toRaw() : $this->field;

        if (empty($field)) {
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
                if (!is_numeric($this->value)) {
                    throw new InvalidArgumentException('Value must be numeric');
                }
                break;
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

            default:
                throw new InvalidArgumentException('Invalid operator');
        }
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
                $parameters[$paramName . '_' . $i] = (string)$values[$i];
            }

            return $parameters;
        }

        return [
            $paramName => (string)$this->value,
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