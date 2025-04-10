import {EditorState, EditorView, basicSetup} from "@codemirror/basic-setup"
import {sql, MySQL, PostgreSQL} from "@codemirror/lang-sql"
import { oneDark } from "@codemirror/theme-one-dark"

const page_data = JSON.parse(document.querySelector('#page-json').innerText)
let queries = page_data.queries

const form = document.querySelector('form.sqlexplorer-form')
const queries_select = form.querySelector('[name="fav"]')
const query_textbox = form.querySelector('textarea[name="query"]')
const name_input = form.querySelector('input[name="name"]')
const update_button = form.querySelector('#update_query')
const delete_button = form.querySelector('#delete_query')
const save_button = form.querySelector('#save_query')
const config_button = document.getElementById('sqlexplorer.config')
const stopwords = document.querySelector('[name="stopwords"]')

function setActionToken(action, form) {
    form.querySelector(`[name="${page_data.token.name}"]`)?.setAttribute('value', page_data.token.action[action])
}
config_button.addEventListener('click', () => {
    setActionToken('sqlexplorer.config', form)
    PopUp('sqlexplorer.config', Object.fromEntries(new FormData(form)))
})
document.getElementById('csv').addEventListener('click', function() {
    setActionToken('sqlexplorer.csv', form)
    form.setAttribute('action', 'zabbix.php?action=sqlexplorer.csv')
    setLoadingState(true)
    query_textbox.value = window.btoa(unescape(encodeURIComponent(editor.state.doc.toString())))
    form.submit()
    setTimeout(() => setLoadingState(false), 1000)
});
document.getElementById('preview').addEventListener('click', function(e) {
    setActionToken('sqlexplorer.form', form)
    form.setAttribute('action', 'zabbix.php?action=sqlexplorer.form')

    if (checkStopWords(editor.state.doc.toString()) == false) {
        e.preventDefault()
        e.stopPropagation()

        return false
    }

    query_textbox.value = window.btoa(unescape(encodeURIComponent(editor.state.doc.toString())))
    setLoadingState(true)
    form.submit()
});
queries_select.addEventListener('change', function() {
    if (this.value > 0) {
        query_textbox.value = queries[this.value].query
        query_textbox.dispatchEvent(new Event('change'))
        update_button.removeAttribute('disabled')
        delete_button.removeAttribute('disabled')

        const autoexec = document.querySelector('[type="hidden"][name="autoexec"]').value;

        if (autoexec - 0) {
            form.querySelector('[name="preview"]').click()
        }
    }
    else {
        update_button.setAttribute('disabled', 'disabled')
        delete_button.setAttribute('disabled', 'disabled')
    }
})
update_button.addEventListener('click', e => {
    queries[queries_select.value].query = editor.state.doc.toString()
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

    queries.push({
        title: name_input.value,
        query: editor.state.doc.toString()
    })
    saveQueries().then(json => {
        name_input.value = ''

        queries_select.addOption({value, label: name_input.value})
        queries_select.value = value
    })
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
    let sid = form.querySelector('[name="sid"]')
    let data = {queries: queries.filter(Boolean)}

    if (sid) {
        data.sid = sid.value
    }
    else {
        data[page_data.token.name] = page_data.token.action['sqlexplorer.queries']
    }

    setLoadingState(true)
    return fetch('?action=sqlexplorer.queries', {
            method: 'POST',
            body: JSON.stringify(data)
        })
        .then(resp => resp.json())
        .finally(e => {
            setLoadingState(false)
        })
}

function checkStopWords(query) {
    const match = [...stopwords.value.matchAll(/\w+/g)].filter(match => query.match(new RegExp(match[0], 'i')))

    if (match.length) {
        return confirm(`Are you sure to execute query: "${query.replace(/\s+$/, '')}"`)
    }

    return true
}

// https://www.raresportan.com/how-to-make-a-code-editor-with-codemirror6/
// configuration https://github.com/codemirror/lang-sql
const theme = EditorView.baseTheme({},{dark: false})
let editor = new EditorView({
    state: EditorState.create({
        extensions: [
            page_data.dark_theme ? oneDark : theme,
            basicSetup,
            sql({
                dialect: MySQL,
                schema: page_data.db_schema,
                upperCaseKeywords: true
            })
        ],
        doc: query_textbox.value
    }),
    parent: query_textbox.parentElement
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
