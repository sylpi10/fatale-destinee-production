import type { ReactNode } from "react";

type Props = {
    children: ReactNode;
    as?: "h1" | "h2";
    className?: string;
};

/** Gold, embossed title in Cinzel Decorative, as in the hero mockup. */
export default function GoldTitle({
    children,
    as: Tag = "h1",
    className,
}: Props) {
    return (
        <Tag className={className ? `gold-title ${className}` : "gold-title"}>
            {children}
        </Tag>
    );
}
