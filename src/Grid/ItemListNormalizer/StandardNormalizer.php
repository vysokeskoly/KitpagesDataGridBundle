<?php declare(strict_types=1);
/**
 * Created by levan on 03/07/14.
 */

namespace Kitpages\DataGridBundle\Grid\ItemListNormalizer;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class StandardNormalizer implements NormalizerInterface
{
    public function normalize(Query $query, QueryBuilder $queryBuilder, ?string $hydratorClass = null): array
    {
        if (!$hydratorClass || !\class_exists($hydratorClass)) {
            throw new \InvalidArgumentException(sprintf('HydratorClass "%s" was not found.', $hydratorClass));
        }

        /*
         * Add custom hydrator
         */
        $emConfig = $queryBuilder->getEntityManager()->getConfiguration();
        /** @var class-string<\Doctrine\ORM\Internal\Hydration\AbstractHydrator> $hydratorClass */
        $hydrator = new \ReflectionClass($hydratorClass);
        $hydratorName = $hydrator->getShortName();
        $emConfig->addCustomHydrationMode($hydratorName, $hydratorClass);

        return $query->getResult($hydratorName);
    }
}
