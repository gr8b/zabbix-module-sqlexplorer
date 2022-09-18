<?php

namespace Modules\SqlExplorer\Actions;

use CProfile;
use CControllerResponseData;

class StoredSql extends BaseAction {

	protected function checkInput() {
		$fields = [
			'index' => 'int32|ge 0',
			'query'	=> 'string'
		];

		return $this->validateInput($fields);
	}

	public function doAction() {
		$index = $this->getInput('index', 0);
		$queries = CProfile::get('module-sqlexplorer-queries', []);

		foreach ($queries as &$query) {
			$query = json_decode($query, true);
		}
		unset($query);

		$data = [
			'queries' => $queries
		];

		if ($this->request_method === self::POST) {
			$queries[$index - 1] = $this->getInput('query', '');
			$queries = array_map('json_encode', $queries);
			CProfile::updateArray('module-sqlexplorer-queries', $queries, PROFILE_TYPE_STR);
			$data['updated'] = $index;
		}
		else {
			array_unshift($data['queries'], ['title' => '', 'query' => '']);
		}

		$this->setResponse(new CControllerResponseData(['main_block' => json_encode($data)]));
	}
}
