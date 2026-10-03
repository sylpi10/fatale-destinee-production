import { Head, Link } from "@inertiajs/react";
import PageHeader from "../Components/Site/PageHeader";

type Props = { status: number };

const messages: Record<number, { title: string; description: string }> = {
    403: {
        title: "Accès refusé",
        description: "Vous n'avez pas accès à cette page.",
    },
    404: {
        title: "Page introuvable",
        description: "Désolé, la page que vous cherchez n'existe pas.",
    },
    500: {
        title: "Erreur serveur",
        description:
            "Une erreur est survenue de notre côté. Merci de réessayer plus tard.",
    },
    503: {
        title: "Service indisponible",
        description:
            "Le site est en maintenance. Revenez dans quelques instants.",
    },
};

export default function ErrorPage({ status }: Props) {
    const { title, description } = messages[status] ?? messages[500];

    return (
        <>
            <Head title={`${title} – Fatale Destinée Production`} />
            <PageHeader title={String(status)} subtitle={title} />
            <div className="page-body">
                <section className="panel panel--center">
                    <p>{description}</p>
                    <Link href="/" className="gold-button">
                        Retour à l'accueil
                    </Link>
                </section>
            </div>
        </>
    );
}
