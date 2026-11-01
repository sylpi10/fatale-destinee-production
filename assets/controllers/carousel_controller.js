import { Controller } from '@hotwired/stimulus';

/**
 * Photos of a show that scroll one at a time with the arrows (sketch 5'').
 * The track is a CSS scroll-snap list: swiping and the keyboard also work without JavaScript.
 */
export default class extends Controller {
    static targets = ['track'];

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

        track.scrollTo({ left, behavior: 'smooth' });
    }
}
