<?php

declare(strict_types=1);

namespace ORMBundle\Helper;

class UniqueHelper
{
    /**
     * Get array keys of non unique values (objects or arrays)
     * Comparison through '==' operator.
     *
     * @param array<object|array> $data
     */
    public static function getNonUniqueValuesKeys(array $data): array
    {
        $uniqueValues = [];
        $nonUniqueKeys = [];

        foreach ($data as $key => $val) {
            if (0 === count($uniqueValues)) {
                $uniqueValues[] = $val;

                continue;
            }

            foreach ($uniqueValues as $uniqueValue) {
                if ($uniqueValue == $val) {
                    $nonUniqueKeys[] = $key;
                } else {
                    $uniqueValues[] = $val;
                }
            }
        }

        return $nonUniqueKeys;
    }
}
