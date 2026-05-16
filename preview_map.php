<?php
/**
 * Preview Map - Teacher View
 */
require_once 'app/Config/Database.php';
require_once 'app/Models/User.php';
require_once 'app/Models/CERMap.php';

if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}

$map_id = isset($_GET['map_id']) ? intval($_GET['map_id']) : 0;
if (!$map_id) {
    header("Location: admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$cerMapModel = new CERMap($db);

$maps = $cerMapModel->getAll();
$currentMap = null;
foreach ($maps as $m) {
    if ($m['id'] == $map_id) {
        $currentMap = $m;
        break;
    }
}

if (!$currentMap) {
    die("Map tidak ditemukan.");
}

$triplets = $cerMapModel->getTriplets($map_id);

// Generate synthetic "perfect" connections
$connections = [];
foreach ($triplets as $t) {
    $tripletId = intval($t['id']);
    $connections[] = ['from' => "c-{$tripletId}", 'to' => "e-{$tripletId}"];
    $connections[] = ['from' => "e-{$tripletId}", 'to' => "r-{$tripletId}"];
}

$map_data = [
    'triplets' => $triplets,
    'connections' => $connections,
    'colOrder' => null // Will fallback to natural order
];

$pageTitle = "Preview Map - " . htmlspecialchars($currentMap['title']);
$navContext = 'studio';
$extraHead = '<style>body { overflow-x: hidden; background: #f4f5f7; } .node { width: 16px; height: 16px; background: #4361ee; border-radius: 50%; position: absolute; top: 50%; transform: translateY(-50%); border: 3px solid white; box-shadow: 0 0 0 1px #cbd5e1; } .node.out { right: -8px; } .node.in { left: -8px; }</style>';

include 'partials/header.php';
include 'partials/navbar.php';
?>

<div class="px-6 py-4 flex justify-between items-center bg-white border-b-2 border-slate-200">
    <div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Preview Map: <span class="text-blue-700"><?php echo htmlspecialchars($currentMap['title']); ?></span></h2>
        <p class="text-sm text-emerald-600 font-bold uppercase tracking-widest mt-1">✓ Menampilkan Koneksi Triplet yang Benar</p>
    </div>
    <div class="flex items-center gap-2">
        <button onclick="autoArrange()" class="bg-white border border-slate-300 text-slate-700 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all shadow-sm">
            🪄 Arrange
        </button>
        <a href="admin.php" class="bg-slate-800 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-sm">
            ← Kembali ke Studio
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
                <div id="${id}" class="card-item bg-white border-2 border-slate-200 p-5 mb-4 relative" style="opacity: 0.9;">
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
                handle: '.card-handle',
                onEnd: () => redrawLines()
            });
        });

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
                    
                    // Since this is a preview of correct triplets, all are correct.
                    const isCorrect = true;
                    const strokeColor = "#10b981"; // emerald-500
                    
                    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                    path.setAttribute("d", d);
                    path.setAttribute("fill", "none");
                    path.setAttribute("stroke", strokeColor);
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
