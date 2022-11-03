<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 */

$form = (new CForm())
	->cleanItems()
	->addVar('action', $data['action'])
	->addVar('refresh', 1);

$form_list = (new CFormList())
	->addRow(
        new CLabel(_('Convert URL text into clickable links'), 'clickable_url'),
		(new CCheckBox('clickable_url', 1))->setChecked($data['clickable_url'])
	)
	->addRow(
        new CLabel(_('Automatically execute selected SQL'), 'autoexecute_query'),
		(new CCheckBox('autoexecute_query', 1))->setChecked($data['autoexecute_query'])
	)
	->addRow(
        new CLabel(_('Stop words list'), 'stopwords'),
		(new CTextBox('stopwords', implode(',', $data['stopwords'])))->setWidth(ZBX_TEXTAREA_BIG_WIDTH)
	);

$form
	->addItem($form_list)
	->addItem((new CInput('submit', 'submit'))->addStyle('display: none;'));

$output = [
	'header' => $data['title'],
	'body' => (new CDiv([$data['errors'], $form]))->toString(),
	'buttons' => [
		[
			'title' => _('Apply'),
			'class' => 'dialogue-widget-save',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'return submitMaintenancePeriod(overlay);'
		]
	],
	'params' => $data['params'],
	// 'script_inline' => $this->readJsFile('popup.maintenance.period.js.php')
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
