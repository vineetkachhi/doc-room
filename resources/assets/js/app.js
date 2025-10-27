
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.Ably = require('ably');

const app = new Vue({
    el: '#root',
    components: {
      'add-options-popout': require('./components/add-options-popout.vue').default,
      'settings-popout': require('./components/settings-popout.vue').default,
      'background-player': require('./components/background-player.vue').default,
      'dropdown': require('./components/dropdown.vue').default,

      'rooms-view': require('./components/rooms-view.vue').default,
    },
});
