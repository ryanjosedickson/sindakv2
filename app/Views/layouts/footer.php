</main>

<footer class="site-footer">
    &copy;2026 Sistem Informasi Ditjen Bimas Kristen
</footer>

<script>
    (function () {
        // Dropdown nama pegawai (Akun / Logout)
        var userMenu = document.getElementById('userMenu');
        var userMenuButton = document.getElementById('userMenuButton');

        userMenuButton.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenu.classList.toggle('is-open');
        });

        document.addEventListener('click', function (e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('is-open');
            }
        });

        // Navbar hamburger -> tampilkan/sembunyikan panel menu
        var hamburgerButton = document.getElementById('hamburgerButton');
        var siteNavbar = document.getElementById('siteNavbar');
        var navbarPanel = document.getElementById('navbarPanel');

        hamburgerButton.addEventListener('click', function () {
            var isOpen = navbarPanel.classList.toggle('is-open');
            siteNavbar.classList.toggle('is-open', isOpen);
            hamburgerButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    })();
</script>

</body>
</html>
