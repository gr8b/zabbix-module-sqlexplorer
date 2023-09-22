<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;

class SqlConfigImport extends BaseAction {

    public function checkInput() {
        $ret = array_key_exists('queries', $_FILES) && file_exists($_FILES['queries']['tmp_name']);

        if (!$ret) {
            error('File required.');

            $this->setResponse(
				new CControllerResponseData(['main_block' => json_encode([
                    'success' => false,
                    'messages' => (string) getMessages(false)
				])])
			);
        }

        return $ret;
    }

    public function doAction() {
        $output = [];
        $file = $_FILES['queries'];
        $queries = $this->importQueries(file($file['tmp_name']));

        if (count($queries) == 0) {
            error(_s('No queries were found in file %1$s', $file['name']));
            $output['success'] = false;
        }
        else {
            // Import queries to database
            info(_s('File %1$s imported successfully, %2$s queries created.', $file['name'], count($queries)));

            $output['success'] = true;
        }

        $output += [
            // 'queries' => $queries,
            'messages' => (string) getMessages($output['success'])
        ];

        $this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
    }

    protected function importQueries(array $lines): array {
        $queries = [];
        $query = [];
        $prev_line = '';

        foreach ($lines as $line) {
            if (trim($line) === '' && trim($prev_line) === '') {
                if ($query) {
                    $queries[] = $query;
                    $query = [];
                }

                continue;
            }

            if (!array_key_exists('title', $query)) {
                $query = [
                    'title' => $line,
                    'query' => []
                ];
            }
            else {
                $query['query'][] = $line;
            }

            $prev_line = $line;
        }

        if ($query) {
            $queries[] = $query;
            $query = [];
        }

        foreach ($queries as &$query) {
            $query['query'] = trim(implode("\n", $query['query']));
        }
        unset($query);
        
        return $queries;
    }
}
