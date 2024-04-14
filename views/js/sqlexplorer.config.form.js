(overlay => {
const modal = overlay.$dialogue[0];
const form = modal.querySelector('form');
const token = form.querySelector('#import-token');
const error_container = modal.querySelector('[data-error-container]');
const xhr_json_response = response => response.json();
const xhr_catch_handler = error => {
    overlay.unsetLoading();
    error_container.innerHTML = error;
}
const getUrlFor = action => {
    const url = new Curl('zabbix.php');
    url.setArgument('action', action);
    return url.getUrl();
}
const addPostMessages = (type, messages) => {
    if (window.postMessageDetails) {
        return postMessageDetails(type, messages);
    }

    // Compatibility for 5.0
    return window[type === 'success' ? 'postMessageOk' : 'postMessageError'](messages.join("\n"));
}

// Export.
modal.querySelector('.js-export').addEventListener('click', e => {
    window.location.href = getUrlFor('sqlexplorer.config.export');
    window.addEventListener('focus', _ => overlay.unsetLoading(), {once: true});
});

// Import.
modal.querySelector('.js-import').addEventListener('click', e => {
    import_file.dispatchEvent(new PointerEvent('click'));
    overlay.unsetLoading();
});
const import_file = form.querySelector('[type="file"][name="import"]');
import_file.addEventListener('change', () => {
    const upload_form = new FormData();

    overlay.setLoading();
    upload_form.append('queries', import_file.files[0]);
    token !== null && upload_form.append(token.name, token.value);
    fetch(getUrlFor('sqlexplorer.config.import'), {method: "POST", body: upload_form})
        .then(xhr_json_response)
        .then(json => {
            if (json.success) {
                addPostMessages('success', json.post_messages);
                window.location.href = window.location.href;

                return;
            }

            error_container.innerHTML = json.messages;
            import_file.value = '';
            overlay.unsetLoading();
        })
        .catch(xhr_catch_handler);
});

// Submit configuration form.
modal.querySelector('.js-submit').addEventListener('click', e => {
    const data = new URLSearchParams(new FormData(form));

    error_container.innerHTML = '';
    overlay.setLoading();
    overlay.xhr = (function() {
        const controller = new AbortController();
        const req = fetch(getUrlFor(form.getAttribute('action')), {signal: controller.signal, method: 'POST', body: data})
            .then(xhr_json_response)
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
            .catch(xhr_catch_handler);

        this.abort = () => controller.abort();

        return this;
    })();
});

})(overlays_stack.end())
