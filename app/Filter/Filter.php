<?php

declare(strict_types=1);

namespace App\Filter;


use App\Constants\Constants;

class Filter
{

    /**
     * Filter loader.
     *
     * @param $filter
     * @return mixed
     */
    public static function loader($filter)
    {
        return (method_exists(__CLASS__, $filter) ? call_user_func_array([__CLASS__, $filter], array_slice(func_get_args(), 1)) : null);
    }

    /**
     * Return data from related dictionary table.
     *
     * @param       $row
     * @param       $keyword
     * @param       $column
     * @param null  $languageId
     * @param null  $domainId
     * @param false $dictionary
     *
     * @return string
     */
    public static function dictionaryData($row, $keyword, $column, $languageId = null, $dictionary = false): string
    {
        if (is_null($row)) {
            return ' ';
        }

        if ($languageId) {
            $dictionary = $row->related($keyword . 'dictionary', $keyword . '_id')
                ->where([
                    'language_id' => $languageId,
                ])
                ->limit(1)
                ->fetch();
        }

        if ($dictionary) {
            if (is_null($dictionary->{$column})) {
                return ' ';
            }

            return $dictionary->{$column};
        } else {
            $dictionary = $row->related($keyword . 'dictionary', $keyword . '_id')
                ->where([
                    'language_id' => Constants::DEFAULT_LOCALE_ID,
                ])
                ->limit(1)
                ->fetch();

            if ($dictionary) {
                return $dictionary->{$column};
            }
        }

        return 'missing translation';
    }

}
