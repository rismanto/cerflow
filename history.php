<?php
/**
 * Student History Page
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/Score.php';

if (!User::checkAuth('siswa')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$scoreModel = new Score($db);

$user_id = $_SESSION['user_id'];
$scores = $scoreModel->getByUserId($user_id);

$pageTitle = "Hasil Sebelumnya";
$navContext = 'history';

include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="max-w-6xl mx-auto p-8 pb-24">
    <div class="mb-10 text-center">
        <span class="text-xs font-black text-indigo-700 bg-indigo-100 px-4 py-2 uppercase tracking-widest mb-4 inline-block border border-indigo-300">Riwayat Belajar</span>
        <h2 class="text-4xl font-black text-slate-800 tracking-tight uppercase italic mt-4">Hasil <span class="text-blue-700">Sebelumnya</span></h2>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-3">Lihat kembali map yang telah Anda kerjakan</p>
    </div>

    <div class="bg-white border-2 border-slate-300 shadow overflow-hidden">
        <table class="w-full text-left border-collapse" id="history-table">
            <thead>
                <tr class="bg-slate-100 border-b-2 border-slate-300 text-xs uppercase font-black text-slate-600 tracking-wider">
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(0)">Sesi ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(1)">Materi ↕</th>
                    <th class="p-5 cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(2)">Waktu Submit ↕</th>
                    <th class="p-5 text-center cursor-pointer hover:text-blue-700 transition-colors" onclick="sortTable(3)">Skor ↕</th>
                    <th class="p-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100" id="table-body">
                <?php if (count($scores) > 0): ?>
                    <?php foreach ($scores as $s): ?>
                        <tr class="hover:bg-stone-50 transition-all group" data-full-date="<?php echo $s['submitted_at']; ?>">
                            <td class="p-5 text-sm font-mono font-bold text-slate-500">
                                #S-<?php echo $s['session_id']; ?>
                            </td>
                            <td class="p-5 text-sm font-medium text-slate-800">
                                <?php echo htmlspecialchars($s['map_title']); ?>
                            </td>
                            <td class="p-5 text-sm font-bold text-slate-500">
                                <?php echo date('d M Y, H:i', strtotime($s['submitted_at'])); ?>
                            </td>
                            <td class="p-5 text-center">
                                <span class="px-4 py-1.5 text-xs font-black border <?php echo $s['score'] >= 70 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300'; ?>">
                                    <?php echo $s['score']; ?>%
                                </span>
                            </td>
                            <td class="p-5 text-center">
                                <?php if (!empty($s['map_data'])): ?>
                                    <a href="view_map.php?score_id=<?php echo $s['score_id']; ?>" class="bg-indigo-100 text-indigo-700 border border-indigo-300 px-4 py-2 text-xs font-black uppercase tracking-widest hover:bg-indigo-700 hover:text-white transition-all inline-block">
                                        Lihat Map
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-300 text-[10px] font-bold uppercase tracking-widest">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-6xl mb-6 opacity-50">📭</span>
                                <h3 class="text-xl font-black text-slate-700 uppercase mb-2">Belum Ada Riwayat</h3>
                                <p class="text-slate-400 font-bold italic text-sm uppercase tracking-widest">Anda belum menyelesaikan materi apapun.</p>
                                <a href="siswa.php" class="mt-6 inline-block bg-blue-700 text-white px-8 py-3 text-sm font-bold shadow hover:bg-blue-800 transition-all uppercase tracking-widest">
                                    Mulai Belajar Sekarang
                                </a>
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
    
    // Default sorting logic relies on the SQL ORDER BY s.submitted_at DESC which is already applied
    let sortDirection = 1;
    function sortTable(columnIndex) {
        if(rows.length === 0 || rows[0].children.length < 5) return;
        
        sortDirection *= -1;
        const sortedRows = rows.sort((a, b) => {
            let aText = a.children[columnIndex].innerText.trim();
            let bText = b.children[columnIndex].innerText.trim();

            if (columnIndex === 0 || columnIndex === 3) {
                // Number columns: Sesi (0), Skor (3)
                let numA = parseFloat(aText.replace(/[^0-9.-]+/g, ""));
                let numB = parseFloat(bText.replace(/[^0-9.-]+/g, ""));
                return (numA - numB) * sortDirection;
            }
            
            if (columnIndex === 2) {
                // Date column: Waktu Submit (2)
                let dateA = new Date(a.getAttribute('data-full-date')).getTime();
                let dateB = new Date(b.getAttribute('data-full-date')).getTime();
                return (dateA - dateB) * sortDirection;
            }

            // String columns: Materi (1)
            return aText.localeCompare(bText) * sortDirection;
        });

        tableBody.innerHTML = '';
        sortedRows.forEach(row => tableBody.appendChild(row));
    }
</script>

<?php include 'partials/footer.php'; ?>
