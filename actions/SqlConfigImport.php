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
					'error' => [
						'messages' => array_column(get_and_clear_messages(), 'message')
					]
				])])
			);
        }

        return $ret;
    }

    public function doAction() {
        // $output['error'] = [
        //     'title' => _('Cannot import queries'),
        //     'messages' => array_column(get_and_clear_messages(), 'message')
        // ];
        $queries = $this->importQueries(file($_FILES['queries']['tmp_name']));
        $output = [
            'queries' => $queries
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
