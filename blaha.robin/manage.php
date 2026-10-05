<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

require_once __DIR__ . "/app/SPSTickets.php";
getApplication()->checkUser();
if (!getApplication()->getUser()->user_admin) {
    getApplication()->redirectInternally("dashboard");
}
getApplication()->setPageName("Správa systému");

$section = $_GET["section"] ?? "teachers";
$edit_id = isset($_GET["edit"]) ? (int)$_GET["edit"] : 0;

$teacherRepo = getApplication()->getTeacherRepository();
$userRepo = getApplication()->getUserRepository();
$categoryRepo = getApplication()->getCategoryRepository();
$roomRepo = getApplication()->getRoomRepository();
$priorityRepo = getApplication()->getPriorityRepository();
$guestRepo = getApplication()->getGuestAccessRepository();

$errors = [];
$success = "";

// ===== POST processing =====

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // --- Teachers ---
    if ($section === "teachers") {
        $teacher_name = trim($_POST["teacher_name"] ?? "");

        if ($teacher_name === "") $errors[] = "Jméno učitele je povinné.";

        if (empty($errors)) {
            if ($action === "edit" && !empty($_POST["edit_id"])) {
                $teacherRepo->updateTeacher((int)$_POST["edit_id"], $teacher_name);
                $success = "Učitel byl upraven.";
            } elseif ($action === "add") {
                $teacherRepo->addTeacher($teacher_name);
                $success = "Učitel byl přidán.";
            }
        }
        if ($action === "delete" && !empty($_POST["delete_id"])) {
            $teacherRepo->deleteTeacher((int)$_POST["delete_id"]);
            $success = "Učitel byl smazán.";
        }
    }

    // --- Technicians ---
    if ($section === "technicians") {
        if ($action === "delete" && !empty($_POST["delete_id"])) {
            $userRepo->deleteUser((int)$_POST["delete_id"]);
            $success = "Technik byl smazán.";
        }
        if ($action === "add" || $action === "edit") {
            $tech_teacher = (int)($_POST["tech_teacher"] ?? 0);
            $tech_username = trim($_POST["tech_username"] ?? "");
            $tech_password = $_POST["tech_password"] ?? "";
            $tech_admin = isset($_POST["tech_admin"]);
            $edit_uid = (int)($_POST["edit_id"] ?? 0);

            if ($tech_teacher <= 0 && $action === "add") $errors[] = "Vyberte učitele.";
            if ($tech_username === "") $errors[] = "Uživatelské jméno je povinné.";
            if ($action === "add" && $tech_password === "") $errors[] = "Heslo je povinné.";

            if (empty($errors) && $tech_username !== "") {
                $existing = getApplication()->getUserRepository()->getUserByUsername($tech_username);
                if ($existing !== null && ($action === "add" || $existing->user_id !== $edit_uid)) {
                    $errors[] = "Uživatelské jméno '$tech_username' je již obsazeno.";
                }
            }

            if (empty($errors)) {
                if ($action === "add") {
                    $userRepo->addUser($tech_teacher, $tech_username, $tech_password, $tech_admin);
                    $success = "Technik byl přidán.";
                } elseif ($action === "edit" && $edit_uid > 0) {
                    $userRepo->updateUserUsername($edit_uid, $tech_username);
                    $userRepo->updateUserAdmin($edit_uid, $tech_admin);
                    if ($tech_password !== "") {
                        $userRepo->updateUserPassword($edit_uid, password_hash($tech_password, PASSWORD_DEFAULT));
                    }
                    $success = "Technik byl upraven.";
                }
            }
        }
    }

    // --- Categories ---
    if ($section === "categories") {
        $cat_name = trim($_POST["category_name"] ?? "");
        if ($cat_name === "") $errors[] = "Název kategorie je povinný.";
        if (empty($errors)) {
            if ($action === "edit" && !empty($_POST["edit_id"])) {
                $categoryRepo->updateCategory((int)$_POST["edit_id"], $cat_name);
                $success = "Kategorie byla upravena.";
            } elseif ($action === "add") {
                $categoryRepo->addCategory($cat_name);
                $success = "Kategorie byla přidána.";
            }
        }
        if ($action === "delete" && !empty($_POST["delete_id"])) {
            $categoryRepo->deleteCategory((int)$_POST["delete_id"]);
            $success = "Kategorie byla smazána.";
        }
    }

    // --- Rooms ---
    if ($section === "rooms") {
        $room_name = trim($_POST["room_name"] ?? "");
        if ($room_name === "") $errors[] = "Název místnosti je povinný.";
        if (empty($errors)) {
            if ($action === "edit" && !empty($_POST["edit_id"])) {
                $roomRepo->updateRoom((int)$_POST["edit_id"], $room_name);
                $success = "Místnost byla upravena.";
            } elseif ($action === "add") {
                $roomRepo->addRoom($room_name);
                $success = "Místnost byla přidána.";
            }
        }
        if ($action === "delete" && !empty($_POST["delete_id"])) {
            $roomRepo->deleteRoom((int)$_POST["delete_id"]);
            $success = "Místnost byla smazána.";
        }
    }

    // --- Priorities ---
    if ($section === "priorities") {
        $pri_name = trim($_POST["priority_name"] ?? "");
        $pri_weight = (int)($_POST["priority_weight"] ?? 0);
        $pri_color = trim($_POST["priority_color"] ?? "gray");

        if ($pri_name === "") $errors[] = "Název priority je povinný.";
        if (empty($errors)) {
            if ($action === "edit" && !empty($_POST["edit_id"])) {
                $priorityRepo->updatePriority((int)$_POST["edit_id"], $pri_name, $pri_weight, $pri_color);
                $success = "Priorita byla upravena.";
            } elseif ($action === "add") {
                $priorityRepo->addPriority($pri_name, $pri_weight, $pri_color);
                $success = "Priorita byla přidána.";
            }
        }
        if ($action === "delete" && !empty($_POST["delete_id"])) {
            $priorityRepo->deletePriority((int)$_POST["delete_id"]);
            $success = "Priorita byla smazána.";
        }
    }

    // --- Guest access ---
    if ($section === "guests" && $action === "save") {
        $guest_username = trim($_POST["guest_username"] ?? "");
        $guest_password = $_POST["guest_password"] ?? "";
        $guest_enabled = isset($_POST["guest_enabled"]);
        $current_guest = $guestRepo->get();

        if ($guest_username === "") $errors[] = "Uživatelské jméno hosta je povinné.";
        if ($guest_enabled && $guest_password === "" && !$current_guest->isConfigured()) {
            $errors[] = "Před povolením přístupu pro hosty nastavte heslo.";
        }

        if (empty($errors)) {
            $guestRepo->updateUsername($guest_username);
            if ($guest_password !== "") {
                $guestRepo->updatePassword($guest_password);
            }
            $guestRepo->setEnabled($guest_enabled);
            $success = "Nastavení hostů bylo uloženo.";
        }
    }
}

// ===== Load data for the active section =====

$edit_item = null;

switch ($section) {
    case "teachers":
        $items = $teacherRepo->getAllTeachers();
        if ($edit_id) $edit_item = $teacherRepo->getTeacherById($edit_id);
        break;
    case "technicians":
        $items = $userRepo->getAllUsers();
        $all_teachers = $teacherRepo->getAllTeachers();
        $existing_user_ids = array_map(fn($u) => $u->user_id, $items);
        $available_teachers = array_values(array_filter($all_teachers, fn($t) => !in_array($t->teacher_id, $existing_user_ids)));
        if ($edit_id) $edit_item = $userRepo->getUserById($edit_id);
        break;
    case "categories":
        $items = $categoryRepo->getAllCategories();
        if ($edit_id) $edit_item = $categoryRepo->getCategoryById($edit_id);
        break;
    case "rooms":
        $items = $roomRepo->getAllRooms();
        if ($edit_id) $edit_item = $roomRepo->getRoomById($edit_id);
        break;
    case "priorities":
        $items = $priorityRepo->getAllPriorities();
        if ($edit_id) $edit_item = $priorityRepo->getPriorityById($edit_id);
        break;
    case "guests":
        $items = [];
        $guest_settings = $guestRepo->get();
        break;
    default:
        $section = "teachers";
        $items = $teacherRepo->getAllTeachers();
}

$color_options = [
    "gray" => "Šedá",
    "blue" => "Modrá",
    "green" => "Zelená",
    "orange" => "Oranžová",
    "red" => "Červená",
];

$tabs = [
    "teachers" => ["label" => "Učitelé", "icon" => "graduation-cap"],
    "technicians" => ["label" => "Technici", "icon" => "wrench"],
    "categories" => ["label" => "Kategorie", "icon" => "folder-tree"],
    "rooms" => ["label" => "Místnosti", "icon" => "map-pin"],
    "priorities" => ["label" => "Priority", "icon" => "flag"],
        "guests" => ["label" => "Hosté", "icon" => "key-round"],
];

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <div class="max-w-5xl mx-auto anim-fade-in">

        <?php if ($success): ?>
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-3.5 text-sm font-medium flex items-center gap-2.5 anim-scale-in">
                <i data-lucide="check-circle" size="18" class="text-emerald-500 flex-shrink-0"></i> <?php echo $success ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3.5 text-sm font-medium flex items-center gap-2.5 anim-scale-in">
                <i data-lucide="alert-circle" size="18" class="text-red-500 flex-shrink-0"></i>
                <?php echo implode("<br>", array_map(fn($e) => htmlspecialchars($e), $errors)) ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-1 mb-8 p-1 bg-white rounded-2xl shadow-soft border border-slate-200">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="?section=<?php echo $key ?>"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all <?php echo $section === $key ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100' ?>">
                    <i data-lucide="<?php echo $tab["icon"] ?>" size="16"></i>
                    <?php echo $tab["label"] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ===== Teachers ===== -->
        <?php if ($section === "teachers"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="graduation-cap"></i> Správa učitelů</h2>
                    <p class="text-blue-100 text-sm mt-1">Přidávání, úprava a mazání učitelů</p>
                </div>

                <!-- Add / Edit form -->
                <form method="post" class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_item->teacher_id ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Jméno *</label>
                            <input type="text" name="teacher_name" required
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item->teacher_name) : "" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="Jméno a příjmení">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl <?php echo $edit_item ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg <?php echo $edit_item ? 'shadow-amber-500/30' : 'shadow-blue-500/30' ?>">
                                <i data-lucide="<?php echo $edit_item ? 'save' : 'plus' ?>" size="16"></i>
                                <?php echo $edit_item ? 'Uložit změny' : 'Přidat učitele' ?>
                            </button>
                            <?php if ($edit_item): ?>
                                <a href="?section=teachers"
                                   class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-all active:scale-95 flex items-center gap-1">
                                    <i data-lucide="x" size="16"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">ID</th>
                                <th class="text-left px-4 py-3">Jméno</th>
                                <th class="text-right px-5 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-mono text-slate-400 text-xs">#<?php echo $item->teacher_id ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($item->teacher_name) ?></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="?section=teachers&edit=<?php echo $item->teacher_id ?>"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                            <i data-lucide="pencil" size="12"></i> Upravit
                                        </a>
                                        <form method="post" class="inline" onsubmit="return confirm('Opravdu smazat učitele <?php echo htmlspecialchars(addslashes($item->teacher_name)) ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_id" value="<?php echo $item->teacher_id ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all active:scale-95 ml-1">
                                                <i data-lucide="trash-2" size="12"></i> Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Žádní učitelé
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== Technicians ===== -->
        <?php if ($section === "technicians"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="wrench"></i> Správa techniků</h2>
                    <p class="text-blue-100 text-sm mt-1">Správa uživatelských účtů pro přístup do systému</p>
                </div>

                <!-- Add / Edit form -->
                <form method="post" class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_item->user_id ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Učitel</label>
                            <?php if ($edit_item): ?>
                                <input type="text" readonly disabled
                                       value="<?php echo htmlspecialchars($edit_item->teacher_name) ?>"
                                       class="w-full p-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500">
                            <?php else: ?>
                                <select name="tech_teacher" required
                                        class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all">
                                    <option value="">-- Vyberte učitele --</option>
                                    <?php foreach ($available_teachers as $t): ?>
                                        <option value="<?php echo $t->teacher_id ?>"><?php echo htmlspecialchars($t->teacher_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($available_teachers)): ?>
                                    <p class="text-xs text-amber-600 mt-1">Všichni učitelé již mají účet.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Přihlašovací jméno *</label>
                            <input type="text" name="tech_username" required
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item->user_username) : "" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="např. novak">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">
                                <?php echo $edit_item ? 'Nové heslo (nechte prázdné pro ponechání)' : 'Heslo *' ?>
                            </label>
                            <input type="password" name="tech_password" <?php echo $edit_item ? '' : 'required' ?>
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="••••••••">
                        </div>
                        <div class="flex gap-2 items-center">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-600 cursor-pointer select-none">
                                <input type="checkbox" name="tech_admin" value="1"
                                        <?php echo ($edit_item && $edit_item->user_admin) ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                Administrátor
                            </label>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl <?php echo $edit_item ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg <?php echo $edit_item ? 'shadow-amber-500/30' : 'shadow-blue-500/30' ?>">
                            <i data-lucide="<?php echo $edit_item ? 'save' : 'plus' ?>" size="16"></i>
                            <?php echo $edit_item ? 'Uložit změny' : 'Přidat technika' ?>
                        </button>
                        <?php if ($edit_item): ?>
                            <a href="?section=technicians"
                               class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-all active:scale-95 flex items-center gap-1">
                                <i data-lucide="x" size="16"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">ID</th>
                                <th class="text-left px-4 py-3">Přihlašovací jméno</th>
                                <th class="text-left px-4 py-3">Jméno</th>
                                <th class="text-left px-4 py-3">Admin</th>
                                <th class="text-right px-5 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-mono text-slate-400 text-xs">#<?php echo $item->user_id ?></td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-600"><?php echo htmlspecialchars($item->user_username) ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-7 h-7 rounded-full bg-gradient-to-br <?php echo getApplication()->getAvatarGradient($item->user_id) ?> text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0"><?php echo getApplication()->getInitials($item->teacher_name) ?></span>
                                            <?php echo htmlspecialchars($item->teacher_name) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if ($item->user_admin): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md"><i
                                                        data-lucide="shield-check" size="12"></i> Admin</span>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="?section=technicians&edit=<?php echo $item->user_id ?>"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                            <i data-lucide="pencil" size="12"></i> Upravit
                                        </a>
                                        <form method="post" class="inline" onsubmit="return confirm('Opravdu smazat technika <?php echo htmlspecialchars(addslashes($item->teacher_name)) ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_id" value="<?php echo $item->user_id ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all active:scale-95 ml-1">
                                                <i data-lucide="trash-2" size="12"></i> Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Žádní technici</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== Categories ===== -->
        <?php if ($section === "categories"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="folder-tree"></i> Správa kategorií</h2>
                    <p class="text-blue-100 text-sm mt-1">Kategorie pro třídění ticketů</p>
                </div>

                <form method="post" class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_item->category_id ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Název *</label>
                            <input type="text" name="category_name" required
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item->category_name) : "" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="např. Hardware">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl <?php echo $edit_item ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg <?php echo $edit_item ? 'shadow-amber-500/30' : 'shadow-blue-500/30' ?>">
                                <i data-lucide="<?php echo $edit_item ? 'save' : 'plus' ?>" size="16"></i>
                                <?php echo $edit_item ? 'Uložit změny' : 'Přidat kategorii' ?>
                            </button>
                            <?php if ($edit_item): ?>
                                <a href="?section=categories"
                                   class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-all active:scale-95 flex items-center gap-1">
                                    <i data-lucide="x" size="16"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">ID</th>
                                <th class="text-left px-4 py-3">Název</th>
                                <th class="text-right px-5 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-mono text-slate-400 text-xs">#<?php echo $item->category_id ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($item->category_name) ?></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="?section=categories&edit=<?php echo $item->category_id ?>"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                            <i data-lucide="pencil" size="12"></i> Upravit
                                        </a>
                                        <form method="post" class="inline" onsubmit="return confirm('Opravdu smazat kategorii <?php echo htmlspecialchars(addslashes($item->category_name)) ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_id" value="<?php echo $item->category_id ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all active:scale-95 ml-1">
                                                <i data-lucide="trash-2" size="12"></i> Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Žádné kategorie</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== Rooms ===== -->
        <?php if ($section === "rooms"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="map-pin"></i> Správa místností</h2>
                    <p class="text-blue-100 text-sm mt-1">Místnosti a učebny pro ticket</p>
                </div>

                <form method="post" class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_item->room_id ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Název *</label>
                            <input type="text" name="room_name" required
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item->room_name) : "" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="např. Učebna 205">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl <?php echo $edit_item ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg <?php echo $edit_item ? 'shadow-amber-500/30' : 'shadow-blue-500/30' ?>">
                                <i data-lucide="<?php echo $edit_item ? 'save' : 'plus' ?>" size="16"></i>
                                <?php echo $edit_item ? 'Uložit změny' : 'Přidat místnost' ?>
                            </button>
                            <?php if ($edit_item): ?>
                                <a href="?section=rooms"
                                   class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-all active:scale-95 flex items-center gap-1">
                                    <i data-lucide="x" size="16"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">ID</th>
                                <th class="text-left px-4 py-3">Název</th>
                                <th class="text-right px-5 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-mono text-slate-400 text-xs">#<?php echo $item->room_id ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($item->room_name) ?></td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="?section=rooms&edit=<?php echo $item->room_id ?>"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                            <i data-lucide="pencil" size="12"></i> Upravit
                                        </a>
                                        <form method="post" class="inline" onsubmit="return confirm('Opravdu smazat místnost <?php echo htmlspecialchars(addslashes($item->room_name)) ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_id" value="<?php echo $item->room_id ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all active:scale-95 ml-1">
                                                <i data-lucide="trash-2" size="12"></i> Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Žádné místnosti</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== Priorities ===== -->
        <?php if ($section === "priorities"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="flag"></i> Správa priorit</h2>
                    <p class="text-blue-100 text-sm mt-1">Nastavení priorit a jejich barev</p>
                </div>

                <form method="post" class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_item->priority_id ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Název *</label>
                            <input type="text" name="priority_name" required
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item->priority_name) : "" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="např. Vysoká">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Váha</label>
                            <input type="number" name="priority_weight"
                                   value="<?php echo $edit_item ? $edit_item->priority_weight : "0" ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Barva</label>
                            <select name="priority_color"
                                    class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all">
                                <?php foreach ($color_options as $val => $label): ?>
                                    <option value="<?php echo $val ?>" <?php echo ($edit_item && $edit_item->priority_color === $val) ? "selected" : "" ?>>
                                        <?php echo $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl <?php echo $edit_item ? 'bg-amber-500 hover:bg-amber-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg <?php echo $edit_item ? 'shadow-amber-500/30' : 'shadow-blue-500/30' ?>">
                                <i data-lucide="<?php echo $edit_item ? 'save' : 'plus' ?>" size="16"></i>
                                <?php echo $edit_item ? 'Uložit změny' : 'Přidat prioritu' ?>
                            </button>
                            <?php if ($edit_item): ?>
                                <a href="?section=priorities"
                                   class="px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-sm transition-all active:scale-95 flex items-center gap-1">
                                    <i data-lucide="x" size="16"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-5 py-3">ID</th>
                                <th class="text-left px-4 py-3">Název</th>
                                <th class="text-left px-4 py-3">Váha</th>
                                <th class="text-left px-4 py-3">Barva</th>
                                <th class="text-right px-5 py-3">Akce</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <?php $color_hex = ["red" => "#ef4444", "orange" => "#f97316", "green" => "#22c55e", "blue" => "#3b82f6", "gray" => "#94a3b8"][$item->priority_color] ?? "#94a3b8"; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-3 font-mono text-slate-400 text-xs">#<?php echo $item->priority_id ?></td>
                                    <td class="px-4 py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($item->priority_name) ?></td>
                                    <td class="px-4 py-3 text-slate-600"><?php echo $item->priority_weight ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium">
                                            <span class="w-3 h-3 rounded-full" style="background: <?php echo $color_hex ?>"></span>
                                            <?php echo $color_options[$item->priority_color] ?? $item->priority_color ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="?section=priorities&edit=<?php echo $item->priority_id ?>"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-all active:scale-95">
                                            <i data-lucide="pencil" size="12"></i> Upravit
                                        </a>
                                        <form method="post" class="inline" onsubmit="return confirm('Opravdu smazat prioritu <?php echo htmlspecialchars(addslashes($item->priority_name)) ?>?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="delete_id" value="<?php echo $item->priority_id ?>">
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-all active:scale-95 ml-1">
                                                <i data-lucide="trash-2" size="12"></i> Smazat
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">Žádné priority</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ===== Guest access ===== -->
        <?php if ($section === "guests"): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
                <div class="bg-blue-600 p-5 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="key-round"></i> Přístup pro
                        hosty</h2>
                    <p class="text-blue-100 text-sm mt-1">Přihlašovací údaje pro odesílání ticketů bez přihlášení
                        (index.php)</p>
                </div>

                <form method="post" class="p-5 space-y-5">
                    <input type="hidden" name="action" value="save">

                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Odesílání ticketů bez přihlášení</p>
                            <p class="text-xs text-slate-500 mt-0.5">Když je vypnuto, formulář na index.php je
                                nedostupný a je nutné se přihlásit jako technik.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 ml-4">
                            <input type="checkbox" name="guest_enabled" value="1"
                                   class="sr-only peer" <?php echo $guest_settings->guest_enabled ? "checked" : "" ?>>
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5 shadow-sm"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">Uživatelské jméno *</label>
                            <input type="text" name="guest_username" required
                                   value="<?php echo htmlspecialchars($guest_settings->guest_username) ?>"
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="např. host">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 mb-1 block">
                                <?php echo $guest_settings->isConfigured() ? "Nové heslo (nechte prázdné pro ponechání)" : "Heslo *" ?>
                            </label>
                            <input type="password"
                                   name="guest_password" <?php echo $guest_settings->isConfigured() ? "" : "required" ?>
                                   class="w-full p-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none text-sm transition-all"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <?php if (!$guest_settings->isConfigured()): ?>
                        <p class="text-xs text-amber-600 flex items-center gap-1.5"><i data-lucide="alert-triangle"
                                                                                       size="14"></i> Přístup pro hosty
                            zatím nebyl nastaven.</p>
                    <?php endif; ?>

                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95 flex items-center gap-2 shadow-lg shadow-blue-500/30">
                        <i data-lucide="save" size="16"></i> Uložit nastavení
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>

<?php require_once __DIR__ . "/app/includes/footer.php"; ?>
