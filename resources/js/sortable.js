export default class SortableList {
    static instances = [];

    constructor(element) {
        this.element = element;
        this.dragHandleSelector = element.dataset.sortableHandle || ".drag";

        this.onDragStart = this.onDragStart.bind(this);
        this.onDragOver = this.onDragOver.bind(this);
        this.onDragEnd = this.onDragEnd.bind(this);
        this.onMouseDown = this.onMouseDown.bind(this);
        this.onMouseUp = this.onMouseUp.bind(this);

        this.element.addEventListener('mousedown', this.onMouseDown);
        this.element.addEventListener('dragstart', this.onDragStart);
        this.element.addEventListener('dragover', this.onDragOver);
        this.element.addEventListener('dragend', this.onDragEnd);
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
            const itemElement = this.getDirectChild(e.target);
            if (itemElement) {
                itemElement.setAttribute('draggable', 'true');
            }
        }
    }

    onMouseUp(e) {
        const itemElement = this.getDirectChild(e.target);
        if (itemElement) {
            itemElement.setAttribute('draggable', 'false');
        }
    }

    getDirectChild(target) {
        let el = target;
        while (el && el.parentNode !== this.element) {
            el = el.parentNode;
        }
        return el;
    }

    onDragStart(e) {
        e.target.classList.add('sortable-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
    }

    onDragOver(e) {
        e.preventDefault();

        const afterElement = this.getDragAfterElement(this.element, e.clientY);
        const currentDraggable = this.element.querySelector('.sortable-dragging');

        if (!currentDraggable) return;

        if (afterElement == null) {
            this.element.appendChild(currentDraggable);
        } else {
            this.element.insertBefore(currentDraggable, afterElement);
        }
    }

    onDragEnd(e) {
        e.target.classList.remove('sortable-dragging');
        e.target.setAttribute('draggable', 'false');
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
            SortableList.instances.push(new SortableList(element));
        });

        return SortableList.instances;
    }

    static stop() {
        SortableList.instances.forEach(instance => instance.destroy());
        SortableList.instances = [];
    }
}
