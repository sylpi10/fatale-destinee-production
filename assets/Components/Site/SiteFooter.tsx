import { Link } from "@inertiajs/react";
import Bolt from "./Bolt";

export default function SiteFooter() {
    return (
        <footer className="site-footer">
            <Bolt />
            <p>
                © {new Date().getFullYear()} Teddy Moulin –{" "}
                <a href="tel:+33686554086">+33 6 86 55 40 86</a>
            </p>
            <p className="site-footer-small">
                Fatale Destinée Production · Atelier théâtre adulte · Cie
                Domovoi · <Link href="/contact">Contact</Link>
            </p>
        </footer>
    );
}
