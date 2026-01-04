<?php

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();
getApplication()->setPageName("Moje statistiky");

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <div id="view-stats" class="view-section fade-in max-w-4xl mx-auto space-y-6">
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-soft border border-slate-200">
            <h3 class="text-lg font-bold mb-6 flex items-center gap-2"><i data-lucide="pie-chart"
                                                                          class="text-blue-500"></i> Statistiky technika
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-5 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500 font-medium">AAAA</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">xxxx</p>
                </div>
                <div class="p-5 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500 font-medium">BBBB</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">xxxx</p>
                </div>
                <div class="p-5 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500 font-medium">CCCC</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">xxxx</p>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>