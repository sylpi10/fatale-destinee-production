import { Controller } from '@hotwired/stimulus';

/**
 * Photos of a show that scroll one at a time with the arrows (sketch 5'').
 * The track is a CSS scroll-snap list: swiping and the keyboard arrows (once the list
 * has the focus) also work without JavaScript. Screen readers hear "Photo 2 sur 5"
 * after each move.
 */
export default class extends Controller {
    static targets = ['track', 'status'];

    previous() {
        this.scrollBy(-1);
    }

    next() {
        this.scrollBy(1);
    }

    // Past the last photo, goes back to the first one (and the other way round).
    scrollBy(direction) {
        const track = this.trackTarget;
        const last = track.scrollWidth - track.clientWidth;
        let left = track.scrollLeft + direction * track.clientWidth;

        if (left > last + 1) {
            left = 0;
        } else if (left < -1) {
            left = last;
        }

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        track.scrollTo({ left, behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    // Called on every scroll event: announces the photo once the scrolling stops.
    update() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            if (!this.hasStatusTarget) {
                return;
            }
            const track = this.trackTarget;
            const count = track.children.length;
            const index = Math.round(track.scrollLeft / track.clientWidth) + 1;
            this.statusTarget.textContent = `Photo ${Math.min(index, count)} sur ${count}`;
        }, 150);
    }

    disconnect() {
        clearTimeout(this.timeout);
    }
}
