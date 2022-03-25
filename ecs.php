<?php

declare(strict_types=1);

use ECSPrefix20211002\Symplify\Skipper\ValueObject\Option;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SKIP,
        [
            ForbiddenFunctionsSniff::class => [
                __DIR__ . '/src/Grid/Grid.php',
            ],
            PhpdocToParamTypeFixer::class => [
                __DIR__ . '/src/Hydrators/DataGridHydrator.php',
            ],
        ]
    );

    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');
    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs-7.4.php');
};
