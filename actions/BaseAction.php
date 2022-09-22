<?php

namespace Modules\SqlExplorer\Actions;

use CWebUser;
use CController as Action;

abstract class BaseAction extends Action {

    const GET = 'get';
    const POST = 'post';

    protected const TYPE_FORM_URLENCODED = 0;
    protected const TYPE_JSON = 1;

    /** @property \Modules\SqlExplorer\Module $module */
    public $module;

    /** @property int $post_content_type  Type of content expected by action checkInput method. */
    protected $post_content_type = self::TYPE_FORM_URLENCODED;

    protected $request_method = self::GET;

    public function init() {
        $this->request_method = strtolower($_SERVER['REQUEST_METHOD']);

        if ($this->request_method === self::GET) {
            $this->disableSIDvalidation();
        }

        if (version_compare(ZABBIX_VERSION, '6.0', '<')) {
            if ($this->post_content_type == self::TYPE_JSON) {
                $input = json_decode(file_get_contents('php://input'), true);
                \CSession::setValue('formData', $input);
            }
        }
        else {
            // Fix for broken visibility - private vs public.
            $this->setPostContentType($this->post_content_type);
        }
    }

    protected function checkPermissions() {
        return CWebUser::getType() == USER_TYPE_SUPER_ADMIN;
    }
}
