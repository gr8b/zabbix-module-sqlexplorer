<?php

namespace Modules\SqlExplorer\Helpers;

use DB;
use CProfile;

class ProfileHelper {

    const QUERIES_PROFILE_KEY = 'module-sqlexplorer-queries';

    /**
     * Get stored SQL queries from profile.
     *
     * @return array of queries.
     *              []['title']  stored query name.
     *              []['query']  stored query SQL.
     */
    public static function getQueries(): array {
        $queries = [];
        $query = '';

        foreach (CProfile::getArray(static::QUERIES_PROFILE_KEY, []) as $query_chunk) {
            $query .= $query_chunk;
            $decoded_query = json_decode($query, true);

            if (is_array($decoded_query)) {
                $queries[] = $decoded_query;
                $query = '';
            }
        }

        return $queries;
    }

    /**
     * Store queries in profile table.
     */
    public static function updateQueries(array $queries): void {
        if (!$queries) {
            CProfile::delete(static::QUERIES_PROFILE_KEY);

            return;
        }

        $column_size = DB::getFieldLength('profiles', 'value_str');
        $encoded_queries = [];

        foreach ($queries as $query) {
            $value_str = json_encode($query, JSON_UNESCAPED_UNICODE);

            if (strlen($value_str) <= $column_size) {
                $encoded_queries[] = $value_str;

                continue;
            }

            while (strlen($value_str) > $column_size || $value_str !== '') {
                $encoded_queries[] = substr($value_str, 0, $column_size);
                $value_str = (string) substr($value_str, $column_size);
            }
        }

        CProfile::updateArray(static::QUERIES_PROFILE_KEY, $encoded_queries, PROFILE_TYPE_STR);
    }
}
