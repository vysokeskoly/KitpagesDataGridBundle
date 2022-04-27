<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::SKIP,
        [
            ForbiddenFunctionsSniff::class => [
                'src/Grid/Grid.php',
            ],
            PhpdocToParamTypeFixer::class => [
                'src/Hydrators/DataGridHydrator.php',
            ],
            ParameterTypeHintSniff::class . '.' . ParameterTypeHintSniff::CODE_MISSING_NATIVE_TYPE_HINT => [
                'src/Hydrators/DataGridHydrator.php',
            ],
        ],
    );

    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs.php');
    $containerConfigurator->import(__DIR__ . '/vendor/lmc/coding-standard/ecs-8.1.php');
};
