import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// PWA Install prompt
import './pwa/install-prompt';

// Push notification manager
import './pwa/push-manager';
