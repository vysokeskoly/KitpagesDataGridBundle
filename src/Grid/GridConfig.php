<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Doctrine\ORM\QueryBuilder;
use Kitpages\DataGridBundle\Paginator\PaginatorConfig;
use SebastianBergmann\CodeCoverage\Driver\Selector;

class GridConfig
{
    protected string $name = 'grid';
    protected ?PaginatorConfig $paginatorConfig = null;

    /**
     * @phpstan-var callable(PaginatorConfig): PaginatorConfig
     * @var callable|null
     */
    protected $configurePaginator;

    /** @var Field[] */
    protected array $fieldList = [];

    /** @var Selector[] */
    protected array $selectorList = [];

    public function __construct(protected QueryBuilder $queryBuilder, protected string $countFieldName)
    {
    }

    /**
     * @param string[] $options list of tags
     *
     * @return GridConfig Fluent interface
     */
    public function addField(Field|string $field, array $options = [], mixed $tagList = []): self
    {
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

    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getQueryBuilder(): QueryBuilder
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

    /** @phpstan-param callable(PaginatorConfig): PaginatorConfig $configurePaginator */
    public function setConfigurePaginator(callable $configurePaginator): self
    {
        if ($this->configurePaginator !== null) {
            throw new \InvalidArgumentException('Only one configure paginator callback can be set. Do all configuration in that one callback.');
        }
        $this->configurePaginator = $configurePaginator;

        return $this;
    }

    /** @phpstan-return null|callable(PaginatorConfig): PaginatorConfig */
    public function getConfigurePaginator(): ?callable
    {
        return $this->configurePaginator;
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
    public function getFieldByName(string $name): Field
    {
        foreach ($this->fieldList as $field) {
            if ($field->getFieldName() === $name) {
                return $field;
            }
        }

        throw new \InvalidArgumentException(sprintf('There is no defined field by name "%s".', $name));
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
