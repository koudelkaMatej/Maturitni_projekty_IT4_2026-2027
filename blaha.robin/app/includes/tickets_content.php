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
    <div id="loading-indicator" class="p-8 text-center text-slate-400 animate-bounce">Načítání ticketů...</div>
</div>