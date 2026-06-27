<?php

require_once __DIR__ . "/app/SPSTickets.php";
getApplication()->checkUser();
getApplication()->setPageName("Vytvořit ticket");

$teachers = getApplication()->getTeacherRepository()->getAllTeachers();
$rooms = getApplication()->getRoomRepository()->getAllRooms();
$categories = getApplication()->getCategoryRepository()->getAllCategories();
$priorities = getApplication()->getPriorityRepository()->getAllPriorities();
$current_user = getApplication()->getUser();

$ticketCreated = false;

if (isset($_POST["teacher_id"], $_POST["room_id"], $_POST["category"], $_POST["title"], $_POST["description"])) {
    $origin = (int)$_POST["teacher_id"];
    $category = (int)$_POST["category"];
    $room = (int)$_POST["room_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $priority = !empty($_POST["priority"]) ? (int)$_POST["priority"] : null;
    $deadline = !empty($_POST["deadline"]) ? $_POST["deadline"] : null;

    $ticket_id = getApplication()->getTicketRepository()->addTicket($origin, $category, $room, $title, $description);

    if ($ticket_id && $priority) {
        getApplication()->getTicketRepository()->updateTicket((int)$ticket_id, ["ticket_priority" => $priority]);
    }

    if ($ticket_id && $deadline) {
        getApplication()->getTicketRepository()->updateTicket((int)$ticket_id, ["ticket_deadline" => $deadline]);
    }

    if ($ticket_id) {
        getApplication()->getAssignmentRepository()->addAssignment((int)$ticket_id, $current_user->user_id);

        $auto_users = getApplication()->getAutoAssignRepository()->getAutoAssignUsersByCategory($category);
        foreach ($auto_users as $au) {
            if ((int)$au["user_id"] !== (int)$current_user->user_id) {
                getApplication()->getAssignmentRepository()->addAssignment((int)$ticket_id, (int)$au["user_id"]);
            }
        }
    }

    $ticketCreated = true;
}

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>
<?php require_once __DIR__ . "/app/includes/sidebar.php"; ?>

    <div id="view-create" class="view-section max-w-2xl mx-auto anim-fade-in">
        <?php if ($ticketCreated): ?>
            <div class="bg-white rounded-2xl shadow-soft border border-slate-200 p-12 text-center anim-scale-in">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                    <i data-lucide="check" size="40"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Ticket vytvořen!</h2>
                <p class="text-slate-500 mb-8">Ticket byl úspěšně vytvořen a přiřazen k vašemu účtu.</p>
                <a href="create.php"
                   class="inline-block px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/30 transition-all hover:scale-105 active:scale-95">
                    Vytvořit další
                </a>
            </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden">
            <div class="bg-blue-600 p-6 text-white">
                <h2 class="text-xl font-bold flex items-center gap-2"><i data-lucide="plus-circle"></i> Nový ticket</h2>
                <p class="text-blue-100 text-sm mt-1">Vytvoření nového ticketu v systému</p>
            </div>

            <form method="post" class="p-8 space-y-6">
                <div class="space-y-2 relative" id="teacher-search-container">
                    <label class="text-sm font-bold text-slate-700">Učitel / zaměstnanec</label>
                    <p class="text-xs text-slate-500">Vyhledejte jméno učitele, který problém nahlásil.</p>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-3.5 text-slate-400 w-5 h-5 pointer-events-none"></i>
                        <input type="text" id="teacher-search-input" autocomplete="off"
                               class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"
                               placeholder="Vyhledat učitele..." name="teacher">
                        <input type="hidden" id="teacher-selected-id" name="teacher_id">
                        <button type="button" id="teacher-search-clear"
                                class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600 hidden transition-colors"
                                onclick="clearTeacherSelection()">
                            <i data-lucide="x" size="18"></i>
                        </button>
                    </div>
                    <div id="teacher-search-results"
                         class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-64 overflow-y-auto hidden divide-y divide-slate-50"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 relative" id="room-search-container">
                        <label class="text-sm font-bold text-slate-700">Učebna</label>
                        <div class="relative">
                            <i data-lucide="map-pin" class="absolute left-3 top-3.5 text-slate-400 w-5 h-5 pointer-events-none"></i>
                            <input type="text" id="room-search-input" autocomplete="off"
                                   class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"
                                   placeholder="Vyhledat učebnu..." name="room">
                            <input type="hidden" id="room-selected-id" name="room_id">
                            <button type="button" id="room-search-clear"
                                    class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600 hidden transition-colors"
                                    onclick="clearRoomSelection()">
                                <i data-lucide="x" size="18"></i>
                            </button>
                        </div>
                        <div id="room-search-results"
                             class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-64 overflow-y-auto hidden divide-y divide-slate-50"></div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Kategorie</label>
                        <select name="category" required
                                class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="" disabled selected>Vybrat kategorii...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->category_id ?>"><?php echo $category->category_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Priorita</label>
                        <select name="priority"
                                class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="" selected>Bez priority</option>
                            <?php foreach ($priorities as $priority): ?>
                                <option value="<?php echo $priority->priority_id ?>"><?php echo $priority->priority_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Termín řešení</label>
                        <input type="date" name="deadline"
                               class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Předmět ticketu</label>
                    <input type="text" required name="title"
                           class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                           placeholder="Krátký předmět ticketu">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Detailní popis</label>
                    <textarea required name="description" rows="5"
                              class="w-full p-4 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                              placeholder="Detailní popis obsahující všechny důležité informace."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/30 transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                        <i data-lucide="send" size="18"></i> Vytvořit ticket
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <script>
        const teachers = [
            <?php foreach ($teachers as $teacher) : ?>
            {id: "<?php echo $teacher->teacher_id ?>", name: "<?php echo $teacher->teacher_name ?>",
                initials: "<?php echo getApplication()->getInitials($teacher->teacher_name) ?>"},
            <?php endforeach; ?>
        ];
        const rooms = [
            <?php foreach ($rooms as $room) : ?>
            {id: "<?php echo $room->room_id ?>", name: "<?php echo $room->room_name ?>"},
            <?php endforeach; ?>
        ];

        const searchInput = document.getElementById('teacher-search-input');
        const searchResults = document.getElementById('teacher-search-results');
        const hiddenId = document.getElementById('teacher-selected-id');
        const clearBtn = document.getElementById('teacher-search-clear');

        function renderTeacherResults(query) {
            const filtered = teachers.filter(t => t.name.toLowerCase().includes(query.toLowerCase()));
            searchResults.innerHTML = filtered.length === 0
                ? '<div class="p-3 text-sm text-slate-500 text-center">Učitel nenalezen</div>'
                : filtered.map(t => `<div onclick="selectTeacher('${t.id}', '${t.name}')" class="p-3 hover:bg-blue-50 cursor-pointer flex items-center gap-3 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${t.initials}</div>
                    <p class="text-sm font-semibold text-slate-800">${t.name}</p>
                </div>`).join('');
        }

        window.selectTeacher = function (id, name) {
            searchInput.value = name; hiddenId.value = id;
            searchResults.classList.add('hidden'); clearBtn.classList.remove('hidden');
        }
        window.clearTeacherSelection = function () {
            searchInput.value = ''; hiddenId.value = '';
            clearBtn.classList.add('hidden'); searchInput.focus();
            renderTeacherResults(''); searchResults.classList.remove('hidden');
        }
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const val = e.target.value;
                if (val.length > 0) { searchResults.classList.remove('hidden'); clearBtn.classList.remove('hidden'); renderTeacherResults(val); }
                else { searchResults.classList.add('hidden'); clearBtn.classList.add('hidden'); }
            });
            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim() === "") { searchResults.classList.remove('hidden'); renderTeacherResults(''); }
            });
            document.addEventListener('click', (e) => {
                const container = document.getElementById('teacher-search-container');
                if (container && !container.contains(e.target)) searchResults.classList.add('hidden');
            });
        }

        const roomInput = document.getElementById('room-search-input');
        const roomResults = document.getElementById('room-search-results');
        const roomHiddenId = document.getElementById('room-selected-id');
        const roomClearBtn = document.getElementById('room-search-clear');

        function renderRoomResults(query) {
            const filtered = rooms.filter(r => r.name.toLowerCase().includes(query.toLowerCase()) || r.id.toLowerCase().includes(query.toLowerCase()));
            roomResults.innerHTML = filtered.length === 0
                ? '<div class="p-3 text-sm text-slate-500 text-center">Učebna nenalezena</div>'
                : filtered.map(r => `<div onclick="selectRoom('${r.id}', '${r.name}')" class="p-3 hover:bg-slate-100 cursor-pointer flex items-center gap-3 transition-colors">
                    <i data-lucide="map-pin" size="14" class="text-slate-400"></i>
                    <p class="text-sm font-semibold text-slate-800">${r.name}</p>
                </div>`).join('');
            lucide.createIcons();
        }
        window.selectRoom = function (id, name) {
            roomInput.value = name; roomHiddenId.value = id;
            roomResults.classList.add('hidden'); roomClearBtn.classList.remove('hidden');
        }
        window.clearRoomSelection = function () {
            roomInput.value = ''; roomHiddenId.value = '';
            roomClearBtn.classList.add('hidden'); roomInput.focus();
            renderRoomResults(''); roomResults.classList.remove('hidden');
        }
        if (roomInput) {
            roomInput.addEventListener('input', (e) => {
                const val = e.target.value;
                if (val.length > 0) { roomResults.classList.remove('hidden'); roomClearBtn.classList.remove('hidden'); renderRoomResults(val); }
                else { roomResults.classList.add('hidden'); roomClearBtn.classList.add('hidden'); }
            });
            roomInput.addEventListener('focus', () => {
                if (roomInput.value.trim() === "") { roomResults.classList.remove('hidden'); renderRoomResults(''); }
            });
            document.addEventListener('click', (e) => {
                const container = document.getElementById('room-search-container');
                if (container && !container.contains(e.target)) roomResults.classList.add('hidden');
            });
        }
    </script>

<?php require_once __DIR__ . "/app/includes/scripts.php"; ?>
<?php require_once __DIR__ . "/app/includes/footer.php"; ?>