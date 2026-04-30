import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import router from './router';
import AppRoot from './AppRoot.vue';

const app = createApp({
    render: () => h(AppRoot),
});

app.use(router);
app.mount('#app');
