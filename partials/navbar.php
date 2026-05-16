<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$navContext = isset($navContext) ? $navContext : 'admin'; // 'admin' | 'logs' | 'report'
?>
<nav class="bg-white border-b-2 border-slate-300 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
    <div class="flex items-center gap-4">
        <a href="index.php">
            <h1 class="text-xl font-black text-blue-700 italic uppercase">CER<span class="text-slate-800">Flow</span></h1>
        </a>
        
        <?php if ($role == 'siswa'): ?>
        <div id="active-title-container" class="hidden flex items-center gap-2">
            <span class="text-slate-300">/</span>
            <div id="active-title" class="text-blue-700 font-black text-[11px] uppercase italic tracking-tight bg-blue-50 px-3 py-1.5 border border-blue-200 shadow-inner">
                <?php echo isset($activeTitle) ? $activeTitle : ''; ?>
            </div>
            <button onclick="backToSelector()" class="bg-white border border-slate-300 text-slate-600 hover:text-blue-700 hover:border-blue-400 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest transition-all ml-1">
                Pilih materi lain
            </button>
        </div>
        <?php endif; ?>

        <?php if ($role == 'guru'): ?>
            <span class="text-slate-300">/</span>
            <span class="text-sm font-black text-slate-500 uppercase tracking-wider">
                <?php
                    if ($navContext === 'logs') echo 'Log Aktivitas';
                    elseif ($navContext === 'report') echo 'Laporan Nilai';
                    elseif ($navContext === 'users') echo 'User Management';
                    elseif ($navContext === 'settings') echo 'Pengaturan';
                    elseif ($navContext === 'maps') echo 'Manajer Materi';
                    else echo 'Studio';
                ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-3">
        <?php if ($role == 'guru'): ?>
            <?php if ($navContext === 'admin'): ?>
                <!-- Admin / CER Map Studio navbar -->
                <a href="maps.php" class="text-sm font-bold text-slate-600 hover:text-blue-700 hover:bg-blue-50 px-4 py-2 border border-transparent hover:border-blue-200 transition-all">📚 Materi</a>
                <a href="users.php" class="text-sm font-bold text-slate-600 hover:text-blue-700 hover:bg-blue-50 px-4 py-2 border border-transparent hover:border-blue-200 transition-all">👥 Users</a>
                <a href="logs.php" class="text-sm font-bold text-slate-600 hover:text-blue-700 hover:bg-blue-50 px-4 py-2 border border-transparent hover:border-blue-200 transition-all">📝 Log Aktivitas</a>
                <a href="report.php" class="text-sm font-bold text-slate-600 hover:text-blue-700 hover:bg-blue-50 px-4 py-2 border border-transparent hover:border-blue-200 transition-all">📊 Laporan Nilai</a>
                <a href="settings.php" class="text-sm font-bold text-slate-600 hover:text-blue-700 hover:bg-blue-50 px-4 py-2 border border-transparent hover:border-blue-200 transition-all">⚙️ Settings</a>
            <?php else: ?>
                <!-- Logs / Report / Users page navbar — no publish button -->
                <a href="maps.php" class="text-sm font-bold <?php echo $navContext === 'maps' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700'; ?> px-4 py-2 transition-all">📚 Materi</a>
                <a href="users.php" class="text-sm font-bold <?php echo $navContext === 'users' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700'; ?> px-4 py-2 transition-all">👥 Users</a>
                <a href="logs.php" class="text-sm font-bold <?php echo $navContext === 'logs' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700'; ?> px-4 py-2 transition-all">📝 Log Aktivitas</a>
                <a href="report.php" class="text-sm font-bold <?php echo $navContext === 'report' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700'; ?> px-4 py-2 transition-all">📊 Laporan Nilai</a>
                <a href="settings.php" class="text-sm font-bold <?php echo $navContext === 'settings' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-slate-600 hover:text-blue-700'; ?> px-4 py-2 transition-all">⚙️ Settings</a>
                <a href="admin.php" class="bg-slate-800 text-white px-6 py-2 text-sm font-bold shadow hover:bg-black transition-all">← Kembali ke Studio</a>
            <?php endif; ?>
        <?php elseif ($role == 'siswa'): ?>
            <?php if ($navContext === 'history' || $navContext === 'report'): ?>
                <a href="siswa.php" class="bg-slate-800 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow hover:bg-black transition-all mr-1">← Kembali</a>
            <?php endif; ?>
            <div id="editor-actions" class="hidden flex gap-2">
                <button id="btn-reading" onclick="toggleReading()" class="hidden bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">📖 Bacaan</button>
                <?php if (!isset($allowFeedbackDuringWork) || $allowFeedbackDuringWork == '1'): ?>
                    <button id="btn-feedback" onclick="toggleFeedback()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">💡 Feedback</button>
                <?php endif; ?>
                <button onclick="autoArrange()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">🪄 Arrange</button>
                <button onclick="submitScore()" class="bg-blue-700 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-blue-800 shadow transition-all">Submit</button>
            </div>
        <?php endif; ?>

        <?php if ($role): ?>
            <a href="logout.php" class="text-[10px] font-black text-red-600 hover:bg-red-50 px-3 py-1.5 border border-red-200 hover:border-red-400 transition-all ml-1">LOGOUT</a>
        <?php endif; ?>
    </div>
</nav>
