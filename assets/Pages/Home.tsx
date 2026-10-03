import { Head, Link } from "@inertiajs/react";
import Bolt from "../Components/Site/Bolt";
import HeroArt from "../Components/Site/HeroArt";

export default function Home() {
    return (
        <section className="hero">
            <Head title="Fatale Destinée Production" />
            <HeroArt />

            <div className="hero-content">
                <div className="hero-title">
                    <h1 className="gold-title gold-title--hero">
                        Fatale
                        <br />
                        Destinée
                    </h1>
                    <svg
                        className="hero-title-bolt"
                        viewBox="0 0 800 230"
                        aria-hidden="true"
                    >
                        <g
                            fill="none"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        >
                            <path
                                d="M500 -5 L470 40 L486 52 L440 102 L458 112 L398 178 L410 186 L360 236"
                                stroke="#5aa8ff"
                                strokeWidth="12"
                                opacity="0.5"
                                filter="url(#soft)"
                            />
                            <path
                                d="M500 -5 L470 40 L486 52 L440 102 L458 112 L398 178 L410 186 L360 236"
                                stroke="#ffffff"
                                strokeWidth="3.5"
                                filter="url(#glow)"
                            />
                            <path
                                d="M440 102 L410 100 L392 118 L370 116"
                                stroke="#eef6ff"
                                strokeWidth="1.4"
                                filter="url(#glow)"
                            />
                            <path
                                d="M458 112 L492 130 L506 150"
                                stroke="#eef6ff"
                                strokeWidth="1.4"
                                filter="url(#glow)"
                            />
                            <path
                                d="M130 70 L150 84 L142 96 L168 112"
                                stroke="#eef6ff"
                                strokeWidth="1.2"
                                opacity="0.8"
                                filter="url(#glow)"
                            />
                            <path
                                d="M690 150 L668 160 L676 172 L650 188"
                                stroke="#eef6ff"
                                strokeWidth="1.2"
                                opacity="0.8"
                                filter="url(#glow)"
                            />
                        </g>
                    </svg>
                </div>

                <div className="ornament ornament--hero">
                    <span className="ornament-line" />
                    <span className="ornament-text">Production</span>
                    <span className="ornament-line ornament-line--right" />
                </div>

                <Bolt />

                <Link href="/ateliers" className="gold-button">
                    Découvrir les ateliers
                </Link>
            </div>
        </section>
    );
}
