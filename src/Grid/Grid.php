<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Grid;

use Kitpages\DataGridBundle\Event\AfterDisplayGridValueConversion;
use Kitpages\DataGridBundle\Event\DataGridEvent;
use Kitpages\DataGridBundle\Event\OnDisplayGridValueConversion;
use Kitpages\DataGridBundle\Paginator\Paginator;
use Kitpages\DataGridBundle\Tool\UrlTool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Grid
{
    protected Paginator $paginator;
    protected GridConfig $gridConfig;
    protected array $itemList = [];
    protected UrlTool $urlTool;
    protected string $requestUri;
    protected ?string $filterValue = null;
    protected ?string $sortField = null;
    protected string $sortOrder = 'ASC';
    protected bool $debugMode = false;
    protected EventDispatcherInterface $dispatcher;
    protected ?string $selectorField = null;
    protected ?string $selectorValue = null;
    protected ?string $requestCurrentRoute = null;
    protected array $requestCurrentRouteParams = [];

    public function __construct(
        UrlTool $urlTool,
        string $requestUri,
        EventDispatcherInterface $dispatcher,
        GridConfig $gridConfig
    ) {
        $this->urlTool = $urlTool;
        $this->requestUri = $requestUri;
        $this->dispatcher = $dispatcher;
        $this->gridConfig = $gridConfig;
    }

    public function getSelectorUrl(string $selectorField, string $selectorValue): string
    {
        if (!$this->isSelectorSelected($selectorField, $selectorValue)) {
            $uri = $this->urlTool->changeRequestQueryString(
                $this->requestUri,
                [
                    $this->getSelectorFieldFormName() => $selectorField,
                    $this->getSelectorValueFormName() => $selectorValue,
                ]
            );
        } else {
            $uri = $this->urlTool->changeRequestQueryString(
                $this->requestUri,
                [
                    $this->getSelectorFieldFormName() => '',
                    $this->getSelectorValueFormName() => '',
                ]
            );
        }

        return $uri;
    }

    public function getSortUrl(string $fieldName): string
    {
        $uri = $this->urlTool->changeRequestQueryString(
            $this->requestUri,
            $this->getSortFieldFormName(),
            $fieldName
        );
        if ($fieldName == $this->getSortField()) {
            $order = ($this->getSortOrder() === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $order = 'ASC';
        }

        return $this->urlTool->changeRequestQueryString(
            $uri,
            $this->getSortOrderFormName(),
            $order
        );
    }

    public function getSortCssClass(string $fieldName): string
    {
        $css = '';
        if ($fieldName == $this->getSortField()) {
            $css .= ' kit-grid-sort ';
            $css .= ' kit-grid-sort-' . mb_strtolower($this->getSortOrder()) . ' ';
        }

        return $css;
    }

    /** @return mixed */
    public function displayGridValue(array $row, Field $field)
    {
        $value = null;
        $fieldName = $field->getFieldName();
        if (array_key_exists($fieldName, $row)) {
            $value = $row[$fieldName];
        }

        // real treatment
        if (\is_callable($field->getFormatValueCallback())) {
            $value = call_user_func($field->getFormatValueCallback(), $value, $row);
        }

        // send event for changing grid query builder
        $event = new DataGridEvent();
        $event->set('value', $value);
        $event->set('row', $row);
        $event->set('field', $field);

        /** @var DataGridEvent $event */
        $event = $this->dispatcher->dispatch(new OnDisplayGridValueConversion($event));

        if (!$event->isDefaultPrevented()) {
            $value = $event->get('value');
            if ($value instanceof \DateTimeInterface) {
                $returnValue = $value->format('Y-m-d H:i:s');
            } else {
                $returnValue = $value;
            }
            $event->set('returnValue', $returnValue);
        }

        /** @var DataGridEvent $event */
        $event = $this->dispatcher->dispatch(new AfterDisplayGridValueConversion($event));
        $returnValue = $event->get('returnValue');

        if ($field->getAutoEscape() && $returnValue !== null && is_string($returnValue)) {
            $returnValue = htmlspecialchars($returnValue);
        }

        return $returnValue;
    }

    public function getFilterFormName(): string
    {
        return 'kitdg_grid_' . $this->getGridConfig()->getName() . '_filter';
    }

    public function getSortFieldFormName(): string
    {
        return 'kitdg_grid_' . $this->getGridConfig()->getName() . '_sort_field';
    }

    public function getSortOrderFormName(): string
    {
        return 'kitdg_grid_' . $this->getGridConfig()->getName() . '_sort_order';
    }

    public function getSelectorCssSelected(string $selectorField, string $selectorValue): ?string
    {
        if ($this->isSelectorSelected($selectorField, $selectorValue)) {
            return 'kit-grid-selector-selected';
        }

        return null;
    }

    public function isSelectorSelected(string $selectorField, string $selectorValue): bool
    {
        return $this->getSelectorField() === $selectorField
            && $this->getSelectorValue() === $selectorValue;
    }

    public function getSelectorFieldFormName(): string
    {
        return 'kitdg_grid_' . $this->getGridConfig()->getName() . '_selector_field';
    }

    public function getSelectorValueFormName(): string
    {
        return 'kitdg_grid_' . $this->getGridConfig()->getName() . '_selector_value';
    }

    public function getGridCssName(): string
    {
        return 'kit-grid-' . $this->getGridConfig()->getName();
    }

    public function setGridConfig(GridConfig $gridConfig): void
    {
        $this->gridConfig = $gridConfig;
    }

    public function getGridConfig(): GridConfig
    {
        return $this->gridConfig;
    }

    public function setItemList(array $itemList): void
    {
        $this->itemList = $itemList;
    }

    public function getItemList(): array
    {
        return $this->itemList;
    }

    public function dump(bool $escape = true): string
    {
        $content = print_r($this->itemList, true);
        if ($escape) {
            $content = htmlspecialchars($content);
        }

        $html = '<pre class="kit-grid-debug">';
        $html .= $content;
        $html .= '</pre>';

        return $html;
    }

    public function setPaginator(Paginator $paginator): void
    {
        $this->paginator = $paginator;
    }

    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    public function setUrlTool(UrlTool $urlTool): void
    {
        $this->urlTool = $urlTool;
    }

    public function getUrlTool(): UrlTool
    {
        return $this->urlTool;
    }

    public function setRequestUri(string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getRequestCurrentRoute(): ?string
    {
        return $this->requestCurrentRoute;
    }

    public function setRequestCurrentRoute(?string $requestCurrentRoute): void
    {
        $this->requestCurrentRoute = $requestCurrentRoute;
    }

    public function getRequestCurrentRouteParams(): array
    {
        return $this->requestCurrentRouteParams;
    }

    public function setRequestCurrentRouteParams(array $requestCurrentRouteParams): void
    {
        $this->requestCurrentRouteParams = $requestCurrentRouteParams;
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    public function setFilterValue(?string $filterValue): void
    {
        $this->filterValue = $filterValue;
    }

    public function getFilterValue(): ?string
    {
        return $this->filterValue;
    }

    public function setSortField(?string $sortField): void
    {
        $this->sortField = $sortField;
    }

    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    public function setSortOrder(string $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    public function setSelectorField(?string $selectorField): void
    {
        $this->selectorField = $selectorField;
    }

    public function getSelectorField(): ?string
    {
        return $this->selectorField;
    }

    public function setSelectorValue(?string $selectorValue): void
    {
        $this->selectorValue = $selectorValue;
    }

    public function getSelectorValue(): ?string
    {
        return $this->selectorValue;
    }

    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    public function getDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function getDispatcher(): \Symfony\Component\EventDispatcher\EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
