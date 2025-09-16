<?php

namespace Inquisition\Core\Infrastructure\Http\Controller;

use Inquisition\Core\Infrastructure\Persistence\Repository\QueryCriteria;
use Inquisition\Core\Infrastructure\Persistence\Repository\QueryOperatorEnum;
use RuntimeException;
use Inquisition\Core\Infrastructure\Http\Request\RequestInterface;
use Inquisition\Core\Infrastructure\Http\Response\ResponseInterface;

/**
 * Abstract REST Controller
 * Base implementation for RESTful API controllers
 */
abstract readonly class AbstractRestController extends AbstractApiController
    implements RestControllerInterface
{

    public const string PAGE_PARAM = 'page';
    public const string PER_PAGE_PARAM = 'per_page';
    public const int PER_PAGE_DEFAULT = 20;
    public const int PER_PAGE_MAX = 100;
    public const int PER_PAGE_MIN = 1;
    public const string SORT_PARAM = 'sort';
    public const string SORT_DIRECTION_PARAM = 'direction';
    public const string FILTER_OPERATOR_SUFFIX = '_operator';


    /**
     * GET /resource - List all resources
     *
     * @param RequestInterface $request
     * @param array<string, string> $parameters
     * @return ResponseInterface
     */
    public function index(RequestInterface $request, array $parameters): ResponseInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * GET /resource/{id} - Show a specific resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function show(RequestInterface $request, array $parameters): ResponseInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * POST /resource - Create a new resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function store(RequestInterface $request, array $parameters): ResponseInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * PUT/PATCH /resource/{id} - Update existing resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function update(RequestInterface $request, array $parameters): ResponseInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * DELETE /resource/{id} - Delete resource
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return ResponseInterface
     */
    public function destroy(RequestInterface $request, array $parameters): ResponseInterface
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * Extract resource ID from parameters
     *
     * @param array $parameters
     * @param string $key
     * @return string|null
     */
    protected function getResourceId(array $parameters, string $key = 'id'): ?string
    {
        return $parameters[$key] ?? null;
    }

    /**
     * Get pagination parameters from request
     *
     * @param RequestInterface $request
     * @return array{page: int, per_page: int}
     */
    protected function getPaginationParams(RequestInterface $request): array
    {
        $page = max(1, (int)($request->getParameter(static::PAGE_PARAM, 1)));
        $perPage = min(
            static::PER_PAGE_MAX,
            max(
                static::PER_PAGE_MIN,
                (int)($request->getParameter(static::PER_PAGE_PARAM, static::PER_PAGE_DEFAULT)
                )
            )
        );

        return [
            'page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * Get filtering parameters from request
     *
     * @param RequestInterface $request
     * @param array $allowedFilters
     * @return QueryCriteria[]
     */
    protected function getFilterParams(RequestInterface $request, array $allowedFilters = []): array
    {
        $filters = [];

        foreach ($allowedFilters as $filter) {
            $value = $request->getParameter($filter);
            if ($value !== null) {
                $operator = QueryOperatorEnum::EQUALS;
                $operatorName = $request->getParameter($filter . static::FILTER_OPERATOR_SUFFIX);
                if (!is_null($operatorName)) {
                    $operator = QueryOperatorEnum::tryFrom($operatorName) ?? QueryOperatorEnum::EQUALS;
                }
                $filters[] = new QueryCriteria(
                    field: $filter,
                    value: $value,
                    operator: $operator,
                );
            }
        }

        return $filters;
    }

    /**
     * Get sorting parameters from request
     *
     * @param RequestInterface $request
     * @param array $allowedSortFields
     * @param string $defaultSort
     * @return array{field: string, direction: string}
     */
    protected function getSortParams(
        RequestInterface $request,
        array            $allowedSortFields = [],
        string           $defaultSort = 'id'
    ): array
    {
        $sort = $request->getParameter(static::SORT_PARAM, $defaultSort);
        $direction = strtolower($request->getParameter(static::SORT_DIRECTION_PARAM, 'asc'));

        if (!empty($allowedSortFields) && !in_array($sort, $allowedSortFields)) {
            $sort = $defaultSort;
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        return [
            'field' => $sort,
            'direction' => $direction
        ];
    }
}