{{-- Machine translation for admin forms: drafts Filipino and Cebuano text for the admin to review before saving. --}}
<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-sky-50 px-4 py-3 text-sm text-sky-900 ring-1 ring-sky-100">
    <p class="flex items-center gap-2">
        <ion-icon name="language" class="text-lg text-sky-600"></ion-icon>
        <span>Use <strong>Translate</strong> to draft Filipino and Cebuano from the English. Check the wording before you save.</span>
    </p>
    <button type="button" id="translate-all" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-sky-700 shadow-sm ring-1 ring-sky-200 transition hover:bg-sky-100 disabled:opacity-50">
        <ion-icon name="sparkles-outline" class="text-sm"></ion-icon>
        <span>Translate empty fields</span>
    </button>
</div>
<p id="translate-status" class="hidden text-sm" role="status" aria-live="polite"></p>

<script>
    (() => {
        const status = document.getElementById('translate-status');
        const token = document.querySelector('input[name="_token"]').value;
        const show = (message, error = false) => {
            status.textContent = message;
            status.className = 'mt-2 flex items-center gap-1.5 text-sm ' + (error ? 'text-red-600' : 'text-emerald-700');
        };

        async function translate(button) {
            const source = document.getElementById(button.dataset.translateSource);
            const target = document.getElementById(button.dataset.translateTarget);
            const text = source.value.trim();
            if (!text) {
                show('Write the English text first.', true);
                source.focus();
                return false;
            }
            const label = button.querySelector('[data-translate-label]');
            button.disabled = true;
            label.textContent = 'Translating…';
            try {
                const response = await fetch(@json(route('admin.translate')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ text, target: button.dataset.translateLang }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Translation failed. Try again.');
                target.value = data.translation;
                target.classList.add('ring-4', 'ring-sky-200');
                setTimeout(() => target.classList.remove('ring-4', 'ring-sky-200'), 1500);
                return true;
            } catch (error) {
                show(error.message, true);
                return false;
            } finally {
                button.disabled = false;
                label.textContent = 'Translate';
            }
        }

        document.querySelectorAll('[data-translate-source]').forEach((button) => {
            button.addEventListener('click', async () => {
                if (await translate(button)) show('Translated. Check the wording, then save.');
            });
        });

        document.getElementById('translate-all').addEventListener('click', async (event) => {
            const all = event.currentTarget;
            const empty = [...document.querySelectorAll('[data-translate-source]')].filter((button) =>
                !document.getElementById(button.dataset.translateTarget).value.trim()
                && document.getElementById(button.dataset.translateSource).value.trim());
            if (!empty.length) {
                show('Every translation box already has text.');
                return;
            }
            all.disabled = true;
            let done = 0;
            for (const button of empty) {
                show(`Translating ${done + 1} of ${empty.length}…`);
                if (!(await translate(button))) break;
                done++;
            }
            all.disabled = false;
            if (done === empty.length) show(`Filled ${done} ${done === 1 ? 'box' : 'boxes'}. Check the wording, then save.`);
        });
    })();
</script>
