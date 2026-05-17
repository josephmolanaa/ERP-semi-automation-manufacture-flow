<script>
    (() => {
        const mode = localStorage.getItem('erp-theme-mode') || localStorage.getItem('theme') || 'light';
        const isDark = mode === 'dark';

        document.documentElement.classList.toggle('dark', isDark);
        document.documentElement.dataset.erpTheme = isDark ? 'dark' : 'light';
    })();
</script>
