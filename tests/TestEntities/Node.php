<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\TestEntities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Node
{
    protected ?int $id = null;
    protected ?string $user = null;
    protected ?string $content = null;
    protected \DateTime $createdAt;
    protected ?int $parentId = null;
    protected Collection $subNodeList;
    protected ?self $mainNode = null;
    protected ?NodeAssoc $assoc = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->subNodeList = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setSubNodeList(Collection $subNodeList): self
    {
        $this->subNodeList = $subNodeList;

        return $this;
    }

    public function getSubNodeList(): Collection
    {
        return $this->subNodeList;
    }

    public function setMainNode(?self $mainNode): self
    {
        $this->mainNode = $mainNode;

        return $this;
    }

    public function getMainNode(): ?self
    {
        return $this->mainNode;
    }

    public function setAssoc(?NodeAssoc $assoc): self
    {
        $this->assoc = $assoc;

        return $this;
    }

    public function getAssoc(): ?NodeAssoc
    {
        return $this->assoc;
    }
}
