<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle;

use Kitpages\DataGridBundle\Event\AfterApplyFilter;
use Kitpages\DataGridBundle\Event\AfterApplySelector;
use Kitpages\DataGridBundle\Event\AfterApplySort;
use Kitpages\DataGridBundle\Event\AfterDisplayGridValueConversion;
use Kitpages\DataGridBundle\Event\AfterGetGridQuery;
use Kitpages\DataGridBundle\Event\AfterGetPaginatorQuery;
use Kitpages\DataGridBundle\Event\OnApplyFilter;
use Kitpages\DataGridBundle\Event\OnApplySelector;
use Kitpages\DataGridBundle\Event\OnApplySort;
use Kitpages\DataGridBundle\Event\OnDisplayGridValueConversion;
use Kitpages\DataGridBundle\Event\OnGetGridQuery;
use Kitpages\DataGridBundle\Event\OnGetPaginatorQuery;

final class KitpagesDataGridEvents
{
    public const ON_GET_GRID_QUERY = OnGetGridQuery::class;
    public const AFTER_GET_GRID_QUERY = AfterGetGridQuery::class;

    public const ON_GET_PAGINATOR_QUERY = OnGetPaginatorQuery::class;
    public const AFTER_GET_PAGINATOR_QUERY = AfterGetPaginatorQuery::class;

    public const ON_APPLY_FILTER = OnApplyFilter::class;
    public const AFTER_APPLY_FILTER = AfterApplyFilter::class;

    public const ON_APPLY_SELECTOR = OnApplySelector::class;
    public const AFTER_APPLY_SELECTOR = AfterApplySelector::class;

    public const ON_APPLY_SORT = OnApplySort::class;
    public const AFTER_APPLY_SORT = AfterApplySort::class;

    public const ON_DISPLAY_GRID_VALUE_CONVERSION = OnDisplayGridValueConversion::class;
    public const AFTER_DISPLAY_GRID_VALUE_CONVERSION = AfterDisplayGridValueConversion::class;
}
