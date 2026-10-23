import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import menuApp from './menu';

window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.data('menuApp', menuApp);

Alpine.start();
