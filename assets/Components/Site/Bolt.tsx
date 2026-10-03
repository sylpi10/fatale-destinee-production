export default function Bolt({ size = 22 }: { size?: number }) {
    return (
        <svg
            className="bolt"
            viewBox="0 0 24 24"
            width={size}
            height={size}
            aria-hidden="true"
        >
            <path
                d="M13 2 L4 14 H11 L10 22 L20 9 H13 Z"
                fill="#f1cf73"
                stroke="#7a4e12"
                strokeWidth="1"
            />
        </svg>
    );
}
