/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import {loadStripe} from '@stripe/stripe-js/pure';
import VuejsDialog from 'vuejs-dialog';
import VuejsDialogMixin from 'vuejs-dialog/dist/vuejs-dialog-mixin.min.js'; // only needed in custom components

// include the default style
import 'vuejs-dialog/dist/vuejs-dialog.min.css';

// const stripe = await loadStripe('pk_test_5b20haUIW3xRYL1M64O9bsCl008DBREz6Z');

require('./bootstrap');

window.Vue = require('vue');
// Tell Vue to install the plugin.
Vue.use(VuejsDialog);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('add-attribute', require('./components/AddAttribute.vue').default);
Vue.component('add-to-wishlist', require('./components/AddToWishlist.vue').default);
Vue.component('wishlist', require('./components/Wishlist.vue').default);
Vue.component('admin-notifications', require('./components/AdminNotifications.vue').default);
Vue.component('delete-image', require('./components/DeleteImage.vue').default);
Vue.component('categories', require('./components/Categories.vue').default);
Vue.component('create-product', require('./components/CreateProduct.vue').default);
Vue.component('flash', require('./components/Flash.vue').default);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
