<?php

require_once __DIR__ . "/app/SPSTickets.php";

getApplication()->checkUser();
getApplication()->setPageName("Heslo změněno");

$user = getApplication()->getUser();
$user_id = $user->user_id;
$username = $user->user_username;
$name = $user->teacher_name;
$initials = getApplication()->getInitials($name);

getApplication()->getSessionRepository()->deleteUserSessions($user_id);
getApplication()->destroySession();

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>

    <div id="view-logged-out" class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-4">
        <div class="w-full max-w-sm anim-scale-in my-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden text-center p-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                    <i data-lucide="lock" size="40"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">Heslo změněno!</h2>

                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 flex items-center gap-3 text-left" style="animation: fadeIn 0.4s ease 0.2s forwards; opacity: 0;">
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600"><?php echo $initials ?></div>
                    <div>
                        <p class="font-bold text-sm text-slate-800"><?php echo $username ?></p>
                        <p class="text-xs text-slate-500"><?php echo $name ?></p>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex gap-3 text-sm text-blue-800" style="animation: fadeIn 0.4s ease 0.35s forwards; opacity: 0;">
                    <i data-lucide="info" class="flex-shrink-0 w-5 h-5"></i>
                    <p>Z bezpečnostních důvodu jste byli odhlášeni na <strong>všech zařízeních</strong>.</p>
                </div>

                <div class="p-4 flex gap-3" style="animation: fadeIn 0.4s ease 0.5s forwards; opacity: 0;">
                    <button onclick="window.location.href ='login.php'"
                            class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Znovu přihlásit
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/footer.php"; ?>