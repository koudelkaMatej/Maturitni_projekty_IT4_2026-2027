<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/app/SPSTickets.php";
require_once __DIR__ . "/app/model/login/LoginResult.php";

if (getApplication()->getUser() != null) {
    getApplication()->redirectInternally("dashboard");
}

$loginResult = LoginResult::Default;

if (isset($_POST["username"], $_POST["password"])) {
    $loginResult = LoginResult::Failed;

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $user = getApplication()->getUserRepository()->getUserByUsername($username);

    if ($user && $user->verifyPassword($password)) {
        $userId = $user->user_id;
        $loginResult = LoginResult::Success;

        getApplication()->createUserSession($userId);
        getApplication()->redirectInternally("dashboard");
    }
}

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>

    <div id="view-login" class="fixed inset-0 z-50 bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md anim-scale-in">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 pb-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white mb-6 shadow-lg shadow-blue-500/30">
                        <i data-lucide="ticket" size="32"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">SPŠ HelpDesk</h1>
                    <p class="text-slate-500 mt-2 text-sm">Přihlášení pro administrátorský přístup k ticketům</p>
                </div>

                <form method="post" class="p-8 pt-0 space-y-5">
                    <?php if ($loginResult == LoginResult::Failed): ?>
                        <div id="login-error"
                             class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm flex items-center gap-2 animate-pulse">
                            <i data-lucide="alert-circle" size="18"></i>
                            <span>Nesprávné uživatelské jméno nebo heslo.</span>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Uživatelské jméno</label>
                        <div class="relative">
                            <i data-lucide="user" class="absolute left-3 top-3 text-slate-400 w-5 h-5"></i>
                            <input type="text"
                                   class="w-full pl-10 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                   placeholder="uzivatel" name="username">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700 ml-1">Heslo</label>
                        <div class="relative">
                            <i data-lucide="lock" class="absolute left-3 top-3 text-slate-400 w-5 h-5"></i>
                            <input type="password"
                                   class="w-full pl-10 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                   placeholder="••••••••" name="password">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        Přihlásit se <i data-lucide="arrow-right" size="18"></i>
                    </button>
                </form>

                <div class="bg-slate-50 p-2 text-center border-t border-slate-100">
                    <button onclick="window.location.href='index.php'"
                            class="text-blue-600 hover:text-blue-700 font-bold text-sm hover:underline flex items-center justify-center gap-1 w-full py-2 transition-colors">
                        Vytvořit ticket bez přihlášení <i data-lucide="external-link" size="14"></i>
                    </button>
                </div>

                <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
                    <p class="text-xs text-slate-400">© SPŠ Kladno • Systém vytvořil Robin Bláha</p>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/footer.php"; ?>