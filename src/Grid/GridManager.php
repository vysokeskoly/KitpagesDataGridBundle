<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Kitpages\DataGridBundle\Event\AfterApplyFilter;
use Kitpages\DataGridBundle\Event\AfterApplySelector;
use Kitpages\DataGridBundle\Event\AfterApplySort;
use Kitpages\DataGridBundle\Event\AfterGetGridQuery;
use Kitpages\DataGridBundle\Event\DataGridEvent;
use Kitpages\DataGridBundle\Event\OnApplyFilter;
use Kitpages\DataGridBundle\Event\OnApplySelector;
use Kitpages\DataGridBundle\Event\OnApplySort;
use Kitpages\DataGridBundle\Event\OnGetGridQuery;
use Kitpages\DataGridBundle\Grid\ItemListNormalizer\NormalizerInterface;
use Kitpages\DataGridBundle\Grid\ItemListNormalizer\StandardNormalizer;
use Kitpages\DataGridBundle\Paginator\PaginatorConfig;
use Kitpages\DataGridBundle\Paginator\PaginatorManager;
use Kitpages\DataGridBundle\Tool\UrlTool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class GridManager
{
    protected string $hydratorClass;

    public function __construct(
        protected EventDispatcherInterface $dispatcher,
        protected PaginatorManager $paginatorManager,
        protected NormalizerInterface $itemListNormalizer,
        mixed $hydratorClass,
    ) {
        $this->hydratorClass = $hydratorClass;
    }

    ////
    // grid methods
    ////

    /**
     * @param Grid $grid : the user can give a instance of Grid (or a subclass
     * of grid) if he wants the grid object to be manually initialized before
     * the getGrid call.
     */
    public function getGrid(
        GridConfig $gridConfig,
        Request $request,
        Grid $grid = null,
    ): Grid {
        $queryBuilder = $gridConfig->getQueryBuilder();

        // create grid objet
        if ($grid === null) {
            $grid = new Grid(
                new UrlTool(),
                $request->getRequestUri(),
                $this->dispatcher,
                $gridConfig,
            );
        } else {
            $grid->setGridConfig($gridConfig);
            $grid->setUrlTool(new UrlTool());
            $grid->setRequestUri($request->getRequestUri());
            $grid->setDispatcher($this->dispatcher);
        }

        $grid->setRequestCurrentRoute($request->attributes->get('_route'));
        $grid->setRequestCurrentRouteParams($request->attributes->get('_route_params') ?? []);

        // create base request
        $gridQueryBuilder = clone ($queryBuilder);

        // Apply filters
        $filter = $request->query->get($grid->getFilterFormName(), '');
        $this->applyFilter($gridQueryBuilder, $grid, $filter);

        // Apply selector
        $selectorField = (string) $request->query->get($grid->getSelectorFieldFormName(), '');
        $selectorValue = $request->query->get($grid->getSelectorValueFormName(), '');
        $this->applySelector($gridQueryBuilder, $grid, $selectorField, $selectorValue);

        // Apply sorting
        $sortField = (string) $request->query->get($grid->getSortFieldFormName(), '');
        $sortOrder = (string) $request->query->get($grid->getSortOrderFormName(), '');
        $this->applySort($gridQueryBuilder, $grid, $sortField, $sortOrder);

        // build paginator
        $paginatorConfig = $gridConfig->getPaginatorConfig();
        if ($paginatorConfig === null) {
            $paginatorConfig = new PaginatorConfig($gridQueryBuilder, $gridConfig->getCountFieldName());
            $paginatorConfig->setName($gridConfig->getName());
        } else {
            $paginatorConfig->setQueryBuilder($gridQueryBuilder);
        }

        $paginator = $this->paginatorManager
            ->setConfigurePaginator($gridConfig->getConfigurePaginator())
            ->getPaginator($paginatorConfig, $request);
        $grid->setPaginator($paginator);

        // calculate limits
        $gridQueryBuilder->setMaxResults($paginator->getPaginatorConfig()->getItemCountInPage());
        $gridQueryBuilder->setFirstResult(($paginator->getCurrentPage() - 1) * $paginator->getPaginatorConfig()->getItemCountInPage());

        // send event for changing grid query builder
        $event = new DataGridEvent();
        $event->set('grid', $grid);
        $event->set('gridQueryBuilder', $gridQueryBuilder);
        $event->set('request', $request);
        $this->dispatcher->dispatch(new OnGetGridQuery($event));

        if (!$event->isDefaultPrevented()) {
            // execute request
            $query = $gridQueryBuilder->getQuery();
            $event->set('query', $query);
        }

        $this->dispatcher->dispatch(new AfterGetGridQuery($event));

        // hack : recover query from the event so the developper can build a new grid
        // from the gridQueryBuilder in the listener and reinject it in the event.
        $normalizedItemList = $this->itemListNormalizer instanceof StandardNormalizer
            ? $this->itemListNormalizer->normalize(
                $event->get('query'),
                $event->get('gridQueryBuilder'),
                $this->hydratorClass,
            )
            : $this->itemListNormalizer->normalize(
                $event->get('query'),
                $event->get('gridQueryBuilder'),
            );

        // end normalization
        $grid->setItemList($normalizedItemList);

        return $grid;
    }

    protected function applyFilter(QueryBuilder $queryBuilder, Grid $grid, mixed $filter): void
    {
        if (!$filter) {
            return;
        }
        $event = new DataGridEvent();
        $event->set('grid', $grid);
        $event->set('gridQueryBuilder', $queryBuilder);
        $event->set('filter', $filter);
        $this->dispatcher->dispatch(new OnApplyFilter($event));

        if (!$event->isDefaultPrevented()) {
            $fieldList = $grid->getGridConfig()->getFieldList();
            $filterRequestList = [];
            foreach ($fieldList as $field) {
                if ($field->getFilterable()) {
                    $filterRequestList[] = $queryBuilder->expr()->like(sprintf('lower(%s)', $field->getFieldName()), 'lower(:filter)');
                }
            }
            if (count($filterRequestList) > 0) {
                $reflectionMethod = new \ReflectionMethod($queryBuilder->expr(), 'orx');
                $queryBuilder->andWhere($reflectionMethod->invokeArgs($queryBuilder->expr(), $filterRequestList));
                $queryBuilder->setParameter('filter', '%' . $filter . '%');
            }
            $grid->setFilterValue($filter);
        }
        $this->dispatcher->dispatch(new AfterApplyFilter($event));
    }

    protected function applySelector(
        QueryBuilder $queryBuilder,
        Grid $grid,
        string $selectorField,
        mixed $selectorValue,
    ): void {
        if (empty($selectorField)) {
            return;
        }
        $event = new DataGridEvent();
        $event->set('grid', $grid);
        $event->set('gridQueryBuilder', $queryBuilder);
        $event->set('selectorField', $selectorField);
        $event->set('selectorValue', $selectorValue);
        $this->dispatcher->dispatch(new OnApplySelector($event));

        if (!$event->isDefaultPrevented()) {
            $queryBuilder->andWhere($selectorField . ' = :selectorValue');
            $queryBuilder->setParameter('selectorValue', $selectorValue);

            $grid->setSelectorField($selectorField);
            $grid->setSelectorValue($selectorValue);
        }
        $this->dispatcher->dispatch(new AfterApplySelector($event));
    }

    protected function applySort(
        QueryBuilder $gridQueryBuilder,
        Grid $grid,
        string $sortField,
        string $sortOrder,
    ): void {
        if (empty($sortField)) {
            return;
        }
        $event = new DataGridEvent();
        $event->set('grid', $grid);
        $event->set('gridQueryBuilder', $gridQueryBuilder);
        $event->set('sortField', $sortField);
        $event->set('sortOrder', $sortOrder);
        $this->dispatcher->dispatch(new OnApplySort($event));

        if (!$event->isDefaultPrevented()) {
            $sortFieldObject = null;
            $fieldList = $grid->getGridConfig()->getFieldList();
            foreach ($fieldList as $field) {
                if ($field->getFieldName() === $sortField) {
                    if ($field->getSortable() === true) {
                        $sortFieldObject = $field;
                        break;
                    }
                }
            }
            if (!$sortFieldObject) {
                return;
            }
            if ($sortOrder !== 'DESC') {
                $sortOrder = 'ASC';
            }
            $gridQueryBuilder->orderBy($sortField, $sortOrder);
            $grid->setSortField($sortField);
            $grid->setSortOrder($sortOrder);
        }

        $this->dispatcher->dispatch(new AfterApplySort($event));
    }
}
