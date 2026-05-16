<?php
/**
 * Map Management Dashboard - Content Hub
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Manajer Materi";
$navContext = 'maps';
include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="flex-1 p-8 pb-24 overflow-y-auto bg-stone-100 min-h-screen">
    <div class="max-w-6xl mx-auto">
        
        <!-- Header & Search -->
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase italic">Content <span class="text-blue-700">Hub</span></h2>
                <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Kelola dan pantau semua modul pembelajaran</p>
            </div>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <input type="text" id="hub-search" placeholder="Cari judul materi..." class="w-full pl-12 pr-4 py-4 bg-white border-2 border-slate-200 text-sm font-bold outline-none focus:border-blue-600 transition-all shadow-sm">
                    <span class="absolute left-4 top-4 text-xl">🔍</span>
                </div>
                <a href="admin.php" class="bg-blue-700 text-white px-8 py-4 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow-lg shrink-0">
                    + Map Baru
                </a>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 border-2 border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-black">📚</div>
                <div>
                    <div id="stat-total" class="text-2xl font-black text-slate-800">0</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Materi</div>
                </div>
            </div>
            <div class="bg-white p-6 border-2 border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-black">💡</div>
                <div>
                    <div id="stat-active" class="text-2xl font-black text-slate-800">0</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Feedback Aktif</div>
                </div>
            </div>
            <div class="bg-white p-6 border-2 border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-black">🧩</div>
                <div>
                    <div id="stat-triplets" class="text-2xl font-black text-slate-800">0</div>
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Triplet</div>
                </div>
            </div>
        </div>

        <!-- Map Grid -->
        <div id="hub-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Loaded via JS -->
            <div class="col-span-full py-20 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-700 border-t-transparent mb-4"></div>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Memuat koleksi materi...</p>
            </div>
        </div>

    </div>
</div>

<script>
    let allMaps = [];

    function loadHub() {
        fetch('api.php?action=get_maps')
        .then(res => res.json())
        .then(data => {
            allMaps = data;
            
            // Stats
            document.getElementById('stat-total').innerText = data.length;
            document.getElementById('stat-active').innerText = data.filter(m => m.allow_feedback == 1).length;
            
            // For total triplets, we'd need another API call or embed it in get_maps
            // For now, let's just render
            renderHub(data);
        });
    }

    function renderHub(maps) {
        const grid = document.getElementById('hub-grid');
        if (maps.length === 0) {
            grid.innerHTML = `<div class="col-span-full py-20 text-center bg-white border-2 border-dashed border-slate-300">
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Tidak ada materi ditemukan.</p>
            </div>`;
            return;
        }

        grid.innerHTML = maps.map((m, i) => `
            <div class="bg-white border-2 border-slate-300 hover:border-blue-600 transition-all group shadow-sm hover:shadow-xl relative overflow-hidden flex flex-col">
                <div class="absolute top-0 left-0 w-full h-1 bg-blue-700 opacity-0 group-hover:opacity-100 transition-all"></div>
                
                <div class="p-8 flex-1">
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black text-blue-700 bg-blue-50 px-3 py-1 border border-blue-200 uppercase tracking-widest">Materi #${m.id}</span>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                <span title="${m.allow_feedback == 1 ? 'Feedback Aktif' : 'Feedback Nonaktif'}" class="${m.allow_feedback == 1 ? '' : 'grayscale opacity-40'}">💡</span>
                            </div>
                            ${m.reading_text && m.reading_text.trim() !== '' ? `
                                <div class="flex items-center gap-1">
                                    <span title="${m.allow_reading == 1 ? 'Bacaan Aktif' : 'Bacaan Nonaktif'}" class="${m.allow_reading == 1 ? '' : 'grayscale opacity-40'}">📖</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-800 group-hover:text-blue-700 transition-colors leading-tight mb-4">${m.title}</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-8">Dibuat pada: ${new Date(m.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'})}</p>
                </div>

                <div class="p-6 bg-slate-50 border-t-2 border-slate-100 flex gap-2">
                    <button onclick="editMap(${m.id})" class="flex-1 bg-white border-2 border-slate-200 text-slate-700 py-3 text-[10px] font-black uppercase tracking-widest hover:border-blue-600 hover:text-blue-700 transition-all shadow-sm">
                        ✏️ Edit Studio
                    </button>
                    <a href="preview_map.php?map_id=${m.id}" target="_blank" class="px-4 bg-white border-2 border-slate-200 text-slate-700 py-3 flex items-center justify-center hover:bg-slate-100 transition-all shadow-sm" title="Preview">
                        👁
                    </a>
                </div>
            </div>
        `).join('');
    }

    function editMap(id) {
        window.location.href = `admin.php?edit_id=${id}`;
    }

    document.getElementById('hub-search').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = allMaps.filter(m => 
            m.title.toLowerCase().includes(term) || 
            m.id.toString().includes(term)
        );
        renderHub(filtered);
    });

    document.addEventListener('DOMContentLoaded', loadHub);
</script>

<?php include 'partials/footer.php'; ?>
