import './axios';
import Alpine from 'alpinejs';
import { initExam } from './exam';

window.Alpine = Alpine;
Alpine.start();

const examRoot = document.getElementById('exam');
if (examRoot) {
    initExam(examRoot);
}
