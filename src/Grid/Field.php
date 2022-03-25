<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Kitpages\DataGridBundle\DataGridException;

class Field
{
    protected string $fieldName;
    protected string $label;
    protected bool $sortable = false;
    protected bool $filterable = false;
    protected bool $visible = true;
    /** @var callable */
    protected $formatValueCallback;
    protected bool $autoEscape = true;
    protected bool $translatable = false;
    protected string $category;
    protected bool $nullIfNotExists = false;
    protected array $dataList = [];
    protected string $uniqueId;
    /**
     * List of tags associated to a field. Used only by users of the bundles.
     * No influence in the internals of the bundle.
     * @var string[]
     */
    protected array $tagList = [];

    public function __construct(
        string $fieldName,
        array $optionList = [],
        array $tagList = []
    ) {
        $this->fieldName = $fieldName;
        $this->label = $fieldName;
        foreach ($optionList as $key => $val) {
            if (\in_array($key, [
                'label',
                'sortable',
                'filterable',
                'visible',
                'formatValueCallback',
                'autoEscape',
                'translatable',
                'category',
                'nullIfNotExists',
                'dataList',
                'uniqueId',
            ], true)) {
                $this->$key = $val;
            } else {
                throw new \InvalidArgumentException("key $key doesn't exist in option list");
            }
        }
        $this->tagList = $tagList;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }

    public function getFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFormatValueCallback(callable $formatValueCallback): void
    {
        $this->formatValueCallback = $formatValueCallback;
    }

    public function getFormatValueCallback(): ?callable
    {
        return $this->formatValueCallback;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    public function getSortable(): bool
    {
        return $this->sortable;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getVisible(): bool
    {
        return $this->visible;
    }

    public function setAutoEscape(bool $autoEscape): void
    {
        $this->autoEscape = $autoEscape;
    }

    public function getAutoEscape(): bool
    {
        return $this->autoEscape;
    }

    public function setTranslatable(bool $translatable): void
    {
        $this->translatable = $translatable;
    }

    public function getTranslatable(): bool
    {
        return $this->translatable;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setNullIfNotExists(bool $nullIfNotExists): void
    {
        $this->nullIfNotExists = $nullIfNotExists;
    }

    public function getNullIfNotExists(): bool
    {
        return $this->nullIfNotExists;
    }

    /**
     * @throws DataGridException
     */
    public function getData(string $key): array
    {
        if (!array_key_exists($key, $this->dataList)) {
            throw new DataGridException(
                "key [$key] is not defined in the data-list (should be defined in the dataList parameter in the new Field..."
            );
        }

        return $this->dataList[$key];
    }

    /**
     * @return string[]
     */
    public function getTagList(): array
    {
        return $this->tagList;
    }

    /**
     * @param string[] $tagList
     */
    public function setTagList(array $tagList): self
    {
        $this->tagList = $tagList;

        return $this;
    }

    /**
     * Returns true if the given $tag is present in the tag list of the field.
     */
    public function hasTag(string $tag): bool
    {
        return \in_array($tag, $this->tagList, true);
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }
}
