<?php
/**
 * View Student Map - Teacher View
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/Score.php';

if (!User::checkAuth('guru') && !User::checkAuth('siswa')) {
    header("Location: index.php");
    exit;
}

$score_id = isset($_GET['score_id']) ? intval($_GET['score_id']) : 0;
if (!$score_id) {
    header("Location: report.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$scoreModel = new Score($db);

$score = $scoreModel->getById($score_id);

if (!$score || empty($score['map_data'])) {
    die("Data map tidak ditemukan atau tidak valid.");
}

// Security: If user is siswa, ensure they own this score
if ($_SESSION['role'] === 'siswa' && $score['user_id'] != $_SESSION['user_id']) {
    die("Anda tidak memiliki akses ke map ini.");
}

$map_data = json_decode($score['map_data'], true);
if (!$map_data) {
    die("Data map corrupt.");
}

$backUrl = $_SESSION['role'] === 'guru' ? 'report.php' : 'history.php';
$backText = $_SESSION['role'] === 'guru' ? '← Kembali ke Analytics' : '← Kembali ke Riwayat';

$pageTitle = "View Student Map - " . htmlspecialchars($score['namalengkap']);
$navContext = 'report';
$extraHead = '<style>body { overflow-x: hidden; background: #f4f5f7; } .node { width: 16px; height: 16px; background: #4361ee; border-radius: 50%; position: absolute; top: 50%; transform: translateY(-50%); border: 3px solid white; box-shadow: 0 0 0 1px #cbd5e1; } .node.out { right: -8px; } .node.in { left: -8px; }</style>';

include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="px-6 py-4 flex justify-between items-center bg-white border-b-2 border-slate-200">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Hasil Map: <span class="text-blue-700"><?php echo htmlspecialchars($score['namalengkap']); ?></span></h2>
        <p class="text-sm text-slate-500 font-bold uppercase tracking-widest mt-1">Materi: <?php echo htmlspecialchars($score['map_title']); ?> | Skor: <?php echo $score['score']; ?>%</p>
    </div>
    <div class="flex items-center gap-2">
        <button id="btn-feedback" onclick="toggleFeedback()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm">💡 Feedback</button>
        <button onclick="autoArrange()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm">
            🪄 Arrange
        </button>
        <a href="<?php echo $backUrl; ?>" class="bg-slate-800 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-sm">
            <?php echo $backText; ?>
        </a>
    </div>
</div>

<div id="view-editor" class="p-6 h-[calc(100vh-200px)]">
    <svg id="svg-canvas">
        <g id="line-group"></g>
    </svg>
    
    <div class="flex gap-6 max-w-[1600px] mx-auto h-full relative z-10">
        <!-- Columns -->
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-blue-100 text-blue-800 px-6 py-2 uppercase tracking-widest border border-blue-300">1. Claims</span>
            </div>
            <div id="col-claim" class="space-y-4 bg-blue-50/60 border-2 border-blue-100 flex-1 p-4"></div>
        </div>
        
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-emerald-100 text-emerald-800 px-6 py-2 uppercase tracking-widest border border-emerald-300">2. Evidences</span>
            </div>
            <div id="col-evidence" class="space-y-4 bg-emerald-50/60 border-2 border-emerald-100 flex-1 p-4"></div>
        </div>
        
        <div class="flex-1 flex flex-col">
            <div class="text-center mb-4">
                <span class="text-xs font-black bg-amber-100 text-amber-800 px-6 py-2 uppercase tracking-widest border border-amber-300">3. Reasonings</span>
            </div>
            <div id="col-reasoning" class="space-y-4 bg-amber-50/60 border-2 border-amber-100 flex-1 p-4"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const mapData = <?php echo json_encode($map_data); ?>;
    let feedbackActive = true; // Default to true for evaluation view

    function getTripletCardId(type, tripletId) {
        return `${type[0]}-${tripletId}`;
    }

    function getCardTripletId(cardId) {
        const [, tripletId] = String(cardId).split('-');
        return String(tripletId);
    }

    function getTripletByCardId(cardId) {
        const tripletId = parseInt(getCardTripletId(cardId), 10);
        return mapData.triplets.find(t => parseInt(t.id, 10) === tripletId) || null;
    }

    function toggleFeedback() {
        feedbackActive = !feedbackActive;
        const btn = document.getElementById('btn-feedback');
        if (btn) {
            btn.innerText = feedbackActive ? '💡 Feedback' : '💡 Feedback';
            btn.classList.toggle('bg-blue-50', feedbackActive);
            btn.classList.toggle('border-blue-400', feedbackActive);
            btn.classList.toggle('text-blue-700', feedbackActive);
        }
        redrawLines();
    }
    
    function renderReadonlyCards() {
        if (!mapData || !mapData.triplets) return;

        ['claim', 'evidence', 'reasoning'].forEach(type => {
            const col = document.getElementById(`col-${type}`);
            if(!col) return;

            let orderedIds = mapData.colOrder && mapData.colOrder[type] ? mapData.colOrder[type] : [];
            
            // If colOrder is missing, just fallback to triplet order
            if (orderedIds.length === 0) {
                orderedIds = mapData.triplets.map(t => getTripletCardId(type, t.id));
            }
            
            col.innerHTML = orderedIds.map(id => {
                const t = getTripletByCardId(id);
                if (!t) return '';
                
                return `
                <div id="${id}" class="card-item bg-white border-2 border-slate-200 p-5 mb-4">
                    <div class="card-handle cursor-move">
                        <p class="text-sm leading-relaxed text-slate-700 font-medium">${t[type]}</p>
                    </div>
                    ${type !== 'reasoning' ? '<div class="node out pointer-events-none"></div>' : ''}
                    ${type !== 'claim' ? '<div class="node in pointer-events-none"></div>' : ''}
                </div>`;
            }).join('');

            // Initialize Sortable for teacher to rearrange cards visually
            new Sortable(col, { 
                animation: 250, 
                easing: "cubic-bezier(0.34, 1.56, 0.64, 1)",
                handle: '.card-handle',
                onEnd: () => redrawLines()
            });
        });

        // Initial UI update for feedback button
        const btn = document.getElementById('btn-feedback');
        if (btn && feedbackActive) {
            btn.innerText = '💡 Feedback';
            btn.classList.add('bg-blue-50', 'border-blue-400', 'text-blue-700');
        }

        // Draw connections after DOM is ready
        setTimeout(redrawLines, 100);
    }

    function getNodePos(node) {
        const rect = node.getBoundingClientRect();
        return {
            x: rect.left + (rect.width / 2),
            y: rect.top + (rect.height / 2)
        };
    }

    function redrawLines() {
        const lineGroup = document.getElementById('line-group');
        if(!lineGroup) return;
        
        lineGroup.innerHTML = '';
        if(!mapData.connections) return;

        mapData.connections.forEach(c => {
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
                    
                    // Check if correct (indices match)
                    const isCorrect = getCardTripletId(c.from) === getCardTripletId(c.to);
                    
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", d);
                    path.setAttribute("fill", "none");
                    
                    if (feedbackActive) {
                        if (isCorrect) {
                            path.style.stroke = "#10b981"; // emerald-500
                            path.style.strokeDasharray = "none";
                        } else {
                            path.style.stroke = "#ef4444"; // red-500
                            path.style.strokeDasharray = "8,8";
                        }
                    } else {
                        path.style.stroke = "#4361ee"; // blue-600
                        path.style.strokeDasharray = "none";
                    }

                    path.setAttribute("stroke-width", "4");
                    path.setAttribute("stroke-linecap", "round");
                    
                    lineGroup.appendChild(path);
                }
            }
        });
    }

    window.addEventListener('resize', redrawLines);
    document.addEventListener('DOMContentLoaded', renderReadonlyCards);

    function autoArrange() {
        if (!mapData.connections || mapData.connections.length === 0) return alert("Belum ada koneksi untuk diatur!");

        const colC = document.getElementById('col-claim');
        const colE = document.getElementById('col-evidence');
        const colR = document.getElementById('col-reasoning');

        const claims = Array.from(colC.children);
        const evidences = Array.from(colE.children);
        const reasonings = Array.from(colR.children);

        const initialPositions = new Map();
        [...claims, ...evidences, ...reasonings].forEach(el => {
            initialPositions.set(el.id, el.getBoundingClientRect());
        });

        let newOrderC = [], newOrderE = [], newOrderR = [];
        let processedC = new Set(), processedE = new Set(), processedR = new Set();

        claims.forEach(cEl => {
            const connCE = mapData.connections.find(conn => conn.from === cEl.id);
            if (connCE) {
                newOrderC.push(cEl);
                processedC.add(cEl.id);
                const eEl = evidences.find(e => e.id === connCE.to);
                if (eEl) {
                    newOrderE.push(eEl);
                    processedE.add(eEl.id);
                    const connER = mapData.connections.find(conn => conn.from === eEl.id);
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

        evidences.forEach(eEl => {
            if (processedE.has(eEl.id)) return;
            const connER = mapData.connections.find(conn => conn.from === eEl.id);
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

        claims.forEach(el => { if (!processedC.has(el.id)) newOrderC.push(el); });
        evidences.forEach(el => { if (!processedE.has(el.id)) newOrderE.push(el); });
        reasonings.forEach(el => { if (!processedR.has(el.id)) newOrderR.push(el); });

        [
            {col: colC, order: newOrderC},
            {col: colE, order: newOrderE},
            {col: colR, order: newOrderR}
        ].forEach(conf => {
            conf.col.innerHTML = '';
            conf.order.forEach(el => conf.col.appendChild(el));
        });

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
    }
</script>

<?php include 'partials/footer.php'; ?>
