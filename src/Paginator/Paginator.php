<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Paginator;

use Kitpages\DataGridBundle\Tool\UrlTool;

class Paginator
{
    protected ?int $totalPageCount = null;
    protected ?int $minPage = null;
    protected ?int $maxPage = null;
    protected ?int $nextButtonPage = null;
    protected ?int $previousButtonPage = null;
    protected int $totalItemCount = 0;
    protected int $currentPage = 1;
    protected ?UrlTool $urlTool = null;
    protected ?PaginatorConfig $paginatorConfig = null;
    protected ?string $requestUri = null;

    public function getPageRange(): array
    {
        $tab = [];
        for ($i = $this->minPage; $i <= $this->maxPage; $i++) {
            $tab[] = $i;
        }

        return $tab;
    }

    /** @param mixed $val */
    public function getUrl(string $key, $val): string
    {
        return $this->urlTool->changeRequestQueryString(
            $this->requestUri,
            $this->paginatorConfig->getRequestQueryName($key),
            $val
        );
    }

    public function setMaxPage(?int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

    public function getMaxPage(): ?int
    {
        return $this->maxPage;
    }

    public function setMinPage(?int $minPage): void
    {
        $this->minPage = $minPage;
    }

    public function getMinPage(): ?int
    {
        return $this->minPage;
    }

    public function setNextButtonPage(?int $nextButtonPage): void
    {
        $this->nextButtonPage = $nextButtonPage;
    }

    public function getNextButtonPage(): ?int
    {
        return $this->nextButtonPage;
    }

    public function setPreviousButtonPage(?int $previousButtonPage): void
    {
        $this->previousButtonPage = $previousButtonPage;
    }

    public function getPreviousButtonPage(): ?int
    {
        return $this->previousButtonPage;
    }

    public function setTotalItemCount(?int $totalItemCount): void
    {
        $this->totalItemCount = $totalItemCount;
    }

    public function getTotalItemCount(): ?int
    {
        return $this->totalItemCount;
    }

    public function setTotalPageCount(?int $totalPageCount): void
    {
        $this->totalPageCount = $totalPageCount;
    }

    public function getTotalPageCount(): ?int
    {
        return $this->totalPageCount;
    }

    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setUrlTool(UrlTool $urlTool): void
    {
        $this->urlTool = $urlTool;
    }

    public function getUrlTool(): UrlTool
    {
        return $this->urlTool;
    }

    public function setPaginatorConfig(PaginatorConfig $paginatorConfig): void
    {
        $this->paginatorConfig = $paginatorConfig;
    }

    public function getPaginatorConfig(): PaginatorConfig
    {
        return $this->paginatorConfig;
    }

    public function setRequestUri(string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }
}
