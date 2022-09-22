<?php

namespace Modules\SqlExplorer\Actions;

use CProfile;
use CControllerResponseData;

class StoredSql extends BaseAction {

	const QUERIES_PROFILE_KEY = 'module-sqlexplorer-queries';

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

			if ($queries) {
				$value = array_values(array_map('json_encode', $queries));
				CProfile::updateArray(static::QUERIES_PROFILE_KEY, $value, PROFILE_TYPE_STR);
			}
			else {
				CProfile::delete(static::QUERIES_PROFILE_KEY);
			}
		}
		else {
			$queries = CProfile::get(static::QUERIES_PROFILE_KEY, []);

			foreach ($queries as &$query) {
				$query = json_decode($query, true);
			}
			unset($query);
		}

		array_unshift($queries, ['title' => '', 'query' => "\n\n\n"]);
		$this->setResponse(new CControllerResponseData(['main_block' => json_encode(['queries' => $queries])]));
	}
}
