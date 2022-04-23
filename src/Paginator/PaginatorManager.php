<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Paginator;

use Kitpages\DataGridBundle\Event\AfterGetPaginatorQuery;
use Kitpages\DataGridBundle\Event\DataGridEvent;
use Kitpages\DataGridBundle\Event\OnGetPaginatorQuery;
use Kitpages\DataGridBundle\Tool\UrlTool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorManager
{
    protected EventDispatcherInterface $dispatcher;
    protected array $paginatorParameterList;
    /**
     * @phpstan-var null|callable(PaginatorConfig): PaginatorConfig
     * @var callable|null
     */
    protected $configurePaginator;

    /**
     * @param mixed $paginatorParameterList
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        $paginatorParameterList
    ) {
        $this->dispatcher = $dispatcher;
        $this->paginatorParameterList = $paginatorParameterList;
    }

    /** @phpstan-param null|callable(PaginatorConfig): PaginatorConfig $configurePaginator */
    public function setConfigurePaginator(?callable $configurePaginator): self
    {
        if ($this->configurePaginator !== null) {
            throw new \InvalidArgumentException('Only one configure paginator callback can be set. Do all configuration in that one callback.');
        }

        $this->configurePaginator = $configurePaginator;

        return $this;
    }

    ////
    // paginator
    ////

    public function getPaginator(PaginatorConfig $paginatorConfig, Request $request): Paginator
    {
        if (is_callable($this->configurePaginator)) {
            $configuredPaginator = call_user_func($this->configurePaginator, $paginatorConfig);

            if ($configuredPaginator instanceof PaginatorConfig) {
                $paginatorConfig = $configuredPaginator;
            } else {
                throw new \LogicException(
                    sprintf(
                        'Configure Paginator function return %s instead of a PaginatorConfig.',
                        gettype($configuredPaginator),
                    )
                );
            }
        }

        $queryBuilder = $paginatorConfig->getQueryBuilder();

        // insert default values in paginator config
        $paginatorConfig = clone ($paginatorConfig);
        if ($paginatorConfig->getItemCountInPage() === null) {
            $paginatorConfig->setItemCountInPage($this->paginatorParameterList['item_count_in_page']);
        }
        if ($paginatorConfig->getVisiblePageCountInPaginator() === null) {
            $paginatorConfig->setVisiblePageCountInPaginator($this->paginatorParameterList['visible_page_count_in_paginator']);
        }

        // create paginator object
        $paginator = new Paginator($paginatorConfig, new UrlTool(), $request->getRequestUri());

        // get currentPage
        $paginator->setCurrentPage($request->query->getInt($paginatorConfig->getRequestQueryName('currentPage'), 1));

        // calculate total object count
        $countQueryBuilder = clone ($queryBuilder);
        $countQueryBuilder->select('count(DISTINCT ' . $paginatorConfig->getCountFieldName() . ')');
        $countQueryBuilder->setMaxResults(null);
        $countQueryBuilder->setFirstResult(null);
        $countQueryBuilder->resetDQLPart('groupBy');
        $countQueryBuilder->resetDQLPart('orderBy');

        // event to change paginator query builder
        $event = new DataGridEvent();
        $event->set('paginator', $paginator);
        $event->set('paginatorQueryBuilder', $countQueryBuilder);
        $event->set('request', $request);
        $this->dispatcher->dispatch(new OnGetPaginatorQuery($event));

        if (!$event->isDefaultPrevented()) {
            $query = $countQueryBuilder->getQuery();
            $event->set('query', $query);
        }
        $this->dispatcher->dispatch(new AfterGetPaginatorQuery($event));

        // hack : recover query from the event so the developper can build a new query
        // from the paginatorQueryBuilder in the listener and reinject it in the event.
        $query = $event->get('query');

        try {
            $totalCount = $query->getSingleScalarResult();
            $paginator->setTotalItemCount((int) $totalCount);
        } catch (\Doctrine\ORM\NoResultException $e) {
            $paginator->setTotalItemCount(0);
        }

        // calculate total page count
        if ($paginator->getTotalItemCount() == 0) {
            $paginator->setTotalPageCount(0);
        } else {
            $paginator->setTotalPageCount(
                (int) ((($paginator->getTotalItemCount() - 1) / $paginatorConfig->getItemCountInPage()) + 1)
            );
        }

        // change current page if needed
        if ($paginator->getCurrentPage() > $paginator->getTotalPageCount()) {
            $paginator->setCurrentPage(1);
        }

        // calculate nbPageLeft and nbPageRight
        $nbPageLeft = (int) ($paginatorConfig->getVisiblePageCountInPaginator() / 2);
        $nbPageRight = $paginatorConfig->getVisiblePageCountInPaginator() - 1 - $nbPageLeft;

        // calculate lastPage to display
        $maxPage = min($paginator->getTotalPageCount(), $paginator->getCurrentPage() + $nbPageRight);
        // adapt minPage and maxPage
        $minPage = max(1, $maxPage - ($paginatorConfig->getVisiblePageCountInPaginator() - 1));
        $maxPage = min($paginator->getTotalPageCount(), $minPage + ($paginatorConfig->getVisiblePageCountInPaginator() - 1));

        $paginator->setMinPage($minPage);
        $paginator->setMaxPage($maxPage);

        // calculate previousButton
        if ($paginator->getCurrentPage() == 1) {
            $paginator->setPreviousButtonPage(null);
        } else {
            $paginator->setPreviousButtonPage($paginator->getCurrentPage() - 1);
        }
        // calculate nextButton
        if ($paginator->getCurrentPage() == $paginator->getTotalPageCount()) {
            $paginator->setNextButtonPage(null);
        } else {
            $paginator->setNextButtonPage($paginator->getCurrentPage() + 1);
        }

        return $paginator;
    }
}
