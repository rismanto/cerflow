<?php
/**
 * Log Evaluation Page - View Student Interaction Logs
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/UserLog.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$logModel = new UserLog($db);

$filters = [
    'materi' => $_GET['materi'] ?? '',
    'siswa' => $_GET['siswa'] ?? '',
    'start' => $_GET['start'] ?? '',
    'end' => $_GET['end'] ?? ''
];

$sessions = $logModel->getCompletedSessions($filters);
$loggedMaps = $logModel->getLoggedMaps();

$pageTitle = "Interaction Log Analytics";
$navContext = 'logs';
include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="w-fit min-w-full mx-auto p-8 pb-24">
    <div class="mb-8">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Interaction <span class="text-blue-700">Log Analytics</span></h2>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Behavioral insights and session timelines</p>
    </div>

    <form method="GET" action="logs.php" class="bg-white p-6 border-2 border-slate-300 shadow-sm mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Filter Materi</label>
            <select name="materi" id="filter-materi" class="w-full text-sm" placeholder="Cari materi...">
                <option value="">Semua Materi</option>
                <?php foreach($loggedMaps as $map): ?>
                    <option value="<?php echo htmlspecialchars($map['title']); ?>" <?php echo ($filters['materi'] === $map['title']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($map['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Filter Siswa</label>
            <input type="text" name="siswa" value="<?php echo htmlspecialchars($filters['siswa']); ?>" placeholder="Cari siswa..." class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <div class="w-52">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Time Start</label>
            <input type="datetime-local" name="start" value="<?php echo htmlspecialchars($filters['start']); ?>" class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <div class="w-52">
            <label class="text-xs font-black text-slate-500 uppercase tracking-widest block mb-2">Time End</label>
            <input type="datetime-local" name="end" value="<?php echo htmlspecialchars($filters['end']); ?>" class="w-full p-3 bg-slate-50 border-2 border-slate-200 text-sm outline-none focus:border-blue-600 transition-all">
        </div>
        <button type="submit" class="bg-blue-700 text-white px-8 py-3 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow">Go</button>
        <button type="button" onclick="exportToExcel()" class="bg-emerald-600 text-white px-8 py-3 text-sm font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow">Export to Excel</button>
        <a href="logs.php" class="px-6 py-3 text-sm font-black text-slate-500 hover:text-blue-700 uppercase tracking-widest transition-all border-2 border-transparent hover:border-slate-300">Reset</a>
    </form>

    <div class="bg-white border-2 border-slate-300 shadow overflow-hidden">
        <table id="logs-table" class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-300 text-xs uppercase font-black text-slate-600 tracking-wider">
                    <th class="px-2 py-3">Sesi ID</th>
                    <th class="px-2 py-3">Materi</th>
                    <th class="px-2 py-3">Siswa</th>
                    <th class="px-2 py-3">Start</th>
                    <th class="px-2 py-3">End</th>
                    <th class="px-2 py-3">Durasi</th>
                    <th class="px-2 py-3 text-center">Score</th>
                    <th class="px-2 py-3 text-center">Total</th>
                    <th class="px-2 py-3 text-center">Connect</th>
                    <th class="px-2 py-3 text-center">Disconnect</th>
                    <th class="px-2 py-3 text-center">Move</th>
                    <th class="px-2 py-3 text-center">Arrange</th>
                    <th class="px-2 py-3 text-center">Feedback</th>
                    <th class="px-2 py-3 text-center">Reading</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if(empty($sessions)): ?>
                    <tr>
                        <td colspan="14" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-4">📝</span>
                                <p class="text-slate-400 font-bold italic text-sm uppercase tracking-widest">Belum ada log aktivitas yang tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach($sessions as $s): 
                    $start = new DateTime($s['first_action']);
                    $end = new DateTime($s['last_action']);
                    $interval = $start->diff($end);
                    $duration = $interval->format('%H:%I:%S');
                ?>
                <tr class="hover:bg-stone-50 transition-all">
                    <td class="px-2 py-3 text-sm font-mono font-bold text-blue-700">#S-<?php echo $s['session_id']; ?></td>
                    <td class="px-2 py-3 text-sm font-bold text-slate-700 max-w-[180px] truncate">
                        <?php echo htmlspecialchars($s['title']); ?>
                    </td>
                    <td class="px-2 py-3">
                        <div class="flex flex-col">
                            <span class="text-sm font-black text-slate-800"><?php echo htmlspecialchars($s['namalengkap'] ?? '-'); ?></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($s['username']); ?></span>
                        </div>
                    </td>
                    <td class="px-2 py-3 text-[11px] font-bold text-slate-500" data-timestamp="<?php echo strtotime($s['first_action']); ?>">
                        <?php echo date('d/m/y H:i', strtotime($s['first_action'])); ?>
                    </td>
                    <td class="px-2 py-3 text-[11px] font-bold text-slate-500" data-timestamp="<?php echo strtotime($s['last_action']); ?>">
                        <?php echo date('d/m/y H:i', strtotime($s['last_action'])); ?>
                    </td>
                    <td class="px-2 py-3 text-sm font-black text-blue-700">
                        <?php echo $duration; ?>
                    </td>
                    <td class="px-2 py-3 text-center font-black text-sm text-blue-700">
                        <?php echo isset($s['final_score']) ? number_format($s['final_score'], 0).'%' : '-'; ?>
                    </td>
                    <td class="px-2 py-3 text-center font-bold text-slate-700">
                        <?php echo $s['total_actions']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-emerald-600 font-bold">
                        <?php echo $s['count_connect']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-red-600 font-bold">
                        <?php echo $s['count_disconnect']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-blue-600 font-bold">
                        <?php echo $s['count_move']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-amber-600 font-bold">
                        <?php echo $s['count_auto_arrange']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-indigo-600 font-bold">
                        <?php echo $s['count_feedback']; ?>
                    </td>
                    <td class="px-2 py-3 text-center text-teal-600 font-bold">
                        <?php echo $s['count_view_reading']; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-control { border: 2px solid #e2e8f0; border-radius: 0; padding: 0.75rem; background: #f8fafc; font-size: 0.875rem; box-shadow: none; }
    .ts-control.focus { border-color: #2563eb; box-shadow: none; }
    .ts-dropdown { border: 2px solid #e2e8f0; border-top: none; border-radius: 0; font-size: 0.875rem; }
    .ts-dropdown .active { background-color: #eff6ff; color: #1d4ed8; }
</style>
<script>
    new TomSelect("#filter-materi", {
        plugins: ['remove_button'],
        create: false,
        sortField: { field: "text", direction: "asc" },
        allowEmptyOption: true,
    });

    function exportToExcel() {
        const table = document.getElementById('logs-table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        const data = [];
        // Header
        data.push([
            'Sesi ID', 'Materi', 'Nama (Username)', 'Nama Lengkap', 
            'Time Start', 'Time End', 'Durasi', 'Score (%)', 
            'Total Aksi', 'Aksi Connect', 'Aksi Disconnect', 
            'Aksi Move', 'Aksi Auto Arrange', 'Aksi Feedback', 'Aksi Reading'
        ]);

        rows.forEach(row => {
            if (row.children.length < 10) return; // Skip empty message rows

            const sesiId = row.children[0].innerText.trim().replace('#S-', '');
            const materi = row.children[1].innerText.trim();
            
            // Siswa column parsing
            const namaLengkap = row.children[2].querySelector('span.text-slate-800').innerText.trim();
            const username = row.children[2].querySelector('span.text-slate-400').innerText.replace(/user:\s*/i, '').trim();
            
            const timeStart = row.children[3].innerText.trim();
            const timeEnd = row.children[4].innerText.trim();
            const durasi = row.children[5].innerText.trim();
            const score = row.children[6].innerText.trim().replace('%', '');
            const totalAksi = row.children[7].innerText.trim();
            const countConnect = row.children[8].innerText.trim();
            const countDisconnect = row.children[9].innerText.trim();
            const countMove = row.children[10].innerText.trim();
            const countAutoArrange = row.children[11].innerText.trim();
            const countFeedback = row.children[12].innerText.trim();
            const countReading = row.children[13].innerText.trim();

            data.push([
                sesiId, materi, username, namaLengkap, 
                timeStart, timeEnd, durasi, score, 
                totalAksi, countConnect, countDisconnect, 
                countMove, countAutoArrange, countFeedback, countReading
            ]);
        });

        if (data.length <= 1) {
            alert('Tidak ada data yang tersedia untuk diekspor.');
            return;
        }

        // Create workbook
        const ws = XLSX.utils.aoa_to_sheet(data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Interaction Logs");

        // Download
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        XLSX.writeFile(wb, `CERFlow_Logs_${timestamp}.xlsx`);
    }
</script>

<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<?php include 'partials/footer.php'; ?>
