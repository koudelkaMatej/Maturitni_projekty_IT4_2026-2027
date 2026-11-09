<div class="anim-fade-in" style="animation-delay:0.1s">

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-card border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-[11px] uppercase text-slate-300 font-bold tracking-wider sticky top-0 z-10">
                    <th class="p-3 pl-5 w-16 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('id')"><span class="flex items-center gap-1.5"><i data-lucide="hash" size="13"
                                                                                           class="text-slate-500"></i> ID <span
                                    data-sort-arrow="id"></span></span></th>
                    <th class="p-3 min-w-[200px] cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('title')"><span class="flex items-center gap-1.5"><i data-lucide="file-text"
                                                                                              size="13"
                                                                                              class="text-slate-500"></i> Předmět <span
                                    data-sort-arrow="title"></span></span></th>
                    <th class="p-3 w-28 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('category')"><span class="flex items-center gap-1.5"><i data-lucide="folder"
                                                                                                 size="13"
                                                                                                 class="text-slate-500"></i> Kategorie <span
                                    data-sort-arrow="category"></span></span></th>
                    <th class="p-3 w-20 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('room')"><span class="flex items-center gap-1.5"><i data-lucide="map-pin"
                                                                                             size="13"
                                                                                             class="text-slate-500"></i> Lokace <span
                                    data-sort-arrow="room"></span></span></th>
                    <th class="p-3 w-36 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('origin')"><span class="flex items-center gap-1.5"><i data-lucide="user"
                                                                                               size="13"
                                                                                               class="text-slate-500"></i> Zadal <span
                                    data-sort-arrow="origin"></span></span></th>
                    <th class="p-3 w-44"><span class="flex items-center gap-1.5"><i data-lucide="users" size="13" class="text-slate-500"></i> Řešitel</span></th>
                    <th class="p-3 w-24 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('priority')"><span class="flex items-center gap-1.5"><i data-lucide="flag"
                                                                                                 size="13"
                                                                                                 class="text-slate-500"></i> Priorita <span
                                    data-sort-arrow="priority"></span></span></th>
                    <th class="p-3 w-28 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('deadline')"><span class="flex items-center gap-1.5"><i data-lucide="calendar"
                                                                                                 size="13"
                                                                                                 class="text-slate-500"></i> Termín <span
                                    data-sort-arrow="deadline"></span></span></th>
                    <th class="p-3 w-28 cursor-pointer select-none hover:text-white transition-colors"
                        onclick="setSort('created')"><span class="flex items-center gap-1.5"><i
                                    data-lucide="calendar-plus" size="13" class="text-slate-500"></i> Vytvořeno <span
                                    data-sort-arrow="created"></span></span></th>
                    <th class="p-3 w-10"></th>
                </tr>
                </thead>
                <tbody id="ticket-table-body" class="text-sm"></tbody>
            </table>
        </div>
        <div id="load-more-container"
             class="hidden px-4 py-4 border-t border-slate-100 flex flex-col items-center gap-2">
            <p id="load-more-count" class="text-xs text-slate-400"></p>
            <button onclick="loadMoreTickets()" id="load-more-btn"
                    class="px-5 py-2 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-200 hover:border-blue-600 rounded-xl transition-all active:scale-95">
                Načíst další
            </button>
        </div>
        <div id="load-more-sentinel" class="h-1"></div>
    </div>

    <!-- Mobile list -->
    <div id="mobile-ticket-list" class="md:hidden space-y-2.5"></div>
    <div id="mobile-load-more-container" class="hidden md:hidden px-2 py-4 flex flex-col items-center gap-2">
        <p id="mobile-load-more-count" class="text-xs text-slate-400"></p>
        <button onclick="loadMoreTickets()"
                class="w-full px-5 py-2.5 text-sm font-semibold text-blue-600 hover:text-white hover:bg-blue-600 border border-blue-200 hover:border-blue-600 rounded-xl transition-all active:scale-95">
            Načíst další
        </button>
    </div>

    <!-- Loading / Empty state -->
    <div id="loading-indicator" class="p-12 text-center">
        <div class="spinner mx-auto mb-3"></div>
        <p class="text-sm text-slate-400">Načítání ticketů...</p>
    </div>

</div>
