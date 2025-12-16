export default class SortableList {
    constructor(element) {
        this.element = element;
        this.draggingItem = null;
        this.dragHandleSelector = ".drag";

        // Bind methods
        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDragEnd = this.onDragEnd.bind(this);
        this.onMouseDown = this.onMouseDown.bind(this);
        this.onMouseUp = this.onMouseUp.bind(this);

        // Attach listeners
        this.element.addEventListener('mousedown', this.onMouseDown);
        this.element.addEventListener('dragstart', this.onDragStart);
        this.element.addEventListener('dragover', this.onDragOver);
        this.element.addEventListener('dragend', this.onDragEnd);
        // We might need to listen to mouseup globally or on the element to reset draggable if drag didn't occur
        this.element.addEventListener('mouseup', this.onMouseUp); 
    }

    destroy() {
        this.element.removeEventListener('mousedown', this.onMouseDown);
        this.element.removeEventListener('dragstart', this.onDragStart);
        this.element.removeEventListener('dragover', this.onDragOver);
        this.element.removeEventListener('dragend', this.onDragEnd);
        this.element.removeEventListener('mouseup', this.onMouseUp);
    }

    onMouseDown(e) {
        const handle = e.target.closest(this.dragHandleSelector);
        if (handle) {
            const item = handle.closest('[data-sortable-item]') || handle.parentNode; // Assuming item is parent of handle if not marked
            // Better strategy: Find the direct child of this.element that contains the handle
            const itemElement = this.getDirectChild(e.target);
            
            if (itemElement) {
                itemElement.setAttribute('draggable', 'true');
            }
        }
    }

    onMouseUp(e) {
        // Reset draggable to false on mouse up to prevent dragging from non-handle areas later
        const itemElement = this.getDirectChild(e.target);
        if (itemElement) {
            itemElement.setAttribute('draggable', 'false');
        }
    }

    getDirectChild(target) {
        // traverse up until we find an element that is a direct child of this.element
        let el = target;
        while (el && el.parentNode !== this.element) {
            el = el.parentNode;
        }
        return el;
    }

    onDragStart(e) {
        this.draggingItem = e.target;
        e.target.classList.add('sortable-dragging');
        // Needed for Firefox to allow drag
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', ''); 
    }

    onDragOver(e) {
        e.preventDefault(); // Allow dropping
        
        const afterElement = this.getDragAfterElement(this.element, e.clientY);
        const currentDraggable = document.querySelector('.sortable-dragging');
        
        if (afterElement == null) {
            this.element.appendChild(currentDraggable);
        } else {
            this.element.insertBefore(currentDraggable, afterElement);
        }
    }

    onDragEnd(e) {
        e.target.classList.remove('sortable-dragging');
        e.target.setAttribute('draggable', 'false'); // Reset draggable state
        
        this.draggingItem = null;

        // Trigger Update
        this.updatePosition(e.target);
    }

    getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll(':scope > :not(.sortable-dragging)')];

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

    updatePosition(item) {
        const newIndex = Array.from(this.element.children).indexOf(item);
        const updateUrl = item.dataset.sortableUpdateUrl;

        if (!updateUrl) {
            console.warn('No update URL found for sortable item.');
            return;
        }

        const form = new FormData();
        form.append("_method", "put");
        form.append("position", newIndex);

        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content;

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: form
        })
            .then(response => {
                if (!response.ok) {
                    console.error('Position update failed:', response.statusText);
                }
            })
            .catch(error => {
                console.error('Position update error:', error);
            });
    }

    static start() {
        document.querySelectorAll('[data-sortable]').forEach(element => {
            new SortableList(element);
        });
    }
}
