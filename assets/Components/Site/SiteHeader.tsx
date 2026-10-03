import { Link, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";

const links = [
    { href: "/", label: "Accueil" },
    { href: "/ateliers", label: "Les ateliers" },
    { href: "/intervenant", label: "Intervenant" },
    { href: "/photos", label: "Photos" },
    { href: "/contact", label: "Contact" },
];

export default function SiteHeader() {
    const { url } = usePage();
    const [open, setOpen] = useState(false);

    // Close the mobile menu after each navigation.
    useEffect(() => setOpen(false), [url]);

    return (
        <header className="site-header">
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
                    </Link>
                ))}
            </nav>
        </header>
    );
}
