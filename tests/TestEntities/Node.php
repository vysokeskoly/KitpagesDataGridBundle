<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\TestEntities;

class Node
{
    protected int $id;
    protected string $user;
    protected string $content;
    protected \DateTime $createdAt;
    protected string $parentId;
    protected array $subNodeList;
    protected self $mainNode;
    protected NodeAssoc $assoc;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getParentId(): string
    {
        return $this->parentId;
    }

    public function setParentId(string $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getSubNodeList(): array
    {
        return $this->subNodeList;
    }

    public function setSubNodeList(array $subNodeList): self
    {
        $this->subNodeList = $subNodeList;

        return $this;
    }

    public function getMainNode(): self
    {
        return $this->mainNode;
    }

    public function setMainNode(self $mainNode): self
    {
        $this->mainNode = $mainNode;

        return $this;
    }

    public function getAssoc(): NodeAssoc
    {
        return $this->assoc;
    }

    public function setAssoc(NodeAssoc $assoc): self
    {
        $this->assoc = $assoc;

        return $this;
    }
}
