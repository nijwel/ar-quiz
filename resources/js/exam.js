/**
 * Exam runner with anti-cheat protection.
 *
 * Once the exam starts it is submitted automatically when the student:
 *   - switches tab or minimizes the browser   (tab_hidden)
 *   - moves focus to another window or app    (window_blur)
 *   - closes, reloads or navigates away       (page_close)
 *   - leaves fullscreen                       (fullscreen_exit)
 * Copy, paste, right-click, text selection and common shortcuts are blocked.
 *
 * The time limit comes from the server (the attempt's ends_at), so changing the
 * page or local storage cannot extend it.
 */

// Ignore focus changes right after starting, while the browser switches to fullscreen
const GUARD_DELAY_MS = 1500;

// Ctrl/Cmd + key combinations that are blocked during the exam
const BLOCKED_SHORTCUTS = ['a', 'c', 'f', 'p', 's', 'u', 'v', 'x'];

const SUBMIT_MESSAGES = {
    manual: 'উত্তর জমা হচ্ছে…',
    time_up: 'সময় শেষ! উত্তর জমা হচ্ছে…',
    tab_hidden: 'আপনি অন্য ট্যাবে গিয়েছেন। পরীক্ষা জমা হয়ে যাচ্ছে।',
    window_blur: 'আপনি পরীক্ষার উইন্ডো ছেড়ে গিয়েছেন। পরীক্ষা জমা হয়ে যাচ্ছে।',
    fullscreen_exit: 'আপনি ফুলস্ক্রিন থেকে বের হয়েছেন। পরীক্ষা জমা হয়ে যাচ্ছে।',
    page_close: 'পরীক্ষা জমা হয়ে যাচ্ছে।',
};

export function initExam(root) {
    const config = root.dataset;
    const find = (name) => root.querySelector(`[data-field="${name}"]`);
    const screen = (name) => root.querySelector(`[data-screen="${name}"]`);

    const state = {
        running: false,
        submitted: false,
        endsAt: 0,
        guardsActiveFrom: 0,
        enteredFullscreen: false,
        timerId: null,
        form: null,
    };

    // ---------- Start / resume ----------

    const agree = find('agree');
    const startButton = root.querySelector('[data-action="start"]');

    agree?.addEventListener('change', () => {
        startButton.disabled = !agree.checked;
    });

    startButton?.addEventListener('click', startExam);

    root.querySelector('[data-action="resume"]')?.addEventListener('click', () => {
        enterFullscreen();
        beginExam(Number(config.resumeSeconds));
    });

    async function startExam() {
        startButton.disabled = true;
        // Must be requested directly inside the click, before any await
        enterFullscreen();

        try {
            const response = await fetch(config.startUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'পরীক্ষা শুরু করা যায়নি।');
            }

            find('questions').innerHTML = data.html;
            beginExam(data.remaining_seconds);
        } catch (error) {
            const message = find('start-error');
            message.textContent = error.message;
            message.classList.remove('hidden');
            startButton.disabled = !agree.checked;
            exitFullscreen();
        }
    }

    function beginExam(seconds) {
        screen('start')?.classList.add('hidden');
        screen('resume')?.classList.add('hidden');
        screen('exam').classList.remove('hidden');
        document.querySelectorAll('[data-exam-hide]').forEach((element) => element.classList.add('hidden'));

        state.form = root.querySelector('#quizForm');
        state.form.addEventListener('submit', (event) => {
            event.preventDefault();
            askToSubmit();
        });
        find('questions').addEventListener('change', updateProgress);
        updateProgress();

        state.running = true;
        state.endsAt = Date.now() + seconds * 1000;
        state.guardsActiveFrom = Date.now() + GUARD_DELAY_MS;
        tick();
        state.timerId = setInterval(tick, 500);

        armGuards();
    }

    // ---------- Timer and progress ----------

    function tick() {
        const remaining = Math.max(0, Math.round((state.endsAt - Date.now()) / 1000));
        const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
        const seconds = String(remaining % 60).padStart(2, '0');
        find('timer').textContent = `${minutes}:${seconds}`;

        if (remaining < 60) {
            const box = find('timer-box');
            box.classList.remove('bg-volt-400', 'text-snow');
            box.classList.add('bg-white', 'text-volt-500', 'animate-pulse');
        }

        if (remaining === 0) {
            submitExam('time_up');
        }
    }

    function answeredQuestionIds() {
        const checked = state.form.querySelectorAll('input[type="radio"]:checked');
        return new Set([...checked].map((radio) => radio.dataset.question));
    }

    function updateProgress() {
        const answered = answeredQuestionIds();
        const total = Number(config.totalQuestions) || 1;

        find('answered').textContent = answered.size;
        find('progress').style.width = `${(answered.size / total) * 100}%`;

        root.querySelectorAll('[data-nav]').forEach((link) => {
            const done = answered.has(link.dataset.nav);
            ['bg-brand-600', 'text-snow', 'ring-brand-600'].forEach((name) => link.classList.toggle(name, done));
            ['bg-white/5', 'text-ink-400', 'ring-white/10'].forEach((name) => link.classList.toggle(name, !done));
        });
    }

    // ---------- Manual submit with an in-page confirmation ----------

    function askToSubmit() {
        const unanswered = Number(config.totalQuestions) - answeredQuestionIds().size;
        find('confirm-text').textContent = unanswered > 0
            ? `${unanswered}টি প্রশ্নের উত্তর এখনো দেওয়া হয়নি। জমা দেওয়ার পর আর পরিবর্তন করা যাবে না।`
            : 'সব প্রশ্নের উত্তর দেওয়া হয়েছে। জমা দেওয়ার পর আর পরিবর্তন করা যাবে না।';
        toggleScreen('confirm', true);
    }

    root.querySelector('[data-action="cancel-submit"]').addEventListener('click', () => toggleScreen('confirm', false));
    root.querySelector('[data-action="confirm-submit"]').addEventListener('click', () => submitExam('manual'));

    // ---------- Anti-cheat guards ----------

    function armGuards() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) violation('tab_hidden');
        });
        window.addEventListener('blur', () => violation('window_blur'));
        window.addEventListener('pagehide', () => violation('page_close'));

        document.addEventListener('fullscreenchange', () => {
            if (document.fullscreenElement) {
                state.enteredFullscreen = true;
            } else if (state.enteredFullscreen) {
                violation('fullscreen_exit');
            }
        });

        ['copy', 'cut', 'paste', 'contextmenu', 'dragstart', 'selectstart'].forEach((type) => {
            document.addEventListener(type, blockWhileRunning);
        });
        document.addEventListener('keydown', blockShortcuts);
    }

    function violation(reason) {
        if (!state.running || state.submitted || Date.now() < state.guardsActiveFrom) {
            return;
        }
        submitExam(reason);
    }

    function blockWhileRunning(event) {
        if (state.running) event.preventDefault();
    }

    function blockShortcuts(event) {
        if (!state.running) return;

        const key = event.key.toLowerCase();
        const withModifier = event.ctrlKey || event.metaKey;
        const devTools = key === 'f12' || (withModifier && event.shiftKey && ['i', 'j', 'c'].includes(key));

        if (devTools || (withModifier && BLOCKED_SHORTCUTS.includes(key))) {
            event.preventDefault();
            event.stopPropagation();
        }
    }

    // ---------- Submitting ----------

    function submitExam(reason) {
        if (state.submitted) return;

        state.submitted = true;
        state.running = false;
        clearInterval(state.timerId);

        toggleScreen('confirm', false);
        find('submitting-text').textContent = SUBMIT_MESSAGES[reason] ?? '';
        toggleScreen('submitting', true);

        const data = new FormData(state.form);
        data.set('reason', reason);

        // The page is going away: a beacon is the only request guaranteed to be sent
        if (reason === 'page_close') {
            navigator.sendBeacon(config.submitUrl, data);
            return;
        }

        fetch(config.submitUrl, {
            method: 'POST',
            body: data,
            keepalive: true,
            headers: { Accept: 'application/json' },
        })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then(({ redirect }) => {
                exitFullscreen();
                window.location.replace(redirect);
            })
            .catch(() => {
                // Fall back to a normal form post so the answers are never lost
                const field = Object.assign(document.createElement('input'), { type: 'hidden', name: 'reason', value: reason });
                state.form.append(field);
                HTMLFormElement.prototype.submit.call(state.form);
            });
    }

    // ---------- Helpers ----------

    function toggleScreen(name, visible) {
        const element = screen(name);
        element.classList.toggle('hidden', !visible);
        element.classList.toggle('grid', visible);
    }

    function enterFullscreen() {
        document.documentElement.requestFullscreen?.().catch(() => {
            // Not supported (e.g. iPhone Safari) or refused: the other guards still apply
        });
    }

    function exitFullscreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(() => {});
        }
    }
}
