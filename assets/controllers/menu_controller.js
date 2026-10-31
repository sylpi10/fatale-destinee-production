import { Controller } from '@hotwired/stimulus';

/**
 * The MENU button, fixed top right on the inner pages, opens the menu over the page.
 */
export default class extends Controller {
    static targets = ['button', 'panel'];

    toggle() {
        this.setOpen(this.panelTarget.hidden);
    }

    // Also runs before Turbo snapshots the page, so going back never shows an open menu.
    close() {
        this.setOpen(false);
    }

    setOpen(open) {
        this.panelTarget.hidden = !open;
        this.buttonTarget.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('is-menu-open', open);

        if (open) {
            this.panelTarget.querySelector('a')?.focus();
        }
    }
}
