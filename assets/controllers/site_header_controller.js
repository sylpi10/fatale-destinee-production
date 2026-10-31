import { Controller } from '@hotwired/stimulus';

/**
 * Past the top of the page, the header becomes a fixed bar.
 * On mobile, the burger opens and closes the menu.
 */
export default class extends Controller {
    static targets = ['burger', 'nav'];

    connect() {
        this.updateFixed();
    }

    updateFixed() {
        this.element.classList.toggle('is-fixed', window.scrollY > 80);
    }

    toggle() {
        this.setOpen(this.burgerTarget.getAttribute('aria-expanded') !== 'true');
    }

    // Before Turbo snapshots the page, so going back never shows an open menu.
    close() {
        this.setOpen(false);
    }

    setOpen(open) {
        this.burgerTarget.setAttribute('aria-expanded', String(open));
        this.navTarget.classList.toggle('is-open', open);
    }
}
