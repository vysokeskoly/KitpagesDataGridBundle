<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

class Item
{
    /** @var mixed */
    protected $entity;
    /** @var mixed */
    protected $row;

    /**
     * @param mixed $entity
     */
    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $row
     */
    public function setRow($row): void
    {
        $this->row = $row;
    }

    /**
     * @return mixed
     */
    public function getRow()
    {
        return $this->row;
    }
}
