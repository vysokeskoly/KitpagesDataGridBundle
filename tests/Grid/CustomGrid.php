<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

/**
 * Just a subclass of Grid used to test the mecanism of giving a grid object
 * to getGrid in order to use its own subclass of Grid instead of the Grid class
 */
class CustomGrid extends Grid
{
    /** @var mixed */
    private $myCustomParamter;

    /**
     * @return mixed
     */
    public function getMyCustomParamter()
    {
        return $this->myCustomParamter;
    }

    /**
     * @param mixed $myCustomParamter
     */
    public function setMyCustomParamter($myCustomParamter): void
    {
        $this->myCustomParamter = $myCustomParamter;
    }
}
