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
            <svg
                className="page-header-bolt"
                viewBox="0 0 200 160"
                preserveAspectRatio="xMidYMid meet"
                aria-hidden="true"
            >
                <defs>
                    <filter
                        id="pageGlow"
                        x="-50%"
                        y="-50%"
                        width="200%"
                        height="200%"
                    >
                        <feGaussianBlur stdDeviation="4" result="b" />
                        <feMerge>
                            <feMergeNode in="b" />
                            <feMergeNode in="b" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>
                <g fill="none" strokeLinecap="round" strokeLinejoin="round">
                    <path
                        d="M120 -5 L100 40 L112 48 L84 92 L96 100 L64 165"
                        stroke="#5aa8ff"
                        strokeWidth="10"
                        opacity="0.45"
                        filter="url(#pageGlow)"
                    />
                    <path
                        d="M120 -5 L100 40 L112 48 L84 92 L96 100 L64 165"
                        stroke="#ffffff"
                        strokeWidth="2.5"
                        filter="url(#pageGlow)"
                    />
                </g>
            </svg>
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
