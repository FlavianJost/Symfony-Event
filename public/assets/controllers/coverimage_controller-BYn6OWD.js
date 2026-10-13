import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];

    connect() {
        const input = this.inputTarget;
        if (!input) {
            return;
        }
        const eventData = input.dataset.eventCover;
        if (eventData && eventData.trim().length > 0) {
            const dataTransfer = new DataTransfer();
            const file = new File([''], eventData, { type: 'application/octet-stream' });
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
        }
    }
}
