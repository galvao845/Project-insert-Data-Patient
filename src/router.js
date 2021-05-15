import Vue from 'vue';
import Router from 'vue-router';
import Home from '@/components/Home.vue';
/*
import CadVaccine from '@/components/CadVaccine.vue';
import CadClient from '@/components/CadClient.vue';
import CadVaccinated from '@/components/CadVaccinated.vue';
*/
const VueInputMask = require('vue-inputmask').default;
Vue.use(VueInputMask);

Vue.use(Router);
const router = new Router({
  routes: [
    {
      path: '/',
      name: '',
      component: Home,
    },
    {
      path: '/Home',
      name: 'Home',
      component: Home,
    },
    /*
    {
      path: '/CadVaccine',
      name: 'CadVaccine',
      component: CadVaccine
    },
    {
      path: '/CadClient',
      name: 'CadClient',
      component: CadClient
    },
    {
      path: '/CadVaccinated',
      name: 'CadVaccinated',
      component: CadVaccinated
    }
    */
  ]
});

export default router;
