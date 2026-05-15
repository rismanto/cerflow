<?php
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$pageTitle = "User Management";
$navContext = 'users';
include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="max-w-6xl mx-auto p-8 pb-24">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase">User <span class="text-blue-700">Management</span></h2>
            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-2">Add, edit, or remove system users</p>
        </div>
        <div class="flex gap-3">
            <a href="api.php?action=download_template" class="bg-white border-2 border-slate-300 text-slate-700 px-6 py-3 text-sm font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm">
                📥 Template CSV
            </a>
            <button onclick="document.getElementById('csv-input').click()" class="bg-stone-800 text-white px-6 py-3 text-sm font-black uppercase tracking-widest hover:bg-black transition-all shadow-sm">
                📤 Import CSV
            </button>
            <input type="file" id="csv-input" accept=".csv" class="hidden" onchange="importCSV(this)">
            <button onclick="openUserModal()" class="bg-blue-700 text-white px-8 py-3 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow-sm">
                + Tambah User
            </button>
        </div>
    </div>

    <div class="bg-white border-2 border-slate-300 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white uppercase text-xs font-black tracking-widest">
                    <th class="px-6 py-4 border-r border-slate-700">ID</th>
                    <th class="px-6 py-4 border-r border-slate-700">Username</th>
                    <th class="px-6 py-4 border-r border-slate-700">Nama Lengkap</th>
                    <th class="px-6 py-4 border-r border-slate-700 text-center">Role</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                <!-- User rows will be loaded here via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div id="user-modal" class="fixed inset-0 z-[200] bg-slate-900/85 backdrop-blur-sm flex items-center justify-center p-6 hidden">
    <div class="bg-white p-10 max-w-md w-full shadow-2xl border-2 border-slate-300 relative">
        <button onclick="closeUserModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-800 text-xl font-black">✕</button>
        
        <h2 id="modal-title" class="text-2xl font-black text-slate-800 uppercase italic mb-6">Tambah User</h2>
        
        <form id="user-form" onsubmit="saveUser(event)">
            <input type="hidden" id="user-id">
            
            <div class="mb-5">
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Username</label>
                <input type="text" id="user-username" required class="w-full border-2 border-slate-200 px-4 py-3 focus:border-blue-700 outline-none transition-all font-bold text-slate-700">
            </div>

            <div class="mb-5">
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Nama Lengkap</label>
                <input type="text" id="user-namalengkap" required class="w-full border-2 border-slate-200 px-4 py-3 focus:border-blue-700 outline-none transition-all font-bold text-slate-700">
            </div>
            
            <div class="mb-5">
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Password <span id="pwd-note" class="text-[10px] lowercase italic font-normal text-slate-400 hidden">(biarkan kosong jika tidak ingin ganti)</span></label>
                <input type="password" id="user-password" class="w-full border-2 border-slate-200 px-4 py-3 focus:border-blue-700 outline-none transition-all font-bold text-slate-700">
            </div>
            
            <div class="mb-8">
                <label class="text-xs uppercase font-black text-slate-500 tracking-widest block mb-2">Role</label>
                <select id="user-role" class="w-full border-2 border-slate-200 px-4 py-3 focus:border-blue-700 outline-none transition-all font-bold text-slate-700 bg-white">
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru (Admin)</option>
                </select>
            </div>
            
            <button type="submit" class="w-full bg-blue-700 text-white py-4 font-black hover:bg-blue-800 transition-all shadow-lg uppercase tracking-widest text-sm">
                Simpan User
            </button>
        </form>
    </div>
</div>

<script>
let allUsers = [];

async function loadUsers() {
    const res = await fetch('api.php?action=get_users');
    const data = await res.json();
    if (data.status === 'success') {
        allUsers = data.data;
        renderUsers();
    }
}

function renderUsers() {
    const tbody = document.getElementById('user-table-body');
    tbody.innerHTML = allUsers.map(u => `
        <tr class="border-b border-slate-200 hover:bg-slate-50 transition-all">
            <td class="px-6 py-4 font-bold text-slate-400 text-sm border-r border-slate-100 w-16">#${u.id}</td>
            <td class="px-6 py-4 font-black text-slate-700">${u.username}</td>
            <td class="px-6 py-4 font-medium text-slate-600 border-r border-slate-100">${u.namalengkap}</td>
            <td class="px-6 py-4 text-center border-r border-slate-100">
                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest border-2 ${u.role === 'guru' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-slate-100 border-slate-200 text-slate-600'}">
                    ${u.role}
                </span>
            </td>
            <td class="px-6 py-4 text-right">
                <button onclick="editUser(${u.id})" class="text-xs font-black text-blue-700 hover:bg-blue-700 hover:text-white px-3 py-1 border-2 border-blue-700 transition-all mr-2 uppercase">Edit</button>
                <button onclick="deleteUser(${u.id})" class="text-xs font-black text-red-600 hover:bg-red-600 hover:text-white px-3 py-1 border-2 border-red-600 transition-all uppercase">Delete</button>
            </td>
        </tr>
    `).join('');
}

function openUserModal() {
    document.getElementById('modal-title').innerText = "Tambah User Baru";
    document.getElementById('user-form').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-password').required = true;
    document.getElementById('pwd-note').classList.add('hidden');
    document.getElementById('user-modal').classList.remove('hidden');
}

function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
}

function editUser(id) {
    const user = allUsers.find(u => u.id == id);
    if (!user) return;
    
    document.getElementById('modal-title').innerText = "Edit User";
    document.getElementById('user-id').value = user.id;
    document.getElementById('user-username').value = user.username;
    document.getElementById('user-namalengkap').value = user.namalengkap;
    document.getElementById('user-role').value = user.role;
    document.getElementById('user-password').value = '';
    document.getElementById('user-password').required = false;
    document.getElementById('pwd-note').classList.remove('hidden');
    document.getElementById('user-modal').classList.remove('hidden');
}

async function saveUser(e) {
    e.preventDefault();
    const payload = {
        id: document.getElementById('user-id').value,
        username: document.getElementById('user-username').value,
        namalengkap: document.getElementById('user-namalengkap').value,
        password: document.getElementById('user-password').value,
        role: document.getElementById('user-role').value
    };
    
    const res = await fetch('api.php?action=save_user', {
        method: 'POST',
        body: JSON.stringify(payload)
    });
    
    const data = await res.json();
    if (data.status === 'success') {
        closeUserModal();
        loadUsers();
    } else {
        alert(data.message);
    }
}

async function importCSV(input) {
    if (!input.files || input.files.length === 0) return;
    
    const formData = new FormData();
    formData.append('csv_file', input.files[0]);
    
    const res = await fetch('api.php?action=import_users', {
        method: 'POST',
        body: formData
    });
    
    const data = await res.json();
    if (data.status === 'success') {
        alert(data.message);
        loadUsers();
    } else {
        alert(data.message);
    }
    input.value = ''; // Reset input
}

async function deleteUser(id) {
    if (!(await CustomConfirm('Yakin ingin menghapus user ini?'))) return;
    
    const res = await fetch('api.php?action=delete_user', {
        method: 'POST',
        body: JSON.stringify({ id })
    });
    
    const data = await res.json();
    if (data.status === 'success') {
        loadUsers();
    } else {
        alert(data.message);
    }
}

document.addEventListener('DOMContentLoaded', loadUsers);
</script>

<?php include 'partials/footer.php'; ?>
