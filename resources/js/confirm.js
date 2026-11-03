// Themed replacement for window.confirm(): confirmDialog(message, options) resolves to true / false
const tones = {
    danger: {
        icon: 'icon-[tabler--trash]',
        badge: 'bg-rose-500/10 text-rose-400 ring-rose-500/30',
        glow: 'bg-rose-500/25',
        ok: 'bg-gradient-to-b from-rose-500 to-rose-700 text-snow shadow-lg shadow-rose-600/30 ring-1 ring-rose-400/40 hover:-translate-y-0.5 hover:shadow-rose-500/50 focus-visible:ring-rose-500/40',
        okText: 'হ্যাঁ, মুছে ফেলুন',
    },
    warning: {
        icon: 'icon-[tabler--alert-triangle]',
        badge: 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
        glow: 'bg-amber-500/20',
        ok: 'btn-primary',
        okText: 'হ্যাঁ, নিশ্চিত',
    },
};

let dialog;

function build() {
    dialog = document.createElement('dialog');
    dialog.className =
        'm-auto w-[calc(100%-2rem)] max-w-sm overflow-visible bg-transparent p-0 text-ink-200 backdrop:bg-black/70 backdrop:backdrop-blur-sm open:animate-pop';
    dialog.innerHTML = `
        <form method="dialog" class="relative overflow-hidden rounded-3xl bg-ink-950/90 p-6 text-center shadow-2xl ring-1 ring-white/10 backdrop-blur-2xl">
            <span data-glow class="pointer-events-none absolute -top-20 left-1/2 size-44 -translate-x-1/2 rounded-full blur-3xl"></span>
            <span data-badge class="relative mx-auto mb-4 grid size-14 place-items-center rounded-2xl ring-1">
                <span data-icon class="size-7"></span>
            </span>
            <p data-title class="relative font-display text-lg font-bold text-white"></p>
            <p data-message class="relative mt-1.5 text-sm leading-relaxed whitespace-pre-line text-ink-400"></p>
            <div class="relative mt-6 grid grid-cols-2 gap-2">
                <button value="cancel" data-cancel class="btn btn-ghost"></button>
                <button value="confirm" data-ok class="btn"></button>
            </div>
        </form>`;

    // A click outside the panel lands on the dialog itself (its ::backdrop)
    dialog.addEventListener('click', (event) => event.target === dialog && dialog.close('cancel'));
    document.body.append(dialog);
}

export function confirmDialog(message, { title = 'আপনি কি নিশ্চিত?', confirmText, cancelText = 'বাতিল', tone = 'warning' } = {}) {
    if (!dialog) build();
    const theme = tones[tone] ?? tones.warning;
    const part = (name) => dialog.querySelector(`[data-${name}]`);

    part('glow').className = `pointer-events-none absolute -top-20 left-1/2 size-44 -translate-x-1/2 rounded-full blur-3xl ${theme.glow}`;
    part('badge').className = `relative mx-auto mb-4 grid size-14 place-items-center rounded-2xl ring-1 ${theme.badge}`;
    part('icon').className = `size-7 ${theme.icon}`;
    part('title').textContent = title;
    part('message').textContent = message;
    part('cancel').textContent = cancelText;
    part('ok').textContent = confirmText ?? theme.okText;
    part('ok').className = `btn ${theme.ok}`;

    dialog.returnValue = '';
    dialog.showModal();
    // Destructive actions start on "cancel" so a stray Enter doesn't delete anything
    (tone === 'danger' ? part('cancel') : part('ok')).focus();

    return new Promise((resolve) => {
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true });
    });
}

// Declarative use: <form data-confirm="..."> or <button type="submit" data-confirm="...">
// Optional: data-confirm-tone="danger", data-confirm-title, data-confirm-ok
export function initConfirmForms() {
    const approved = new WeakSet();

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        const source = event.submitter?.dataset.confirm ? event.submitter : form;
        const message = source.dataset.confirm;

        if (!message || approved.delete(form)) return;

        event.preventDefault();
        const ok = await confirmDialog(message, {
            tone: source.dataset.confirmTone,
            title: source.dataset.confirmTitle,
            confirmText: source.dataset.confirmOk,
        });

        if (ok) {
            approved.add(form);
            form.requestSubmit(event.submitter);
        }
    });
}
