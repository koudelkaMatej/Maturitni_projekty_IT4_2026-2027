<?php

$current_user = getApplication()->getUser();
$current_name = $current_user->teacher_name;
$current_username = $current_user->user_username;
$current_initials = getApplication()->getInitials($current_name);

$page_name = getApplication()->getPageName();

$pages = [
        "Přehled ticketů" => [
                "has_border" => false,
                "link" => "dashboard.php",
                "icon" => "layout-dashboard",
        ],
        "Vytvořit ticket" => [
                "has_border" => false,
                "link" => "create.php",
                "icon" => "plus-circle",
        ],
        "Moje statistiky" => [
                "has_border" => false,
                "link" => "stats.php",
                "icon" => "bar-chart-2",
        ],
        "Správa systému" => [
                "has_border" => false,
                "link" => "manage.php",
                "icon" => "settings",
        ],
        "Změna hesla" => [
                "has_border" => true,
                "link" => "password.php",
                "icon" => "lock",
        ],
];

?>

<!-- Mobile top bar -->
<div class="md:hidden fixed top-0 inset-x-0 bg-slate-900/95 backdrop-blur text-white z-40 px-4 h-14 flex items-center justify-between shadow-lg border-b border-slate-800">
    <div class="flex items-center gap-3">
        <button onclick="toggleMobileMenu()" class="p-2 -ml-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 active:scale-95 transition-all" aria-label="Menu">
            <i data-lucide="menu" size="22"></i>
        </button>
        <div class="flex items-center gap-2.5">
            <div class="bg-blue-600 p-1.5 rounded-lg shadow-glow">
                <i data-lucide="ticket" class="text-white w-4 h-4"></i>
            </div>
            <span class="font-bold text-base tracking-tight">SPŠ HelpDesk</span>
        </div>
    </div>
    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-xs text-white border-2 border-blue-400/50 shadow-lg flex-shrink-0"><?php echo $current_initials ?></div>
</div>

<!-- Overlay -->
<div id="mobile-sidebar-overlay" onclick="closeMobileMenu()"
     class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-300 md:hidden"></div>

<!-- Sidebar (shared desktop + mobile) -->
<aside id="sidebar"
       class="fixed md:static inset-y-0 left-0 w-72 bg-slate-900 text-white z-50 flex flex-col shadow-2xl transition-all duration-300 ease-out md:translate-x-0 -translate-x-full">

    <!-- Header -->
    <div class="p-5 border-b border-slate-800 flex items-center justify-between md:justify-start">
        <h1 class="text-lg font-bold flex items-center gap-3 tracking-tight">
            <div class="bg-blue-600 p-1.5 rounded-lg shadow-glow flex-shrink-0">
                <i data-lucide="ticket" class="text-white w-5 h-5"></i>
            </div>
            <span class="hidden md:inline">SPŠ HelpDesk</span>
        </h1>
        <button onclick="closeMobileMenu()" class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
            <i data-lucide="x" size="20"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 space-y-1 overflow-y-auto overflow-x-hidden">
        <?php foreach ($pages as $name => $data): ?>
            <?php if ($data["has_border"]): ?>
                <div class="pt-3 mt-2 border-t border-slate-800/60"></div>
            <?php endif; ?>
            <button onclick="window.location.href='<?php echo $data["link"] ?>'"
                    class="w-full flex items-center gap-3 px-3.5 py-2.5 min-h-11 rounded-xl text-sm font-medium transition-all duration-150 active:scale-[0.97] <?php if ($page_name == $name): ?>bg-blue-600 text-white shadow-lg shadow-blue-900/30<?php else: ?>text-slate-400 hover:bg-slate-800 hover:text-white<?php endif; ?>">
                <i data-lucide="<?php echo $data["icon"] ?>" size="20" class="flex-shrink-0"></i>
                <span><?php echo $name ?></span>
            </button>
        <?php endforeach; ?>
    </nav>

    <!-- User footer -->
    <div class="p-4 border-t border-slate-800 bg-slate-900/60">
        <div class="flex items-center gap-3 px-1 mb-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white text-xs shadow-lg border-2 border-slate-800 flex-shrink-0"><?php echo $current_initials ?></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate leading-tight"><?php echo $current_username ?></p>
                <p class="text-xs text-slate-400 truncate"><?php echo $current_name ?></p>
            </div>
        </div>
        <button onclick="window.location.href='logout.php'"
                class="w-full flex items-center justify-center gap-2 text-xs font-semibold text-red-400 hover:text-white hover:bg-red-500/20 py-2.5 rounded-xl transition-all active:scale-95">
            <i data-lucide="log-out" size="15"></i> Odhlásit se
        </button>
    </div>
</aside>

<!-- Main area wrapper -->
<main class="flex-1 flex flex-col h-dvh overflow-hidden relative bg-slate-50 md:ml-0 pt-14 md:pt-0">

    <!-- Desktop top bar -->
    <header class="hidden md:flex bg-white/90 backdrop-blur border-b border-slate-200 h-16 items-center justify-between px-6 lg:px-8 sticky top-0 z-10">
        <h2 id="page-title" class="text-lg lg:text-xl font-bold text-slate-800 tracking-tight"><?php echo getApplication()->getPageName() ?></h2>
    </header>

    <!-- Scrollable content area -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden p-3 sm:p-4 md:p-6 lg:p-8 scroll-smooth">
