import { Head, useForm } from "@inertiajs/react";
import type { FormEvent } from "react";
import PageHeader from "../Components/Site/PageHeader";
import type { ContactPageProps } from "../types/generated";

const fields = [
    { name: "name", label: "Nom", type: "text", autoComplete: "name" },
    { name: "email", label: "E-mail", type: "email", autoComplete: "email" },
    { name: "subject", label: "Sujet", type: "text", autoComplete: "off" },
] as const;

export default function Contact({
    name,
    phone,
    email,
    registrationPhone,
    registrationEmail,
}: ContactPageProps) {
    const form = useForm({
        name: "",
        email: "",
        subject: "",
        message: "",
        website: "",
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post("/contact", {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }

    return (
        <>
            <Head title="Contact – Fatale Destinée Production" />
            <PageHeader title="Contact" subtitle="Écrivez-nous" />

            <div className="page-body contact">
                <aside className="panel contact-info">
                    <h2>{name}</h2>
                    <p>
                        <a href={`tel:${phone.replace(/\s/g, "")}`}>{phone}</a>
                        <br />
                        <a href={`mailto:${email}`}>{email}</a>
                    </p>
                    <h3>Renseignements et inscriptions</h3>
                    <p>
                        <a href={`tel:${registrationPhone.replace(/\s/g, "")}`}>
                            {registrationPhone}
                        </a>
                        <br />
                        <a href={`mailto:${registrationEmail}`}>
                            {registrationEmail}
                        </a>
                    </p>
                </aside>

                <form
                    onSubmit={submit}
                    className="panel contact-form"
                    noValidate
                >
                    {fields.map((field) => (
                        <div className="input-wrapper" key={field.name}>
                            <label htmlFor={field.name}>{field.label}</label>
                            <input
                                id={field.name}
                                type={field.type}
                                autoComplete={field.autoComplete}
                                value={form.data[field.name]}
                                onChange={(e) =>
                                    form.setData(field.name, e.target.value)
                                }
                            />
                            {form.errors[field.name] && (
                                <p className="form-error">
                                    {form.errors[field.name]}
                                </p>
                            )}
                        </div>
                    ))}
                    <div className="input-wrapper">
                        <label htmlFor="message">Message</label>
                        <textarea
                            id="message"
                            rows={7}
                            value={form.data.message}
                            onChange={(e) =>
                                form.setData("message", e.target.value)
                            }
                        />
                        {form.errors.message && (
                            <p className="form-error">{form.errors.message}</p>
                        )}
                    </div>

                    {/* Honeypot: hidden from humans, filled in by bots */}
                    <div className="honeypot" aria-hidden="true">
                        <label htmlFor="website">Site web</label>
                        <input
                            id="website"
                            type="text"
                            tabIndex={-1}
                            autoComplete="off"
                            value={form.data.website}
                            onChange={(e) =>
                                form.setData("website", e.target.value)
                            }
                        />
                    </div>

                    <button
                        type="submit"
                        className="gold-button"
                        disabled={form.processing}
                    >
                        Envoyer
                    </button>
                </form>
            </div>
        </>
    );
}
