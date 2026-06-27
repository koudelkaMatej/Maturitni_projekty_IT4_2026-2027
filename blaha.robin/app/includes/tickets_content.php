<div class="anim-fade-in" style="animation-delay:0.1s">

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-2xl shadow-card border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-[11px] uppercase text-slate-300 font-bold tracking-wider sticky top-0 z-10">
                    <th class="p-3 pl-5 w-16"><span class="flex items-center gap-1.5"><i data-lucide="hash" size="13" class="text-slate-500"></i> ID</span></th>
                    <th class="p-3 min-w-[200px]"><span class="flex items-center gap-1.5"><i data-lucide="file-text" size="13" class="text-slate-500"></i> Předmět</span></th>
                    <th class="p-3 w-28"><span class="flex items-center gap-1.5"><i data-lucide="folder" size="13" class="text-slate-500"></i> Kategorie</span></th>
                    <th class="p-3 w-20"><span class="flex items-center gap-1.5"><i data-lucide="map-pin" size="13" class="text-slate-500"></i> Lokace</span></th>
                    <th class="p-3 w-36"><span class="flex items-center gap-1.5"><i data-lucide="user" size="13" class="text-slate-500"></i> Zadal</span></th>
                    <th class="p-3 w-44"><span class="flex items-center gap-1.5"><i data-lucide="users" size="13" class="text-slate-500"></i> Řešitel</span></th>
                    <th class="p-3 w-24"><span class="flex items-center gap-1.5"><i data-lucide="flag" size="13" class="text-slate-500"></i> Priorita</span></th>
                    <th class="p-3 w-28"><span class="flex items-center gap-1.5"><i data-lucide="calendar" size="13" class="text-slate-500"></i> Termín</span></th>
                    <th class="p-3 w-10"></th>
                </tr>
                </thead>
                <tbody id="ticket-table-body" class="text-sm"></tbody>
            </table>
        </div>
        <div id="pagination-controls" class="hidden px-4 py-3 border-t border-slate-100 flex items-center justify-between"></div>
    </div>

    <!-- Mobile list -->
    <div id="mobile-ticket-list" class="md:hidden space-y-2.5 pb-4"></div>

    <!-- Loading / Empty state -->
    <div id="loading-indicator" class="hidden p-12 text-center">
        <div class="spinner mx-auto mb-3"></div>
        <p class="text-sm text-slate-400">Načítání ticketů...</p>
    </div>

</div>
