/**
 * Admin Dashboard Logic
 * 
 * Handles CRUD operations for CER maps and triplets.
 */

let currentMapId = null; 
let triplets = [];
let allMapsCache = []; // Cache to store fetched maps for searching
let editingTripletIndex = null;

/**
 * Read the map ID requested by Mission Control.
 * @returns {number|null}
 */
function getRequestedEditId() {
    const params = new URLSearchParams(window.location.search);
    const editId = parseInt(window.INITIAL_EDIT_MAP_ID || params.get('edit_id'), 10);
    return Number.isInteger(editId) && editId > 0 ? editId : null;
}

/**
 * Put a map object into the editor.
 * @param {Object} selectedMap
 */
function applyMapToEditor(selectedMap) {
    const id = parseInt(selectedMap.id, 10);

    currentMapId = id;
    window.history.replaceState({}, '', `${window.location.pathname}?edit_id=${id}`);
    document.getElementById('map-title').value = selectedMap.title || '';
    document.getElementById('allow-feedback').checked = (selectedMap.allow_feedback == 1);
    document.getElementById('allow-reading').checked = (selectedMap.allow_reading == 1);
    document.getElementById('reading-text-input').value = selectedMap.reading_text || '';
    
    renderMapList(allMapsCache);
    
    const btnPreview = document.getElementById('btn-preview-map');
    btnPreview.href = `preview_map.php?map_id=${id}`;
    btnPreview.classList.remove('hidden');

    const btnDelete = document.getElementById('btn-delete-map');
    if (btnDelete) btnDelete.classList.remove('hidden');

    triplets = (selectedMap.triplets || []).map(t => ({
        id: parseInt(t.id, 10),
        claim: t.claim,
        evidence: t.evidence,
        reasoning: t.reasoning
    }));
    resetTripletForm();
    render();
}

function resetTripletForm() {
    editingTripletIndex = null;
    document.getElementById('in-c').value = '';
    document.getElementById('in-e').value = '';
    document.getElementById('in-r').value = '';

    const saveButton = document.getElementById('btn-save-triplet');
    const cancelButton = document.getElementById('btn-cancel-triplet');
    if (saveButton) {
        saveButton.innerText = '+ Tambahkan Triplet ke Map Ini';
    }
    if (cancelButton) {
        cancelButton.classList.add('hidden');
    }
}

/**
 * Reset the editor for a new map
 */
function newMap() {
    currentMapId = null; 
    triplets = [];
    window.history.replaceState({}, '', window.location.pathname);
    
    document.getElementById('map-title').value = '';
    document.getElementById('allow-feedback').checked = true;
    document.getElementById('allow-reading').checked = true;
    document.getElementById('reading-text-input').value = '';
    resetTripletForm();
    
    const items = document.querySelectorAll('.sidebar-item');
    items.forEach(item => item.classList.remove('active'));
    
    const btnPreview = document.getElementById('btn-preview-map');
    if (btnPreview) btnPreview.classList.add('hidden');
    
    const btnDelete = document.getElementById('btn-delete-map');
    if (btnDelete) btnDelete.classList.add('hidden');
    
    render();
    console.log("Mode: Buat Map Baru");
}

/**
 * Add a triplet to the local list
 */
async function addLocal() {
    const c = document.getElementById('in-c');
    const e = document.getElementById('in-e');
    const r = document.getElementById('in-r');
    
    if(!c.value || !e.value || !r.value) {
        await CustomAlert("Isi semua bagian triplet!");
        return;
    }

    const tripletPayload = {
        id: editingTripletIndex !== null ? triplets[editingTripletIndex].id : null,
        claim: c.value,
        evidence: e.value,
        reasoning: r.value
    };

    if (editingTripletIndex !== null) {
        triplets[editingTripletIndex] = tripletPayload;
    } else {
        triplets.push(tripletPayload);
    }

    resetTripletForm();
    render();
}

/**
 * Render the triplets table
 */
function render() {
    const b = document.getElementById('table-body');
    b.innerHTML = '';
    if (triplets.length === 0) {
        b.innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-slate-400 italic text-sm">Belum ada triplet ditambahkan.</td>
            </tr>`;
        return;
    }

    triplets.forEach((t, i) => {
        b.innerHTML += `
            <tr class="border-b hover:bg-slate-50 transition-colors">
                <td class="p-3">${t.claim}</td>
                <td class="p-3">${t.evidence}</td>
                <td class="p-3">${t.reasoning}</td>
                <td class="p-3">
                    <div class="flex items-center justify-center gap-3">
                        <button onclick="editLocalTriplet(${i})" class="text-blue-600 hover:text-blue-800 font-bold">Edit</button>
                        <button onclick="removeTriplet(${i})" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                    </div>
                </td>
            </tr>`;
    });
}

function editLocalTriplet(index) {
    const triplet = triplets[index];
    if (!triplet) return;

    editingTripletIndex = index;
    document.getElementById('in-c').value = triplet.claim;
    document.getElementById('in-e').value = triplet.evidence;
    document.getElementById('in-r').value = triplet.reasoning;

    const saveButton = document.getElementById('btn-save-triplet');
    const cancelButton = document.getElementById('btn-cancel-triplet');
    if (saveButton) {
        saveButton.innerText = 'Simpan Perubahan Triplet';
    }
    if (cancelButton) {
        cancelButton.classList.remove('hidden');
    }

    document.getElementById('in-c').focus();
}

function cancelTripletEdit() {
    resetTripletForm();
}

/**
 * Remove a triplet from the local list
 * @param {number} index 
 */
function removeTriplet(index) {
    if (editingTripletIndex === index) {
        resetTripletForm();
    } else if (editingTripletIndex !== null && index < editingTripletIndex) {
        editingTripletIndex -= 1;
    }

    triplets.splice(index, 1);
    render();
}

/**
 * Save the map and triplets to the database
 */
async function saveToDB() {
    const title = document.getElementById('map-title').value;
    if(!title || triplets.length === 0) {
        await CustomAlert("Judul dan Triplet harus diisi!");
        return;
    }

    try {
        const res = await fetch('api.php?action=save_map', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                map_id: currentMapId, 
                title: title, 
                triplets: triplets.map(t => ({
                    id: t.id,
                    claim: t.claim,
                    evidence: t.evidence,
                    reasoning: t.reasoning
                })),
                allow_feedback: document.getElementById('allow-feedback').checked ? 1 : 0,
                allow_reading: document.getElementById('allow-reading').checked ? 1 : 0,
                reading_text: document.getElementById('reading-text-input').value
            })
        });
        const data = await res.json();
        if(data.status === 'ok') {
            await CustomAlert(currentMapId ? 'Map Berhasil Diperbarui!' : 'Map Baru Berhasil Disimpan!');
            await loadMaps();
            editMap(data.id);
        } else {
            await CustomAlert("Gagal menyimpan: " + data.message);
        }
    } catch(err) {
        console.error("Error:", err);
        await CustomAlert("Terjadi kesalahan sistem.");
    }
}

/**
 * Load all maps into the sidebar
 */
function loadMaps() {
    return fetch('api.php?action=get_maps')
    .then(res => res.json())
    .then(data => {
        allMapsCache = data;
        renderMapList(data);
        return data;
    });
}

/**
 * Load a single map and its triplets from the database.
 * @param {number} id
 * @returns {Promise<Object>}
 */
function fetchMapForEditing(id) {
    return fetch(`api.php?action=get_map&map_id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success' || !data.data) {
                throw new Error(data.message || 'Map tidak ditemukan.');
            }
            return data.data;
        });
}

/**
 * Render the map list in the sidebar
 * @param {Array} maps 
 */
function renderMapList(maps) {
    const list = document.getElementById('map-list');
    if (!list) return;

    if (maps.length === 0) {
        list.innerHTML = `<div class="p-8 text-center text-slate-400 italic text-sm">Tidak ada materi ditemukan.</div>`;
        return;
    }

    list.innerHTML = maps.map(m => `
        <div class="sidebar-item p-4 border-b flex justify-between items-center group cursor-pointer transition-all hover:bg-indigo-50 ${currentMapId == m.id ? 'active' : ''}" data-map-id="${m.id}" onclick="editMap(${m.id})">
            <div>
                <div class="flex items-center gap-2">
                    <div class="text-[10px] font-bold text-indigo-500 uppercase">Map ID: ${m.id}</div>
                    <span title="${m.allow_feedback == 1 ? 'Feedback Aktif' : 'Feedback Nonaktif'}" class="${m.allow_feedback == 1 ? '' : 'grayscale opacity-40'} text-[10px]">
                        💡
                    </span>
                    ${m.reading_text && m.reading_text.trim() !== '' ? `
                        <span title="${m.allow_reading == 1 ? 'Bacaan Aktif' : 'Bacaan Nonaktif'}" class="${m.allow_reading == 1 ? 'text-blue-500' : 'text-slate-300 grayscale opacity-40'} text-[10px]">
                            📖
                        </span>
                    ` : ''}
                </div>
                <div class="text-sm font-bold text-slate-700">${m.title}</div>
            </div>
            <button onclick="event.stopPropagation(); deleteMap(${m.id})" class="opacity-0 group-hover:opacity-100 p-2 text-slate-400 hover:text-red-500 transition-all">
                ✕
            </button>
        </div>
    `).join('');
}

// Search Listener
document.addEventListener('DOMContentLoaded', () => {
    loadMaps()
        .then(() => {
            const editId = getRequestedEditId();
            if (window.INITIAL_EDIT_MAP_DATA && window.INITIAL_EDIT_MAP_DATA.id) {
                applyMapToEditor(window.INITIAL_EDIT_MAP_DATA);
            } else if (editId) {
                editMap(editId);
            }
        })
        .catch(async err => {
            console.error("Gagal memuat daftar map:", err);
            await CustomAlert("Terjadi kesalahan saat memuat daftar map.");
        });
    
    const searchInput = document.getElementById('map-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = allMapsCache.filter(m => 
                m.title.toLowerCase().includes(term) || 
                m.id.toString().includes(term)
            );
            renderMapList(filtered);
        });
    }
});

/**
 * Delete a map from the database
 * @param {number} id 
 */
async function deleteMap(id) {
    if (await CustomConfirm("Apakah Anda yakin ingin menghapus Map ini? Semua data triplet di dalamnya akan ikut terhapus.")) {
        try {
            const res = await fetch(`api.php?action=delete_map&map_id=${id}`, { method: 'GET' });
            const data = await res.json();
            if (data.status === 'success') {
                await CustomAlert("Map berhasil dihapus");
                await loadMaps();
                if(currentMapId == id) newMap();
            } else {
                await CustomAlert("Gagal menghapus: " + data.message);
            }
        } catch(err) {
            console.error("Error:", err);
            await CustomAlert("Terjadi kesalahan sistem.");
        }
    }
}

/**
 * Load a map into the editor for editing
 * @param {number} id 
 */
function editMap(id) {
    fetchMapForEditing(id)
    .then(selectedMap => {
        applyMapToEditor(selectedMap);
    })
    .catch(async err => {
        console.error("Gagal memuat map:", err);
        await CustomAlert("Terjadi kesalahan saat memuat isi map.");
    });
}

function openReadingEditor() {
    const dialog = document.getElementById('dialog-reading-edit');
    if (dialog) {
        dialog.classList.remove('hidden');
        DialogUtils.init('dialog-reading-edit', 'dialog-reading-edit-header', 'dialog-reading-edit-resize');
    }
}

function closeReadingEditor() {
    const dialog = document.getElementById('dialog-reading-edit');
    if (dialog) {
        dialog.classList.add('hidden');
    }
}

/**
 * AI Extractor Logic
 */
function openAIExtractor() {
    const dialog = document.getElementById('dialog-ai-extract');
    if (dialog) {
        dialog.classList.remove('hidden');
        document.getElementById('ai-extract-input').value = '';
        DialogUtils.init('dialog-ai-extract', 'dialog-ai-extract-header', 'dialog-ai-extract-resize');
    }
}

function closeAIExtractor() {
    const dialog = document.getElementById('dialog-ai-extract');
    if (dialog) {
        dialog.classList.add('hidden');
    }
}

async function runAIExtraction() {
    const text = document.getElementById('ai-extract-input').value.trim();
    if (!text) {
        await CustomAlert("Tempelkan teks bacaan terlebih dahulu!");
        return;
    }

    const loading = document.getElementById('ai-loading');
    const btn = document.getElementById('btn-run-ai');

    loading.classList.remove('hidden');
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');

    try {
        const res = await fetch('api.php?action=extract_cer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: text })
        });
        const data = await res.json();
        if (data.status === 'success') {
            // 1. Update the Reading Text field
            document.getElementById('reading-text-input').value = text;
            
            // 2. Append new triplets to the local list
            const extractedTriplets = data.data.map(t => ({
                id: null,
                claim: t.claim,
                evidence: t.evidence,
                reasoning: t.reasoning
            }));

            triplets = [...triplets, ...extractedTriplets];
            
            // 3. UI Updates
            render();
            closeAIExtractor();
            await CustomAlert(`Berhasil mengekstraksi ${extractedTriplets.length} komponen CER! Silakan cek daftar di bawah untuk menyesuaikannya.`);
        } else {
            await CustomAlert("AI Error: " + data.message);
        }
    } catch(err) {
        console.error("AI Fetch Error:", err);
        await CustomAlert("Gagal menghubungi server AI.");
    } finally {
        loading.classList.add('hidden');
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Initial load handled by DOMContentLoaded above
