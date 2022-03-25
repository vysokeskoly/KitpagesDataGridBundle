<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Kitpages\DataGridBundle\Paginator\PaginatorConfig;
use SebastianBergmann\CodeCoverage\Driver\Selector;

class GridConfig
{
    protected string $name = 'grid';
    protected ?QueryBuilder $queryBuilder = null;
    protected ?PaginatorConfig $paginatorConfig = null;

    /** @var Field[] */
    protected array $fieldList = [];

    /** @var Selector[] */
    protected array $selectorList = [];

    protected ?string $countFieldName = null;

    /**
     * @param Field|string $field
     * @param string[] $options list of tags
     * @param mixed $tagList
     *
     * @return GridConfig Fluent interface
     */
    public function addField($field, array $options = [], $tagList = []): self
    {
        if (!(\is_string($field) || $field instanceof Field)) {
            throw new \InvalidArgumentException('Argument $field should be string or instance of Kitpages\DataGridBundle\Grid\Field');
        }

        if (\is_string($field)) {
            $field = new Field($field, $options, $tagList);
        }

        $this->fieldList[] = $field;

        return $this;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getQueryBuilder(): ?\Doctrine\ORM\QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setPaginatorConfig(PaginatorConfig $paginatorConfig): self
    {
        $this->paginatorConfig = $paginatorConfig;

        return $this;
    }

    public function getPaginatorConfig(): ?PaginatorConfig
    {
        return $this->paginatorConfig;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setFieldList(array $fieldList): self
    {
        $this->fieldList = $fieldList;

        return $this;
    }

    public function getFieldList(): array
    {
        return $this->fieldList;
    }

    /**
     * Returns the field corresponding to the name
     */
    public function getFieldByName(string $name): ?Field
    {
        foreach ($this->fieldList as $field) {
            if ($field->getFieldName() === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Returns a list of fields that contains the given $tag.
     *
     * @return Field[]
     */
    public function getFieldListByTag(string $tag): array
    {
        $matchingFieldList = [];
        foreach ($this->fieldList as $field) {
            if ($field->hasTag($tag)) {
                $matchingFieldList[] = $field;
            }
        }

        return $matchingFieldList;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setCountFieldName(string $countFieldName): self
    {
        $this->countFieldName = $countFieldName;

        return $this;
    }

    public function getCountFieldName(): string
    {
        return $this->countFieldName;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function addSelector(Selector $selector): self
    {
        $this->selectorList[] = $selector;

        return $this;
    }

    public function getSelectorList(): array
    {
        return $this->selectorList;
    }

    /**
     * @return GridConfig Fluent interface
     */
    public function setSelectorList(array $selectorList): self
    {
        $this->selectorList = $selectorList;

        return $this;
    }
}
