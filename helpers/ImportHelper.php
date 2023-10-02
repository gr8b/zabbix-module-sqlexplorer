<?php

namespace Modules\SqlExplorer\Helpers;


class ImportHelper {

    /**
     * Parse array of lines to array of stored SQL queries.
     *
     * @param array $lines  Array of strings.
     *
     * @return array of imported queries, each query is array with keys 'title' and 'query'.
     */
    public static function fromLinesArray(array $lines): array {
        $queries = [];
        $query = [];

        foreach ($lines as $line) {
            $trim_line = trim($line);

            if ($trim_line === '--' && array_key_exists('title', $query)) {
                if ($query) {
                    $queries[] = $query;
                    $query = [];
                }

                continue;
            }

            if (strncmp($trim_line, '--', 2) == 0 && !array_key_exists('title', $query)) {
                $query = [
                    'title' => trim(substr($trim_line, 2)),
                    'query' => []
                ];
            }
            else {
                $query['query'][] = rtrim($line);
            }
        }

        foreach ($queries as &$query) {
            $query['query'] = implode("\n", $query['query']);
        }
        unset($query);
        
        return $queries;
    }
}
