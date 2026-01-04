<?php

global $view;
$current_page = $view->getPage();

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


<div id="view-dashboard" class="view-section fade-in space-y-6 md:space-y-8 max-w-7xl mx-auto">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i data-lucide="inbox" class="w-6 h-6"></i></div>
            <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Nepřiřazené</p>
                <p class="text-2xl md:text-3xl font-bold text-slate-800">0</p></div>
        </div>
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div class="p-3 bg-orange-50 text-orange-600 rounded-xl"><i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Moje úkoly</p>
                <p class="text-2xl md:text-3xl font-bold text-slate-800">0</p></div>
        </div>
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i data-lucide="check-circle"
                                                                      class="w-6 h-6"></i></div>
            <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Vyřešeno</p>
                <p class="text-2xl md:text-3xl font-bold text-slate-800">0</p></div>
        </div>
        <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
            <div class="p-3 bg-red-50 text-red-600 rounded-xl"><i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Po termínu</p>
                <p class="text-2xl md:text-3xl font-bold text-red-600">0</p></div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between gap-4 md:items-center bg-white p-2 md:p-4 rounded-2xl shadow-soft border border-slate-100 sticky top-0 md:static z-10">
        <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
            <?php foreach ($pages as $name => $data): ?>
                <button onclick="window.location.href = '<?php echo $data["link"] ?>'"
                        class="filter-btn flex-1 md:flex-none whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-lg <?php if ($data["value"] == $current_page): ?>bg-white shadow-sm text-slate-800<?php else: ?>text-slate-500 hover:text-slate-800<?php endif; ?> transition-all">
                    <?php echo $name ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-64 group">
                <i data-lucide="search"
                   class="absolute left-3 top-2.5 text-slate-400 w-4 h-4 group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" oninput="handleSearch(this)" placeholder="Hledat..."
                       class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border-transparent focus:bg-white border focus:border-blue-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner">
            </div>
            <select onchange="handleSort(this)"
                    class="hidden md:block text-sm border border-slate-200 rounded-xl px-3 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer hover:border-blue-300 transition-colors">
                <option value="newest">Nejnovější</option>
                <option value="oldest">Nejstarší</option>
                <option value="priority">Priorita</option>
                <option value="deadline">Termín</option>
            </select>
        </div>
    </div>