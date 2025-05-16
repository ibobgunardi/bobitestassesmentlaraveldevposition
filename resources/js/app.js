import "./bootstrap";
import Auth from "./auth";
// import { initLoginPage } from './pages/auth/login';
// import { initNavigationSearch } from './pages/navigation-search';
import { getCsrfToken, refreshCsrfToken } from "./csrf";

// Make Auth available globally
window.Auth = Auth;

// For dark mode support
document.addEventListener("DOMContentLoaded", () => {
    // Initialize page-specific JavaScript
    initNavigationSearch();

    // Initialize dark mode from localStorage or system preference
    const darkMode = localStorage.getItem("darkMode");
    if (darkMode === null) {
        // Check user preference
        if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
            document.documentElement.classList.add("dark");
            localStorage.setItem("darkMode", "true");
        } else {
            localStorage.setItem("darkMode", "false");
        }
    } else if (darkMode === "true") {
        document.documentElement.classList.add("dark");
    }

    // Listen for changes to system preferences
    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", (e) => {
            const newDarkMode = e.matches;
            if (newDarkMode) {
                document.documentElement.classList.add("dark");
                localStorage.setItem("darkMode", "true");
            } else {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("darkMode", "false");
            }
        });
});
