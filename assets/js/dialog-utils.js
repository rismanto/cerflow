/**
 * Dialog Utils
 * Handles dragging and resizing of floating dialogs.
 */
const DialogUtils = {
    init(dialogId, headerId, resizeId) {
        const dialog = document.getElementById(dialogId);
        const header = document.getElementById(headerId);
        const resize = document.getElementById(resizeId);

        if (!dialog) return;

        // DRAGGING
        let isDragging = false;
        let dragStartX, dragStartY;
        let initialX, initialY;

        header.addEventListener('mousedown', (e) => {
            if (e.target.closest('button')) return; // Don't drag if clicking buttons
            isDragging = true;
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            
            const rect = dialog.getBoundingClientRect();
            initialX = rect.left;
            initialY = rect.top;
            
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', stopDrag);
            header.style.cursor = 'grabbing';
        });

        function onDrag(e) {
            if (!isDragging) return;
            const dx = e.clientX - dragStartX;
            const dy = e.clientY - dragStartY;
            
            let nextX = initialX + dx;
            let nextY = initialY + dy;

            // Boundary checks
            const minX = 0;
            const minY = 0;
            const maxX = window.innerWidth - dialog.offsetWidth;
            const maxY = window.innerHeight - dialog.offsetHeight;

            if (nextX < minX) nextX = minX;
            if (nextX > maxX) nextX = maxX;
            if (nextY < minY) nextY = minY;
            if (nextY > maxY) nextY = maxY;

            dialog.style.left = `${nextX}px`;
            dialog.style.top = `${nextY}px`;
            dialog.style.right = 'auto'; // Reset right if set
            dialog.style.bottom = 'auto';
            dialog.style.transform = 'none'; // Clear translate-x/y if set
        }

        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', onDrag);
            document.removeEventListener('mouseup', stopDrag);
            header.style.cursor = 'grab';
        }

        // RESIZING
        let isResizing = false;
        let resizeStartX, resizeStartY;
        let initialWidth, initialHeight;

        if (resize) {
            resize.addEventListener('mousedown', (e) => {
                isResizing = true;
                resizeStartX = e.clientX;
                resizeStartY = e.clientY;
                
                // Get current pixel position to lock it
                const rect = dialog.getBoundingClientRect();
                dialog.style.left = `${rect.left}px`;
                dialog.style.top = `${rect.top}px`;
                dialog.style.right = 'auto';
                dialog.style.bottom = 'auto';
                dialog.style.transform = 'none';

                initialWidth = dialog.offsetWidth;
                initialHeight = dialog.offsetHeight;
                
                document.addEventListener('mousemove', onResize);
                document.addEventListener('mouseup', stopResize);
                e.preventDefault();
            });

            function onResize(e) {
                if (!isResizing) return;
                const dw = e.clientX - resizeStartX;
                const dh = e.clientY - resizeStartY;
                
                let nextWidth = initialWidth + dw;
                let nextHeight = initialHeight + dh;

                // Max size based on current position and window size
                const maxWidth = window.innerWidth - dialog.offsetLeft;
                const maxHeight = window.innerHeight - dialog.offsetTop;

                if (nextWidth > maxWidth) nextWidth = maxWidth;
                if (nextHeight > maxHeight) nextHeight = maxHeight;

                // Min size
                if (nextWidth < 200) nextWidth = 200;
                if (nextHeight < 150) nextHeight = 150;

                dialog.style.width = `${nextWidth}px`;
                dialog.style.height = `${nextHeight}px`;
            }

            function stopResize() {
                isResizing = false;
                document.removeEventListener('mousemove', onResize);
                document.removeEventListener('mouseup', stopResize);
            }
        }
    }
};
