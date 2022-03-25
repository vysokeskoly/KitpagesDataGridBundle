<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\TestEntities;

use Doctrine\Common\Collections\ArrayCollection;

class NodeAssoc
{
    protected int $id;
    protected string $name;
    protected ArrayCollection $nodeList;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addNodeList(Node $node): self
    {
        $this->nodeList[] = $node;

        return $this;
    }

    public function removeNodeList(Node $node): self
    {
        $this->nodeList->removeElement($node);

        return $this;
    }

    public function getNodeList(): ArrayCollection
    {
        return $this->nodeList;
    }
}
