<script>
    (() => {
        const storageKey = 'erp-theme-mode';
        const legacyKey = 'theme';

        const applyTheme = (mode) => {
            const isDark = mode === 'dark';

            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.dataset.erpTheme = isDark ? 'dark' : 'light';
            localStorage.setItem(storageKey, isDark ? 'dark' : 'light');
            localStorage.setItem(legacyKey, isDark ? 'dark' : 'light');

            document.querySelectorAll('[data-erp-theme-toggle]').forEach((button) => {
                button.setAttribute('aria-pressed', String(isDark));
                button.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            });
        };

        const getTheme = () => (
            localStorage.getItem(storageKey) ||
            localStorage.getItem(legacyKey) ||
            'light'
        );

        const boot = () => {
            applyTheme(getTheme());

            document.querySelectorAll('[data-erp-theme-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot, { once: true });
        } else {
            boot();
        }
    })();
</script>
