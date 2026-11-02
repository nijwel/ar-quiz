import './axios';
import Alpine from 'alpinejs';
import { initExam } from './exam';
import loadMore from './load-more';

window.Alpine = Alpine;
Alpine.data('loadMore', loadMore);

// Remembers a list's layout ('grid' or 'list') in the browser
Alpine.data('viewToggle', (storageKey, fallback = 'grid') => ({
    view: localStorage.getItem(storageKey) || fallback,
    set(view) {
        this.view = view;
        localStorage.setItem(storageKey, view);
    },
}));
Alpine.start();

const examRoot = document.getElementById('exam');
if (examRoot) {
    initExam(examRoot);
}
