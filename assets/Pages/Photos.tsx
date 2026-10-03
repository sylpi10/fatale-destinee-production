import { Head } from "@inertiajs/react";
import { useEffect, useState } from "react";
import PageHeader from "../Components/Site/PageHeader";
import type { PhotosPageProps } from "../types/generated";

export default function Photos({ title, caption, photos }: PhotosPageProps) {
    const [current, setCurrent] = useState<number | null>(null);

    const close = () => setCurrent(null);
    const move = (step: number) =>
        setCurrent((index) =>
            index === null
                ? null
                : (index + step + photos.length) % photos.length,
        );

    useEffect(() => {
        if (current === null) return;

        function onKeyDown(event: KeyboardEvent) {
            if (event.key === "Escape") close();
            if (event.key === "ArrowRight") move(1);
            if (event.key === "ArrowLeft") move(-1);
        }
        document.addEventListener("keydown", onKeyDown);
        return () => document.removeEventListener("keydown", onKeyDown);
    }, [current]);

    return (
        <>
            <Head title="Photos – Fatale Destinée Production" />
            <PageHeader title="Photos" subtitle={title} />

            <div className="page-body page-body--wide">
                <p className="gallery-caption">{caption}</p>
                <ul className="gallery">
                    {photos.map((photo, index) => (
                        <li key={photo}>
                            <button
                                type="button"
                                onClick={() => setCurrent(index)}
                            >
                                <img
                                    src={photo}
                                    alt={`${title} – photo ${index + 1}`}
                                    loading="lazy"
                                />
                            </button>
                        </li>
                    ))}
                </ul>
            </div>

            {current !== null && (
                <div
                    className="lightbox"
                    role="dialog"
                    aria-modal="true"
                    aria-label={`Photo ${current + 1} sur ${photos.length}`}
                    onClick={close}
                >
                    <img
                        src={photos[current]}
                        alt={`${title} – photo ${current + 1}`}
                        onClick={(event) => event.stopPropagation()}
                    />
                    <button
                        type="button"
                        className="lightbox-close"
                        aria-label="Fermer"
                        onClick={close}
                    >
                        ×
                    </button>
                    <button
                        type="button"
                        className="lightbox-prev"
                        aria-label="Photo précédente"
                        onClick={(event) => {
                            event.stopPropagation();
                            move(-1);
                        }}
                    >
                        ‹
                    </button>
                    <button
                        type="button"
                        className="lightbox-next"
                        aria-label="Photo suivante"
                        onClick={(event) => {
                            event.stopPropagation();
                            move(1);
                        }}
                    >
                        ›
                    </button>
                </div>
            )}
        </>
    );
}
