<?php
/**
 * Application Settings - Admin/Teacher Only
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/Setting.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$settingModel = new Setting($db);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $settingModel->set($key, $value);
    }
    $message = 'Pengaturan berhasil diperbarui.';
}

$pageTitle = "Settings";
$navContext = 'settings';
include 'partials/header.php';
include 'partials/navbar.php';

$geminiKey = $settingModel->get('gemini_api_key', '');
?>

<div class="w-fit min-w-full mx-auto p-8 pb-24">
    <div class="max-w-3xl">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">Pengaturan <span class="text-blue-700">Aplikasi</span></h2>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Kelola fitur dan perilaku sistem</p>
        </div>

        <?php if($message): ?>
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 font-bold uppercase text-xs tracking-widest">
                ✓ <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 font-bold uppercase text-xs tracking-widest">
                ✗ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white border-2 border-slate-300 shadow-sm">
            <div class="p-6 border-b-2 border-slate-100">
                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Fitur Workspace Siswa</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-5 bg-blue-50 border border-blue-200">
                    <p class="text-sm font-bold text-blue-800 uppercase tracking-tight mb-2">💡 Info Pengaturan Feedback</p>
                    <p class="text-xs text-blue-700 leading-relaxed font-medium">Pengaturan "Izinkan Feedback" sekarang dikelola secara individual per modul. Silakan buka <a href="admin.php" class="underline font-black">Studio Map</a> untuk mengaktifkan/menonaktifkan feedback pada masing-masing materi.</p>
                </div>
            </div>

            <div class="p-6 border-b-2 border-slate-100 bg-slate-50/50">
                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Integrasi AI (Gemini)</p>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Google Gemini API Key</label>
                    <input type="password" name="gemini_api_key" value="<?php echo htmlspecialchars($geminiKey); ?>" class="w-full bg-slate-50 border-2 border-slate-200 p-3 text-sm font-bold focus:border-blue-500 focus:bg-white outline-none transition-all" placeholder="Pate your API Key here...">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-2">Dapatkan API Key gratis di <a href="https://aistudio.google.com/" target="_blank" class="text-blue-600 underline">Google AI Studio</a>. Digunakan untuk fitur ekstraksi komponen CER otomatis.</p>
                </div>
            </div>

            <div class="px-6 py-5 border-t-2 border-slate-100 bg-slate-50">
                <button type="submit" class="bg-blue-700 text-white px-10 py-3 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
