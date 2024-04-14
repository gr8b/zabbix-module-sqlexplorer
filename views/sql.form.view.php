<?php

use Modules\SqlExplorer\Helpers\Html\CFormGrid;
use Modules\SqlExplorer\Helpers\Html\CFormField;
use Modules\SqlExplorer\Helpers\Html\StyleTag;
use Modules\SqlExplorer\Helpers\Html\JsonDataTag;
use Modules\SqlExplorer\Helpers\Html\ScriptTag;

$url = (new Curl())
    ->setArgument('action', 'sqlexplorer.form')
    ->getUrl();
$db_label = [
    ZBX_DB_MYSQL => _('MySQL'),
    ZBX_DB_POSTGRESQL => _('Postgre'),
    ZBX_DB_ORACLE => _('Oracle')
][$data['database']['type']];
$token_name = '';
$page_title = sprintf('%s - %s:%s', _('SQL Explorer'), $db_label, $data['database']['table']);
$widget = (new CWidget())->setTitle($page_title);
$form = (new CForm('post', $url))
    ->addClass('sqlexplorer-form')
    ->addVar('text_to_url', $data['text_to_url'])
    ->addVar('autoexec', $data['autoexec'])
    ->addVar('add_column_names', $data['add_column_names'])
    ->addVar('stopwords', $data['stopwords']);

if (version_compare(ZABBIX_VERSION, '6.4.0', '<')) {
    $form->setAttribute('aria-labelledby', ZBX_STYLE_PAGE_TITLE);
}
else {
    $token_name = CCsrfTokenHelper::CSRF_TOKEN_NAME;
    $form->addVar($token_name, $data['csrf_token']['sqlexplorer.form']);
}

$grid = new CFormGrid();

$grid->addItem([
    new CLabel(_('Saved SQL')),
    new CFormField((new CDiv([
        (new CSelect('fav'))
            ->setId('fav')
            ->setValue($data['fav'])
            ->addOptions(CSelect::createOptionsFromArray(array_column($data['queries'], 'title')))
            ->setWidth(ZBX_TEXTAREA_BIG_WIDTH),
        (new CButton('delete_query', _('Remove')))
            ->addClass(ZBX_STYLE_BTN_ALT)
            ->setEnabled($data['fav'] > 0),
    ]))->addClass('margin-between'))
]);

$grid->addItem([
    null,
    new CFormField((new CDiv([
        (new CTextBox('name', $data['name']))
            ->setAttribute('autocomplete', 'off')
            ->setWidth(ZBX_TEXTAREA_BIG_WIDTH),
        (new CButton('update_query', _('Update')))
            ->addClass(ZBX_STYLE_BTN_ALT)
            ->setEnabled($data['fav'] > 0),
        (new CButton('save_query', _('New')))
            ->addClass(ZBX_STYLE_BTN_ALT)
            ->setEnabled(trim($data['name']) !== '')
    ]))->addClass('margin-between'))
]);

$grid->addItem([
    new CLabel(_('Query')),
    new CFormField(
        (new CTextArea('query', $data['query']))
            ->addClass(ZBX_STYLE_DISPLAY_NONE)
            ->removeAttribute('maxlength')
    )
]);

$table = null;

if (array_key_exists('rows', $data)) {
    $table = (new CTable)->addClass(ZBX_STYLE_LIST_TABLE);

    if ($data['add_column_names'] && $data['rows']) {
        $table->setHeader(array_keys($data['rows'][0]));
    }

    if ($data['rows_count'] > $data['rows_limit']) {
        $table->setFooter(new CRow(
            (new CCol(_s('Displaying %1$s of %2$s found', $data['rows_limit'], $data['rows_count'])))
                ->setColSpan(count(array_keys($data['rows'][0])))
                ->addClass(ZBX_STYLE_RIGHT)
        ));
    }

    $regex = '/^(?<file>[a-z0-9_]+\\.php)(\\?(?<params>.+)){0,1}$/';
    foreach ($data['rows'] as $row) {
        if ($data['text_to_url']) {
            foreach ($row as &$col) {
                $match = [];
                $params = [];

                if (trim($col) !== ''
                        && preg_match($regex, trim($col), $match, PREG_UNMATCHED_AS_NULL)) {
                    parse_str((string) $match['params'], $params);
                    $url = new CUrl(trim($col));
                    $col = new CCol((new CLink($params ? end($params) : 'link', $url->toString()))
                        ->setAttribute('target', $data['tab_url'] ? '_blank' : null)
                    );
                }
            }
        }

        $table->addRow($row);
    }
}

$form->addItem((new CTabView())
    ->addTab('default', null, $grid)
    ->setFooter(makeFormFooter(
        (new CSubmit('preview', _('Preview')))->setAttribute('value', 1),
        [new CButton('csv', _('CSV'))]
    ))
);
$controls = [(new CButton('sqlexplorer.config', _('Configuration')))->addClass(ZBX_STYLE_BTN_ALT)];
$widget
    ->setControls(
        version_compare(ZABBIX_VERSION, '6.4.0', '>=')
        ? (new CTag('nav', true, new CList($controls)))->setAttribute('aria-label', _('Content controls'))
        : $controls
    )
    ->addItem(new StyleTag(<<<'CSS'
.margin-between > * { vertical-align: middle; margin-right: 5px !important; }
/* Codemirror styles */
.cm-wrap.cm-focused { outline: 0 none; }
.cm-wrap { border: 1px solid silver; }
.cm-scroller { font-family: Consolas, Menlo, Monaco, source-code-pro, Courier New, monospace !important; font-size: 12px; }
CSS
    ))
    ->addItem(new JsonDataTag('page-json', [
        'dark_theme' => in_array(getUserTheme(CWebUser::$data), ['dark-theme']),
        'queries' => $data['queries'],
        'db_schema' => $data['db_schema'],
        'token' => [
            'name' => $token_name,
            'action' => $data['csrf_token']
        ]
    ]))
    ->addItem($form)
    ->addItem($table)
    ->addItem((new ScriptTag())->setAttribute('src', $data['public_path'].'app.min.js'))
    ->show();
