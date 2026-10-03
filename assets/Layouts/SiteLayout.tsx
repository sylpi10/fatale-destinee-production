import { usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import FlashMessages from "../Components/FlashMessages";
import SiteFooter from "../Components/Site/SiteFooter";
import SiteHeader from "../Components/Site/SiteHeader";
import VerifyEmailBanner from "../Components/VerifyEmailBanner";
import BigBolt from "../Components/Site/BigBolt";

export default function SiteLayout({ children }: { children: ReactNode }) {
    const { component } = usePage();
    const isHome = component === "Home";

    return (
        <div className={isHome ? "site site--home" : "site"}>
            <SiteHeader />
            <main>
                <VerifyEmailBanner />
                <FlashMessages />
                {children}
            </main>
            <BigBolt />
            {!isHome && <SiteFooter />}
        </div>
    );
}
