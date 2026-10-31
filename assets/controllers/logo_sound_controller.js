import { Controller } from '@hotwired/stimulus';

/**
 * Clicking the logo makes it complain ("Aïe !", "Touche pas à mon chapeau !"…), in turn.
 * Plays the recorded sounds when "sounds" is set; until then, the browser speaks the
 * phrases with its own voice.
 */
export default class extends Controller {
    static values = {
        phrases: Array,
        sounds: Array,
    };

    connect() {
        this.index = 0;
    }

    play() {
        this.element.classList.remove('is-hit');
        // Restart the wobble animation, even on quick repeated clicks.
        void this.element.offsetWidth;
        this.element.classList.add('is-hit');

        if (this.soundsValue.length > 0) {
            new Audio(this.soundsValue[this.index % this.soundsValue.length]).play();
        } else if ('speechSynthesis' in window && this.phrasesValue.length > 0) {
            const utterance = new SpeechSynthesisUtterance(this.phrasesValue[this.index % this.phrasesValue.length]);
            utterance.lang = 'fr-FR';
            utterance.pitch = 1.4;
            speechSynthesis.cancel();
            speechSynthesis.speak(utterance);
        }

        this.index++;
    }
}
