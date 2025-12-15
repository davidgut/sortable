import Sortable from 'sortablejs';

export default class SortableList {
    constructor(element) {
        this.element = element;

        // Explicitly bind the handler to ensure 'this' context if needed later
        this.onEnd = this.onEnd.bind(this);

        this.sortable = Sortable.create(this.element, {
            animation: 150,
            handle: ".drag",
            onEnd: this.onEnd
        });
    }

    destroy() {
        if (this.sortable) {
            this.sortable.destroy();
            this.sortable = null;
        }
    }

    onEnd(event) {
        const item = event.item;
        const newIndex = event.newIndex;
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
