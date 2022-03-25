<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalsTwigExtension extends AbstractExtension implements GlobalsInterface
{
    protected array $gridParameterList;
    protected array $paginatorParameterList;

    public function __construct(
        array $gridParameterList,
        array $paginatorParameterList
    ) {
        $this->gridParameterList = $gridParameterList;
        $this->paginatorParameterList = $paginatorParameterList;
    }

    public function getGlobals(): array
    {
        return [
            'kitpages_data_grid' => [
                'grid' => $this->gridParameterList,
                'paginator' => $this->paginatorParameterList,
            ],
        ];
    }
}
