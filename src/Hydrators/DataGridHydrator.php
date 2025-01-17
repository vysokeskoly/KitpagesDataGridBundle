<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

class DataGridHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        $result = [];

        while ($data = $this->statement()->fetchAssociative()) {
            $this->hydrateRowData($data, $result);
        }

        return $result;
    }

    protected function hydrateRowData(array $data, array &$result): void
    {
        $result[] = $this->gatherScalarRowData($data);
    }

    /**
     * Processes a row of the result set.
     *
     * Used for HYDRATE_SCALAR. This is a variant of _gatherRowData() that
     * simply converts column names to field names and properly converts the
     * values according to their types. The resulting row has the same number
     * of elements as before.
     *
     * @param mixed[] $data
     *
     * @return mixed[] The processed row.
     */
    protected function gatherScalarRowData(&$data): array
    {
        $rowData = [];
        foreach ($data as $key => $value) {
            if (($cacheKeyInfo = $this->hydrateColumnInfo($key)) === null) {
                continue;
            }
            $fieldName = $cacheKeyInfo['fieldName'];
            // WARNING: BC break! We know this is the desired behavior to type convert values, but this
            // erroneous behavior exists since 2.0 and we're forced to keep compatibility.
            if (!isset($cacheKeyInfo['isScalar'])) {
                $dqlAlias = $cacheKeyInfo['dqlAlias'];
                $type = $cacheKeyInfo['type'];
                $fieldName = $dqlAlias . $this->getFieldSeparator() . $fieldName;
                $value = $type
                    ? $type->convertToPHPValue($value, $this->_platform)
                    : $value;
            }
            $rowData[$fieldName] = $value;
        }

        return $rowData;
    }

    protected function getFieldSeparator(): string
    {
        return '.';
    }
}
