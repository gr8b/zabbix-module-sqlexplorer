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
    ->addItem((new CInput('submit', 'submit', 1))->addStyle('display: none;'));

$js_submit_handler = <<<'JS'
function submitModuleConfig(overlay) {
    const form = overlay.$dialogue[0].querySelector('form');
    const url = new Curl(form.getAttribute('action'));
    const data = new URLSearchParams(new FormData(form));
    const error_container = overlay.$dialogue[0].querySelector('[data-error-container]');

    error_container.innerHTML = '';
    overlay.setLoading();
    overlay.xhr = (function() {
        const controller = new AbortController();
        const req = fetch(url.getUrl(), {signal: controller.signal, method: 'POST', body: data})
            .then(r => r.json())
            .then(json => {
                overlay.unsetLoading();

                if (json.errors) {
                    error_container.innerHTML = json.errors;
                }
                else {
                    overlayDialogueDestroy(overlay.dialogueid);
                    Object.entries(json.params).forEach(([key, value]) => {
                        let input = document.querySelector(`[type="hidden"][name="${key}"]`);

                        if (input !== null) {
                            input.value = json.params[key];
                        }
                    });
                }
            })
            .catch(error => {
                overlay.unsetLoading();
                error_container.innerHTML = error;
            });

        this.abort = () => controller.abort();

        return this;
    })();
}
JS;

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
                'title' => _('Apply'),
                'class' => 'dialogue-widget-save',
                'keepOpen' => true,
                'isSubmit' => true,
                'action' => 'return submitModuleConfig(overlay);'
            ]
        ],
        'params' => $data['params'],
        'script_inline' => $js_submit_handler
    ];
}

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
    CProfiler::getInstance()->stop();
    $output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output);
