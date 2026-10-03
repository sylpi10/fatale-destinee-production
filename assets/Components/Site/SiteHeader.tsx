import { Link, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";

const links = [
    { href: "/", label: "Accueil" },
    { href: "/ateliers", label: "Les ateliers" },
    { href: "/intervenant", label: "Intervenant" },
    { href: "/photos", label: "Photos" },
    { href: "/contact", label: "Contact" },
];

const boltPath =
    "M0 6 L12 5 L17 10 L29 2 L35 8 L49 4 L54 11 L67 3 L73 8 L87 2 L100 6";

/** Crackling lightning under a menu item, shown on hover and on the active page. */
function NavBolt() {
    return (
        <svg
            className="nav-bolt"
            viewBox="0 0 100 12"
            preserveAspectRatio="none"
            aria-hidden="true"
        >
            <path className="nav-bolt-glow" d={boltPath} pathLength={1} />
            <path className="nav-bolt-core" d={boltPath} pathLength={1} />
        </svg>
    );
}

export default function SiteHeader() {
    const { url } = usePage();
    const [open, setOpen] = useState(false);

    // Close the mobile menu after each navigation.
    useEffect(() => setOpen(false), [url]);

    // Past the top of the page, the header becomes a fixed bar.
    const [fixed, setFixed] = useState(false);
    useEffect(() => {
        const onScroll = () => setFixed(scrollY > 80);
        onScroll();
        addEventListener("scroll", onScroll, { passive: true });
        return () => removeEventListener("scroll", onScroll);
    }, []);

    return (
        <header className={fixed ? "site-header is-fixed" : "site-header"}>
            <Link href="/" className="site-brand" aria-label="Accueil">
                F · D · P
            </Link>
            <button
                type="button"
                className="site-burger"
                aria-expanded={open}
                aria-controls="site-nav"
                onClick={() => setOpen(!open)}
            >
                <span className="sr-only">Menu</span>
                <span aria-hidden="true" />
            </button>
            <nav
                id="site-nav"
                className={open ? "site-nav is-open" : "site-nav"}
            >
                {links.map((link) => (
                    <Link
                        key={link.href}
                        href={link.href}
                        aria-current={url === link.href ? "page" : undefined}
                    >
                        {link.label}
                        <NavBolt />
                    </Link>
                ))}
            </nav>
        </header>
    );
}
