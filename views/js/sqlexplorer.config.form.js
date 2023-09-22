(overlay => {
const modal = overlay.$dialogue[0];
const form = modal.querySelector('form');
const token = form.querySelector('#post-token');
const error_container = modal.querySelector('[data-error-container]');
const xhr_json_response = response => response.json();
const xhr_catch_handler = error => {
    overlay.unsetLoading();
    error_container.innerHTML = error;
}

// Export.
modal.querySelector('.js-export').addEventListener('click', e => {
    window.location.href = '?action=sqlexplorer.config.export';
    window.addEventListener('focus', _ => overlay.unsetLoading(), {once: true});
});

// Import.
modal.querySelector('.js-import').addEventListener('click', e => {
    import_file.dispatchEvent(new PointerEvent('click'));
    overlay.unsetLoading();
});
const import_file = form.querySelector('[type="file"][name="import"]');
import_file.addEventListener('change', e => {
    const upload_form = new FormData();

    overlay.setLoading();
    upload_form.append('queries', import_file.files[0]);
    upload_form.append(token.name, token.value);
    fetch('?action=sqlexplorer.config.import', {method: "POST", body: upload_form})
        .then(xhr_json_response)
        .then(json => {
            console.log('json', json);

            if (json.errors) {
                error_container.innerHTML = json.errors;
            }
            else {

            }
            overlay.unsetLoading();
        })
        .catch(xhr_catch_handler);
});

// Submit configuration form.
modal.querySelector('.js-submit').addEventListener('click', e => {
    const url = new Curl(form.getAttribute('action'));
    const data = new URLSearchParams(new FormData(form));

    error_container.innerHTML = '';
    overlay.setLoading();
    overlay.xhr = (function() {
        const controller = new AbortController();
        const req = fetch(url.getUrl(), {signal: controller.signal, method: 'POST', body: data})
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
