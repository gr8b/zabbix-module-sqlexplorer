<?php

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
