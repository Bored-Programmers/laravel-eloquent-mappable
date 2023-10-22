<?php declare(strict_types=1);

if (!function_exists('array_key_rename')) {
    function array_key_rename(array $array, $oldKey, $newKey): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[$key === $oldKey ? $newKey : $key] = $value;
        }

        return $newArray;
    }
}
