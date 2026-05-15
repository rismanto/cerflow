<?php
/**
 * Main Entry Point - Login Page
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Redirect if already logged in
if (User::checkAuth()) {
    header($_SESSION['role'] == 'guru' ? "Location: admin.php" : "Location: siswa.php");
    exit;
}

$error = "";
if(isset($_POST['login'])) {
    if($user->login($_POST['username'], $_POST['password'])) {
        header($_SESSION['role'] == 'guru' ? "Location: admin.php" : "Location: siswa.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}

$pageTitle = "Secure Login";
include 'partials/header.php';
?>
<script>sessionStorage.clear();</script>


<div class="flex items-center justify-center h-screen bg-slate-800">
    <form method="POST" class="bg-white p-10 shadow-2xl w-96 border-2 border-slate-300">
        <h1 class="text-2xl font-black text-blue-700 mb-2 italic uppercase">CER<span class="text-slate-800">Flow</span> <span class="text-slate-400 font-normal normal-case text-lg">Portal</span></h1>
        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mb-8">Sistem Manajemen Pembelajaran</p>
        
        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 mb-6 text-sm font-bold border-l-4 border-red-500">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="space-y-5">
            <div>
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Username</label>
                <input type="text" name="username" onfocus="this.select()" placeholder="Masukkan Username" class="w-full p-3 border-2 border-slate-300 outline-none focus:border-blue-600 text-sm transition-all" required>
            </div>
            
            <div>
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Password</label>
                <input type="password" name="password" onfocus="this.select()" placeholder="Masukkan Password" class="w-full p-3 border-2 border-slate-300 outline-none focus:border-blue-600 text-sm transition-all" required>
            </div>
        </div>

        <button name="login" class="w-full bg-blue-700 text-white p-4 font-bold hover:bg-blue-800 transition-all shadow mt-8 text-sm uppercase tracking-wider">
            MASUK KE DASHBOARD
        </button>
        
        <p class="text-center text-slate-400 text-xs font-bold uppercase tracking-widest mt-6">
            Cerflow Modular v2.0
        </p>
    </form>
</div>

<?php include 'partials/footer.php'; ?>