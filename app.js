import {EditorState, EditorView, basicSetup} from "@codemirror/basic-setup"
import {sql, MySQL, PostgreSQL} from "@codemirror/lang-sql"

// https://www.raresportan.com/how-to-make-a-code-editor-with-codemirror6/
/**
 * Ampersand prefixed rules do not work. For example "&.cm-focused" is nijected into style tag as
 * ".c1 &.cm-focused" but should be inserted as ".c1.cm-focused".
 */
const customTheme = EditorView.baseTheme({
    // "&.cm-focused": {
    //     outline: '0 none !important'
    // },
    // "&.cm-editor": {
    //     fontSize: '16px',
    // },
    ".cm-scroller": {
        fontFamily:'Consolas, Menlo, Monaco, source-code-pro, Courier New, monospace'
    },
},{dark: false});

/** example for autocompletion */
const dbschema = {
    users: [
        'userid',
        'username',
        'password'
    ]
}

const query_textbox = document.querySelector('textarea[name="query"]')

query_textbox.addEventListener('change', e => {
    let old_value = editor.state.doc.toString();

    if (query_textbox.value === old_value) {
        console.log('no changes')
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
            schema: dbschema,
            upperCaseKeywords: false
        }), customTheme],
        doc: query_textbox.value
    }),
    parent: query_textbox.parentElement
})
