<?php

namespace Inquisition\Core\Infrastructure\Persistence\Repository;

use InvalidArgumentException;

final readonly class QueryCriteria
{
    public string $alias;

    public function __construct(
        public string            $field,
        public QueryOperatorEnum $operator,
        public mixed             $value,
        ?string                  $alias = null,
    )
    {
        $this->alias = $alias ?? $this->field;

        $this->validate();
    }

    private function validate()
    {
        if (empty($this->field)) {
            throw new InvalidArgumentException('Field cannot be empty');
        }

        if (str_contains($this->field, '.')) {
            throw new InvalidArgumentException('Field cannot contain dots');
        }

        if ($this->field !== $this->alias) {
            if (empty($this->alias)) {
                throw new InvalidArgumentException('Alias cannot be empty');
            }

            if (str_contains($this->alias, ' ')) {
                throw new InvalidArgumentException('Field or Alias cannot contain spaces');
            }
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
        }
    }

    public function compile(): string
    {
        switch ($this->operator) {
            case QueryOperatorEnum::EQUALS:
                return "`$this->alias` = :$this->alias";
            case QueryOperatorEnum::NOT_EQUALS:
                return "`$this->alias` != :$this->alias";
            case QueryOperatorEnum::LESS_THAN:
                return "`$this->alias` < :$this->alias";
            case QueryOperatorEnum::LESS_THAN_OR_EQUALS:
                return "`$this->alias` <= :$this->alias";
            case QueryOperatorEnum::GREATER_THAN:
                return "`$this->alias` > :$this->alias";
            case QueryOperatorEnum::GREATER_THAN_OR_EQUALS:
                return "`$this->alias` >= :$this->alias";
            case QueryOperatorEnum::LIKE:
                return "`$this->alias` LIKE :$this->alias";
            case QueryOperatorEnum::NOT_LIKE:
                return "`$this->alias` NOT LIKE :$this->alias";
            case QueryOperatorEnum::IN:
                $paramNames = array_keys($this->getParameters());
                return "`$this->alias` IN (:" . implode(', :', $paramNames) . ")";
            case QueryOperatorEnum::NOT_IN:
                $paramNames = array_keys($this->getParameters());
                return "`$this->alias` NOT IN (:" . implode(', :', $paramNames) . ")";

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

    private function getParamName(): string
    {
        return $this->alias;
    }
}