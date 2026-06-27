<?php

require_once __DIR__ . "/app/SPSTickets.php";
require_once __DIR__ . "/app/model/password/PasswordChangeResult.php";

getApplication()->checkUser();
getApplication()->setPageName("Změna hesla");

$changeResult = PasswordChangeResult::Default;

if (isset($_POST["current"], $_POST["new1"], $_POST["new2"])) {
    $changeResult = PasswordChangeResult::IncorrectCurrentPassword;

    $current = $_POST["current"];
    $new1 = $_POST["new1"];
    $new2 = $_POST["new2"];

    $user = getApplication()->getUser();
    if ($user && $user->verifyPassword($current)) {
        $changeResult = PasswordChangeResult::MismatchedNewPasswords;

        if ($new1 == $new2) {
            $changeResult = PasswordChangeResult::InvalidNewPassword;

            if (strlen($new1) > 4) {
                $changeResult = PasswordChangeResult::Success;

                getApplication()->getUserRepository()->updateUserPassword($user->user_id, password_hash($new1, PASSWORD_DEFAULT));
                getApplication()->redirectInternally("changedpassword");
            }
        }
    }
}

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <div id="view-password" class="view-section max-w-xl mx-auto anim-fade-in">
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="bg-amber-100 p-3 rounded-xl text-amber-600"><i data-lucide="lock" size="24"></i></div>
                    <div><h3 class="text-xl font-bold text-slate-800">Změna hesla</h3>
                        <p class="text-slate-500 text-sm">Pro zvýšení bezpečnosti doporučujeme silné heslo.</p></div>
                </div>
            </div>
            <form method="post" class="p-6 md:p-8 space-y-6">
                <?php if ($changeResult != PasswordChangeResult::Default): ?>
                    <div id="password-error"
                         class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
                         style="animation: fadeIn 0.3s ease forwards">
                        <i data-lucide="alert-circle" size="18"></i>
                        <?php if ($changeResult == PasswordChangeResult::IncorrectCurrentPassword): ?>
                            <span>Nesprávné heslo.</span>
                        <?php elseif ($changeResult == PasswordChangeResult::MismatchedNewPasswords): ?>
                            <span>Nová hesla se neshodují.</span>
                        <?php elseif ($changeResult == PasswordChangeResult::InvalidNewPassword): ?>
                            <span>Nové heslo nesplňuje bezpečnostní požadavky.</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-2"><label class="text-sm font-semibold text-slate-700">Současné heslo</label><input
                            type="password"
                            class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                            placeholder="Zadejte aktuální heslo" name="current"></div>
                <div class="pt-4 border-t border-slate-100 space-y-4">
                    <div class="space-y-2"><label class="text-sm font-semibold text-slate-700">Nové heslo</label><input
                                type="password"
                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                placeholder="Zadejte nové heslo" name="new1"></div>
                    <div class="space-y-2"><label class="text-sm font-semibold text-slate-700">Potvrzení
                            hesla</label><input type="password"
                                                class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                                placeholder="Zadejte nové heslo znovu" name="new2"></div>
                </div>
                <div class="pt-6 flex flex-col md:flex-row items-center justify-end gap-3 border-t border-slate-100">
                    <button type="submit"
                            class="w-full md:w-auto px-6 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-500 font-medium shadow-lg shadow-blue-500/30 transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="check" size="18"></i> Provést
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>