<?php
/**
 * Report Page - View Student Scores
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/Score.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$scoreModel = new Score($db);

$reports = $scoreModel->getAllReports();

$pageTitle = "Performance Analytics";
$navContext = 'report';
include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="w-fit min-w-full mx-auto p-8 pb-24">
    <div class="mb-8">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Academic <span class="text-blue-700">Performance Analytics</span></h2>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Learning outcome data and session metrics</p>
    </div>

    <div class="bg-white p-6 border-2 border-slate-300 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Filter Nama Siswa</label>
            <input type="text" id="filter-student" placeholder="Cari siswa..." class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <div class="flex-1 min-w-[200px]">
            <?php $uniqueModules = array_unique(array_column($reports, 'title')); ?>
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Filter Materi</label>
            <select id="filter-module" placeholder="Cari materi..." class="w-full text-sm">
                <option value="">Semua Materi</option>
                <?php foreach ($uniqueModules as $mod): ?>
                    <option value="<?php echo htmlspecialchars($mod); ?>"><?php echo htmlspecialchars($mod); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-36">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Map ID</label>
            <input type="text" id="filter-id" placeholder="ID..." class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <div class="w-48">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Tanggal</label>
            <input type="date" id="filter-date" class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <button onclick="applyFilters()" class="bg-blue-700 text-white px-8 py-3 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow">Go</button>
        <button onclick="exportToExcel()" class="bg-emerald-600 text-white px-8 py-3 text-sm font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow">Export to Excel</button>
        <button onclick="resetFilters()" class="px-6 py-3 text-sm font-black text-slate-500 hover:text-blue-700 uppercase tracking-widest transition-all border-2 border-transparent hover:border-slate-300">Reset</button>
    </div>

    <div class="bg-white border-2 border-slate-300 shadow overflow-hidden">
        <table class="w-full text-left border-collapse whitespace-nowrap" id="reports-table">
            <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-300 text-xs uppercase font-black text-slate-600 tracking-wider">
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(0)">Map ID ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(1)">Session ID ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(2)">Siswa ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(3)">Materi ↕</th>
                    <th class="p-5 text-center cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(4)">Skor ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(5)">Waktu Submit ↕</th>
                    <th class="p-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="table-body">
                <?php if (count($reports) > 0): ?>
                    <?php foreach($reports as $r): ?>
                        <tr class="hover:bg-stone-50 transition-all group" 
                            data-date="<?php echo date('Y-m-d', strtotime($r['submitted_at'])); ?>"
                            data-full-date="<?php echo $r['submitted_at']; ?>">
                        <td class="p-5 text-sm font-mono font-bold text-blue-700">
                            #<?php echo $r['map_id']; ?>
                        </td>
                        <td class="p-5 text-sm font-mono font-bold text-slate-500">
                            <?php echo $r['session_id'] ? '#S-' . $r['session_id'] : '-'; ?>
                        </td>
                        <td class="p-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-800"><?php echo htmlspecialchars($r['namalengkap']); ?></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">User: <?php echo htmlspecialchars($r['username']); ?></span>
                            </div>
                        </td>
                        <td class="p-5 text-sm font-medium text-slate-700">
                            <?php echo htmlspecialchars($r['title']); ?>
                        </td>
                        <td class="p-5 text-center">
                            <span class="px-4 py-1.5 text-xs font-black border <?php echo $r['score'] >= 70 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'; ?>">
                                <?php echo $r['score']; ?>%
                            </span>
                        </td>
                        <td class="p-5 text-sm font-bold text-slate-500">
                            <?php echo date('d M Y, H:i', strtotime($r['submitted_at'])); ?>
                        </td>
                        <td class="p-5 text-center flex gap-2 justify-center">
                            <?php if (!empty($r['map_data'])): ?>
                                <a href="view_map.php?score_id=<?php echo $r['score_id']; ?>" class="bg-indigo-100 text-indigo-700 border border-indigo-300 px-4 py-2 text-xs font-black uppercase tracking-widest hover:bg-indigo-700 hover:text-white transition-all">Lihat Map</a>
                            <?php endif; ?>
                            <?php if ($r['session_id']): ?>
                                <a href="logs.php?materi=<?php echo urlencode($r['title']); ?>&siswa=<?php echo urlencode($r['username']); ?>" class="bg-blue-100 text-blue-700 border border-blue-300 px-4 py-2 text-xs font-black uppercase tracking-widest hover:bg-blue-700 hover:text-white transition-all">Lihat Log</a>
                            <?php else: ?>
                                <?php if (empty($r['map_data'])): ?>
                                    <span class="text-slate-300 text-xs font-bold uppercase tracking-widest">N/A</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-4">📊</span>
                                <p class="text-slate-400 font-bold italic text-sm uppercase tracking-widest">Belum ada data skor yang masuk.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const tableBody = document.getElementById('table-body');
    const rows = Array.from(tableBody.getElementsByTagName('tr'));
    
    // Filtering Logic
    const studentInput = document.getElementById('filter-student');
    const moduleInput = document.getElementById('filter-module');
    const idInput = document.getElementById('filter-id');
    const dateInput = document.getElementById('filter-date');

    // Handle Enter key for all inputs
    [studentInput, idInput, dateInput].forEach(input => {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') applyFilters();
        });
    });

    function applyFilters() {
        const studentVal = studentInput.value.toLowerCase();
        const moduleVal = moduleInput.value.toLowerCase();
        const idVal = idInput.value.toLowerCase();
        const dateVal = dateInput.value; // YYYY-MM-DD

        rows.forEach(row => {
            if (row.children.length < 5) return; // empty row fallback
            const idText = row.children[0].innerText.toLowerCase();
            const sessionText = row.children[1].innerText.toLowerCase();
            const studentText = row.children[2].innerText.toLowerCase();
            const moduleText = row.children[3].innerText.toLowerCase();
            const dateText = row.getAttribute('data-date'); 

            const matchesStudent = studentText.includes(studentVal);
            const matchesModule = moduleText.includes(moduleVal);
            const matchesId = idText.includes(idVal) || sessionText.includes(idVal);
            const matchesDate = !dateVal || dateText === dateVal;

            row.style.display = (matchesStudent && matchesModule && matchesId && matchesDate) ? '' : 'none';
        });
    }

    function resetFilters() {
        [studentInput, moduleInput, idInput, dateInput].forEach(input => input.value = '');
        applyFilters();
    }

    // Sorting Logic
    let sortDirection = 1;
    function sortTable(columnIndex) {
        sortDirection *= -1;
        const sortedRows = rows.sort((a, b) => {
            let aText = a.children[columnIndex].innerText.trim();
            let bText = b.children[columnIndex].innerText.trim();

            if (columnIndex === 0 || columnIndex === 3) {
                return (parseFloat(aText.replace(/[^0-9.]/g, '')) - parseFloat(bText.replace(/[^0-9.]/g, ''))) * sortDirection;
            }
            
            if (columnIndex === 5) {
                return (new Date(a.getAttribute('data-full-date')) - new Date(b.getAttribute('data-full-date'))) * sortDirection;
            }

            return aText.localeCompare(bText) * sortDirection;
        });

        tableBody.innerHTML = '';
        sortedRows.forEach(row => tableBody.appendChild(row));
    }
</script>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    /* Customize TomSelect to match CERFlow aesthetic */
    .ts-control { border: 2px solid #e2e8f0; border-radius: 0; padding: 0.75rem; background: #f8fafc; font-size: 0.875rem; box-shadow: none; }
    .ts-control.focus { border-color: #2563eb; box-shadow: none; }
    .ts-dropdown { border: 2px solid #e2e8f0; border-top: none; border-radius: 0; font-size: 0.875rem; }
    .ts-dropdown .active { background-color: #eff6ff; color: #1d4ed8; }
</style>
<script>
    const tsModule = new TomSelect("#filter-module", {
        plugins: ['remove_button'],
        create: false,
        sortField: { field: "text", direction: "asc" },
        allowEmptyOption: true,
    });

    // Override resetFilters to also clear TomSelect
    const originalResetFilters = resetFilters;
    window.resetFilters = function() {
        [studentInput, idInput, dateInput].forEach(input => input.value = '');
        tsModule.clear();
        applyFilters();
    };

    function exportToExcel() {
        // Data extraction
        const data = [];
        // Header
        data.push(['Map ID', 'Session ID', 'Materi', 'Nama (Username)', 'Nama Lengkap', 'Skor (%)', 'Waktu Submit']);
        
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            if (row.children.length < 5) return; // Skip empty message rows

            const mapId = row.children[0].innerText.trim().replace('#', '');
            const sessionId = row.children[1].innerText.trim().replace('#S-', '');
            
            // Siswa column parsing
            const namaLengkap = row.children[2].querySelector('span.text-slate-800').innerText.trim();
            const username = row.children[2].querySelector('span.text-slate-400').innerText.replace(/user:\s*/i, '').trim();
            
            const materi = row.children[3].innerText.trim();
            const skor = row.children[4].innerText.trim().replace('%', '');
            const waktuSubmit = row.children[5].innerText.trim();

            data.push([mapId, sessionId, materi, username, namaLengkap, skor, waktuSubmit]);
        });

        if (data.length <= 1) {
            alert('Tidak ada data yang tersedia untuk diekspor (cek filter Anda).');
            return;
        }

        // Create workbook
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Reports");

        // Download
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        XLSX.writeFile(wb, `CERFlow_Report_${timestamp}.xlsx`);
    }
</script>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<?php include 'partials/footer.php'; ?>