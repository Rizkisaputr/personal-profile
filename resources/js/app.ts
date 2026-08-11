import { createInertiaApp } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import LoginLayout from '@/layouts/auth/LoginLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/public/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import { initializeUiLocalization, translateUi } from '@/lib/uiLocalization';

const appName = 'Rizki Saputra';

createInertiaApp({
    title: (title) => {
        const pageTitle = title ? translateUi(title).trim() : '';

        if (!pageTitle || pageTitle === appName) {
            return appName;
        }

        return pageTitle.endsWith(`| ${appName}`)
            ? pageTitle
            : `${pageTitle} | ${appName}`;
    },
    layout: (name) => {
        switch (true) {
            case name === 'auth/Login':
                return LoginLayout;
            case name.startsWith('public/'):
                return PublicLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#315CFF',
    },
});

// The product currently has one deliberate, light-first visual identity.
document.documentElement.classList.remove('dark');

// This will listen for flash toast data from the server...
initializeFlashToast();
initializeUiLocalization();
