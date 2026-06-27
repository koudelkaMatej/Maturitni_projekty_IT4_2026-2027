<?php

global $view;
$current_page = $view->getPage();

$current_user = getApplication()->getUser();
$unassigned_count = getApplication()->getTicketRepository()->getCountUnassigned();
$my_tickets = getApplication()->getAssignmentRepository()->getActiveAssignedTicketsToUser($current_user->user_id);
$my_tasks_count = $my_tickets ? count($my_tickets) : 0;
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

        <!-- Search + Sort -->
        <div class="flex gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-56 lg:w-64 group">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 group-focus-within:text-blue-500 transition-colors pointer-events-none"></i>
                <input type="text" oninput="handleSearch(this)" placeholder="Hledat tickety..."
                       class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-transparent focus:bg-white focus:border-blue-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
            </div>
            <select onchange="handleSort(this)"
                    class="hidden sm:block text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-300 transition-colors appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2394a3b8%22%20stroke-width%3D%222%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E')] bg-[length:12px] bg-[right_10px_center] bg-no-repeat pr-8">
                <option value="newest">Nejnovější</option>
                <option value="oldest">Nejstarší</option>
                <option value="priority">Priorita</option>
                <option value="deadline">Termín</option>
            </select>
        </div>
    </div>
