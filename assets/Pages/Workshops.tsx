import { Head, Link } from "@inertiajs/react";
import PageHeader from "../Components/Site/PageHeader";
import type { WorkshopsPageProps } from "../types/generated";

export default function Workshops({
    intro,
    slots,
    schedulingNotes,
    content,
    price,
    priceNote,
}: WorkshopsPageProps) {
    return (
        <>
            <Head title="Les ateliers – Fatale Destinée Production" />
            <PageHeader title="Les ateliers" subtitle="Théâtre adulte" />

            <div className="page-body">
                <section className="panel">
                    <h2>Les créneaux</h2>
                    <p className="lead">{intro}</p>
                    <ul className="slots">
                        {slots.map((slot) => (
                            <li key={slot.day} className="slot">
                                <span className="slot-day">{slot.day}</span>
                                <span className="slot-hours">{slot.hours}</span>
                                <span className="slot-place">{slot.place}</span>
                            </li>
                        ))}
                    </ul>
                    {schedulingNotes.map((note) => (
                        <p key={note}>{note}</p>
                    ))}
                </section>

                <section className="panel">
                    <h2>Contenu</h2>
                    {content.map((paragraph) => (
                        <p key={paragraph}>{paragraph}</p>
                    ))}
                </section>

                <section className="panel panel--center">
                    <h2>Tarifs</h2>
                    <p className="price">{price}</p>
                    <p>{priceNote}</p>
                    <Link href="/contact" className="gold-button">
                        S'inscrire
                    </Link>
                </section>
            </div>
        </>
    );
}
