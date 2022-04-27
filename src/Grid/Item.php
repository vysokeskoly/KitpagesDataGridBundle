<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

class Item
{
    protected mixed $entity;
    protected mixed $row;

    public function setEntity(mixed $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }

    public function setRow(mixed $row): void
    {
        $this->row = $row;
    }

    public function getRow(): mixed
    {
        return $this->row;
    }
}
