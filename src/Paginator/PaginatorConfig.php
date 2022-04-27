<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Paginator;

use Doctrine\ORM\QueryBuilder;

class PaginatorConfig
{
    protected string $name = 'paginator';
    protected ?int $itemCountInPage = null;
    protected ?int $visiblePageCountInPaginator = null;

    public function __construct(protected QueryBuilder $queryBuilder, protected string $countFieldName)
    {
    }

    public function getRequestQueryName(string $key): string
    {
        return 'kitdg_paginator_' . $this->getName() . '_' . $key;
    }

    /**
     * @return PaginatorConfig Fluent interface
     */
    public function setItemCountInPage(int $itemCountInPage): self
    {
        $this->itemCountInPage = $itemCountInPage;

        return $this;
    }

    public function getItemCountInPage(): ?int
    {
        return $this->itemCountInPage;
    }

    /**
     * @return PaginatorConfig Fluent interface
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return PaginatorConfig Fluent interface
     */
    public function setVisiblePageCountInPaginator(int $visiblePageCountInPaginator): self
    {
        $this->visiblePageCountInPaginator = $visiblePageCountInPaginator;

        return $this;
    }

    public function getVisiblePageCountInPaginator(): ?int
    {
        return $this->visiblePageCountInPaginator;
    }

    public function getCountFieldName(): string
    {
        return $this->countFieldName;
    }
}
