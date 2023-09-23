<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;
use Modules\SqlExplorer\Helpers\ExportHelper as Export;

class SqlConfigExport extends BaseAction {

    public function checkInput() {
        return true;
    }

    public function doAction() {
        $data = [
            'page' => ['file' => 'queries.txt'],
            'main_block' => implode("\n", Export::toText(Profile::getQueries()))
        ];

        $this->setResponse(new CControllerResponseData($data));
    }
}
