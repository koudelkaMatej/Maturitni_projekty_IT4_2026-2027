<?php

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();
getApplication()->setPageName("Přehled ticketů");

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <!-- DASHBOARD VIEW -->
    <div id="view-dashboard" class="view-section fade-in space-y-6 md:space-y-8 max-w-7xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
            <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i data-lucide="inbox" class="w-6 h-6"></i></div>
                <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Otevřené</p>
                    <p class="text-2xl md:text-3xl font-bold text-slate-800">12</p></div>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="p-3 bg-orange-50 text-orange-600 rounded-xl"><i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Moje</p>
                    <p class="text-2xl md:text-3xl font-bold text-slate-800">4</p></div>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="p-3 bg-green-50 text-green-600 rounded-xl"><i data-lucide="check-circle"
                                                                          class="w-6 h-6"></i></div>
                <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Vyřešeno</p>
                    <p class="text-2xl md:text-3xl font-bold text-slate-800">28</p></div>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl shadow-soft border border-slate-100 flex flex-col md:flex-row items-start md:items-center gap-3 transition-transform hover:-translate-y-1 hover:shadow-lg">
                <div class="p-3 bg-red-50 text-red-600 rounded-xl"><i data-lucide="alert-circle" class="w-6 h-6"></i>
                </div>
                <div><p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Po termínu</p>
                    <p class="text-2xl md:text-3xl font-bold text-red-600">2</p></div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between gap-4 md:items-center bg-white p-2 md:p-4 rounded-2xl shadow-soft border border-slate-100 sticky top-0 md:static z-10">
            <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
                <button onclick="setFilter('all')"
                        class="filter-btn flex-1 md:flex-none whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-lg bg-white shadow-sm text-slate-800 transition-all">
                    Všechny
                </button>
                <button onclick="setFilter('mine')"
                        class="filter-btn flex-1 md:flex-none whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all">
                    Moje tikety
                </button>
                <button onclick="setFilter('closed')"
                        class="filter-btn flex-1 md:flex-none whitespace-nowrap px-4 py-2 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-800 transition-all">
                    Dokončené
                </button>
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

        <div class="bg-white md:rounded-2xl md:shadow-soft md:border md:border-slate-200 overflow-hidden bg-transparent">
            <table class="w-full text-left border-collapse hidden md:table">
                <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200 text-xs uppercase text-slate-400 font-bold tracking-wider">
                    <th class="p-5">ID</th>
                    <th class="p-5">Předmět / Popis</th>
                    <th class="p-5">Kategorie</th>
                    <th class="p-5">Lokace</th>
                    <th class="p-5">Zadal</th>
                    <th class="p-5">Řešitel</th>
                    <th class="p-5">Priorita</th>
                    <th class="p-5">Termín</th>
                    <th class="p-5"></th>
                </tr>
                </thead>
                <tbody id="ticket-table-body" class="text-sm divide-y divide-slate-100"></tbody>
            </table>
            <div id="mobile-ticket-list" class="md:hidden space-y-3 pb-20"></div>
        </div>
    </div>

    <!-- TICKET DETAIL MODAL -->
    <div id="ticket-detail-modal" class="hidden fixed inset-0 z-50 transition-opacity">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetail()"></div>
        <div class="absolute inset-x-0 bottom-0 md:inset-y-0 md:left-auto md:right-0 bg-white w-full md:w-[600px] h-[90vh] md:h-full rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none shadow-2xl overflow-y-auto transform transition-transform duration-300 translate-y-full md:translate-y-0 md:translate-x-full flex flex-col"
             id="ticket-modal-content">

            <div class="md:hidden w-full flex justify-center pt-3 pb-1 flex-shrink-0">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
            </div>

            <!-- Dynamic Content will be injected here -->
            <div id="ticket-modal-inner" class="flex flex-col h-full"></div>

        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>