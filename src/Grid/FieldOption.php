<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

enum FieldOption: string
{
    case Label = 'label';
    case Sortable = 'sortable';
    case Filterable = 'filterable';
    case Visible = 'visible';
    case FormatValueCallback = 'formatValueCallback';
    case AutoEscape = 'autoEscape';
    case Translatable = 'translatable';
    case Category = 'category';
    case NullIfNotExists = 'nullIfNotExists';
    case DataList = 'dataList';
    case UniqueId = 'uniqueId';
}
