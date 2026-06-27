<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();
getApplication()->setPageName("Statistiky");

$current_user = getApplication()->getUser();

$all_users = getApplication()->getUserRepository()->getAllUsers();

$selected_user_id = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : $current_user->user_id;
$selected_user = null;
foreach ($all_users as $u) {
    if ($u->user_id === $selected_user_id) {
        $selected_user = $u;
        break;
    }
}
if (!$selected_user) {
    $selected_user = $current_user;
    $selected_user_id = $current_user->user_id;
}

$user_stats = getApplication()->getTicketRepository()->getUserStats($selected_user_id);
$total_minutes = getApplication()->getTicketRepository()->getUserTotalWorkMinutes($selected_user_id);
$monthly_minutes = getApplication()->getTicketRepository()->getUserMonthlyWorkMinutes($selected_user_id);
$monthly_stats = getApplication()->getTicketRepository()->getUserMonthlyStats($selected_user_id, 6);

$total_assigned = (int)($user_stats["total_assigned"] ?? 0);
$active = (int)($user_stats["active"] ?? 0);
$resolved = (int)($user_stats["resolved"] ?? 0);

$all_open = (int)(getApplication()->getTicketRepository()->getCountByStatus()["open"] ?? 0);
$unassigned = getApplication()->getTicketRepository()->getCountUnassigned();
$overdue = getApplication()->getTicketRepository()->getCountOverdue();

$is_self = $selected_user_id === $current_user->user_id;
$page_title = $is_self ? "Moje statistiky" : "Statistiky — " . $selected_user->teacher_name;
$initials = getApplication()->getInitials($selected_user->teacher_name);

function avatarColor($name): string {
    $colors = ["bg-blue-500", "bg-emerald-500", "bg-violet-500", "bg-amber-500", "bg-rose-500", "bg-cyan-500", "bg-orange-500", "bg-indigo-500"];
    return $colors[crc32($name) % count($colors)];
}

$max_assigned = 0;
foreach ($monthly_stats as $m) {
    if ($m["assigned"] > $max_assigned) $max_assigned = $m["assigned"];
}
$max_assigned = max($max_assigned, 1);

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <div id="view-stats" class="view-section max-w-5xl mx-auto space-y-6 anim-fade-in">

        <!-- Technician selector -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-4 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full <?php echo avatarColor($selected_user->teacher_name) ?> text-white flex items-center justify-center text-sm font-bold"><?php echo $initials ?></div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900"><?php echo $page_title ?></h2>
                        <p class="text-sm text-slate-500">Statistiky přiřazených ticketů a odpracovaného času</p>
                    </div>
                </div>
                <form method="get" class="flex items-center gap-2">
                    <label for="user-select" class="text-sm text-slate-500 font-medium">Technik:</label>
                    <div class="relative">
                        <select id="user-select" name="user_id" onchange="this.form.submit()"
                                class="appearance-none pl-3 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all cursor-pointer">
                            <?php foreach ($all_users as $u): ?>
                                <option value="<?php echo $u->user_id ?>" <?php echo $u->user_id === $selected_user_id ? "selected" : "" ?>>
                                    <?php echo $u->teacher_name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" size="14" class="absolute right-2.5 top-2.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </form>
            </div>
        </div>

        <!-- Personal overview cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 hover-lift" style="animation: fadeIn 0.4s ease 0.05s forwards; opacity: 0;">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-slate-500">Přiřazeno</p>
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="layers" size="18"></i></div>
                </div>
                <p class="text-3xl font-bold text-slate-900"><?php echo $total_assigned ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 hover-lift" style="animation: fadeIn 0.4s ease 0.1s forwards; opacity: 0;">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-slate-500">Aktivní</p>
                    <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="activity" size="18"></i></div>
                </div>
                <p class="text-3xl font-bold text-slate-900"><?php echo $active ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 hover-lift" style="animation: fadeIn 0.4s ease 0.15s forwards; opacity: 0;">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-slate-500">Vyřešeno</p>
                    <div class="w-9 h-9 rounded-xl bg-green-100 text-green-600 flex items-center justify-center"><i data-lucide="check-circle" size="18"></i></div>
                </div>
                <p class="text-3xl font-bold text-slate-900"><?php echo $resolved ?></p>
            </div>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 hover-lift" style="animation: fadeIn 0.4s ease 0.2s forwards; opacity: 0;">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-slate-500">Úspěšnost</p>
                    <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center"><i data-lucide="target" size="18"></i></div>
                </div>
                <p class="text-3xl font-bold text-slate-900"><?php echo $total_assigned > 0 ? round($resolved / $total_assigned * 100) : 0 ?><span class="text-lg text-slate-400">%</span></p>
            </div>
        </div>

        <!-- Work minutes -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 md:p-6 hover-lift" style="animation: fadeIn 0.4s ease 0.25s forwards; opacity: 0;">
            <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center"><i data-lucide="clock" size="20"></i></div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Odpracováno celkem</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo $total_minutes ?> <span class="text-base font-normal text-slate-400">min</span></p>
                    </div>
                </div>
                <div class="hidden md:block w-px h-10 bg-slate-200"></div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="calendar" size="20"></i></div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Tento měsíc</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo $monthly_minutes ?> <span class="text-base font-normal text-slate-400">min</span></p>
                    </div>
                </div>
                <div class="hidden md:block w-px h-10 bg-slate-200"></div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center"><i data-lucide="clock" size="20"></i></div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Hodin celkem</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo round($total_minutes / 60, 1) ?> <span class="text-base font-normal text-slate-400">hod</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly table -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden" style="animation: fadeIn 0.4s ease 0.3s forwards; opacity: 0;">
            <div class="p-5 md:p-6 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center"><i data-lucide="bar-chart-3" size="20"></i></div>
                    <div>
                        <h3 class="font-bold text-slate-900">Měsíční přehled</h3>
                        <p class="text-sm text-slate-500">Posledních 6 měsíců</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider font-semibold">
                            <th class="text-left px-5 py-3">Měsíc</th>
                            <th class="text-center px-4 py-3">Přiřazeno</th>
                            <th class="text-center px-4 py-3">Vyřešeno</th>
                            <th class="text-center px-4 py-3">Aktivní</th>
                            <th class="text-center px-4 py-3 hidden md:table-cell">Graf</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($monthly_stats as $idx => $m): ?>
                        <?php
                            $bar_pct = $max_assigned > 0 ? round($m["assigned"] / $max_assigned * 100) : 0;
                            $row_delay = 0.35 + ($idx * 0.05);
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors" style="animation: fadeIn 0.3s ease <?php echo $row_delay ?>s forwards; opacity: 0;">
                            <td class="px-5 py-4 font-semibold text-slate-800 whitespace-nowrap"><?php echo $m["label"] ?></td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-700 font-bold text-sm"><?php echo $m["assigned"] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-700 font-bold text-sm"><?php echo $m["resolved"] ?></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php if ($m["still_open"] > 0): ?>
                                    <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-lg bg-orange-50 text-orange-700 font-bold text-sm"><?php echo $m["still_open"] ?></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400 font-bold text-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <div class="flex items-center gap-1.5 h-8">
                                    <div class="h-full bg-blue-500 rounded-md transition-all" style="width: <?php echo $bar_pct ?>%; min-width: <?php echo $m["assigned"] > 0 ? "4" : "0" ?>px; opacity: 0.7;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System overview -->
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-5 md:p-6" style="animation: fadeIn 0.4s ease 0.45s forwards; opacity: 0;">
            <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2"><i data-lucide="bar-chart-2" size="18" class="text-slate-400"></i> Systémové přehledy</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><i data-lucide="inbox" size="18"></i></div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Otevřené tickety</p>
                        <p class="text-xl font-bold text-slate-800"><?php echo $all_open ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="user-plus" size="18"></i></div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Nepřiřazené</p>
                        <p class="text-xl font-bold text-slate-800"><?php echo $unassigned ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center"><i data-lucide="alert-triangle" size="18"></i></div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium">Po termínu</p>
                        <p class="text-xl font-bold text-slate-800"><?php echo $overdue ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>
