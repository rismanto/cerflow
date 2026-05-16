/**
 * Student Interactive Learning Logic
 * 
 * Handles the interactive canvas for connecting CER triplets.
 */

let currentMap = null;
let currentMapIndex = null;
let connections = [];
let isDrawing = false;
let startNode = null;
let currentSessionId = null;
let feedbackActive = false;

// ─── Session Storage Keys ────────────────────────────────────────────────────
const SK_MAP_INDEX   = 'cer_map_index_v2';
const SK_SESSION_ID  = 'cer_session_id_v2';
const SK_CONNECTIONS = 'cer_connections_v2';
const SK_COL_ORDER   = 'cer_col_order_v2'; // JSON: { claim: [id,...], evidence: [...], reasoning: [...] }

function getTripletCardId(type, tripletId) {
    return `${type[0]}-${tripletId}`;
}

function getCardTripletId(cardId) {
    const [, tripletId] = String(cardId).split('-');
    return String(tripletId);
}

function getTripletByCardId(cardId, map = currentMap) {
    if (!map || !map.triplets) return null;
    const tripletId = parseInt(getCardTripletId(cardId), 10);
    return map.triplets.find(t => parseInt(t.id, 10) === tripletId) || null;
}

function isCorrectConnection(connection) {
    return getCardTripletId(connection.from) === getCardTripletId(connection.to);
}

/**
 * Persist current state to sessionStorage
 */
function saveState() {
    if (currentMapIndex === null) return;
    sessionStorage.setItem(SK_MAP_INDEX,   currentMapIndex);
    sessionStorage.setItem(SK_SESSION_ID,  currentSessionId || '');
    sessionStorage.setItem(SK_CONNECTIONS, JSON.stringify(connections));

    // Capture card order per column
    const order = {};
    ['claim', 'evidence', 'reasoning'].forEach(type => {
        const col = document.getElementById(`col-${type}`);
        if (col) {
            order[type] = Array.from(col.children).map(el => el.id);
        }
    });
    sessionStorage.setItem(SK_COL_ORDER, JSON.stringify(order));
}

/**
 * Clear persisted state (called after submit or back-to-selector)
 */
function clearState() {
    [SK_MAP_INDEX, SK_SESSION_ID, SK_CONNECTIONS, SK_COL_ORDER].forEach(k => sessionStorage.removeItem(k));
}

/**
 * Log student interaction
 */
function logAction(type, data = {}) {
    if (!currentSessionId) return;
    fetch('api.php?action=log_action', {
        method: 'POST',
        body: JSON.stringify({ 
            session_id: currentSessionId,
            type: type,
            data: data
        })
    });
}

const lineGroup = document.getElementById('line-group');
const drawingLine = document.getElementById('drawing-line');

/**
 * Start learning a specific module
 * @param {number} i Index in cerMaps array
 */
function startLearning(i) {
    if (typeof cerMaps === 'undefined') return;
    currentMapIndex = i;
    currentMap = cerMaps[i];
    
    // Initialize session
    fetch('api.php?action=start_session', {
        method: 'POST',
        body: JSON.stringify({ map_id: currentMap.id })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            currentSessionId = data.session_id;
            saveState();
        }
    });

    // UI Updates
    document.getElementById('active-title').innerText = currentMap.title;
    document.getElementById('active-title-container').classList.remove('hidden');
    document.getElementById('editor-actions').classList.remove('hidden');
    
    // Reset reading dialog
    document.getElementById('dialog-reading').classList.add('hidden');
    
    restoreEditorActions();
    
    document.getElementById('view-selector').classList.add('hidden');
    document.getElementById('view-editor').classList.remove('hidden');
    
    renderCards();
}

/**
 * Restore a previously active session from sessionStorage
 * @param {number} i   Map index
 * @param {string} sid Session ID
 * @param {Array}  savedConnections
 * @param {Object} colOrder  { claim:[ids], evidence:[ids], reasoning:[ids] }
 */
function restoreSession(i, sid, savedConnections, colOrder) {
    currentMapIndex = i;
    currentMap = cerMaps[i];
    currentSessionId = sid;
    connections = savedConnections;

    document.getElementById('active-title').innerText = currentMap.title;
    document.getElementById('active-title-container').classList.remove('hidden');
    document.getElementById('editor-actions').classList.remove('hidden');
    restoreEditorActions();
    
    document.getElementById('view-selector').classList.add('hidden');
    document.getElementById('view-editor').classList.remove('hidden');

    renderCards(colOrder);
}

/**
 * Go back to the module selector
 */
async function backToSelector() {
    if (connections.length > 0 && !isLocked) {
        if (!(await CustomConfirm("Jawaban yang belum di-submit akan hilang. Lanjutkan?"))) return;
    }
    
    connections = [];
    currentMap = null;
    currentMapIndex = null;
    isLocked = false;
    restoreEditorActions();
    clearState();
    if(lineGroup) lineGroup.innerHTML = '';
    
    document.getElementById('active-title-container').classList.add('hidden');
    document.getElementById('editor-actions').classList.add('hidden');
    
    document.getElementById('view-selector').classList.remove('hidden');
    document.getElementById('view-editor').classList.add('hidden');
}

/**
 * Render cards in columns, optionally restoring a saved order.
 * @param {Object|null} colOrder  Saved column order from sessionStorage
 */
function renderCards(colOrder = null) {
    if (!currentMap || !currentMap.triplets) return;

    ['claim', 'evidence', 'reasoning'].forEach(type => {
        const col = document.getElementById(`col-${type}`);
        if(!col) return;

        let ordered;
        if (colOrder && colOrder[type] && colOrder[type].length > 0) {
            // Restore saved card order
            const savedIds = colOrder[type];
            ordered = savedIds
                .map(id => {
                    const triplet = getTripletByCardId(id);
                    return triplet ? { triplet } : null;
                })
                .filter(Boolean);
            // Append any triplets not captured in saved order
            currentMap.triplets.forEach(t => {
                if (!savedIds.includes(getTripletCardId(type, t.id))) {
                    ordered.push({ triplet: t });
                }
            });
        } else {
            // Fresh shuffle
            const shuffled = [...currentMap.triplets.map(t => ({ triplet: t }))].sort(() => Math.random() - 0.5);
            ordered = shuffled;
        }
        
        col.innerHTML = ordered.map(({ triplet: t }) => {
            return `
            <div id="${getTripletCardId(type, t.id)}" class="card-item bg-white border-2 border-slate-200 p-5 mb-4">
                <div class="card-handle cursor-move">
                    <p class="text-sm leading-relaxed text-slate-700 font-medium">${t[type]}</p>
                </div>
                ${type !== 'reasoning' ? '<div class="node out"></div>' : ''}
                ${type !== 'claim' ? '<div class="node in"></div>' : ''}
            </div>`;
        }).join('');

        // Initialize Sortable
        new Sortable(col, { 
            animation: 250, 
            easing: "cubic-bezier(0.34, 1.56, 0.64, 1)",
            handle: '.card-handle',
            onEnd: (evt) => {
                redrawLines();
                logAction('move', { card: evt.item.id, from: evt.oldIndex, to: evt.newIndex });
                saveState();
            }
        });
    });

    // Draw restored connections after DOM is ready
    if (connections.length > 0) {
        requestAnimationFrame(() => redrawLines());
    }
}

/**
 * Get the center position of a node
 * @param {HTMLElement} node 
 */
function getNodePos(node) {
    const rect = node.getBoundingClientRect();
    return {
        x: rect.left + (rect.width / 2),
        y: rect.top + (rect.height / 2)
    };
}

/**
 * Reset the drawing line position
 */
function resetDrawingLine() {
    if(!drawingLine) return;
    drawingLine.setAttribute('x1', -1000);
    drawingLine.setAttribute('y1', -1000);
    drawingLine.setAttribute('x2', -1000);
    drawingLine.setAttribute('y2', -1000);
}

/**
 * Redraw all connection lines
 */
function redrawLines() {
    if(!lineGroup) return;
    lineGroup.innerHTML = '';
    connections.forEach(c => {
        const elFrom = document.getElementById(c.from);
        const elTo = document.getElementById(c.to);
        
        if (elFrom && elTo) {
            const nOut = elFrom.querySelector('.node.out');
            const nIn = elTo.querySelector('.node.in');
            
            if (nOut && nIn) {
                const p1 = getNodePos(nOut);
                const p2 = getNodePos(nIn);
                
                const dist = Math.abs(p2.x - p1.x) * 0.5;
                const d = `M ${p1.x} ${p1.y} C ${p1.x + dist} ${p1.y}, ${p2.x - dist} ${p2.y}, ${p2.x} ${p2.y}`;
                
                const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                path.setAttribute("d", d);
                path.setAttribute("fill", "none");
                
                // Feedback logic
                const isCorrect = isCorrectConnection(c);
                if (feedbackActive) {
                    if (isCorrect) {
                        path.style.stroke = "#10b981"; // emerald-500 (green)
                        path.style.strokeDasharray = "none";
                    } else {
                        path.style.stroke = "#ef4444"; // red-500
                        path.style.strokeDasharray = "8,8";
                    }
                } else {
                    path.style.stroke = "#4361ee"; // blue-600
                    path.style.strokeDasharray = "none";
                }
                
                path.setAttribute("stroke-width", "3");
                path.setAttribute("stroke-linecap", "round");
                path.classList.add('line-path');
                lineGroup.appendChild(path);
            }
        }
    });
}

let isLocked = false; // True after submit — prevents any editing

/**
 * Restore the editor action buttons to their original state.
 * Called whenever leaving a locked session.
 */
function restoreEditorActions() {
    const actionsEl = document.getElementById('editor-actions');
    if (actionsEl) {
        const feedbackBtn = (currentMap && currentMap.allow_feedback == 0) 
            ? '' 
            : `<button id="btn-feedback" onclick="toggleFeedback()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">💡 Feedback</button>`;

        const readingBtn = (currentMap && currentMap.allow_reading == 1 && currentMap.reading_text) 
            ? `<button id="btn-reading" onclick="toggleReading()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">📖 Bacaan</button>` 
            : '';

        actionsEl.innerHTML = `
            ${readingBtn}
            ${feedbackBtn}
            <button onclick="autoArrange()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">🪄 Arrange</button>
            <button onclick="submitScore()" class="bg-blue-700 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-blue-800 shadow transition-all">Submit</button>
        `;
        updateFeedbackUI();
    }
}

/**
 * Toggle reading dialog
 */
function toggleReading() {
    const dialog = document.getElementById('dialog-reading');
    const content = document.getElementById('dialog-reading-content');
    const btn = document.getElementById('btn-reading');
    
    if (!dialog || !currentMap) return;

    if (dialog.classList.contains('hidden')) {
        // Opening
        content.innerText = currentMap.reading_text || 'Tidak ada teks bacaan untuk materi ini.';
        dialog.classList.remove('hidden');
        if (btn) btn.classList.add('bg-blue-50', 'border-blue-400', 'text-blue-700');
        logAction('view_reading');
        
        // Initialize draggable/resizable
        DialogUtils.init('dialog-reading', 'dialog-reading-header', 'dialog-reading-resize');
    } else {
        // Closing
        dialog.classList.add('hidden');
        if (btn) btn.classList.remove('bg-blue-50', 'border-blue-400', 'text-blue-700');
    }
}

/**
 * Update the Feedback button UI state
 */
function updateFeedbackUI() {
    const btn = document.getElementById('btn-feedback');
    if (btn) {
        btn.innerText = feedbackActive ? '💡 Feedback' : '💡 Feedback';
        btn.classList.toggle('bg-blue-50', feedbackActive);
        btn.classList.toggle('border-blue-400', feedbackActive);
        btn.classList.toggle('text-blue-700', feedbackActive);
    }
}

/**
 * Toggle feedback mode
 */
function toggleFeedback() {
    feedbackActive = !feedbackActive;
    
    if (feedbackActive) {
        logAction('feedback', { active: true });
    }
    
    updateFeedbackUI();
    redrawLines();
}

function lockEditor() {
    isLocked = true;
    feedbackActive = true; // Auto-enable feedback after submission

    // Disable all connection nodes visually and functionally
    document.querySelectorAll('.node').forEach(n => {
        n.style.cursor = 'default';
        n.style.opacity = '0.5';
        n.style.pointerEvents = 'none';
    });

    const actionsEl = document.getElementById('editor-actions');
    if (actionsEl) {
        const readingBtn = (currentMap && currentMap.allow_reading == 1 && currentMap.reading_text) 
            ? `<button id="btn-reading" onclick="toggleReading()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">📖 Bacaan</button>` 
            : '';

        actionsEl.innerHTML = `
            ${readingBtn}
            <button id="btn-feedback" onclick="toggleFeedback()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">💡 Feedback</button>
            <button onclick="autoArrange()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">🪄 Arrange</button>
            <span class="bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm">
                🔒 Mode Lihat — Terkirim
            </span>
        `;
        updateFeedbackUI();
        redrawLines();
    }
}

/**
 * Option 1 (default): Close the result modal and show the read-only editor.
 */
function stayOnPage() {
    document.getElementById('view-result').classList.add('hidden');
    lockEditor();
}

/**
 * Option 2: Start the same module from scratch with a new session.
 */
function redoActivity() {
    const idx = currentMapIndex;
    clearState();
    connections = [];
    isLocked = false;
    restoreEditorActions();
    if (lineGroup) lineGroup.innerHTML = '';
    document.getElementById('view-result').classList.add('hidden');
    startLearning(idx);
}

/**
 * Option 3: Go back to the module selector.
 */
function goToSelector() {
    clearState();
    connections = [];
    currentMap = null;
    currentMapIndex = null;
    isLocked = false;
    restoreEditorActions();
    if (lineGroup) lineGroup.innerHTML = '';

    document.getElementById('active-title-container').classList.add('hidden');
    document.getElementById('editor-actions').classList.add('hidden');
    document.getElementById('view-result').classList.add('hidden');
    document.getElementById('view-selector').classList.remove('hidden');
    document.getElementById('view-editor').classList.add('hidden');
}

/**
 * Clicking the dark backdrop outside the result modal → go to view-only mode.
 */
function handleBackdropClick(event) {
    // Only fires when the backdrop itself is clicked (not the inner card, due to stopPropagation)
    stayOnPage();
}

/**
 * Submit the calculated score
 */
async function submitScore() {
    if (!currentMap || connections.length === 0) return alert("Kerjakan dulu kuisnya!");

    if (!(await CustomConfirm("Apakah Anda yakin ingin mengumpulkan jawaban?\nJawaban tidak dapat diubah setelah disubmit."))) return;

    let correct = 0;
    connections.forEach(c => {
        if (isCorrectConnection(c)) correct++;
    });

    const totalShouldBe = currentMap.triplets.length * 2;
    const score = (correct / totalShouldBe) * 100;

    const colOrder = {};
    ['claim', 'evidence', 'reasoning'].forEach(type => {
        const col = document.getElementById(`col-${type}`);
        if (col) {
            colOrder[type] = Array.from(col.children).map(el => el.id);
        }
    });

    const mapData = {
        triplets: currentMap.triplets,
        colOrder: colOrder,
        connections: connections
    };

    fetch('api.php?action=save_score', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            map_id: currentMap.id,
            score: score.toFixed(2),
            session_id: currentSessionId,
            map_data: JSON.stringify(mapData)
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            clearState(); // Wipe saved state — session is permanently done
            document.getElementById('result-score').innerText = score.toFixed(2) + "%";
            document.getElementById('view-result').classList.remove('hidden');
        } else {
            alert("Gagal: " + data.message);
        }
    })
    .catch(err => {
        console.error("Gagal menyimpan nilai:", err);
        alert("Terjadi kesalahan saat menyimpan nilai.");
    });
}

/**
 * Auto-arrange connected cards to be parallel with smooth animation
 */
function autoArrange() {
    if (connections.length === 0) return alert("Belum ada koneksi untuk diatur!");
    logAction('auto_arrange');

    const colC = document.getElementById('col-claim');
    const colE = document.getElementById('col-evidence');
    const colR = document.getElementById('col-reasoning');

    const claims = Array.from(colC.children);
    const evidences = Array.from(colE.children);
    const reasonings = Array.from(colR.children);

    // 0. Record initial positions for animation (FLIP technique)
    const initialPositions = new Map();
    [...claims, ...evidences, ...reasonings].forEach(el => {
        initialPositions.set(el.id, el.getBoundingClientRect());
    });

    let newOrderC = [], newOrderE = [], newOrderR = [];
    let processedC = new Set(), processedE = new Set(), processedR = new Set();

    // 1. Process chains starting from Claim
    claims.forEach(cEl => {
        const connCE = connections.find(conn => conn.from === cEl.id);
        if (connCE) {
            newOrderC.push(cEl);
            processedC.add(cEl.id);
            
            const eEl = evidences.find(e => e.id === connCE.to);
            if (eEl) {
                newOrderE.push(eEl);
                processedE.add(eEl.id);
                
                const connER = connections.find(conn => conn.from === eEl.id);
                if (connER) {
                    const rEl = reasonings.find(r => r.id === connER.to);
                    if (rEl) {
                        newOrderR.push(rEl);
                        processedR.add(rEl.id);
                    }
                }
            }
        }
    });

    // 2. Process remaining connections starting from Evidence
    evidences.forEach(eEl => {
        if (processedE.has(eEl.id)) return;
        const connER = connections.find(conn => conn.from === eEl.id);
        if (connER) {
            newOrderE.push(eEl);
            processedE.add(eEl.id);
            
            const rEl = reasonings.find(r => r.id === connER.to);
            if (rEl) {
                newOrderR.push(rEl);
                processedR.add(rEl.id);
            }
        }
    });

    // 3. Fill gaps to ensure parallel rows
    const maxLen = Math.max(newOrderC.length, newOrderE.length, newOrderR.length);
    const pad = (order, pool, processed) => {
        while (order.length < maxLen) {
            const next = pool.find(el => !processed.has(el.id));
            if (next) {
                order.push(next);
                processed.add(next.id);
            } else {
                break;
            }
        }
    };

    pad(newOrderC, claims, processedC);
    pad(newOrderE, evidences, processedE);
    pad(newOrderR, reasonings, processedR);

    // 4. Add remaining unconnected cards
    claims.forEach(el => { if (!processedC.has(el.id)) newOrderC.push(el); });
    evidences.forEach(el => { if (!processedE.has(el.id)) newOrderE.push(el); });
    reasonings.forEach(el => { if (!processedR.has(el.id)) newOrderR.push(el); });

    // 5. Update DOM and Animate
    [
        {col: colC, order: newOrderC},
        {col: colE, order: newOrderE},
        {col: colR, order: newOrderR}
    ].forEach(conf => {
        conf.col.innerHTML = '';
        conf.order.forEach(el => conf.col.appendChild(el));
    });

    // 6. Play Animation
    requestAnimationFrame(() => {
        [...newOrderC, ...newOrderE, ...newOrderR].forEach(el => {
            const oldPos = initialPositions.get(el.id);
            const newPos = el.getBoundingClientRect();
            
            const dx = oldPos.left - newPos.left;
            const dy = oldPos.top - newPos.top;
            
            if (dx || dy) {
                el.style.transition = 'none';
                el.style.transform = `translate(${dx}px, ${dy}px)`;
                
                requestAnimationFrame(() => {
                    el.style.transition = 'transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    el.style.transform = 'none';
                });
            }
        });
        
        // Smoothly redraw lines during the transition
        let start = null;
        function step(timestamp) {
            if (!start) start = timestamp;
            redrawLines();
            if (timestamp - start < 600) {
                requestAnimationFrame(step);
            }
        }
        requestAnimationFrame(step);
    });

    saveState();
}

// ─── Event Listeners ──────────────────────────────────────────────────────────
document.addEventListener('mousedown', (e) => {
    if (isLocked) return; // Block all interaction in read-only mode
    if (e.target.classList.contains('node')) {
        isDrawing = true;
        startNode = e.target;
        const pos = getNodePos(startNode);
        drawingLine.setAttribute('x1', pos.x);
        drawingLine.setAttribute('y1', pos.y);
        drawingLine.setAttribute('x2', pos.x);
        drawingLine.setAttribute('y2', pos.y);
    }
});

document.addEventListener('mousemove', (e) => {
    if (isDrawing && drawingLine) {
        drawingLine.setAttribute('x2', e.clientX);
        drawingLine.setAttribute('y2', e.clientY);
    }
});

document.addEventListener('mouseup', (e) => {
    if (!isDrawing) return;
    
    if (e.target.classList.contains('node')) {
        const sCard = startNode.closest('.card-item');
        const tCard = e.target.closest('.card-item');
        
        let fromCard, toCard;

        // Determine direction
        if (startNode.classList.contains('out') && e.target.classList.contains('in')) {
            fromCard = sCard;
            toCard = tCard;
        } else if (startNode.classList.contains('in') && e.target.classList.contains('out')) {
            fromCard = tCard;
            toCard = sCard;
        }
        
        if (fromCard && toCard) {
            const fType = fromCard.id[0], tType = toCard.id[0];
            if ((fType === 'c' && tType === 'e') || (fType === 'e' && tType === 'r')) {
                // Strict 1-to-1
                const existingFrom = connections.find(c => c.from === fromCard.id);
                const existingTo = connections.find(c => c.to === toCard.id);
                
                if (existingFrom) logAction('disconnect', { from: existingFrom.from, to: existingFrom.to });
                if (existingTo && existingTo !== existingFrom) logAction('disconnect', { from: existingTo.from, to: existingTo.to });

                connections = connections.filter(c => c.from !== fromCard.id && c.to !== toCard.id);
                connections.push({ from: fromCard.id, to: toCard.id });
                logAction('connect', { from: fromCard.id, to: toCard.id });
                saveState();
            }
        }
    } else {
        // Disconnect if dropped in blank space
        const sCard = startNode.closest('.card-item');
        if (sCard) {
            let existingConn;
            if (startNode.classList.contains('out')) {
                existingConn = connections.find(c => c.from === sCard.id);
            } else if (startNode.classList.contains('in')) {
                existingConn = connections.find(c => c.to === sCard.id);
            }

            if (existingConn) {
                logAction('disconnect', { from: existingConn.from, to: existingConn.to });
                connections = connections.filter(c => c !== existingConn);
                saveState();
            }
        }
    }
    
    if (feedbackActive) {
        feedbackActive = false;
        updateFeedbackUI();
    }
    
    isDrawing = false;
    startNode = null;
    resetDrawingLine();
    redrawLines();
});

document.addEventListener('scroll', () => {
    requestAnimationFrame(redrawLines);
}, true);

window.addEventListener('resize', redrawLines);

// ─── Restore State on Page Load ───────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    const savedIndex      = sessionStorage.getItem(SK_MAP_INDEX);
    const savedSessionId  = sessionStorage.getItem(SK_SESSION_ID);
    const savedConns      = sessionStorage.getItem(SK_CONNECTIONS);
    const savedColOrder   = sessionStorage.getItem(SK_COL_ORDER);

    if (savedIndex !== null && typeof cerMaps !== 'undefined' && cerMaps[savedIndex]) {
        const i    = parseInt(savedIndex);
        const sid  = savedSessionId || null;
        const conns = savedConns ? JSON.parse(savedConns) : [];
        const order = savedColOrder ? JSON.parse(savedColOrder) : null;
        restoreSession(i, sid, conns, order);
    }
});
