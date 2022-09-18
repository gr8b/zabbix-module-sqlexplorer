<?php

use Modules\SqlExplorer\Compatibility\Html\CFormField;
use Modules\SqlExplorer\Helpers\Html\StyleTag;
use Modules\SqlExplorer\Helpers\Html\JsonDataTag;
use Modules\SqlExplorer\Helpers\Html\ScriptTag;

$url = (new Curl())
	->setArgument('action', 'sqlexplorer.form')
	->getUrl();
$page_title = sprintf('%s - %s:%s', _('SQL Explorer'), $data['database']['type'], $data['database']['table']);
$widget = (new CWidget())->setTitle($page_title);
$form = (new CForm('post', $url))
	->addClass('sqlexplorer-form')
	->setAttribute('aria-labelledby', ZBX_STYLE_PAGE_TITLE);

$grid = new CFormGrid();

$grid->addItem([
	new CLabel(_('Column names as first row')),
	new CFormField(
		(new CCheckBox('add_column_names', 1))->setChecked((bool) $data['add_column_names'])
	)
]);

$grid->addItem([
	new CLabel(_('Saved SQL')),
	new CFormField((new CDiv([
		(new CSelect('fav'))
			->setId('fav')
			->setValue($data['fav'])
			->addOptions(CSelect::createOptionsFromArray(array_column($data['queries'], 'title')))
			->setWidth(ZBX_TEXTAREA_BIG_WIDTH),
		(new CButton('update_query', _('Update')))
			->addClass(ZBX_STYLE_BTN_ALT)
			->setEnabled($data['fav'] > 0),
		(new CButton('delete_query', _('Remove')))
			->addClass(ZBX_STYLE_BTN_ALT)
			->setEnabled($data['fav'] > 0)
	]))->addClass('margin-between'))
]);

$grid->addItem([
	new CLabel(_('Save query as')),
	new CFormField((new CDiv([
		(new CTextBox('name', $data['name']))
			->setAttribute('autocomplete', 'off')
			->setWidth(ZBX_TEXTAREA_BIG_WIDTH),
		(new CButton('save_query', _('Save')))
			->addClass(ZBX_STYLE_BTN_ALT)
			->setEnabled(trim($data['name']) !== '')
	]))->addClass('margin-between'))
]);

$grid->addItem([
	new CLabel(_('Query')),
	new CFormField(
		(new CTextArea('query', $data['query']))->addClass(ZBX_STYLE_DISPLAY_NONE)
	)
]);

$table = null;

if (array_key_exists('rows', $data)) {
	$limit = 100;
	$table = (new CTable)->addClass(ZBX_STYLE_LIST_TABLE);

	if ($data['add_column_names']) {
		$table->setHeader(array_shift($data['rows']));
	}

	if (array_key_exists($limit, $data['rows'])) {
		$total = count($data['rows']);
		$table->setFooter(new CRow(
			(new CCol(_s('Displaying %1$s of %2$s found', $limit, $total)))
				->setColSpan(count(array_keys($data['rows'][0])))
				->addClass(ZBX_STYLE_RIGHT)
		));
	}

	array_map([$table, 'addRow'], array_slice($data['rows'], 0, $limit - 1));
}

$form->addItem((new CTabView())
	->addTab('default', null, $grid)
	->setFooter(makeFormFooter(
		(new CSubmit('preview', _('Preview')))->setAttribute('value', 1),
		[new CButton('csv', _('CSV'))]
	))
);

$widget
	->addItem(new StyleTag(<<<'CSS'
		.margin-between > * { vertical-align: middle; margin-right: 5px !important; }
		.processing::before {
			position: absolute;
			top: 0;
			left: 0;
			background: rgba(255, 255, 255, 0.7);
			display: block;
			content: 'Processing.'
		}

		/* Codemirror styles */
		.cm-wrap.cm-focused { outline: 0 none; }
		.cm-wrap { border: 1px solid silver; }
		.cm-scroller { font-family: Consolas, Menlo, Monaco, source-code-pro, Courier New, monospace !important; font-size: 12px; }
	CSS
	))
	->addItem(new JsonDataTag('page-json', [
		'dark_theme' => in_array(getUserTheme(CWebUser::$data), ['dark-theme']),
		'queries' => $data['queries'],
		'db_schema' => $data['db_schema']
	]))
	->addItem($form)
	->addItem($table)
	->addItem((new ScriptTag())->setAttribute('src', $data['public_path'].'app.min.js'))
	->show();
