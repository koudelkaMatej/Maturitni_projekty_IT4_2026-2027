<?php

$current_name = getApplication()->getUser()["teacher_name"];
$current_username = getApplication()->getUser()["user_username"];
$current_initials = getApplication()->getInitials(getApplication()->getUser()["teacher_name"]);

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
        "Změna hesla" => [
                "has_border" => true,
                "link" => "password.php",
                "icon" => "lock",
        ],
];

?>

<aside id="desktop-sidebar"
       class="w-64 bg-slate-900 text-white flex-shrink-0 flex-col transition-all duration-300 hidden md:flex z-20 shadow-xl">
    <div class="p-6 border-b border-slate-800">
        <h1 class="text-xl font-bold flex items-center gap-3 tracking-tight">
            <div class="bg-blue-600 p-1.5 rounded-lg shadow-glow">
                <i data-lucide="ticket" class="text-white w-5 h-5"></i>
            </div>
            SPŠ HelpDesk
        </h1>
    </div>
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <?php foreach ($pages as $name => $data): ?>
            <?php if ($data["has_border"]): ?>
                <div class="pt-4 mt-2 border-t border-slate-800/50">
            <?php endif; ?>
            <button onclick="window.location.href='<?php echo $data["link"] ?>'"
                    class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl <?php if ($page_name == $name): ?>bg-blue-600 text-white shadow-lg shadow-blue-900/20<?php else: ?>text-slate-400 hover:bg-slate-800 hover:text-white<?php endif; ?> transition-all hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="<?php echo $data["icon"] ?>" size="20"></i><span
                        class="font-medium"><?php echo $name ?></span>
            </button>
            <?php if ($data["has_border"]): ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-slate-800 bg-slate-900/50">
        <div class="flex items-center gap-3 px-2">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg border-2 border-slate-800"><?php echo $current_initials ?></div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate"><?php echo $current_username ?></p>
                <p class="text-xs text-slate-400 truncate"><?php echo $current_name ?></p>
            </div>
        </div>
        <button onclick="window.location.href='logout.php'"
                class="mt-4 w-full flex items-center justify-center gap-2 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-400/10 py-2 rounded-lg transition-colors">
            <i data-lucide="log-out" size="14"></i> Odhlásit se
        </button>
    </div>
</aside>

<div id="mobile-sidebar-overlay" onclick="toggleMobileMenu()"
     class="fixed inset-0 bg-black/60 z-40 hidden backdrop-blur-sm transition-opacity md:hidden"></div>
<aside id="mobile-sidebar"
       class="fixed inset-y-0 left-0 w-72 bg-slate-900 text-white z-50 transform -translate-x-full transition-transform duration-300 md:hidden flex flex-col shadow-2xl">
    <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900">
        <h1 class="text-xl font-bold flex items-center gap-2">SPŠ HelpDesk</h1>
        <button onclick="toggleMobileMenu()" class="text-slate-400 p-1 hover:text-white transition-colors"><i
                    data-lucide="x"></i></button>
    </div>
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <?php foreach ($pages as $name => $data): ?>
            <?php if ($data["has_border"]): ?>
                <div class="pt-4 mt-2 border-t border-slate-800/50">
            <?php endif; ?>
            <button onclick="window.location.href='<?php echo $data["link"] ?>'"
                    class="nav-item w-full flex items-center gap-3 px-4 py-3 rounded-xl <?php if ($page_name == $name): ?>bg-blue-600 text-white shadow-lg shadow-blue-900/20<?php else: ?>text-slate-400 hover:bg-slate-800 hover:text-white<?php endif; ?> transition-all hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="<?php echo $data["icon"] ?>" size="20"></i><span
                        class="font-medium"><?php echo $name ?></span>
            </button>
            <?php if ($data["has_border"]): ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="p-4 border-t border-slate-800 bg-slate-900">
        <div class="flex items-center gap-3 px-2 mb-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white shadow-lg border-2 border-slate-800"><?php echo $current_initials ?></div>
            <div><p class="text-sm font-semibold text-white"><?php echo $current_username ?></p>
                <p class="text-xs text-slate-400"><?php echo $current_name ?></p></div>
        </div>
        <button onclick="window.location.href='logout.php'"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-800 text-red-400 font-medium hover:bg-red-500 hover:text-white transition-all">
            <i data-lucide="log-out" size="18"></i> Odhlásit se
        </button>
    </div>
</aside>

<div class="md:hidden fixed top-0 w-full bg-slate-900/95 backdrop-blur text-white z-30 px-4 py-3 flex justify-between items-center shadow-md">
    <div class="flex items-center gap-3">
        <button onclick="toggleMobileMenu()" class="p-1 text-slate-300 hover:text-white"><i data-lucide="menu"
                                                                                            size="24"></i></button>
        <h1 class="font-bold text-lg flex items-center gap-2"><i data-lucide="ticket" class="text-blue-400 w-5 h-5"></i>
            SPŠ HelpDesk</h1>
    </div>
    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-xs border border-blue-400"><?php echo $current_initials ?></div>
</div>

<main class="flex-1 flex flex-col h-full overflow-hidden relative md:ml-0 mt-14 md:mt-0 bg-slate-50">
    <header class="hidden md:flex bg-white/80 backdrop-blur border-b border-slate-200 h-16 items-center justify-between px-8 sticky top-0 z-10">
        <h2 id="page-title"
            class="text-xl font-bold text-slate-800 tracking-tight"><?php echo getApplication()->getPageName() ?></h2>
    </header>

    <div class="flex-1 overflow-auto p-4 md:p-8 relative scroll-smooth">