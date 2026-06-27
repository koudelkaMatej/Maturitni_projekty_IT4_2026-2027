<?php

require_once __DIR__ . "/../tickets/TicketViewPage.php";
require_once __DIR__ . "/../tickets/TicketView.php";

global $view;
$current_user = getApplication()->getUser();

$tickets = [];
$paginator = null;

if ($view instanceof TicketView) {
    $current_page = $view->getPage();
    $page = max(1, (int)($_GET["page"] ?? 1));

    switch ($current_page) {
        case TicketViewPage::All:
            $paginator = getApplication()->getTicketRepository()->getOpenTickets($page);
            $tickets = $paginator->items;
            break;
        case TicketViewPage::Assigned:
            $tickets = getApplication()->getAssignmentRepository()->getActiveAssignedTicketsToUser($current_user->user_id);
            break;
        case TicketViewPage::Closed:
            $paginator = getApplication()->getTicketRepository()->getClosedTickets($page);
            $tickets = $paginator->items;
            break;
    }
}

$users = getApplication()->getUserRepository()->getAllUsers();

?>

<script>
    const tickets = <?php echo json_encode($tickets, JSON_UNESCAPED_UNICODE); ?>;
    const users = <?php echo json_encode($users, JSON_UNESCAPED_UNICODE); ?>;
    const currentUserId = <?php echo $current_user->user_id ?? 0 ?>;
    const pagination = <?php echo json_encode($paginator ? ["page" => $paginator->page, "lastPage" => $paginator->lastPage, "total" => $paginator->total] : null, JSON_UNESCAPED_UNICODE); ?>;

    // ===== Helpers =====

    function api(url, data) {
        const body = new FormData();
        Object.entries(data).forEach(([k, v]) => body.append(k, v));
        return fetch(url, {method: "POST", body}).then(r => r.json());
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    function getInitials(name) {
        if (!name) return '?';
        const caps = name.match(/\p{Lu}/gu);
        const source = caps && caps.length > 0 ? caps.join('') : name;
        return source.slice(0, 2) || '?';
    }

    // ===== Pagination =====

    function goToPage(page) {
        if (!pagination) return;
        if (page < 1 || page > pagination.lastPage) return;
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    }

    function renderPagination() {
        const el = document.getElementById('pagination-controls');
        if (!el) return;
        if (!pagination || pagination.lastPage <= 1) {
            el.classList.add('hidden');
            return;
        }
        el.classList.remove('hidden');
        let html = `<div class="text-sm text-slate-500">${pagination.total} ticketů</div><div class="flex items-center gap-1">`;

        if (pagination.page > 1) {
            html += `<button onclick="goToPage(${pagination.page - 1})" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors active:scale-95">&laquo; Předchozí</button>`;
        }

        for (let p = 1; p <= pagination.lastPage; p++) {
            if (p === pagination.page) {
                html += `<span class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 rounded-lg">${p}</span>`;
            } else if (p === 1 || p === pagination.lastPage || Math.abs(p - pagination.page) <= 2) {
                html += `<button onclick="goToPage(${p})" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors active:scale-95">${p}</button>`;
            } else if (Math.abs(p - pagination.page) === 3) {
                html += `<span class="px-1 text-slate-300">...</span>`;
            }
        }

        if (pagination.page < pagination.lastPage) {
            html += `<button onclick="goToPage(${pagination.page + 1})" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors active:scale-95">Další &raquo;</button>`;
        }

        html += '</div>';
        el.innerHTML = html;
    }

    renderPagination();

    // ===== Ticket list rendering =====

    function renderTickets(data) {
        const tbody = document.getElementById('ticket-table-body');
        const mobileList = document.getElementById('mobile-ticket-list');
        const loading = document.getElementById('loading-indicator');
        if (loading) loading.style.display = 'none';

        const priorityHex = {red: '#ef4444', orange: '#f97316', green: '#22c55e', blue: '#3b82f6', gray: '#94a3b8'};
        const priorityBadge = {red: 'text-red-700 bg-red-50 border-red-200', orange: 'text-orange-700 bg-orange-50 border-orange-200', green: 'text-green-700 bg-green-50 border-green-200', blue: 'text-blue-700 bg-blue-50 border-blue-200', gray: 'text-slate-600 bg-slate-50 border-slate-200'};

        if (!data || data.length === 0) {
            const empty = '<tr><td colspan="9" class="p-16 text-center anim-fade-in"><div class="max-w-xs mx-auto"><div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center"><i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i></div><p class="text-sm font-semibold text-slate-500 mb-1">Žádné tickety</p><p class="text-xs text-slate-400">Jakmile někdo vytvoří ticket, objeví se zde.</p></div></td></tr>';
            if (tbody) tbody.innerHTML = empty;
            if (mobileList) mobileList.innerHTML = '<div class="p-16 text-center anim-fade-in"><div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center"><i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i></div><p class="text-sm font-semibold text-slate-500 mb-1">Žádné tickety</p><p class="text-xs text-slate-400">Jakmile někdo vytvoří ticket, objeví se zde.</p></div>';
            lucide.createIcons();
            return;
        }

        const now = Date.now();

        function statusDot(color) {
            return `<span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5" style="background:${priorityHex[color] || priorityHex.gray}"></span>`;
        }

        function avatar(name) {
            const initials = getInitials(name);
            const colors = ['from-blue-400 to-indigo-500', 'from-emerald-400 to-teal-500', 'from-violet-400 to-purple-500', 'from-amber-400 to-orange-500', 'from-rose-400 to-pink-500'];
            const ci = name ? name.charCodeAt(0) % colors.length : 0;
            return `<span class="inline-flex w-7 h-7 rounded-full bg-gradient-to-br ${colors[ci]} text-white text-[10px] font-bold items-center justify-center flex-shrink-0 shadow-sm ring-2 ring-white" title="${escapeHtml(name)}">${initials}</span>`;
        }

        function assigneeAvatars(namesStr) {
            if (!namesStr) return '';
            const names = namesStr.split(', ');
            if (names.length === 1) {
                return `<div class="flex items-center gap-2">${avatar(names[0])}<span class="text-xs font-medium text-slate-700">${escapeHtml(names[0])}</span></div>`;
            }
            const maxShow = 4;
            const visible = names.slice(0, maxShow);
            const leftover = names.length - maxShow;
            const circles = visible.map(n => avatar(n)).join('');
            const overflow = leftover > 0 ? `<span class="inline-flex w-7 h-7 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold items-center justify-center flex-shrink-0 -ml-1 border-2 border-white shadow-sm">+${leftover}</span>` : '';
            return `<div class="flex items-center avatar-stack" title="${escapeHtml(namesStr)}">${circles}${overflow}</div>`;
        }

        const rows = data.map(t => {
            const originName = t.teacher_name || 'Neznámý';
            const catName = t.category_name || '—';
            const roomName = t.room_name || '—';
            const priorityName = t.priority_name || '—';
            const priorityColor = t.priority_color || 'gray';
            const deadlineRaw = t.ticket_deadline;
            const assigneeNames = t.assignee_names || null;
            const assigneeCount = parseInt(t.assignee_count || 0);
            const borderColor = priorityHex[priorityColor] || priorityHex.gray;

            const created = new Date(t.ticket_creation).getTime();
            const isNew = (now - created) < 86400000;

            let deadlineHtml = '<span class="text-slate-400">—</span>';
            if (deadlineRaw) {
                const deadlineDate = new Date(deadlineRaw + 'T23:59:59').getTime();
                const diffDays = Math.ceil((deadlineDate - now) / 86400000);
                if (diffDays < 0) {
                    deadlineHtml = `<span class="text-red-600 font-semibold text-xs flex items-center gap-1"><i data-lucide="alert-triangle" size="12" class="text-red-500"></i> ${deadlineRaw}</span>`;
                } else if (diffDays === 0) {
                    deadlineHtml = `<span class="text-amber-600 font-semibold text-xs flex items-center gap-1"><i data-lucide="clock" size="12" class="text-amber-500"></i> ${deadlineRaw}</span>`;
                } else if (diffDays <= 3) {
                    deadlineHtml = `<span class="text-amber-600 text-xs flex items-center gap-1">${deadlineRaw}</span>`;
                } else {
                    deadlineHtml = `<span class="text-slate-600 text-xs">${deadlineRaw}</span>`;
                }
            }

            const assigneeCell = assigneeCount > 0
                ? assigneeAvatars(assigneeNames)
                : `<button onclick="event.stopPropagation();selfAssign(${t.ticket_id})" class="text-xs font-semibold text-blue-600 hover:text-white hover:bg-blue-600 px-2.5 py-1 rounded-lg border border-blue-200 hover:border-blue-600 transition-all active:scale-95">+ Přiřadit se</button>`;

            const badgeClass = priorityBadge[priorityColor] || priorityBadge.gray;

            const newBadge = isNew ? `<span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full ml-2 pulse-glow"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Nový</span>` : '';

            return `<tr class="group cursor-pointer transition-all duration-150 hover:shadow-card-hover hover:-translate-y-0.5 anim-fade-in" style="border-left:4px solid ${borderColor}" onclick="openDetail(${t.ticket_id})">
                <td class="pl-5 pr-2 py-4 align-top"><span class="font-mono text-xs text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md">#${t.ticket_id}</span></td>
                <td class="px-2 py-4 align-top">
                    <div class="flex items-start gap-1">
                        <p class="font-semibold text-slate-800 text-sm leading-snug">${escapeHtml(t.ticket_title)}${newBadge}</p>
                    </div>
                    ${t.ticket_description ? `<p class="text-xs text-slate-400 mt-0.5 leading-relaxed max-w-md overflow-hidden line-clamp-2">${escapeHtml(t.ticket_description)}</p>` : ''}
                </td>
                <td class="px-2 py-4 align-top"><span class="inline-flex text-xs font-medium text-slate-600 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">${catName}</span></td>
                <td class="px-2 py-4 align-top"><span class="text-xs font-medium text-slate-500">${roomName}</span></td>
                <td class="px-2 py-4 align-top"><span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">${avatar(originName)} ${escapeHtml(originName)}</span></td>
                <td class="px-2 py-4 align-top">${assigneeCell}</td>
                <td class="px-2 py-4 align-top"><span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-lg border ${badgeClass}">${statusDot(priorityColor)}${priorityName}</span></td>
                <td class="px-2 py-4 align-top">${deadlineHtml}</td>
                <td class="pr-5 pl-2 py-4 align-top text-right"><span class="inline-flex w-7 h-7 rounded-lg bg-slate-50 group-hover:bg-slate-100 items-center justify-center transition-colors"><i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors"></i></span></td>
            </tr>`;
        }).join('');

        const mobileCards = data.map(t => {
            const priorityColor = t.priority_color || 'gray';
            const priorityName = t.priority_name || '—';
            const borderColor = priorityHex[priorityColor] || priorityHex.gray;
            const originName = t.teacher_name || 'Neznámý';
            const badgeClass = priorityBadge[priorityColor] || priorityBadge.gray;

            const created = new Date(t.ticket_creation).getTime();
            const isNew = (now - created) < 86400000;
            const newBadge = isNew ? `<span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full flex-shrink-0 pulse-glow">Nový</span>` : '';

            return `<div class="bg-white rounded-xl shadow-card border border-slate-100 p-3.5 cursor-pointer active:scale-[0.98] transition-all duration-150 hover:shadow-card-hover anim-fade-in" style="border-left:3px solid ${borderColor}" onclick="openDetail(${t.ticket_id})">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <p class="font-semibold text-slate-800 text-sm truncate">${escapeHtml(t.ticket_title)}</p>
                        ${newBadge}
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 flex-shrink-0 ml-2"></i>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="font-mono text-slate-400 flex-shrink-0">#${t.ticket_id}</span>
                    <span class="text-slate-300">·</span>
                    <span class="truncate">${t.category_name || '—'}</span>
                    <span class="text-slate-300">·</span>
                    <span class="text-xs font-bold px-1.5 py-0.5 rounded border ${badgeClass} flex-shrink-0">${statusDot(priorityColor)}${priorityName}</span>
                </div>
            </div>`;
        }).join('');

        if (tbody) tbody.innerHTML = rows;
        if (mobileList) mobileList.innerHTML = mobileCards;
        lucide.createIcons();
    }

    renderTickets(tickets);

    // ===== Search & Sort =====

    function handleSearch(input) {
        const q = input.value.toLowerCase();
        const filtered = tickets.filter(t =>
            (t.ticket_title && t.ticket_title.toLowerCase().includes(q)) ||
            (t.ticket_description && t.ticket_description.toLowerCase().includes(q)) ||
            (t.teacher_name && t.teacher_name.toLowerCase().includes(q)) ||
            (t.category_name && t.category_name.toLowerCase().includes(q)) ||
            (t.room_name && t.room_name.toLowerCase().includes(q))
        );
        renderTickets(filtered);
    }

    function handleSort(select) {
        const val = select.value;
        const sorted = [...tickets];
        if (val === 'newest') sorted.sort((a, b) => new Date(b.ticket_creation) - new Date(a.ticket_creation));
        else if (val === 'oldest') sorted.sort((a, b) => new Date(a.ticket_creation) - new Date(b.ticket_creation));
        else if (val === 'priority') sorted.sort((a, b) => (b.priority_weight || 0) - (a.priority_weight || 0));
        else if (val === 'deadline') sorted.sort((a, b) => (a.ticket_deadline || '9999') > (b.ticket_deadline || '9999') ? 1 : -1);
        renderTickets(sorted);
    }

    function refreshTicketList() {
        renderTickets(tickets);
        renderPagination();
    }

    // ===== Detail Modal =====

    function openDetail(ticketId) {
        const modal = document.getElementById('ticket-detail-modal');
        const inner = document.getElementById('ticket-modal-inner');
        const content = document.getElementById('ticket-modal-content');
        if (!modal || !inner) return;

        // Show modal
        modal.style.visibility = 'visible';
        modal.classList.remove('hidden');

        // Animate backdrop
        requestAnimationFrame(() => {
            modal.style.opacity = '1';

            // Animate panel
            if (content) {
                content.style.transform = '';
                content.style.transition = 'transform 0.35s cubic-bezier(0.16, 1, 0.3, 1)';
            }
        });

        // Loading state
        inner.innerHTML = '<div class="flex-1 flex items-center justify-center p-12"><div class="text-center"><div class="spinner mx-auto mb-4"></div><p class="text-sm text-slate-400">Načítání detailu ticketu...</p></div></div>';
        lucide.createIcons();

        reloadDetail(ticketId);
    }

    function reloadDetail(ticketId) {
        return fetch("app/ajax/ticket_json.php?id=" + ticketId)
            .then(r => r.json())
            .then(d => { renderDetail(d); return d; });
    }

    function renderDetail(d) {
        const t = d.ticket;
        const inner = document.getElementById('ticket-modal-inner');
        if (!inner) return;

        const isOpen = t.ticket_is_open == 1;
        const assignedIds = d.assignments.map(a => a.assignment_user);
        const isAssignedToMe = assignedIds.includes(currentUserId);

        const catOptions = d.categories.map(c =>
            `<option value="${c.category_id}" ${c.category_id == t.ticket_category ? 'selected' : ''}>${c.category_name}</option>`
        ).join('');
        const roomOptions = d.rooms.map(r =>
            `<option value="${r.room_id}" ${r.room_id == t.ticket_room ? 'selected' : ''}>${r.room_name}</option>`
        ).join('');
        const priOptions = d.priorities.map(p =>
            `<option value="${p.priority_id}" ${p.priority_id == t.ticket_priority ? 'selected' : ''}>${p.priority_name}</option>`
        ).join('');

        const logRows = d.work_log.map(w =>
            `<div class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0 group anim-fade-in">
                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 flex items-center justify-center text-xs font-bold shadow-sm">${getInitials(w.teacher_name)}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-700">${escapeHtml(w.teacher_name || '—')}</p>
                    <p class="text-sm text-slate-500">${escapeHtml(w.work_description)}</p>
                    <p class="text-xs text-slate-400 mt-0.5">${w.work_minutes} min</p>
                </div>
                <div class="flex-shrink-0 flex gap-1 items-start pt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="editWorkLog(${t.ticket_id}, ${w.work_id}, '${encodeURIComponent(w.work_description || '')}', ${w.work_minutes})" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all active:scale-90" title="Upravit"><i data-lucide="pencil" size="14"></i></button>
                    <button onclick="deleteWorkLog(${t.ticket_id}, ${w.work_id})" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all active:scale-90" title="Smazat"><i data-lucide="trash-2" size="14"></i></button>
                </div>
            </div>`
        ).join('');

        const totalMinutes = d.work_log.reduce((sum, w) => sum + parseInt(w.work_minutes || 0), 0);

        inner.innerHTML = `
            <div class="flex-shrink-0 bg-gradient-to-r ${isOpen ? 'from-blue-600 to-indigo-600' : 'from-slate-600 to-slate-700'} text-white">
                <div class="px-5 sm:px-6 pt-5 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono text-white/60">#${t.ticket_id}</span>
                                ${isOpen
                                    ? '<span class="text-[11px] bg-green-400/20 text-green-200 px-2 py-0.5 rounded-full font-semibold border border-green-400/30">Otevřený</span>'
                                    : '<span class="text-[11px] bg-white/10 text-white/70 px-2 py-0.5 rounded-full font-semibold">Uzavřený</span>'}
                            </div>
                            <h3 class="text-lg font-bold leading-tight">${escapeHtml(t.ticket_title) || 'Bez předmětu'}</h3>
                            <p class="text-xs text-white/60 mt-1">${new Date(t.ticket_creation).toLocaleString('cs-CZ')}</p>
                        </div>
                        <button onclick="closeDetail()" class="flex-shrink-0 text-white/50 hover:text-white hover:bg-white/10 p-1.5 rounded-lg transition-all active:scale-90 ml-3"><i data-lucide="x" size="18"></i></button>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-5 space-y-4 sm:space-y-5">

                    <div class="bg-slate-50 rounded-xl p-4">
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Popis</h4>
                        <p class="text-sm text-slate-700 leading-relaxed">${escapeHtml(t.ticket_description) || '<span class="text-slate-400 italic">Bez popisu</span>'}</p>
                    </div>

                    <div>
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Vlastnosti</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-200 transition-colors">
                                <label class="text-[10px] text-slate-400 font-semibold uppercase block mb-1">Kategorie</label>
                                <select onchange="updateTicketField(${t.ticket_id}, 'ticket_category', this.value)" class="w-full text-sm bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">${catOptions}</select>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-200 transition-colors">
                                <label class="text-[10px] text-slate-400 font-semibold uppercase block mb-1">Místnost</label>
                                <select onchange="updateTicketField(${t.ticket_id}, 'ticket_room', this.value)" class="w-full text-sm bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">${roomOptions}</select>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-200 transition-colors">
                                <label class="text-[10px] text-slate-400 font-semibold uppercase block mb-1">Priorita</label>
                                <select onchange="updateTicketField(${t.ticket_id}, 'ticket_priority', this.value)" class="w-full text-sm bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">${priOptions}</select>
                            </div>
                            <div class="bg-white border border-slate-200 rounded-lg p-3 hover:border-blue-200 transition-colors">
                                <label class="text-[10px] text-slate-400 font-semibold uppercase block mb-1">Termín</label>
                                <input type="date" value="${t.ticket_deadline || ''}" onchange="updateTicketField(${t.ticket_id}, 'ticket_deadline', this.value)" class="w-full text-sm bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                            </div>
                            <div class="bg-white border border-slate-200 rounded-lg p-3 col-span-2 hover:border-blue-200 transition-colors">
                                <label class="text-[10px] text-slate-400 font-semibold uppercase block mb-1">Zadal</label>
                                <p class="text-sm font-medium text-slate-700">${escapeHtml(t.teacher_name || '—')}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Řešitelé</h4>
                            ${!isAssignedToMe && isOpen
                                ? `<button onclick="selfAssignFromDetail(${t.ticket_id})" class="text-xs font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 transition-all active:scale-95"><i data-lucide="user-plus" size="14"></i> Přiřadit se</button>`
                                : ''}
                        </div>
                        <div class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100">
                            ${d.assignments.map(a => {
                                const isMe = a.assignment_user === currentUserId;
                                return `<div class="flex items-center justify-between px-4 py-2.5 ${isMe ? 'bg-blue-50/50' : ''} anim-fade-in">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br ${isMe ? 'from-blue-400 to-indigo-500 shadow-sm' : 'from-slate-300 to-slate-400'} text-white flex items-center justify-center text-[10px] font-bold ring-2 ring-white">${getInitials(a.teacher_name)}</div>
                                        <span class="text-sm ${isMe ? 'font-bold text-blue-700' : 'text-slate-700'}">${escapeHtml(a.teacher_name)}${isMe ? ' (já)' : ''}</span>
                                    </div>
                                    <button onclick="removeAssignee(${t.ticket_id}, ${a.assignment_user})" class="text-xs text-red-400 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-lg transition-all active:scale-90" title="Odebrat"><i data-lucide="x" size="14"></i></button>
                                </div>`;
                            }).join('')}
                            ${d.assignments.length === 0
                                ? '<div class="px-4 py-3 text-sm text-slate-400 italic text-center">Nikdo není přiřazen</div>'
                                : ''}
                        </div>
                        <div class="flex gap-2 mt-2">
                            <select id="assign-user-select" class="flex-1 text-sm p-2.5 rounded-lg border border-slate-200 bg-white hover:border-blue-200 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all">
                                <option value="">-- Přidat řešitele --</option>
                                ${d.all_users.map(u => `<option value="${u.user_id}">${escapeHtml(u.teacher_name)}</option>`).join('')}
                            </select>
                            <button onclick="addAssignee(${t.ticket_id})" class="px-4 py-2.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-all active:scale-95 font-medium">Přidat</button>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Přípisy práce${totalMinutes > 0 ? ` <span class="text-slate-300 font-normal">(${totalMinutes} min celkem)</span>` : ''}</h4>
                        <div id="work-log-list" class="bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 px-4">
                            ${logRows || '<div class="py-4 text-sm text-slate-400 italic text-center">Zatím žádné přípisy</div>'}
                        </div>
                        <div class="mt-3 flex gap-2">
                            <input type="number" id="work-minutes" min="1" placeholder="Min" class="w-20 sm:w-24 text-sm p-2.5 rounded-lg border border-slate-200 bg-white hover:border-blue-200 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all">
                            <input type="text" id="work-description" placeholder="Popis práce" class="flex-1 text-sm p-2.5 rounded-lg border border-slate-200 bg-white hover:border-blue-200 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all">
                            <button onclick="addWorkLog(${t.ticket_id})" class="px-4 py-2.5 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition-all active:scale-95 font-medium flex items-center gap-1"><i data-lucide="plus" size="14"></i> <span class="hidden sm:inline">Přidat</span></button>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-1">
                        ${isOpen
                            ? `<button onclick="closeTicket(${t.ticket_id})" class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition-all active:scale-95 text-sm flex items-center justify-center gap-2"><i data-lucide="check-circle" size="16"></i> Uzavřít ticket</button>`
                            : `<button onclick="reopenTicket(${t.ticket_id})" class="flex-1 px-4 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold transition-all active:scale-95 text-sm flex items-center justify-center gap-2"><i data-lucide="rotate-ccw" size="16"></i> Znovu otevřít</button>`}
                    </div>

                </div>
            </div>
        `;
        lucide.createIcons();
    }

    function closeDetail() {
        const modal = document.getElementById('ticket-detail-modal');
        const content = document.getElementById('ticket-modal-content');
        if (!modal) return;

        // Animate out
        modal.style.opacity = '0';
        if (content) {
            content.style.transform = 'translateY(100%)';
        }

        // Hide after animation
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.visibility = 'hidden';
            if (content) {
                content.style.transform = '';
                content.style.transition = '';
            }
        }, 300);
    }

    let editingWorkId = null;

    function editWorkLog(ticketId, workId, descriptionEncoded, minutes) {
        const description = decodeURIComponent(descriptionEncoded);
        const list = document.getElementById('work-log-list');
        if (!list) return;
        editingWorkId = workId;
        list.innerHTML = `
            <div class="py-4 space-y-2 anim-fade-in">
                <div class="flex gap-2">
                    <input type="number" id="edit-work-minutes" value="${minutes}" min="1" class="w-20 sm:w-24 text-sm p-2.5 rounded-lg border border-slate-200 bg-white hover:border-blue-200 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all">
                    <input type="text" id="edit-work-description" value="${escapeHtml(description)}" class="flex-1 text-sm p-2.5 rounded-lg border border-slate-200 bg-white hover:border-blue-200 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/10 transition-all">
                </div>
                <div class="flex gap-2">
                    <button onclick="saveWorkLog(${ticketId})" class="px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition-all active:scale-95 font-medium">Uložit</button>
                    <button onclick="reloadDetail(${ticketId})" class="px-4 py-2 bg-slate-200 text-slate-700 text-sm rounded-lg hover:bg-slate-300 transition-all active:scale-95 font-medium">Zrušit</button>
                </div>
            </div>
        `;
        lucide.createIcons();
    }

    function saveWorkLog(ticketId) {
        const minutes = document.getElementById('edit-work-minutes').value;
        const desc = document.getElementById('edit-work-description').value;
        if (!minutes || !desc || !editingWorkId) return;
        api("app/ajax/worklog.php", {action: "update", work_id: editingWorkId, minutes, description: desc})
            .then(r => { if (r.success) { editingWorkId = null; syncTicket(ticketId).then(() => reloadDetail(ticketId)); } });
    }

    function deleteWorkLog(ticketId, workId) {
        if (!confirm('Opravdu chcete smazat tento přípis?')) return;
        api("app/ajax/worklog.php", {action: "delete", work_id: workId})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => reloadDetail(ticketId)); });
    }

    // ===== Ticket Actions =====

    function selfAssign(ticketId) {
        api("app/ajax/assign.php", {ticket_id: ticketId, user_id: currentUserId, action: "add"})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => refreshTicketList()); });
    }

    function selfAssignFromDetail(ticketId) {
        api("app/ajax/assign.php", {ticket_id: ticketId, user_id: currentUserId, action: "add"})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => { reloadDetail(ticketId); refreshTicketList(); }); });
    }

    function updateTicketField(ticketId, field, value) {
        api("app/ajax/updateticket.php", {ticket_id: ticketId, [field]: value})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => refreshTicketList()); });
    }

    function syncTicket(ticketId) {
        return fetch("app/ajax/ticket_json.php?id=" + ticketId)
            .then(r => r.json())
            .then(d => {
                const idx = tickets.findIndex(t => t.ticket_id === ticketId);
                if (idx !== -1) {
                    tickets[idx] = d.ticket;
                }
                return d;
            });
    }

    function addAssignee(ticketId) {
        const sel = document.getElementById('assign-user-select');
        const userId = sel.value;
        if (!userId) return;
        api("app/ajax/assign.php", {ticket_id: ticketId, user_id: userId, action: "add"})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => { reloadDetail(ticketId); refreshTicketList(); }); });
    }

    function removeAssignee(ticketId, userId) {
        api("app/ajax/assign.php", {ticket_id: ticketId, user_id: userId, action: "remove"})
            .then(r => { if (r.success) syncTicket(ticketId).then(() => { reloadDetail(ticketId); refreshTicketList(); }); });
    }

    function addWorkLog(ticketId) {
        const minutes = document.getElementById('work-minutes').value;
        const desc = document.getElementById('work-description').value;
        if (!minutes || !desc) return;
        api("app/ajax/worklog.php", {action: "add", ticket_id: ticketId, user_id: currentUserId, minutes, description: desc})
            .then(r => { if (r.success) { document.getElementById('work-minutes').value = ''; document.getElementById('work-description').value = ''; syncTicket(ticketId).then(() => { reloadDetail(ticketId); refreshTicketList(); }); } });
    }

    function closeTicket(ticketId) {
        if (!confirm('Opravdu chcete uzavřít tento ticket?')) return;
        api("app/ajax/closeticket.php", {ticket_id: ticketId, action: "close"})
            .then(r => { if (r.success) { closeDetail(); location.reload(); } });
    }

    function reopenTicket(ticketId) {
        api("app/ajax/closeticket.php", {ticket_id: ticketId, action: "reopen"})
            .then(r => { if (r.success) { closeDetail(); location.reload(); } });
    }

    // ===== Keyboard =====

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDetail();
    });
</script>
<?php if (isset($_GET["detail"])): ?>
    <script>openDetail(<?php echo $_GET["detail"] ?>)</script>
    <script>
        if (history.replaceState) {
            var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({path: cleanUrl}, "", cleanUrl);
        }
    </script>
<?php endif; ?>
