<script>
    lucide.createIcons();

    // Mobile sidebar
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');
        const isOpen = sidebar && sidebar.classList.contains('translate-x-0') && !sidebar.classList.contains('-translate-x-full');
        if (isOpen) { closeMobileMenu(); }
        else { openMobileMenu(); }
    }
    function openMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');
        if (sidebar) { sidebar.classList.remove('-translate-x-full'); sidebar.classList.add('translate-x-0'); }
        if (overlay) { overlay.classList.remove('hidden'); setTimeout(() => overlay.classList.remove('opacity-0'), 10); }
        document.body.style.overflow = 'hidden';
    }
    function closeMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');
        if (sidebar) { sidebar.classList.remove('translate-x-0'); sidebar.classList.add('-translate-x-full'); }
        if (overlay) { overlay.classList.add('opacity-0'); setTimeout(() => overlay.classList.add('hidden'), 300); }
        document.body.style.overflow = '';
    }

    // Modal helpers (global overrides for animated versions)
    const _origOpenDetail = window.openDetail;
    const _origCloseDetail = window.closeDetail;
</script>
</div>
</main>
</body>
</html>
