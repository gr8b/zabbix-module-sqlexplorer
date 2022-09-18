<?php

namespace Modules\SqlExplorer\Actions;

use CWebUser;
use CController as Action;

abstract class BaseAction extends Action {

    const GET = 'get';
    const POST = 'post';

    /** @property \Modules\SqlExplorer\Module $module */
	public $module;

    protected $request_method = self::GET;

    public function init() {
        $this->request_method = strtolower($_SERVER['REQUEST_METHOD']);

		if ($this->request_method === self::GET) {
			$this->disableSIDvalidation();
		}
	}

    protected function checkPermissions() {
		return CWebUser::getType() == USER_TYPE_SUPER_ADMIN;
	}
}
