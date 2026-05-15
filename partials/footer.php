    <!-- Persistent Footer Bar -->
    <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 py-3 px-8 z-[100] flex justify-between items-center shadow-[0_-4px_10px_rgba(0,0,0,0.03)] backdrop-blur-sm bg-white/90">
        <!-- Login Info (Bottom Left) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="flex items-center gap-3">
                <div class="text-[10px] uppercase font-black tracking-widest text-slate-400 bg-slate-100 px-2 py-1 border border-slate-200">Login as:</div>
                <div class="text-xs font-bold flex items-center gap-2">
                    <span class="text-blue-700">@<?php echo $_SESSION['username']; ?></span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-600"><?php echo $_SESSION['namalengkap']; ?></span>
                </div>
            </div>
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <!-- Copyright (Center/Right) -->
        <div class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
            &copy; <?php echo date('Y'); ?> CER Flow <span class="text-slate-300 mx-2">•</span> Version 2.0
        </div>
    </footer>

    <?php if (isset($extraFooter)) echo $extraFooter; ?>

    <!-- Global Confirmation Modal -->
    <div id="global-confirm-modal" class="fixed inset-0 z-[300] bg-slate-900/85 backdrop-blur-sm items-center justify-center p-6 hidden">
        <div class="bg-white p-8 max-w-sm w-full shadow-2xl border-2 border-slate-300 relative text-center">
            <h2 class="text-xl font-black text-slate-800 uppercase italic mb-4">Konfirmasi</h2>
            <p id="global-confirm-message" class="text-sm font-medium text-slate-600 mb-8 leading-relaxed whitespace-pre-wrap"></p>
            <div class="flex gap-4 justify-center">
                <button id="global-confirm-cancel" class="bg-white border-2 border-slate-300 text-slate-700 px-6 py-2 text-sm font-black uppercase tracking-widest hover:bg-slate-50 hover:border-slate-400 transition-all">Batal</button>
                <button id="global-confirm-ok" class="bg-blue-700 text-white px-6 py-2 text-sm font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow-sm">Ya</button>
            </div>
        </div>
    </div>

    <script>
        window.CustomConfirm = function(message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('global-confirm-modal');
                const msgEl = document.getElementById('global-confirm-message');
                const btnOk = document.getElementById('global-confirm-ok');
                const btnCancel = document.getElementById('global-confirm-cancel');

                msgEl.innerText = message;
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                const cleanup = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    btnOk.removeEventListener('click', onOk);
                    btnCancel.removeEventListener('click', onCancel);
                };

                const onOk = () => { cleanup(); resolve(true); };
                const onCancel = () => { cleanup(); resolve(false); };

                btnOk.addEventListener('click', onOk);
                btnCancel.addEventListener('click', onCancel);
            });
        };
    </script>
</body>
</html>
