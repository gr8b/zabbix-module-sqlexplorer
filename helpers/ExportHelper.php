<?php

namespace Modules\SqlExplorer\Helpers;


class ExportHelper {

    /**
     * Convert array of queries to array of lines as stored in .txt export file.
     *
     * @param array  $queries  Array of stored queries.
     * @param string $queries[][title]  Query title.
     * @param string $queries[][query]  Query body.
     *
     * @return array of string to store in .txt file, each line without line end characters.
     */
    public static function toText(array $queries): array {
        $output = [];

        foreach ($queries as $query) {
            $output[] = '-- '.$query['title'];
            $output[] = $query['query'];
            $output[] = '--';
            $output[] = '';
        }

        return $output;
    }
}
