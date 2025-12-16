function setupDragAndDrop() {
    // جعل الكتل قابلة للسحب
    document.querySelectorAll('.block').forEach(block => {
        block.addEventListener('dragstart', function(e) {
            // تخزين نوع الكتلة فقط وليس العنصر نفسه
            e.dataTransfer.setData('block-type', this.dataset.id);
            this.classList.add('dragging');
            playSound('pick');
            
            // إنشاء صورة للسحب
            const dragImage = this.cloneNode(true);
            dragImage.style.position = 'fixed';
            dragImage.style.opacity = '0.8';
            dragImage.style.pointerEvents = 'none';
            dragImage.style.zIndex = '10000';
            dragImage.style.width = `${this.offsetWidth}px`;
            dragImage.id = 'drag-ghost';
            document.body.appendChild(dragImage);
            e.dataTransfer.setDragImage(dragImage, this.offsetWidth/2, this.offsetHeight/2);
        });

        block.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            const ghost = document.getElementById('drag-ghost');
            if (ghost) ghost.remove();
        });
    });

    // تحسينات منطقة الإفلات
    workspace.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drop-highlight');
        
        // تحديد موضع الإدراج الدقيق
        const afterElement = getDragAfterElement(workspace, e.clientY);
        const draggingElement = document.getElementById('drag-ghost');
        
        if (afterElement) {
            workspace.insertBefore(draggingElement, afterElement);
        }
    });

    workspace.addEventListener('dragleave', function() {
        this.classList.remove('drop-highlight');
    });

    workspace.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drop-highlight');
        
        const blockType = e.dataTransfer.getData('block-type');
        if (blockType) {
            const originalBlock = document.querySelector(`.block[data-id="${blockType}"]`);
            if (originalBlock) {
                addBlockToWorkspace(originalBlock);
                playSound('drop');
            }
        }
    });

    // دالة مساعدة لتحديد موضع الإدراج
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.workspace-block')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
}

function addBlockToWorkspace(originalBlock) {
    // إنشاء نسخة جديدة من الكتلة الأصلية
    const clone = originalBlock.cloneNode(true);
    clone.classList.remove('block', 'dragging');
    clone.classList.add('workspace-block');
    clone.draggable = true; // السماح بإعادة الترتيب
    
    // إضافة زر الإزالة
    const removeBtn = document.createElement('div');
    removeBtn.className = 'workspace-block-remove';
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.addEventListener('click', function() {
        clone.classList.add('removing');
        setTimeout(() => {
            clone.remove();
            playSound('click');
        }, 300);
    });
    
    clone.appendChild(removeBtn);
    
    // تأثيرات الإضافة
    clone.style.opacity = '0';
    clone.style.transform = 'translateY(20px)';
    clone.style.transition = 'none';
    workspace.appendChild(clone);
    
    setTimeout(() => {
        clone.style.transition = 'all 0.3s ease';
        clone.style.opacity = '1';
        clone.style.transform = 'translateY(0)';
        clone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 10);
    
    // إعداد سحب الكتل داخل منطقة العمل
    setupWorkspaceBlockDrag(clone);
}


function setupWorkspaceBlockDrag(block) {
    block.addEventListener('dragstart', function(e) {
        this.classList.add('dragging');
        e.dataTransfer.setData('workspace-block', 'true');
        playSound('pick');
        
        // إنشاء صورة للسحب
        const dragImage = this.cloneNode(true);
        dragImage.style.position = 'fixed';
        dragImage.style.opacity = '0.8';
        dragImage.style.pointerEvents = 'none';
        dragImage.style.zIndex = '10000';
        dragImage.id = 'workspace-drag-ghost';
        document.body.appendChild(dragImage);
        e.dataTransfer.setDragImage(dragImage, this.offsetWidth/2, this.offsetHeight/2);
    });
    
    block.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        const ghost = document.getElementById('workspace-drag-ghost');
        if (ghost) ghost.remove();
    });
}



function addBlockToWorkspace(originalBlock) {
    // ... الكود السابق ...
    
    // إضافة زر النسخ
    const copyBtn = document.createElement('div');
    copyBtn.className = 'workspace-block-copy';
    copyBtn.innerHTML = '<i class="far fa-copy"></i>';
    copyBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        addBlockToWorkspace(originalBlock);
        playSound('pick');
    });
    
    clone.insertBefore(copyBtn, removeBtn);
    
    // ... بقية الكود ...
}


function resetLevel() {
    playSound('click');
    
    // مسح منطقة العمل مع تأثير
    const blocks = workspace.querySelectorAll('.workspace-block');
    blocks.forEach((block, index) => {
        setTimeout(() => {
            block.classList.add('removing');
            setTimeout(() => block.remove(), 300);
        }, index * 50);
    });
    
    // إعادة تحميل المستوى
    setTimeout(() => {
        loadLevel(currentLevel);
    }, blocks.length * 50 + 300);
}