<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();
getApplication()->setPageName("Odhlášení");

$logout_user = getApplication()->getUser();
$logout_username = $logout_user->user_username;
$logout_name = $logout_user->teacher_name;
$logout_initials = getApplication()->getInitials($logout_name);
$logout_avatar_gradient = getApplication()->getAvatarGradient($logout_user->user_id);

getApplication()->destroySession();

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>

    <div id="view-logged-out" class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden text-center p-8 fade-in">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                <i data-lucide="check" size="40"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Na shledanou!</h2>
            <p class="text-slate-500 text-sm mb-6">Bezpečně jsme vás odhlásili ze systému.</p>

            <!-- User Card -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 flex items-center gap-3 text-left">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br <?php echo $logout_avatar_gradient ?> text-white flex items-center justify-center font-bold"><?php echo $logout_initials ?></div>
                <div>
                    <p class="font-bold text-sm text-slate-800"><?php echo $logout_username ?></p>
                    <p class="text-xs text-slate-500"><?php echo $logout_name ?></p>
                </div>
            </div>

            <button onclick="window.location.href ='login.php'"
                    class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-all">
                Znovu přihlásit
            </button>
        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/footer.php"; ?>