<?php

require_once __DIR__ . "/app/SPSTickets.php";

if (getApplication()->getUser() != null) {
    getApplication()->redirectInternally("dashboard");
}

$teachers = getApplication()->getTeacherRepository()->getAllTeachers();
$rooms = getApplication()->getRoomRepository()->getAllRooms();
$categories = getApplication()->getCategoryRepository()->getAllCategories();

?>

<?php require_once __DIR__ . "/app/includes/header.php"; ?>

    <div id="view-teacher-report"
         class="absolute inset-0 z-50 bg-slate-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden fade-in my-8">
            <div class="bg-blue-600 p-6 text-white flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold flex items-center gap-2"><i data-lucide="message-square-plus"></i>
                        Nový ticket</h2>
                    <p class="text-blue-100 text-sm mt-1">Formulář pro učitele a zaměstnance</p>
                </div>
                <button onclick="window.location.href='login.php'"
                        class="bg-blue-500 hover:bg-blue-400 p-2 rounded-lg transition-colors text-white"
                        title="Zpět na přihlášení">
                    <i data-lucide="log-in" size="20"></i>
                </button>
            </div>

            <form method="post" class="p-8 space-y-6">
                <div class="space-y-2 relative" id="teacher-search-container">
                    <label class="text-sm font-bold text-slate-700">Vaše jméno</label>
                    <p class="text-xs text-slate-500">Začněte psát své příjmení pro vyhledání.</p>

                    <div class="relative">
                        <i data-lucide="search"
                           class="absolute left-3 top-3.5 text-slate-400 w-5 h-5 pointer-events-none"></i>
                        <input type="text" id="teacher-search-input" autocomplete="off"
                               class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"
                               placeholder="Vyhledat učitele..." name="teacher">

                        <input type="hidden" id="teacher-selected-id">

                        <button type="button" id="teacher-search-clear"
                                class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600 hidden transition-colors"
                                onclick="clearTeacherSelection()">
                            <i data-lucide="x" size="18"></i>
                        </button>
                    </div>

                    <div id="teacher-search-results"
                         class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-64 overflow-y-auto hidden divide-y divide-slate-50">
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher["teacher_code"] ?>"><?php echo $teacher["teacher_name"] ?></option>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 relative" id="room-search-container">
                        <label class="text-sm font-bold text-slate-700">Učebna</label>

                        <div class="relative">
                            <i data-lucide="map-pin"
                               class="absolute left-3 top-3.5 text-slate-400 w-5 h-5 pointer-events-none"></i>
                            <input type="text" id="teacher-report-room-input" autocomplete="off"
                                   class="w-full pl-10 pr-10 p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"
                                   placeholder="Vyhledat učebnu..." name="room">

                            <input type="hidden" id="teacher-report-room-id">

                            <button type="button" id="room-search-clear"
                                    class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600 hidden transition-colors"
                                    onclick="clearRoomSelection()">
                                <i data-lucide="x" size="18"></i>
                            </button>
                        </div>

                        <div id="room-search-results"
                             class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-64 overflow-y-auto hidden divide-y divide-slate-50">
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room["room_id"] ?>"><?php echo $room["room_name"] ?></option>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700">Kategorie</label>
                        <select id="teacher-report-category" required
                                class="w-full p-3 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none" name="category">
                            <option value="" disabled selected>Vybrat kategorii...</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category["category_id"] ?>"><?php echo $category["category_name"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Předmět ticketu</label>
                    <input id="teacher-report-desc" required rows="5"
                              class="w-full p-4 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                              placeholder="Krátký předmět ticketu" name="title">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Detailní popis</label>
                    <textarea id="teacher-report-desc" required rows="5"
                              class="w-full p-4 bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                              placeholder="Detailní popis obsahující všechny důležité informace." name="description"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="submit"
                            class="px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                        <i data-lucide="send" size="18"></i> Odeslat ticket
                    </button>
                </div>

                <div class="p-1 text-center border-t border-slate-100">
                    <p class="text-xs text-slate-400">© SPŠ Kladno • Systém vytvořil Robin Bláha</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        const teachers = [
            <?php foreach ($teachers as $teacher) : ?>
            {
                id: "<?php echo $teacher["teacher_code"] ?>",
                name: "<?php echo $teacher["teacher_name"] ?>",
                initials: "<?php echo getApplication()->getInitials($teacher["teacher_name"]) ?>"
            },
            <?php endforeach; ?>
        ];

        const rooms = [
            <?php foreach ($rooms as $room) : ?>
            {id: "<?php echo $room["room_id"] ?>", name: "<?php echo $room["room_name"] ?>"},
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