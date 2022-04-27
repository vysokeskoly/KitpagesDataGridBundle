<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

/**
 * Just a subclass of Grid used to test the mecanism of giving a grid object
 * to getGrid in order to use its own subclass of Grid instead of the Grid class
 */
class CustomGrid extends Grid
{
    private mixed $myCustomParamter;

    public function getMyCustomParamter(): mixed
    {
        return $this->myCustomParamter;
    }

    public function setMyCustomParamter(mixed $myCustomParamter): void
    {
        $this->myCustomParamter = $myCustomParamter;
    }
}
