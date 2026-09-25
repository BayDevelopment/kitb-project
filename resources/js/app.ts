import { createInertiaApp } from "@inertiajs/vue3";
import { initializeTheme } from "@/composables/useAppearance";
import AppLayout from "@/layouts/AppLayout.vue";
import SettingsLayout from "@/layouts/settings/Layout.vue";
import { initializeFlashToast } from "@/lib/flashToast";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

void createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),

  layout: (name) => {
    switch (true) {
      // Halaman publik tanpa layout
      case name === "Welcome":
      case name === "Index":
      case name === "auth/Login":
        return null;

      // Halaman settings
      case name.startsWith("settings/"):
        return [AppLayout, SettingsLayout];

      // Halaman admin
      case name.startsWith("Admin/"):
        return AppLayout;

      // Default
      default:
        return AppLayout;
    }
  },

  withApp: (app) => {
    app.directive("focus", {
      mounted: (el: HTMLElement, shouldFocus) => {
        if (shouldFocus.value !== false) {
          el.focus();
        }
      },
    });
  },

  progress: {
    color: "#4B5563",
  },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
