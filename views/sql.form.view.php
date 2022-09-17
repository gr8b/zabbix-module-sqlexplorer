<?php

use Modules\SqlExplorer\Compatibility\Html\CFormField;

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
			->addOptions(CSelect::createOptionsFromArray(array_column($data['favorites'], 'title')))
			->setWidth(ZBX_TEXTAREA_BIG_WIDTH),
		new CButton('save_query', _('Save')),
		(new CButton('delete_query', _('Remove')))->setEnabled($data['fav'] > 0)
	]))->setId('fav-row'))
]);

$grid->addItem([
	new CLabel(_('Query')),
	new CFormField(
		(new CTextArea('query', $data['query']))->addClass(ZBX_STYLE_DISPLAY_NONE)
	)
]);

$table = null;

if ($data['preview']) {
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
		[
			new CButton('csv', _('CSV')),
		]
	))
);
$queries_json = json_encode($data['favorites']);

$widget
	->addItem(new CTag('style', true, <<<'CSS'
		#fav-row z-select,#fav-row button { vertical-align: middle; margin-right: 5px; }
		.cm-wrap.cm-focused { outline: 0 none; }
		.cm-wrap { border: 1px solid silver; }
	CSS
	))
	->addItem($form)
	->addItem($table)
	->addItem(new CScriptTag('let queries = '.json_encode($data['favorites']).';'.<<<'JAVASCRIPT'
		document.getElementById('csv').addEventListener('click', function() {
			let form = this.closest('form');
	
			form.setAttribute('action', 'zabbix.php?action=sqlexplorer.csv');
			form.submit();
		});
		document.getElementById('preview').addEventListener('click', function() {
			let form = this.closest('form');
	
			form.setAttribute('action', 'zabbix.php?action=sqlexplorer.form');
			form.submit();
		});
		document.getElementById('fav').addEventListener('change', function() {
			document.querySelector('textarea[name="query"]').value = queries[this.value].query;
			document.querySelector('textarea[name="query"]').dispatchEvent(new Event('change'));

			if (this.value > 0) {
				document.getElementById('delete_query').removeAttribute('disabled');
			}
			else {
				document.getElementById('delete_query').setAttribute('disabled', 'disabled');
			}
		});
	JAVASCRIPT
	))
	->addItem((new CTag('script', true))
		->setAttribute('src', 'modules/zabbix-module-sqlexplorer/public/app.min.js')
		->setAttribute('type', 'text/javascript'))
	->show();
