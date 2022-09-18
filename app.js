import {EditorState, EditorView, basicSetup} from "@codemirror/basic-setup"
import {sql, MySQL, PostgreSQL} from "@codemirror/lang-sql"

const page_data = JSON.parse(document.querySelector('#page-json').innerText);
let queries = page_data.queries;
const queries_select = document.getElementById('fav');
const query_textbox = document.querySelector('textarea[name="query"]');

console.log('jsonqueries found', queries);

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
queries_select.addEventListener('change', function() {
	if (this.value > 0) {
		query_textbox.value = queries[this.value].query;
		query_textbox.dispatchEvent(new Event('change'));
		document.getElementById('delete_query').removeAttribute('disabled');
		document.getElementById('update_query').removeAttribute('disabled');
	}
	else {
		document.getElementById('delete_query').setAttribute('disabled', 'disabled');
		document.getElementById('update_query').setAttribute('disabled', 'disabled');
	}
});

document.querySelector('#update_query').addEventListener('click', e => {
	queries[queries_select.value].query = query_textbox.value;
});
document.querySelector('#delete_query').addEventListener('click', e => {
	if (confirm(`Delete query "${queries[queries_select.value].title}"`)) {
		delete queries[queries_select.value]
		queries_select.querySelector(`.list li[value="${queries_select.value}"]`).classList.add('display-none');
		queries_select.value = 0;
	}
});
// https://www.raresportan.com/how-to-make-a-code-editor-with-codemirror6/
// TODO:fix styles for dark theme
const customTheme = EditorView.baseTheme({},{dark: page_data.dark_theme})

query_textbox.addEventListener('change', e => {
	let old_value = editor.state.doc.toString();

	if (query_textbox.value === old_value) {
		return;
	}

	editor.dispatch({
		changes: {
			from: 0, 
			to: old_value.length,
			insert: query_textbox.value
		}
	})
})
query_textbox.closest('form').addEventListener('submit', e => {
	query_textbox.value = editor.state.doc.toString()
})

let editor = new EditorView({
	state: EditorState.create({
		// configuration https://github.com/codemirror/lang-sql
		extensions: [basicSetup, sql({
			dialect: MySQL,
			schema: page_data.db_schema,
			upperCaseKeywords: false
		}), customTheme],
		doc: query_textbox.value
	}),
	parent: query_textbox.parentElement
})

