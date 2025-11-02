/**
 * Load all JS dependencies including Vue and other libraries.
 */

require('./bootstrap');
window.Ably = require('ably');

// Vue 3 import
import { createApp } from 'vue';

// Components import
import AddOptionsPopout from './components/add-options-popout.vue';
import SettingsPopout from './components/settings-popout.vue';
import BackgroundPlayer from './components/background-player.vue';
import Dropdown from './components/dropdown.vue';
import RoomsView from './components/rooms-view.vue';

// vuedraggable Vue 3 compatible version
import Draggable from 'vuedraggable';

import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// Create Vue app
const app = createApp({});

// Register components globally
app.component('add-options-popout', AddOptionsPopout);
app.component('settings-popout', SettingsPopout);
app.component('background-player', BackgroundPlayer);
app.component('dropdown', Dropdown);
app.component('rooms-view', RoomsView);
app.component('draggable', Draggable);  // Vue 3 compatible draggable

// Mount Vue app
app.mount('#root');
