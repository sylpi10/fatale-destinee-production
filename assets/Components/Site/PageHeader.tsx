import GoldTitle from "./GoldTitle";

/** Stormy banner on top of the inner pages, echoing the home page hero. */
export default function PageHeader({
    title,
    subtitle,
}: {
    title: string;
    subtitle?: string;
}) {
    return (
        <div className="page-header">
            <GoldTitle>{title}</GoldTitle>
            {subtitle && (
                <div className="ornament">
                    <span className="ornament-line" />
                    <span className="ornament-text">{subtitle}</span>
                    <span className="ornament-line ornament-line--right" />
                </div>
            )}
        </div>
    );
}
