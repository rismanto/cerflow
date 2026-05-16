<?php
/**
 * Student Dashboard - Learning View
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/CERMap.php';

if (!User::checkAuth('siswa')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$cerMapModel = new CERMap($db);

$maps = $cerMapModel->getAll();
// Load triplets for each map for the interactive part
foreach ($maps as &$map) {
    $map['triplets'] = $cerMapModel->getTriplets($map['id']);
}
unset($map);

$pageTitle = "Learning Workspace";
$extraHead = '<style>body { user-select: none; overflow: hidden; background: #f4f5f7; }</style>';
$extraFooter = '';

include 'partials/header.php';
include 'partials/navbar.php';
?>

<!-- Module Selector -->
<div id="view-selector" class="fixed inset-0 z-[40] bg-stone-100 p-12 pt-28 pb-24 overflow-y-auto">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <span class="text-xs font-black text-blue-700 bg-blue-100 px-4 py-2 uppercase tracking-widest mb-4 inline-block border border-blue-300">Curriculum Discovery</span>
            <h2 class="text-4xl font-black text-slate-800 uppercase italic tracking-tighter mt-4">Select Topic</h2>
            <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mt-3 mb-8">Pilih modul pembelajaran untuk memulai sesi Anda</p>
            
            <a href="history.php" class="inline-flex items-center gap-2 bg-white border-2 border-slate-300 text-slate-700 px-6 py-3 text-xs font-black uppercase tracking-widest hover:bg-slate-50 hover:border-blue-600 hover:text-blue-700 transition-all shadow-sm">
                📊 Lihat Hasil Sebelumnya
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="map-grid">
            <?php foreach($maps as $index => $map): ?>
            <div onclick="startLearning(<?= $index ?>)" class="bg-white p-8 border-2 border-slate-300 hover:border-blue-600 cursor-pointer shadow hover:shadow-lg hover:-translate-y-1 transition-all group relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-blue-700 opacity-0 group-hover:opacity-100 transition-all"></div>
                <div class="flex justify-between items-start mb-6">
                    <span class="text-xs font-black text-blue-700 bg-blue-50 px-3 py-1 border border-blue-200 uppercase">MODUL #<?= $map['id'] ?></span>
                    <span class="text-slate-200 group-hover:text-blue-100 transition-colors font-black text-4xl">0<?= $index + 1 ?></span>
                </div>
                <h4 class="font-black text-slate-800 text-xl group-hover:text-blue-700 leading-tight mb-8"><?= htmlspecialchars($map['title']) ?></h4>
                <div class="flex items-center gap-3 text-blue-700">
                    <span class="text-xs font-black uppercase tracking-widest">Mulai Sekarang</span>
                    <div class="w-8 h-[2px] bg-blue-700 transition-all group-hover:w-14"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Interactive Editor -->
<div id="view-editor" class="p-6 hidden h-[calc(100vh-130px)] overflow-hidden">
    <svg id="svg-canvas">
        <g id="line-group"></g>
        <line id="drawing-line" stroke="#4361ee" stroke-width="2" stroke-dasharray="6,4" x1="-1000" y1="-1000" x2="-1000" y2="-1000" />
    </svg>
    
    <div class="flex gap-6 max-w-[1600px] mx-auto h-full">
        <!-- Columns -->
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-blue-100 text-blue-800 px-6 py-2 uppercase tracking-widest border border-blue-300">1. Claims</span>
            </div>
            <div id="col-claim" class="kanban-column space-y-4 bg-blue-50/60 border-2 border-blue-100 flex-1"></div>
        </div>
        
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-emerald-100 text-emerald-800 px-6 py-2 uppercase tracking-widest border border-emerald-300">2. Evidences</span>
            </div>
            <div id="col-evidence" class="kanban-column space-y-4 bg-emerald-50/60 border-2 border-emerald-100 flex-1"></div>
        </div>
        
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-amber-100 text-amber-800 px-6 py-2 uppercase tracking-widest border border-amber-300">3. Reasonings</span>
            </div>
            <div id="col-reasoning" class="kanban-column space-y-4 bg-amber-50/60 border-2 border-amber-100 flex-1"></div>
        </div>
    </div>
</div>

<!-- Backdrop click-outside → stayOnPage -->
<div id="view-result" class="fixed inset-0 z-[200] bg-slate-900/85 backdrop-blur-sm flex items-center justify-center p-6 hidden" onclick="handleBackdropClick(event)">
    <!-- Modal card — stop propagation so clicking inside doesn't trigger backdrop -->
    <div class="bg-white p-12 max-w-lg w-full text-center shadow-2xl border-2 border-slate-300 relative" onclick="event.stopPropagation()">

        <!-- X close button -->
        <button onclick="stayOnPage()" title="Tutup dan lihat rekonstruksi" class="absolute top-4 right-4 w-9 h-9 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 text-xl font-black transition-all border border-transparent hover:border-slate-200">✕</button>

        <div class="w-20 h-20 bg-blue-100 text-blue-700 border-2 border-blue-300 flex items-center justify-center text-4xl mx-auto mb-8">
            🏆
        </div>
        <h2 class="text-3xl font-black text-slate-800 uppercase italic mb-2">Pengerjaan Selesai!</h2>
        <p class="text-slate-500 text-sm font-bold uppercase tracking-widest mb-8">Hasil skor Anda telah disimpan</p>
        
        <div class="bg-stone-100 border-2 border-slate-200 p-8 mb-8">
            <div id="result-score" class="text-6xl font-black text-blue-700 mb-2">0%</div>
            <div class="text-xs font-black text-slate-500 uppercase tracking-widest">Skor Akhir</div>
        </div>

        <!-- 3 post-submit options -->
        <div class="flex flex-col gap-3">
            <button id="btn-stay" onclick="stayOnPage()" class="w-full bg-blue-700 text-white py-4 font-bold hover:bg-blue-800 transition-all shadow uppercase tracking-widest text-sm">
                👁 Lihat Rekonstruksi Saya
            </button>
            <button onclick="redoActivity()" class="w-full bg-white border-2 border-slate-300 text-slate-700 py-3 font-bold hover:bg-stone-100 hover:border-slate-400 transition-all uppercase tracking-widest text-sm">
                🔄 Ulangi dari Awal
            </button>
            <button onclick="goToSelector()" class="w-full text-slate-400 hover:text-blue-700 py-3 font-bold uppercase tracking-widest text-xs transition-all">
                ← Kembali ke Pilihan Materi
            </button>
        </div>
    </div>
</div>

<!-- Reading Dialog -->
<div id="dialog-reading" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-[100] bg-white border-2 border-slate-300 shadow-2xl hidden flex flex-col min-w-[200px] min-h-[150px] rounded-xl overflow-hidden" style="width: 500px; height: 400px;">
    <div id="dialog-reading-header" class="bg-slate-50 border-b-2 border-slate-200 text-slate-800 px-6 py-4 flex justify-between items-center cursor-grab active:cursor-grabbing">
        <span class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
            <span class="text-lg">📖</span> Materi Bacaan
        </span>
        <button onclick="toggleReading()" class="text-slate-400 hover:text-red-500 font-black transition-colors">✕</button>
    </div>
    <div id="dialog-reading-content" class="flex-1 p-6 overflow-y-auto text-sm leading-relaxed text-slate-700 font-medium whitespace-pre-wrap">
        <!-- Text goes here -->
    </div>
    <div id="dialog-reading-resize" class="absolute bottom-0 right-0 w-4 h-4 cursor-nwse-resize bg-slate-200 hover:bg-slate-300"></div>
</div>

<script>
    const cerMaps = <?= json_encode($maps, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="assets/js/dialog-utils.js"></script>
<?php $siswaJsVersion = file_exists('assets/js/siswa.js') ? filemtime('assets/js/siswa.js') : time(); ?>
<script src="assets/js/siswa.js?v=<?php echo $siswaJsVersion; ?>"></script>

<?php include 'partials/footer.php'; ?>
