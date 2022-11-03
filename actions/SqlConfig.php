<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;

class SqlConfig extends BaseAction {

    public function checkInput() {
        $fields = [
            'refresh' => 'in 1',
            'update' => 'in 1',
            'clickable_url' => 'in 1',
            'autoexecute_query' => 'in 1',
            'stopwords' => 'string'
        ];

        $ret = $this->validateInput($fields);

		if (!$ret) {
			$output = [];
			$messages = getMessages();

			if ($messages !== null) {
				$output['errors'] = $messages->toString();
			}

			$this->setResponse(
				(new CControllerResponseData(['main_block' => json_encode($output)]))->disableView()
			);
		}

		return $ret;
    }

    public function doAction() {
        $data = [
            'title' => _('Configuration'),
            'action' => $this->getAction(),
            'autoexecute_query' => 1,
            'clickable_url' => 1,
            'stopwords' => ['insert', 'delete', 'truncate', 'create', 'drop']
        ];

        $this->setResponse(new CControllerResponseData($data));
    }
}
