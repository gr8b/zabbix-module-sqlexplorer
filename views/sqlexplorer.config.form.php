<?php

/**
 * @var CView $this
 */

$form = (new CForm())
    ->cleanItems()
    ->addVar('action', $data['action'])
    ->addVar('refresh', 1)
    ->addVar('tab_url', 0)
    ->addVar('text_to_url', 0)
    ->addVar('autoexec', 0)
    ->addVar('add_column_names', 0);

if (version_compare(ZABBIX_VERSION, '6.4.0', '>=')) {
    $form->addVar(CCsrfTokenHelper::CSRF_TOKEN_NAME, $data['csrf_token']['sqlexplorer.config.import'], 'import-token');
    $form->addVar(CCsrfTokenHelper::CSRF_TOKEN_NAME, $data['csrf_token']['sqlexplorer.config'], 'post-token');
}

$form_list = (new CFormList())
    ->addRow(
        new CLabel(_('Convert URL text into clickable links'), 'text_to_url'),
        (new CCheckBox('text_to_url', 1))->setChecked((bool) $data['text_to_url'])
    )
    ->addRow(
        new CLabel(_('Open URL in new tab'), 'tab_url'),
        (new CCheckBox('tab_url', 1))->setChecked((bool) $data['tab_url'])
    )
    ->addRow(
        new CLabel(_('Automatically execute selected SQL'), 'autoexec'),
        (new CCheckBox('autoexec', 1))->setChecked((bool) $data['autoexec'])
    )
    ->addRow(
        new CLabel(_('Column names as first row')),
        (new CCheckBox('add_column_names', 1))->setChecked((bool) $data['add_column_names'])
    )
    ->addRow(
        new CLabel(_('Stop words list'), 'stopwords'),
        (new CTextBox('stopwords', $data['stopwords']))->setWidth(ZBX_TEXTAREA_BIG_WIDTH)
    );

$form
    ->addItem($form_list)
    ->addItem(
        (new CInput('file', 'import'))
            ->setAttribute('accept', '.txt')
            ->addClass(ZBX_STYLE_DISPLAY_NONE)
    )
    ->addItem((new CInput('submit', 'submit', 1))->addStyle('display: none;'));

if ($data['params']) {
    $output = [
        'params' => $data['params']
    ];
}
else {
    $output = [
        'header' => $data['title'],
        'body' => (new CDiv([(new CDiv($data['errors']))->setAttribute('data-error-container', 1), $form]))->toString(),
        'buttons' => [
            [
                'title' => _('Import'),
                'class' => 'js-import float-left '.ZBX_STYLE_BTN_ALT,
                'keepOpen' => true
            ],
            [
                'title' => _('Export'),
                'class' => 'js-export float-left '.ZBX_STYLE_BTN_ALT,
                'keepOpen' => true
            ],
            [
                'title' => _('Apply'),
                'class' => 'js-submit dialogue-widget-save',
                'keepOpen' => true,
                'isSubmit' => true
            ]
        ],
        'params' => $data['params'],
        'script_inline' => $this->readJsFile('sqlexplorer.config.form.js')
    ];
}

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
    CProfiler::getInstance()->stop();
    $output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
