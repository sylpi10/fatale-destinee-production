import { Controller } from '@hotwired/stimulus';

/**
 * The MENU button, fixed top right on the inner pages, opens the menu over the page.
 *
 * While it is open, the page under it ([data-menu-inert]) is inert: Tab only goes through
 * the button and the menu links, and screen readers don't read the hidden page.
 */
export default class extends Controller {
    static targets = ['button', 'panel'];

    toggle() {
        this.setOpen(this.panelTarget.hidden);
        if (this.panelTarget.hidden) {
            this.buttonTarget.focus();
        }
    }

    // Escape: closes the menu and gives the focus back to the button that opened it.
    close() {
        if (this.panelTarget.hidden) {
            return;
        }
        this.setOpen(false);
        this.buttonTarget.focus();
    }

    // Before Turbo snapshots the page, so going back never shows an open menu.
    reset() {
        this.setOpen(false);
    }

    setOpen(open) {
        this.panelTarget.hidden = !open;
        this.buttonTarget.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('is-menu-open', open);
        document.querySelectorAll('[data-menu-inert]').forEach((element) => {
            element.inert = open;
        });

        if (open) {
            this.panelTarget.querySelector('a')?.focus();
        }
    }
}
