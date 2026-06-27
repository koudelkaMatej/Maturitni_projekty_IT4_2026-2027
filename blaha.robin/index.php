<?php

require_once __DIR__ . "/app/SPSTickets.php";

if (getApplication()->getUser() != null) {
    getApplication()->redirectInternally("dashboard");
}

$teachers = getApplication()->getTeacherRepository()->getAllTeachers();
$rooms = getApplication()->getRoomRepository()->getAllRooms();
$categories = getApplication()->getCategoryRepository()->getAllCategories();

$ticketCreated = false;

if (isset($_POST["teacher_id"], $_POST["room_id"], $_POST["category"], $_POST["title"], $_POST["description"])) {
    $origin = (int)$_POST["teacher_id"];
    $category = (int)$_POST["category"];
    $room = (int)$_POST["room_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    getApplication()->getTicketRepository()->addTicket($origin, $category, $room, $title, $description);
    $ticketCreated = true;
}

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>

    <div id="view-teacher-report"
         class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/40 flex items-start justify-center p-4 pb-12 overflow-y-auto">
        <div class="w-full max-w-2xl anim-scale-in mt-6 md:mt-16 mb-8">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <?php if ($ticketCreated): ?>
                <div class="text-center p-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                        <i data-lucide="check" size="40"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">Ticket odeslán!</h2>
                    <p class="text-slate-500 mb-8">Váš ticket byl úspěšně vytvořen. Technici se jím budou zabývat.</p>
                    <button onclick="window.location.href='index.php'"
                            class="px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/30 transition-all hover:scale-105 active:scale-95">
                        Vytvořit další ticket
                    </button>
                </div>
            <?php else: ?>
            <div class="relative overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-6 md:p-8 text-white">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="hidden md:flex bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                                <i data-lucide="message-square-plus" size="28"></i>
                            </div>
                            <div>
                                <h2 class="text-xl md:text-2xl font-bold">Nový ticket</h2>
                                <p class="text-blue-100 text-sm mt-0.5">Formulář pro učitele a zaměstnance</p>
                            </div>
                        </div>
                        <button onclick="window.location.href='login.php'"
                                class="bg-white/20 hover:bg-white/30 p-2.5 rounded-xl transition-all backdrop-blur-sm hover:scale-105 active:scale-95"
                                title="Přihlásit se">
                            <i data-lucide="log-in" size="20"></i>
                        </button>
                    </div>
                </div>
            </div>

            <form method="post" class="p-6 md:p-8 space-y-5">
                <div class="space-y-2 relative" id="teacher-search-container">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="user" size="15" class="text-slate-400"></i> Vaše jméno
                    </label>
                    <p class="text-xs text-slate-500">Začněte psát své příjmení pro vyhledání.</p>

                    <div class="relative">
                        <i data-lucide="search"
                           class="absolute left-3.5 top-3.5 text-slate-400 w-4 h-4 pointer-events-none"></i>
                        <input type="text" id="teacher-search-input" autocomplete="off"
                               class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all placeholder:text-slate-400"
                               placeholder="Vyhledat učitele..." name="teacher">

                        <input type="hidden" id="teacher-selected-id" name="teacher_id">

                        <button type="button" id="teacher-search-clear"
                                class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1 rounded-lg hidden transition-all"
                                onclick="clearTeacherSelection()">
                            <i data-lucide="x" size="16"></i>
                        </button>
                    </div>

                    <div id="teacher-search-results"
                         class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto hidden">
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher->teacher_code ?>"><?php echo $teacher->teacher_name ?></option>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2 relative" id="room-search-container">
                        <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="map-pin" size="15" class="text-slate-400"></i> Učebna
                        </label>

                        <div class="relative">
                            <i data-lucide="map-pin"
                               class="absolute left-3.5 top-3.5 text-slate-400 w-4 h-4 pointer-events-none"></i>
                            <input type="text" id="teacher-report-room-input" autocomplete="off"
                                   class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all placeholder:text-slate-400"
                                   placeholder="Vyhledat učebnu..." name="room">

                            <input type="hidden" id="teacher-report-room-id" name="room_id">

                            <button type="button" id="room-search-clear"
                                    class="absolute right-3 top-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1 rounded-lg hidden transition-all"
                                    onclick="clearRoomSelection()">
                                <i data-lucide="x" size="16"></i>
                            </button>
                        </div>

                        <div id="room-search-results"
                             class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto hidden">
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room->room_id ?>"><?php echo $room->room_name ?></option>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="folder" size="15" class="text-slate-400"></i> Kategorie
                        </label>
                        <div class="relative">
                            <select id="teacher-report-category" required
                                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all appearance-none cursor-pointer" name="category">
                                <option value="" disabled selected>Vybrat kategorii...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->category_id ?>"><?php echo $category->category_name ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i data-lucide="chevron-down" size="16" class="absolute right-3.5 top-3.5 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="type" size="15" class="text-slate-400"></i> Předmět ticketu
                    </label>
                    <input id="teacher-report-title" required
                           class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all placeholder:text-slate-400"
                           placeholder="Krátký předmět ticketu" name="title">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="align-left" size="15" class="text-slate-400"></i> Detailní popis
                    </label>
                    <textarea id="teacher-report-desc" required rows="5"
                              class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 outline-none transition-all placeholder:text-slate-400 resize-y min-h-[120px]"
                              placeholder="Detailní popis obsahující všechny důležité informace." name="description"></textarea>
                </div>

                <div class="pt-4 flex flex-col-reverse md:flex-row items-center justify-between gap-3 border-t border-slate-100">
                    <button type="button" onclick="window.location.href='login.php'"
                            class="w-full md:w-auto text-sm text-slate-500 hover:text-slate-700 font-medium transition-colors flex items-center justify-center gap-1.5 py-2">
                        <i data-lucide="shield" size="14"></i> Přihlásit se (technici)
                    </button>
                    <button type="submit"
                            class="w-full md:w-auto px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/30 transition-all hover:shadow-blue-500/40 hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="send" size="18"></i> Odeslat ticket
                    </button>
                </div>

                <div class="text-center pt-1">
                    <p class="text-xs text-slate-400">© SPŠ Kladno • Systém vytvořil Robin Bláha</p>
                </div>
            </form>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const teachers = [
            <?php foreach ($teachers as $teacher) : ?>
            {
                id: "<?php echo $teacher->teacher_id ?>",
                name: "<?php echo $teacher->teacher_name ?>",
                initials: "<?php echo getApplication()->getInitials($teacher->teacher_name) ?>"
            },
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

        function renderTeacherResults(query = '') {
            const filtered = teachers.filter(t => t.name.toLowerCase().includes(query.toLowerCase()));

            if (filtered.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-sm text-slate-500 text-center">Učitel nenalezen</div>';
                return;
            }

            searchResults.innerHTML = filtered.map(t => `
                <div onclick="selectTeacher('${t.id}', '${t.name}')" class="p-3 hover:bg-blue-50 cursor-pointer flex items-center gap-3 transition-colors border-b border-slate-50 last:border-none group">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold font-mono group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        ${t.initials}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">${t.name}</p>
                    </div>
                </div>
            `).join('');
        }

        window.selectTeacher = function (id, name) {
            searchInput.value = name;
            hiddenId.value = id;
            searchResults.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        }

        window.clearTeacherSelection = function () {
            searchInput.value = '';
            hiddenId.value = '';
            clearBtn.classList.add('hidden');
            searchInput.focus();
            renderTeacherResults('');
            searchResults.classList.remove('hidden');
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const val = e.target.value;
                if (val.length > 0) {
                    searchResults.classList.remove('hidden');
                    clearBtn.classList.remove('hidden');
                    renderTeacherResults(val);
                } else {
                    searchResults.classList.add('hidden');
                    clearBtn.classList.add('hidden');
                }
            });

            searchInput.addEventListener('focus', () => {
                if (searchInput.value.trim() === "") {
                    searchResults.classList.remove('hidden');
                    renderTeacherResults('');
                }
            });

            document.addEventListener('click', (e) => {
                const container = document.getElementById('teacher-search-container');
                if (container && !container.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }



        const roomInput = document.getElementById('teacher-report-room-input');
        const roomResults = document.getElementById('room-search-results');
        const roomHiddenId = document.getElementById('teacher-report-room-id');
        const roomClearBtn = document.getElementById('room-search-clear');

        function renderRoomResults(query = '') {
            const filtered = rooms.filter(r => r.name.toLowerCase().includes(query.toLowerCase()) || r.id.toLowerCase().includes(query.toLowerCase()));

            if (filtered.length === 0) {
                roomResults.innerHTML = '<div class="p-3 text-sm text-slate-500 text-center">Učebna nenalezena</div>';
                return;
            }

            roomResults.innerHTML = filtered.map(r => `
                <div onclick="selectRoom('${r.id}', '${r.name}')" class="p-3 hover:bg-blue-50 cursor-pointer flex items-center gap-3 transition-colors border-b border-slate-50 last:border-none group">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold font-mono group-hover:bg-slate-200 transition-colors">
                        <i data-lucide="map-pin" size="14"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">${r.name}</p>
                    </div>
                </div>
            `).join('');
            lucide.createIcons();
        }

        window.selectRoom = function (id, name) {
            roomInput.value = name;
            roomHiddenId.value = id;
            roomResults.classList.add('hidden');
            roomClearBtn.classList.remove('hidden');
        }

        window.clearRoomSelection = function () {
            roomInput.value = '';
            roomHiddenId.value = '';
            roomClearBtn.classList.add('hidden');
            roomInput.focus();
            renderRoomResults('');
            roomResults.classList.remove('hidden');
        }

        if (roomInput) {
            roomInput.addEventListener('input', (e) => {
                const val = e.target.value;
                if (val.length > 0) {
                    roomResults.classList.remove('hidden');
                    roomClearBtn.classList.remove('hidden');
                    renderRoomResults(val);
                } else {
                    roomResults.classList.add('hidden');
                    roomClearBtn.classList.add('hidden');
                }
            });

            roomInput.addEventListener('focus', () => {
                if (roomInput.value.trim() === "") {
                    roomResults.classList.remove('hidden');
                    renderRoomResults('');
                }
            });

            document.addEventListener('click', (e) => {
                const container = document.getElementById('room-search-container');
                if (container && !container.contains(e.target)) {
                    roomResults.classList.add('hidden');
                }
            });
        }
    </script>

<?php require_once __DIR__ . "/app/includes/footer.php"; ?>