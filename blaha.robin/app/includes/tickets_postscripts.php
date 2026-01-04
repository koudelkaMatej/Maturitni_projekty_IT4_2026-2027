<?php if (isset($_GET["detail"])): ?>
    <script>openDetail(<?php echo $_GET["detail"] ?>)</script>
    <script>
        if (history.replaceState) {
            var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, "", cleanUrl);
        }
    </script>
<?php endif; ?>