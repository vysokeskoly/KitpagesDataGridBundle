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

    public function __construct(protected PaginatorConfig $paginatorConfig, protected UrlTool $urlTool, protected string $requestUri)
    {
    }

    public function getPageRange(): array
    {
        $tab = [];
        for ($i = $this->minPage; $i <= $this->maxPage; $i++) {
            $tab[] = $i;
        }

        return $tab;
    }

    public function getUrl(string $key, mixed $val): string
    {
        return $this->urlTool->changeRequestQueryString(
            $this->requestUri,
            $this->paginatorConfig->getRequestQueryName($key),
            $val,
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

    public function setTotalItemCount(int $totalItemCount): void
    {
        $this->totalItemCount = $totalItemCount;
    }

    public function getTotalItemCount(): int
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

    public function getUrlTool(): UrlTool
    {
        return $this->urlTool;
    }

    public function getPaginatorConfig(): PaginatorConfig
    {
        return $this->paginatorConfig;
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }
}
