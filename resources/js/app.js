// Alpine is provided and initialized by Livewire.

// Firebase FCM Import (only import, initialization is handled in firebase-config.js)
import './firebase-config.js';

// AOS (Animate On Scroll) Library Import
import AOS from 'aos';
import 'aos/dist/aos.css';

// Initialize AOS with custom configuration
AOS.init({
    duration: 800,
    offset: 100,
    once: false,
    mirror: true,
});
