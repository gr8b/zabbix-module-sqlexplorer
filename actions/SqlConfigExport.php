<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;

class SqlConfigExport extends BaseAction {

    public function checkInput() {
        return true;
    }

    public function doAction() {
        $data = [
            'mime_type' => 'text/plain',
            'page' => ['file' => 'queries.txt'],
            'main_block' => $this->getExportContent()
        ];

        $this->setResponse(new CControllerResponseData($data));
    }

    protected function getExportContent(): string {
        $output = [];

        foreach (Profile::getQueries() as $query) {
            $output[] = implode("\n", [$query['title'], trim($query['query'], " \r\n\t"), '']);
        };

        return implode("\n\n", $output);
    }
}
