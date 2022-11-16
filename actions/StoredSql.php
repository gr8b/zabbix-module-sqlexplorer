<?php

namespace Modules\SqlExplorer\Actions;

use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;
use CControllerResponseData;

class StoredSql extends BaseAction {

    protected $post_content_type = self::TYPE_JSON;

    protected function checkInput() {
        if ($this->request_method !== self::POST) {
            return true;
        }

        $fields = [
            'queries'	=> 'required|array'
        ];

        return $this->validateInput($fields);
    }

    public function doAction() {
        $queries = [];

        if ($this->request_method === self::POST) {
            $queries = array_values($this->getInput('queries', []));
            unset($queries[0]);
            Profile::updateQueries($queries);
        }

        $queries = Profile::getQueries();
        array_unshift($queries, ['title' => '', 'query' => "\n\n\n"]);
        $this->setResponse(new CControllerResponseData(['main_block' => json_encode(['queries' => $queries])]));
    }
}
