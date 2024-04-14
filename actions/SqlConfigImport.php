<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;
use Modules\SqlExplorer\Helpers\ImportHelper as Import;

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
        $file = $_FILES['queries'];
        $queries = Import::fromLinesArray(file($file['tmp_name']));
        $output = ['queries' => $queries];

        if (count($queries) == 0) {
            error(_s('No queries were found in file %1$s', $file['name']));

            $output += [
                'success' => false,
                'messages' => (string) getMessages(false)
            ];
        }
        else {
            Profile::updateQueries($queries);
            $output += [
                'success' => true,
                'messages' => '',
                'post_messages' => [
                    _s('File "%1$s" imported successfully.', $file['name']),
                    _n('%1$s query created.', '%1$s queries created.', count($queries))
                ]
            ];
        }

        $this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
    }
}
