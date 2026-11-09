<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

global $view;
$current_page = $view->getPage();

$current_user = getApplication()->getUser();
$unassigned_count = getApplication()->getTicketRepository()->getCountUnassigned();
$my_tasks_count = getApplication()->getTicketRepository()->getCountActiveAssignedToUser($current_user->user_id);
$closed_count = (int)(getApplication()->getTicketRepository()->getCountByStatus()["closed"] ?? 0);
$overdue_count = getApplication()->getTicketRepository()->getCountOverdue();

$pages = [
        "Všechny tickety" => [
                "value" => TicketViewPage::All,
                "link" => "dashboard.php",
        ],
        "Moje tickety" => [
                "value" => TicketViewPage::Assigned,
                "link" => "assigned.php",
        ],
        "Uzavřené tickety" => [
                "value" => TicketViewPage::Closed,
                "link" => "closed.php",
        ],
];

?>

<div id="view-dashboard" class="anim-fade-in space-y-4 sm:space-y-6 max-w-7xl mx-auto">

    <!-- Stats cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-5">
        <div class="group bg-white p-3 sm:p-4 lg:p-5 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 hover-lift cursor-default">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300"><i data-lucide="inbox" class="w-5 h-5 sm:w-6 sm:h-6"></i></div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Nepřiřazené</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 tabular-nums"><?php echo $unassigned_count ?></p>
                </div>
            </div>
        </div>
        <div class="group bg-white p-3 sm:p-4 lg:p-5 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 hover-lift cursor-default">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-orange-50 text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-all duration-300"><i data-lucide="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i></div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Moje úkoly</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 tabular-nums"><?php echo $my_tasks_count ?></p>
                </div>
            </div>
        </div>
        <div class="group bg-white p-3 sm:p-4 lg:p-5 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 hover-lift cursor-default">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-green-50 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-all duration-300"><i data-lucide="check-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i></div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Vyřešeno</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 tabular-nums"><?php echo $closed_count ?></p>
                </div>
            </div>
        </div>
        <div class="group bg-white p-3 sm:p-4 lg:p-5 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 hover-lift cursor-default">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="p-2.5 sm:p-3 rounded-xl bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-all duration-300"><i data-lucide="alert-circle" class="w-5 h-5 sm:w-6 sm:h-6"></i></div>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Po termínu</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 tabular-nums"><?php echo $overdue_count ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters bar -->
    <div class="flex flex-col sm:flex-row justify-between gap-3 sm:items-center bg-white p-2 sm:p-3 lg:p-4 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 sticky top-0 sm:static z-10">
        <!-- View tabs -->
        <div class="flex bg-slate-100 p-1 rounded-xl w-full sm:w-auto overflow-x-auto scrollbar-none">
            <?php foreach ($pages as $name => $data): ?>
                <button onclick="window.location.href = '<?php echo $data["link"] ?>'"
                        class="flex-1 sm:flex-none whitespace-nowrap px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-150 <?php if ($data["value"] == $current_page): ?>bg-white shadow-sm text-slate-800<?php else: ?>text-slate-500 hover:text-slate-800<?php endif; ?>">
                    <?php echo $name ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Search + Sort + Filter toggle -->
        <div class="flex gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-56 lg:w-64 group">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 group-focus-within:text-blue-500 transition-colors pointer-events-none"></i>
                <input type="text" id="ticket-search-input" oninput="handleSearch(this)" placeholder="Hledat tickety..."
                       class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-transparent focus:bg-white focus:border-blue-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>
            <select id="ticket-sort-select" onchange="handleSort(this)"
                    class="hidden sm:block text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-300 transition-colors appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E')] bg-[length:12px] bg-[right_10px_center] bg-no-repeat pr-8">
                <option value="created:desc">Nejnovější</option>
                <option value="created:asc">Nejstarší</option>
                <option value="priority:desc">Priorita</option>
                <option value="deadline:asc">Termín</option>
                <option value="title:asc">Předmět (A-Z)</option>
                <option value="category:asc">Kategorie</option>
                <option value="origin:asc">Zadal</option>
            </select>
            <button type="button" onclick="toggleFilterPanel()" id="filter-toggle-btn"
                    class="flex-shrink-0 relative text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center gap-1.5 font-medium text-slate-600">
                <i data-lucide="sliders-horizontal" size="15"></i> <span class="hidden lg:inline">Filtry</span>
                <span id="filter-active-dot"
                      class="hidden absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-blue-500 ring-2 ring-white"></span>
            </button>
        </div>
    </div>

    <!-- Filter panel -->
    <div id="filter-panel"
         class="hidden bg-white p-3 sm:p-4 rounded-xl sm:rounded-2xl shadow-card border border-slate-100 anim-fade-in">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3">
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Kategorie</label>
                <select id="filter-category" onchange="applyFilters()"
                        class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 cursor-pointer">
                    <option value="">Všechny</option>
                    <?php foreach ($view->getCategories() as $c): ?>
                        <option value="<?php echo $c->category_id ?>"><?php echo htmlspecialchars($c->category_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Místnost</label>
                <select id="filter-room" onchange="applyFilters()"
                        class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 cursor-pointer">
                    <option value="">Všechny</option>
                    <?php foreach ($view->getRooms() as $r): ?>
                        <option value="<?php echo $r->room_id ?>"><?php echo htmlspecialchars($r->room_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Priorita</label>
                <select id="filter-priority" onchange="applyFilters()"
                        class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 cursor-pointer">
                    <option value="">Všechny</option>
                    <?php foreach ($view->getPriorities() as $p): ?>
                        <option value="<?php echo $p->priority_id ?>"><?php echo htmlspecialchars($p->priority_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1 block">Řešitel</label>
                <select id="filter-assignee" onchange="applyFilters()"
                        class="w-full text-sm border border-slate-200 rounded-lg px-2.5 py-2 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 cursor-pointer">
                    <option value="">Všichni</option>
                    <option value="unassigned">Nepřiřazené</option>
                    <?php foreach ($view->getUsers() as $u): ?>
                        <option value="<?php echo $u->user_id ?>"><?php echo htmlspecialchars($u->teacher_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-2.5 text-right">
            <button type="button" onclick="clearFilters()"
                    class="text-xs font-semibold text-slate-500 hover:text-red-600 transition-colors">Vymazat filtry
            </button>
        </div>
    </div>
