<?php

namespace Modules\SqlExplorer;

use APP;
use CMenu;
use CWebUser;
use Core\CModule as CModule;
use CController as CAction;
use CMenuItem;
use Modules\SqlExplorer\Actions\SqlForm;
use Modules\SqlExplorer\Compatibility\Html\CFormGrid as CFormGridCompat;
use Modules\SqlExplorer\Compatibility\Html\CFormField as CFormFieldCompat;

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
		if (is_a($action, SqlForm::class)) {
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
		if (version_compare($version, '6.2', '<')) {
			class_alias(CFormGridCompat::class, '\\CFormGrid', true);
			class_alias(CFormFieldCompat::class, '\\CFormField', true);
		}
	}
}
