
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));
Vue.component('login', require('./components/Login.vue'));
Vue.component('navbar', require('./components/Navbar.vue'));
Vue.component('game-schedule', require('./components/Schedule.vue'));
Vue.component('game-bets', require('./components/GameBets.vue'));
Vue.component('game-display', require('./components/GameDisplay.vue'));
Vue.component('leader-board', require('./components/LeaderBoard.vue'));
Vue.component('place-bet', require('./components/PlaceBet.vue'));

const app = new Vue({
    el: '#app',
    // data: {
    //     shared: store,
    // }
});
