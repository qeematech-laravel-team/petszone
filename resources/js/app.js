import './bootstrap';

// Import Bootstrap 5 JavaScript components
import {
    Collapse,
    Dropdown,
    Modal,
    Offcanvas,
    Popover,
    Tab,
    Toast,
    Tooltip,
} from 'bootstrap';

// Import Alpine.js
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Register Alpine.js components BEFORE starting Alpine
document.addEventListener('alpine:init', () => {
    // Search Component
    Alpine.data('searchComponent', () => ({
        query: '',
        results: [],
        isLoading: false,

        async search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }

            this.isLoading = true;
            // Simulate API search
            await new Promise(resolve => setTimeout(resolve, 300));

            this.results = [
                { title: 'Dashboard', url: '/', type: 'page' },
                { title: 'Users', url: '/users', type: 'page' },
                { title: 'Settings', url: '/settings', type: 'page' },
                { title: 'Analytics', url: '/analytics', type: 'page' }
            ].filter(item =>
                item.title.toLowerCase().includes(this.query.toLowerCase())
            );

            this.isLoading = false;
        }
    }));

    // Stats Counter Component
    Alpine.data('statsCounter', (initialValue = 0, increment = 1) => ({
        value: initialValue,

        init() {
            // Auto-increment every 5 seconds
            setInterval(() => {
                this.value += Math.floor(Math.random() * increment) + 1;
            }, 5000);
        }
    }));

    // Theme Switch Component
    Alpine.data('themeSwitch', () => ({
        currentTheme: 'light',

        init() {
            this.currentTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', this.currentTheme);
        },

        toggle() {
            this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', this.currentTheme);
            localStorage.setItem('theme', this.currentTheme);
        }
    }));

    // Quick Add Form Component
    Alpine.data('quickAddForm', () => ({
        itemType: 'task',
        title: '',
        description: '',
        priority: 'medium',
        dateTime: '',
        assignee: '',

        saveItem() {
            const item = {
                type: this.itemType,
                title: this.title,
                description: this.description,
                priority: this.priority,
                dateTime: this.dateTime,
                assignee: this.assignee
            };

            console.log('Saving item:', item);
            
            // Reset form
            this.itemType = 'task';
            this.title = '';
            this.description = '';
            this.priority = 'medium';
            this.dateTime = '';
            this.assignee = '';

            // Show success message (you can integrate with a notification system)
            alert('Item created successfully!');
        }
    }));

    // Icon Demo Component (if needed)
    Alpine.data('iconDemo', () => ({
        currentProvider: 'bootstrap',

        switchProvider(provider) {
            this.currentProvider = provider;
            console.log(`🎨 Switched to ${provider} icons`);
        }
    }));
});

// Start Alpine AFTER registering components
Alpine.start();

// Initialize Bootstrap tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new Popover(popoverTriggerEl);
    });
});
