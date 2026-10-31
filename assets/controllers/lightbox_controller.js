import { Controller } from '@hotwired/stimulus';

/**
 * Full-screen viewer for the gallery: opens on a thumbnail, arrows and Escape
 * on the keyboard, click outside the photo to close.
 */
export default class extends Controller {
    static targets = ['thumb', 'dialog', 'image', 'closeButton'];

    open({ params: { index } }) {
        this.show(index);
        this.dialogTarget.hidden = false;
        this.closeButtonTarget.focus();
    }

    close() {
        if (this.dialogTarget.hidden) return;

        this.dialogTarget.hidden = true;
        this.thumbTargets[this.index]?.closest('button')?.focus();
    }

    next() {
        this.show(this.index + 1);
    }

    previous() {
        this.show(this.index - 1);
    }

    keydown(event) {
        if (this.dialogTarget.hidden) return;

        if (event.key === 'Escape') this.close();
        if (event.key === 'ArrowRight') this.next();
        if (event.key === 'ArrowLeft') this.previous();
    }

    show(index) {
        const count = this.thumbTargets.length;
        this.index = (index + count) % count;

        const thumb = this.thumbTargets[this.index];
        this.imageTarget.src = thumb.src;
        this.imageTarget.alt = thumb.alt;
        this.dialogTarget.setAttribute('aria-label', `Photo ${this.index + 1} sur ${count}`);
    }
}
