<?php

namespace Modules\SqlExplorer\Actions;

use CControllerResponseData;
use Modules\SqlExplorer\Helpers\ProfileHelper as Profile;

class SqlConfig extends BaseAction {

    public function checkInput() {
        $fields = [
            'refresh' => 'in 1',
            'text_to_url' => 'in 0,1',
            'tab_url' => 'in 0,1',
            'autoexec' => 'in 0,1',
            'add_column_names' => 'in 0,1',
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
            'csrf_token' => [
                'sqlexplorer.config' => $this->getActionCsrfToken('sqlexplorer.config'),
                'sqlexplorer.config.import' => $this->getActionCsrfToken('sqlexplorer.config.import')
            ],
            'refresh' => 0,
            'errors' => '',
            'params' => [],
            'user' => [
                'debug_mode' => $this->getDebugMode()
            ],
            'tab_url' => Profile::getPersonal(Profile::KEY_TAB_URL, 0),
            'text_to_url' => Profile::getPersonal(Profile::KEY_TEXT_TO_URL, 1),
            'autoexec' => Profile::getPersonal(Profile::KEY_AUTOEXEC_SQL, 1),
            'add_column_names' => Profile::getPersonal(Profile::KEY_SHOW_HEADER, 1),
            'stopwords' => Profile::getPersonal(Profile::KEY_STOP_WORDS, Profile::DEFAULT_STOP_WORDS)
        ];
        $this->getInputs($data, ['refresh', 'tab_url', 'text_to_url', 'autoexec', 'add_column_names', 'stopwords']);

        if ($this->hasInput('refresh')) {
            Profile::updatePersonal(Profile::KEY_TAB_URL, $data['tab_url']);
            Profile::updatePersonal(Profile::KEY_TEXT_TO_URL, $data['text_to_url']);
            Profile::updatePersonal(Profile::KEY_AUTOEXEC_SQL, $data['autoexec']);
            Profile::updatePersonal(Profile::KEY_SHOW_HEADER, $data['add_column_names']);
            Profile::updatePersonal(Profile::KEY_STOP_WORDS, $data['stopwords']);

            $data['params'] = [
                'tab_url' => $data['tab_url'],
                'text_to_url' => $data['text_to_url'],
                'autoexec' => $data['autoexec'],
                'add_column_names' => $data['add_column_names'],
                'stopwords' => $data['stopwords']
            ];
        }

        $this->setResponse(new CControllerResponseData($data));
    }
}
