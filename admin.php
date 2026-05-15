<?php
/**
 * Admin Dashboard - Teacher View
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/CERMap.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Curriculum Studio";
$initialEditMapId = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$initialEditMap = null;

if ($initialEditMapId > 0) {
    $database = new Database();
    $db = $database->getConnection();
    $cerMapModel = new CERMap($db);
    $initialEditMap = $cerMapModel->getById($initialEditMapId);
}

include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="flex flex-1 overflow-hidden">
    <!-- Sidebar: Map List -->
    <div class="w-72 bg-white border-r-2 border-slate-300 flex flex-col">
        <div class="p-4 border-b-2 border-slate-200 space-y-3">
            <button onclick="newMap()" class="w-full py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest hover:bg-black transition-all shadow">
                + Buat Map Baru
            </button>
            <div class="relative">
                <input type="text" id="map-search" placeholder="Cari materi..." class="w-full pl-8 pr-3 py-2 bg-slate-100 border border-slate-200 text-xs font-bold outline-none focus:border-indigo-500 transition-all">
                <span class="absolute left-2.5 top-2 text-slate-400">🔍</span>
            </div>
        </div>
        <div id="map-list" class="flex-1 overflow-y-auto">
            <!-- Loaded via JS -->
            <div class="p-8 text-center text-slate-400 italic text-sm">Memuat data...</div>
        </div>
    </div>

    <!-- Main Content: Map Editor -->
    <div class="flex-1 p-8 pb-24 overflow-y-auto bg-stone-100">
        <div class="max-w-4xl mx-auto">

            <div class="mb-8">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">CER Map <span class="text-blue-700">Studio</span></h2>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Create and publish learning modules</p>
            </div>

            <div class="bg-white p-10 border-2 border-slate-300 shadow-lg">
                <!-- Top Action Bar -->
                <div class="mb-6 flex justify-between items-center bg-slate-50 border border-slate-200 px-6 py-3 -mx-10 -mt-10 mb-10 border-x-0 border-t-0">
                    <div class="flex items-center gap-6">
                        <!-- Feedback Toggle -->
                        <div class="flex items-center gap-3 shrink-0">
                            <div>
                                <label for="allow-feedback" class="text-[9px] font-black text-slate-500 uppercase tracking-widest block cursor-pointer leading-none">Allow Feedback</label>
                                <p class="text-[8px] text-slate-400 font-bold uppercase leading-none mt-1">During work</p>
                            </div>
                            <div class="relative inline-block w-8 h-4">
                                <input type="checkbox" id="allow-feedback" checked class="peer appearance-none w-8 h-4 bg-slate-300 rounded-full checked:bg-blue-600 cursor-pointer transition-colors duration-200">
                                <label for="allow-feedback" class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full transition-transform duration-200 cursor-pointer shadow peer-checked:translate-x-4"></label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <a id="btn-preview-map" href="#" target="_blank" class="hidden bg-white text-indigo-700 border border-indigo-200 px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 hover:text-white transition-all shadow-sm">
                            👁 Preview Map
                        </a>
                        <button onclick="saveToDB()" class="bg-blue-700 text-white px-5 py-2 text-[10px] font-black uppercase tracking-widest shadow hover:bg-blue-800 transition-all">
                            💾 Publikasikan Map
                        </button>
                    </div>
                </div>

                <div class="mb-10">
                    <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Judul Materi / Map</label>
                    <input id="map-title" type="text" placeholder="Masukkan Judul Materi..." class="text-2xl font-black w-full mt-1 border-b-2 border-slate-200 pb-3 outline-none focus:border-blue-600 transition-all text-slate-800">
                </div>

                <!-- Input Triplet Form -->
                <div class="bg-blue-50 p-8 border-2 border-blue-200 mb-8">
                    <div class="grid grid-cols-3 gap-6 mb-6">
                        <div class="space-y-2">
                            <label class="text-xs uppercase font-black text-blue-600 tracking-widest block mb-1">1. Claim</label>
                            <textarea id="in-c" placeholder="Apa pernyataannya?" class="w-full p-4 border-2 border-blue-200 text-sm h-32 focus:border-blue-600 outline-none resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs uppercase font-black text-blue-600 tracking-widest block mb-1">2. Evidence</label>
                            <textarea id="in-e" placeholder="Mana buktinya?" class="w-full p-4 border-2 border-blue-200 text-sm h-32 focus:border-blue-600 outline-none resize-none"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs uppercase font-black text-blue-600 tracking-widest block mb-1">3. Reasoning</label>
                            <textarea id="in-r" placeholder="Apa alasannya?" class="w-full p-4 border-2 border-blue-200 text-sm h-32 focus:border-blue-600 outline-none resize-none"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button id="btn-save-triplet" onclick="addLocal()" class="bg-blue-700 text-white px-8 py-3 text-sm font-bold hover:bg-blue-800 flex-1 transition-all shadow uppercase tracking-tight">
                            + Tambahkan Triplet ke Map Ini
                        </button>
                        <button id="btn-cancel-triplet" onclick="cancelTripletEdit()" class="hidden bg-white border-2 border-blue-200 text-blue-700 px-6 py-3 text-sm font-bold hover:bg-blue-100 transition-all shadow uppercase tracking-tight">
                            Batal Edit
                        </button>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="border-2 border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 border-b-2 border-slate-200">
                            <tr class="text-slate-600">
                                <th class="p-4 font-black uppercase tracking-wider">Claim</th>
                                <th class="p-4 font-black uppercase tracking-wider">Evidence</th>
                                <th class="p-4 font-black uppercase tracking-wider">Reasoning</th>
                                <th class="p-4 font-black uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="divide-y divide-slate-100">
                            <!-- Loaded via JS -->
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate-400 italic text-sm">Belum ada triplet ditambahkan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$adminJsVersion = file_exists('assets/js/admin.js') ? filemtime('assets/js/admin.js') : time();
$initialEditMapJson = json_encode($initialEditMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$extraFooter = '<script>window.INITIAL_EDIT_MAP_ID = ' . $initialEditMapId . ';</script>' .
    '<script>window.INITIAL_EDIT_MAP_DATA = ' . $initialEditMapJson . ';</script>' .
    '<script src="assets/js/admin.js?v=' . $adminJsVersion . '"></script>';
include 'partials/footer.php'; 
?>
