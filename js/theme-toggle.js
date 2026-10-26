(function () {
    var KEY = 'petstagram-theme';

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem(KEY, theme); } catch (e) {}
        updateButtons();
    }

    function updateButtons() {
        var dark = getTheme() === 'dark';
        var label = dark ? '라이트 모드로 전환' : '다크 모드로 전환';
        document.querySelectorAll('.theme-toggle-btn').forEach(function (btn) {
            btn.textContent = dark ? '☀️' : '🌙';
            btn.setAttribute('aria-label', label);
            btn.title = label;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateButtons();
        document.querySelectorAll('.theme-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTheme(getTheme() === 'dark' ? 'light' : 'dark');
            });
        });
    });
})();
