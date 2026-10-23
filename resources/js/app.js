import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import menuApp from './menu';
import cashierApp from './cashier';
import displayApp from './display';
import orderAlerts from './order-alerts';
import swipeConfirm from './swipe-confirm';
import orderTracker from './order-tracker';
import orderHistory from './order-history';
import orderBoard from './order-board';

window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.data('menuApp', menuApp);
Alpine.data('cashierApp', cashierApp);
Alpine.data('displayApp', displayApp);
Alpine.data('orderAlerts', orderAlerts);
Alpine.data('swipeConfirm', swipeConfirm);
Alpine.data('orderTracker', orderTracker);
Alpine.data('orderHistory', orderHistory);
Alpine.data('orderBoard', orderBoard);

Alpine.start();
