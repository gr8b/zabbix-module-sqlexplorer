<?php

namespace Modules\SqlExplorer;

if (version_compare(ZABBIX_VERSION, '6.4.0', '>')) {
    class_exists('\Core\CModule', false) or class_alias('\Zabbix\Core\CModule', '\Core\CModule');
    class_exists('\CWidget', false) or class_alias('\CHtmlPage', '\CWidget');
}

use APP;
use CMenu;
use CWebUser;
use Core\CModule as CModule;
use CController as CAction;
use CMenuItem;
use Modules\SqlExplorer\Actions\BaseAction;
use Modules\SqlExplorer\Helpers\Html\CFormGrid;
use Modules\SqlExplorer\Helpers\Html\CFormField;

class Module extends CModule {

    public function init(): void {
        $this->registerMenuEntry();
        $this->setCompatibilityMode(ZABBIX_VERSION);
    }

    /**
     * Before action event handler.
     *
     * @param CAction $action    Current request handler object.
     */
    public function onBeforeAction(CAction $action): void {
        if (is_a($action, BaseAction::class)) {
            $action->module = $this;
        }
    }

    /**
     * For login/logout actions update user seession state in multiple databases.
     */
    public function onTerminate(CAction $action): void {
    }

    /**
     * Get array of database configuration.
     *
     * @return array
     */
    public function getDatabase() {
        global $DB;

        return [
            'type' => $DB['TYPE'],
            'table' => $DB['DATABASE'],
            'schema' => $DB['SCHEMA']
        ];
    }

    public function dbSelect(string $query) {
        global $DB;

        $db = null;
        $error = null;
        $rows = [];
        $config = $this->getManifest();

        if (array_key_exists('connection', $config) && strpos($config['connection'], ':')) {
            unset($DB['DB']);
            $db = $DB;
            list($DB['USER'], $DB['PASSWORD']) = explode(':', $config['connection']);
            DBconnect($error);
        }

        if ($error === null) {
            $resource = DBselect($query);
            $rows = $resource === false ? [] : DBfetchArray($resource);
        }

        if ($db === null) {
            return $rows;
        }

        if ($error !== null) {
            error($error);
        }

        $DB = $db;
        DBconnect($error);

        return $rows;
    }

    public function getAssetsUrl() {
        return version_compare(ZABBIX_VERSION, '6.4', '>=')
            ? $this->getRelativePath().'/public/'
            : 'modules/'.basename($this->getDir()).'/public/';
    }

    protected function registerMenuEntry() {
        if (CWebUser::getType() != USER_TYPE_SUPER_ADMIN) {
            return;
        }

        /** @var CMenu $menu */
        $menu = APP::Component()->get('menu.main');
        $menu
            ->find(_('Administration'))
            ->getSubMenu()
                ->add((new CMenuItem(_('SQL Explorer')))->setAction('sqlexplorer.form'));
    }

    protected function setCompatibilityMode($version) {
        if (version_compare($version, '6.0', '>=')) {
            class_alias('\\CFormGrid', CFormGrid::class, true);
            class_alias('\\CFormField', CFormField::class, true);
        }
    }
}
