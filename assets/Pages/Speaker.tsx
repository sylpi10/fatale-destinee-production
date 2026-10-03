import { Head } from "@inertiajs/react";
import PageHeader from "../Components/Site/PageHeader";
import type { SpeakerPageProps } from "../types/generated";

export default function Speaker({ name, photo, bio }: SpeakerPageProps) {
    return (
        <>
            <Head title="Intervenant – Fatale Destinée Production" />
            <PageHeader title="Intervenant" subtitle={name} />

            <div className="page-body">
                <article className="panel speaker">
                    <img
                        className="speaker-photo"
                        src={photo}
                        alt={name}
                        width={1600}
                        height={1067}
                    />
                    <div>
                        <h2>{name}</h2>
                        {bio.map((paragraph) => (
                            <p key={paragraph}>{paragraph}</p>
                        ))}
                    </div>
                </article>
            </div>
        </>
    );
}
