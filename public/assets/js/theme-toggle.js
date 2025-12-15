(function () {
    const STORAGE_KEY = 'gch-theme';
    const root = document.documentElement;
    const toggleButtons = document.querySelectorAll('.js-theme-toggle');

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        document.body.classList.remove('theme-dark', 'theme-light');
        document.body.classList.add(`theme-${theme}`);
        toggleButtons.forEach((btn) => {
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            btn.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
        });
    }

    function initTheme() {
        const stored = localStorage.getItem(STORAGE_KEY);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = stored || (prefersDark ? 'dark' : 'light');
        applyTheme(theme);
    }

    function toggleTheme() {
        const current = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(STORAGE_KEY, next);
        applyTheme(next);
    }

    toggleButtons.forEach((btn) => btn.addEventListener('click', toggleTheme));
    initTheme();
})();
