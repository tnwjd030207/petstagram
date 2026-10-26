(function () {
    try {
        var stored = localStorage.getItem('petstagram-theme');
        var theme = (stored === 'dark' || stored === 'light')
            ? stored
            : (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    } catch (e) {}
})();
