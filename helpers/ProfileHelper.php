<?php

namespace Modules\SqlExplorer\Helpers;

use DB;
use CProfile;
use CWebUser;

class ProfileHelper {

    const PREFIX = 'module-sqlexplorer-';

    const KEY_QUERIES_PROFILE = 'queries';
    const KEY_TAB_URL = 'tab_url';
    const KEY_TEXT_TO_URL = 'texturl';
    const KEY_AUTOEXEC_SQL = 'autoexec';
    const KEY_SHOW_HEADER = 'header';
    const KEY_STOP_WORDS = 'stopwords';

    const DEFAULT_STOP_WORDS = 'insert delete truncate create drop';
    /**
     * Get current user profile preference
     *
     * @param string $key      Preference name.
     * @param string $default  Default value when preference not set in profile.
     */
    public static function getPersonal($key, $default = null) {
        return CProfile::get(static::PREFIX.$key, $default, CWebUser::$data['userid']);
    }

    /**
     * Update current user profile preference.
     *
     * @param string $key    Preference name.
     * @param string $value  Value string to store, can be anything with length of up to 255 characters
     */
    public static function updatePersonal($key, $value) {
        return CProfile::update(static::PREFIX.$key, $value, PROFILE_TYPE_STR, CWebUser::$data['userid']);
    }

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

        foreach (CProfile::getArray(static::PREFIX.static::KEY_QUERIES_PROFILE, []) as $query_chunk) {
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
            CProfile::delete(static::PREFIX.static::KEY_QUERIES_PROFILE);

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

        CProfile::updateArray(static::PREFIX.static::KEY_QUERIES_PROFILE, $encoded_queries, PROFILE_TYPE_STR);
    }
}
