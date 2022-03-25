<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid\ItemListNormalizer;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

interface NormalizerInterface
{
    public function normalize(Query $query, QueryBuilder $queryBuilder): array;
}
