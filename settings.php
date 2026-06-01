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
    $userModel = new User($db);
    foreach ($_POST as $key => $value) {
        if ($key === 'gemini_api_key') {
            // Save to current user's profile
            $currentUser = $userModel->getById($_SESSION['user_id']);
            $userModel->update($_SESSION['user_id'], $currentUser['username'], $currentUser['namalengkap'], $currentUser['role'], null, $value);
        } else {
            // Save to global settings
            $settingModel->set($key, $value);
        }
    }
    $message = 'Pengaturan berhasil diperbarui.';
}

$pageTitle = "Settings";
$navContext = 'settings';
include 'partials/header.php';
include 'partials/navbar.php';

$userModel = new User($db);
$currentUser = $userModel->getById($_SESSION['user_id']);
$geminiKey = $currentUser['gemini_api_key'] ?? '';
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

            <div class="p-6 border-b-2 border-slate-100 bg-slate-50/50">
                <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Integrasi AI (Gemini)</p>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Your Gemini API Key</label>
                    <div class="flex gap-2">
                        <input type="password" id="gemini_api_key" name="gemini_api_key" value="<?php echo htmlspecialchars($geminiKey); ?>" class="flex-1 bg-slate-50 border-2 border-slate-200 p-3 text-sm font-bold focus:border-blue-500 focus:bg-white outline-none transition-all" placeholder="AIzaSy...">
                        <button type="button" id="btn-test-connection" class="bg-slate-800 text-white px-5 py-3 text-xs font-black uppercase tracking-widest hover:bg-slate-700 transition-all shadow flex items-center gap-2 whitespace-nowrap active:scale-95 duration-100">
                            <span id="btn-test-text">Test Connection</span>
                            <span id="btn-test-spinner" class="hidden">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <div id="test-result" class="hidden mt-3 p-3 text-xs font-bold uppercase tracking-wider border-l-4 transition-all duration-300"></div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight mt-2">Dapatkan API Key gratis di <a href="https://aistudio.google.com/" target="_blank" class="text-blue-600 underline">Google AI Studio</a>. Pengaturan ini khusus untuk akun Anda (<strong><?php echo $_SESSION['username']; ?></strong>).</p>
                </div>
                <div class="p-4 bg-indigo-50 border border-indigo-200">
                    <p class="text-[11px] text-indigo-700 leading-relaxed font-medium"><strong>Note:</strong> Gemini API Key sekarang dikelola secara individual per akun. Admin juga bisa mengatur key untuk guru lain melalui menu <a href="users.php" class="underline font-black">User Management</a>.</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btn-test-connection');
    const btnText = document.getElementById('btn-test-text');
    const btnSpinner = document.getElementById('btn-test-spinner');
    const apiKeyInput = document.getElementById('gemini_api_key');
    const testResult = document.getElementById('test-result');

    btnTest.addEventListener('click', function() {
        const apiKey = apiKeyInput.value.trim();
        
        // Clear previous results
        testResult.classList.add('hidden');
        testResult.innerHTML = '';
        
        // Show loading state
        btnTest.disabled = true;
        btnText.innerText = 'Testing...';
        btnSpinner.classList.remove('hidden');
        btnTest.classList.add('opacity-75', 'cursor-not-allowed');

        fetch('api.php?action=test_gemini_key', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                gemini_api_key: apiKey
            })
        })
        .then(response => response.json())
        .then(data => {
            testResult.classList.remove('hidden');
            if (data.status === 'success') {
                testResult.className = 'mt-3 p-3 text-xs font-bold uppercase tracking-wider border-l-4 bg-emerald-50 border-emerald-500 text-emerald-700 block transition-all duration-300';
                testResult.innerHTML = `✓ ${data.message}`;
            } else {
                testResult.className = 'mt-3 p-3 text-xs font-bold uppercase tracking-wider border-l-4 bg-red-50 border-red-500 text-red-700 block transition-all duration-300';
                testResult.innerHTML = `✗ ${data.message}`;
            }
        })
        .catch(err => {
            testResult.classList.remove('hidden');
            testResult.className = 'mt-3 p-3 text-xs font-bold uppercase tracking-wider border-l-4 bg-red-50 border-red-500 text-red-700 block transition-all duration-300';
            testResult.innerHTML = '✗ Gagal melakukan tes koneksi. Silakan coba lagi.';
            console.error('Error testing Gemini API key:', err);
        })
        .finally(() => {
            // Reset button state
            btnTest.disabled = false;
            btnText.innerText = 'Test Connection';
            btnSpinner.classList.add('hidden');
            btnTest.classList.remove('opacity-75', 'cursor-not-allowed');
        });
    });
});
</script>

<?php include 'partials/footer.php'; ?>

