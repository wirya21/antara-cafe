import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import menuApp from './menu';
import cashierApp from './cashier';
import displayApp from './display';
import orderAlerts from './order-alerts';

window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.data('menuApp', menuApp);
Alpine.data('cashierApp', cashierApp);
Alpine.data('displayApp', displayApp);
Alpine.data('orderAlerts', orderAlerts);

Alpine.start();
