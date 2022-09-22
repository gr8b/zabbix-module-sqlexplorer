import {EditorState, EditorView, basicSetup} from "@codemirror/basic-setup"
import {sql, MySQL, PostgreSQL} from "@codemirror/lang-sql"

const page_data = JSON.parse(document.querySelector('#page-json').innerText)
let queries = page_data.queries

const form = document.querySelector('form.sqlexplorer-form')
const queries_select = form.querySelector('[name="fav"]')
const query_textbox = form.querySelector('textarea[name="query"]')
const name_input = form.querySelector('input[name="name"]')
const update_button = form.querySelector('#update_query')
const delete_button = form.querySelector('#delete_query')
const save_button = form.querySelector('#save_query')

document.getElementById('csv').addEventListener('click', function() {
	form.setAttribute('action', 'zabbix.php?action=sqlexplorer.csv')
	setLoadingState(true)
	form.submit()
	setTimeout(() => setLoadingState(false), 1000)
});
document.getElementById('preview').addEventListener('click', function() {
	form.setAttribute('action', 'zabbix.php?action=sqlexplorer.form')
	setLoadingState(true)
	form.submit()
});
queries_select.addEventListener('change', function() {
	if (this.value > 0) {
		query_textbox.value = queries[this.value].query
		query_textbox.dispatchEvent(new Event('change'))
		update_button.removeAttribute('disabled')
		delete_button.removeAttribute('disabled')
	}
	else {
		update_button.setAttribute('disabled', 'disabled')
		delete_button.setAttribute('disabled', 'disabled')
	}
})
update_button.addEventListener('click', e => {
	queries[queries_select.value].query = query_textbox.value
	saveQueries()
})
delete_button.addEventListener('click', e => {
	if (confirm(`Delete query "${queries[queries_select.value]?.title}"`)) {
		delete queries[queries_select.value]
		queries_select.querySelector(`.list li[value="${queries_select.value}"]`).style.display = 'none';
		queries_select.value = 0
		saveQueries()
	}
})
name_input.addEventListener('keyup', e => {
	let name = name_input.value.replace(/\s+/g, '')

	if (name.length > 0) {
		save_button.removeAttribute('disabled')
	}
	else {
		save_button.setAttribute('disabled', 'disabled')
	}
})
save_button.addEventListener('click', e => {
	let value = queries.length

	queries_select.addOption({value, label: name_input.value})
	queries.push({
		title: name_input.value,
		query: editor.state.doc.toString()
	})
	queries_select.value = value
	saveQueries()
})

function setLoadingState(is_loading) {
	if (is_loading) {
		form.classList.add('is-loading')
	}
	else {
		form.classList.remove('is-loading')
	}
}

function saveQueries() {
	let sid = form.querySelector('[name="sid"]').value

	setLoadingState(true)
	fetch(`?action=sqlexplorer.queries&sid=${sid}`, {
			method: 'POST',
			body: JSON.stringify({queries: queries.filter(Boolean)})
		})
		.finally(e => {
			setLoadingState(false)
		})
}

// https://www.raresportan.com/how-to-make-a-code-editor-with-codemirror6/
// configuration https://github.com/codemirror/lang-sql
// TODO:fix styles for dark theme
const theme = EditorView.baseTheme({},{dark: page_data.dark_theme})
let editor = new EditorView({
	state: EditorState.create({
		extensions: [basicSetup, sql({
			dialect: MySQL,
			schema: page_data.db_schema,
			upperCaseKeywords: true
		}), theme],
		doc: query_textbox.value
	}),
	parent: query_textbox.parentElement
})
form.addEventListener('submit', e => {
	query_textbox.value = editor.state.doc.toString()
})
query_textbox.addEventListener('change', e => {
	let old_value = editor.state.doc.toString()

	if (query_textbox.value === old_value) {
		return
	}

	editor.dispatch({
		changes: {
			from: 0, 
			to: old_value.length,
			insert: query_textbox.value
		}
	})
})
