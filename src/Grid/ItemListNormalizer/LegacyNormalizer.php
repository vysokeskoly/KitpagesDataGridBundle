<?php declare(strict_types=1);
/**
 * Created by levan on 03/07/14.
 */

namespace Kitpages\DataGridBundle\Grid\ItemListNormalizer;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class LegacyNormalizer implements NormalizerInterface
{
    public function normalize(Query $query, QueryBuilder $queryBuilder): array
    {
        // execute the query
        $itemList = $query->getArrayResult();

        // normalize result (for request of type $queryBuilder->select("item, bp, item.id * 3 as titi"); )
        $normalizedItemList = [];
        foreach ($itemList as $item) {
            $normalizedItem = [];
            foreach ($item as $key => $val) {
                // hack : is_array is added according to this issue : https://github.com/kitpages/KitpagesDataGridBundle/issues/18
                // can't reproduce this error...
                if (is_int($key) && is_array($val)) {
                    foreach ($val as $newKey => $newVal) {
                        $normalizedItem[$newKey] = $newVal;
                    }
                } else {
                    $normalizedItem[$key] = $val;
                }
            }
            $normalizedItemList[] = $normalizedItem;
        }

        return $normalizedItemList;
    }
}
