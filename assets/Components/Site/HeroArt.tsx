import { useEffect, useRef } from "react";
import zeus from "../../images/zeus.webp";

const layerProps = {
    viewBox: "0 0 1440 900",
    preserveAspectRatio: "xMidYMid slice",
    "aria-hidden": true,
} as const;

/**
 * Mouse parallax: writes --mx / --my (from -1 to 1, eased) on the wrapper; each layer
 * translates by its own depth in CSS. Skipped on touch screens and with reduced motion.
 */
function useParallax() {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const el = ref.current;
        if (
            !el ||
            !matchMedia("(hover: hover) and (pointer: fine)").matches ||
            matchMedia("(prefers-reduced-motion: reduce)").matches
        ) {
            return;
        }

        const target = { x: 0, y: 0 };
        const current = { x: 0, y: 0 };
        let frame = 0;

        function tick() {
            current.x += (target.x - current.x) * 0.08;
            current.y += (target.y - current.y) * 0.08;
            el!.style.setProperty("--mx", current.x.toFixed(4));
            el!.style.setProperty("--my", current.y.toFixed(4));
            const settled =
                Math.abs(target.x - current.x) < 0.001 &&
                Math.abs(target.y - current.y) < 0.001;
            frame = settled ? 0 : requestAnimationFrame(tick);
        }

        function move(x: number, y: number) {
            target.x = x;
            target.y = y;
            if (!frame) frame = requestAnimationFrame(tick);
        }

        const onMove = (e: PointerEvent) =>
            move(
                (e.clientX / innerWidth) * 2 - 1,
                (e.clientY / innerHeight) * 2 - 1,
            );
        const onLeave = () => move(0, 0);

        addEventListener("pointermove", onMove);
        document.documentElement.addEventListener("pointerleave", onLeave);
        return () => {
            removeEventListener("pointermove", onMove);
            document.documentElement.removeEventListener(
                "pointerleave",
                onLeave,
            );
            cancelAnimationFrame(frame);
        };
    }, []);

    return ref;
}

/**
 * Stormy backdrop of the home page, a faithful port of the "Hero Zeus du logo"
 * mockup (1440×900 artboard). The viewBox is cropped with "slice" so it fills any
 * viewport; Zeus sits at the centre of the artboard and stays visible.
 *
 * The scene is split into stacked SVG layers (sky, Zeus, foreground, haze) so the
 * parallax only moves composited layers: the full-screen turbulence filters of the
 * sky are never re-rendered while the mouse moves. Filter ids defined in the first
 * layer are shared by the others.
 */
export default function HeroArt() {
    const ref = useParallax();

    return (
        <div
            ref={ref}
            className="hero-art"
            role="img"
            aria-label="Zeus brandissant la foudre, couronné de laurier"
        >
            <svg className="hero-layer hero-layer--far" {...layerProps}>
                <defs>
                    <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stopColor="#2a2c31" />
                        <stop offset="0.55" stopColor="#121315" />
                        <stop offset="1" stopColor="#050506" />
                    </linearGradient>
                    <radialGradient id="lightSpot" cx="0.66" cy="0.22" r="0.6">
                        <stop offset="0" stopColor="#ffffff" />
                        <stop offset="0.5" stopColor="#777777" />
                        <stop offset="1" stopColor="#000000" />
                    </radialGradient>
                    <mask id="lightMask">
                        <rect
                            width="1440"
                            height="900"
                            fill="url(#lightSpot)"
                        />
                    </mask>
                    <radialGradient id="backlight" cx="0.5" cy="0.5" r="0.5">
                        <stop
                            offset="0"
                            stopColor="#d9a441"
                            stopOpacity="0.35"
                        />
                        <stop offset="1" stopColor="#d9a441" stopOpacity="0" />
                    </radialGradient>
                    <radialGradient id="flash" cx="0.5" cy="0.5" r="0.5">
                        <stop
                            offset="0"
                            stopColor="#ffffff"
                            stopOpacity="0.95"
                        />
                        <stop
                            offset="0.3"
                            stopColor="#5aa8ff"
                            stopOpacity="0.45"
                        />
                        <stop offset="1" stopColor="#5aa8ff" stopOpacity="0" />
                    </radialGradient>
                    <radialGradient id="eyeGlow" cx="0.5" cy="0.5" r="0.5">
                        <stop offset="0" stopColor="#ffffff" stopOpacity="1" />
                        <stop
                            offset="0.35"
                            stopColor="#7fc4ff"
                            stopOpacity="0.7"
                        />
                        <stop offset="1" stopColor="#7fc4ff" stopOpacity="0" />
                    </radialGradient>
                    <linearGradient
                        id="faceLight"
                        x1="1"
                        y1="0"
                        x2="0"
                        y2="0.3"
                    >
                        <stop offset="0" stopColor="#d9d5cc" />
                        <stop offset="0.45" stopColor="#8f8b84" />
                        <stop offset="1" stopColor="#2e2d2b" />
                    </linearGradient>
                    <linearGradient id="bottomFade" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stopColor="#050506" stopOpacity="0" />
                        <stop
                            offset="1"
                            stopColor="#050506"
                            stopOpacity="0.95"
                        />
                    </linearGradient>
                    <radialGradient id="vignette" cx="0.5" cy="0.45" r="0.75">
                        <stop
                            offset="0.55"
                            stopColor="#000000"
                            stopOpacity="0"
                        />
                        <stop
                            offset="1"
                            stopColor="#000000"
                            stopOpacity="0.85"
                        />
                    </radialGradient>
                    <filter
                        id="cloudsLight"
                        x="0"
                        y="0"
                        width="100%"
                        height="100%"
                    >
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.0032 0.0065"
                            numOctaves="6"
                            seed="4"
                        />
                        <feColorMatrix
                            type="matrix"
                            values="0 0 0 0 0.72  0 0 0 0 0.73  0 0 0 0 0.76  2.4 0 0 0 -1.0"
                        />
                    </filter>
                    <filter
                        id="cloudsDark"
                        x="0"
                        y="0"
                        width="100%"
                        height="100%"
                    >
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.0045 0.009"
                            numOctaves="5"
                            seed="12"
                        />
                        <feColorMatrix
                            type="matrix"
                            values="0 0 0 0 0.03  0 0 0 0 0.03  0 0 0 0 0.035  2.2 0 0 0 -0.75"
                        />
                    </filter>
                    <filter id="smoke" x="0" y="0" width="100%" height="100%">
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.006 0.012"
                            numOctaves="5"
                            seed="21"
                        />
                        <feColorMatrix
                            type="matrix"
                            values="0 0 0 0 0.35  0 0 0 0 0.35  0 0 0 0 0.36  2 0 0 0 -0.8"
                        />
                    </filter>
                    <filter id="stains" x="0" y="0" width="100%" height="100%">
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.04"
                            numOctaves="4"
                            seed="9"
                        />
                        <feColorMatrix
                            type="matrix"
                            values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -1.4 0.75"
                        />
                    </filter>
                    <filter id="grain" x="0" y="0" width="100%" height="100%">
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.9"
                            numOctaves="2"
                            seed="2"
                            stitchTiles="stitch"
                        />
                        <feColorMatrix type="saturate" values="0" />
                    </filter>
                    <filter
                        id="glow"
                        x="-50%"
                        y="-50%"
                        width="200%"
                        height="200%"
                    >
                        <feGaussianBlur stdDeviation="5" result="b" />
                        <feMerge>
                            <feMergeNode in="b" />
                            <feMergeNode in="b" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                    <filter
                        id="bigGlow"
                        x="-50%"
                        y="-50%"
                        width="200%"
                        height="200%"
                    >
                        <feGaussianBlur stdDeviation="16" />
                    </filter>
                    <filter
                        id="soft"
                        x="-50%"
                        y="-50%"
                        width="200%"
                        height="200%"
                    >
                        <feGaussianBlur stdDeviation="9" />
                    </filter>
                    <filter
                        id="wobble"
                        x="-10%"
                        y="-10%"
                        width="120%"
                        height="120%"
                    >
                        <feTurbulence
                            type="fractalNoise"
                            baseFrequency="0.03"
                            numOctaves="2"
                            seed="5"
                            result="n"
                        />
                        <feDisplacementMap
                            in="SourceGraphic"
                            in2="n"
                            scale="9"
                            xChannelSelector="R"
                            yChannelSelector="G"
                        />
                    </filter>
                    {/* Fades the top edge of the smoke band, which is a hard line in the mockup */}
                    <linearGradient id="smokeFade" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stopColor="#ffffff" stopOpacity="0" />
                        <stop
                            offset="0.35"
                            stopColor="#ffffff"
                            stopOpacity="1"
                        />
                    </linearGradient>
                    <mask id="smokeMask">
                        <rect
                            x="0"
                            y="540"
                            width="1440"
                            height="360"
                            fill="url(#smokeFade)"
                        />
                    </mask>
                    <clipPath id="faceClip">
                        <path d="M700 128 C 765 118 818 160 832 214 L 840 250 C 843 262 834 268 832 273 C 842 292 860 316 880 342 C 888 354 877 364 862 361 C 852 363 848 370 851 380 L 848 405 L 760 455 C 700 450 660 420 650 380 C 640 300 650 180 700 128 Z" />
                    </clipPath>
                </defs>

                {/* ciel d'orage */}
                <rect width="1440" height="900" fill="url(#sky)" />
                <rect width="1440" height="900" filter="url(#cloudsDark)" />
                <g mask="url(#lightMask)">
                    <rect
                        width="1440"
                        height="900"
                        filter="url(#cloudsLight)"
                    />
                </g>
                <ellipse
                    cx="720"
                    cy="250"
                    rx="560"
                    ry="360"
                    fill="url(#backlight)"
                />

                {/* éclairs lointains */}
                <g fill="none" strokeLinecap="round" strokeLinejoin="round">
                    <g
                        stroke="#5aa8ff"
                        strokeWidth="10"
                        opacity="0.35"
                        filter="url(#soft)"
                    >
                        <path d="M260 -10 L257 -5 L254 0 L252 3 L250 8 L251 15 L248 20 L245 25 L239 31 L238 39 L239 47 L237 54 L233 61 L238 67 L240 74 L241 80 L245 85 L242 91 L237 97 L236 103 L235 110 L235 118 L235 125 L240 132 L245 140 L238 145 L236 151 L230 156 L220 161 L218 166 L217 171 L214 177 L208 184 L204 193 L204 201 L201 210 L202 220 L199 227 L194 235 L191 242 L185 250 L181 258 L175 266 L174 274 L175 283 L174 291 L175 300 L177 307 L178 315 L179 321 L183 329 L185 337 L182 344 L176 350 L172 358 L169 365 L167 373 L163 379 L155 385 L144 391 L137 397 L132 402 L132 407 L126 414 L120 420" />
                        <path d="M1380 -10 L1381 1 L1388 12 L1389 22 L1394 34 L1403 43 L1418 52 L1425 63 L1430 74 L1431 84 L1430 94 L1433 102 L1443 112 L1440 121 L1433 130 L1429 138 L1432 147 L1438 159 L1436 171 L1436 182 L1443 195 L1437 209 L1429 221 L1429 233 L1425 246 L1424 256 L1426 269 L1426 281 L1421 291 L1426 300 L1425 311 L1423 321 L1420 330" />
                    </g>
                    <g stroke="#eef6ff" filter="url(#glow)" opacity="0.8">
                        <path
                            strokeWidth="2"
                            d="M260 -10 L257 -5 L254 0 L252 3 L250 8 L251 15 L248 20 L245 25 L239 31 L238 39 L239 47 L237 54 L233 61 L238 67 L240 74 L241 80 L245 85 L242 91 L237 97 L236 103 L235 110 L235 118 L235 125 L240 132 L245 140 L238 145 L236 151 L230 156 L220 161 L218 166 L217 171 L214 177 L208 184 L204 193 L204 201 L201 210 L202 220 L199 227 L194 235 L191 242 L185 250 L181 258 L175 266 L174 274 L175 283 L174 291 L175 300 L177 307 L178 315 L179 321 L183 329 L185 337 L182 344 L176 350 L172 358 L169 365 L167 373 L163 379 L155 385 L144 391 L137 397 L132 402 L132 407 L126 414 L120 420"
                        />
                        <path
                            strokeWidth="1"
                            d="M237 54 L235 57 L231 62 L224 68 L214 74 L214 77 L210 80 L215 85 L215 90 L208 97 L201 104 L193 113 L181 119 L176 126 L168 131 L165 136 L158 140"
                        />
                        <path
                            strokeWidth="1"
                            d="M191 242 L197 249 L204 257 L204 263 L209 269 L210 278 L217 288 L222 295 L232 302 L232 310 L230 317 L233 323 L242 331 L236 340 L234 347 L236 356 L236 366"
                        />
                        <path
                            strokeWidth="1"
                            d="M235 118 L236 125 L239 130 L237 137 L232 142 L221 148 L211 155 L204 161 L202 166 L194 173 L192 179 L186 185 L173 190 L160 194 L151 199 L136 204 L116 209"
                        />
                        <path
                            strokeWidth="2"
                            d="M1380 -10 L1381 1 L1388 12 L1389 22 L1394 34 L1403 43 L1418 52 L1425 63 L1430 74 L1431 84 L1430 94 L1433 102 L1443 112 L1440 121 L1433 130 L1429 138 L1432 147 L1438 159 L1436 171 L1436 182 L1443 195 L1437 209 L1429 221 L1429 233 L1425 246 L1424 256 L1426 269 L1426 281 L1421 291 L1426 300 L1425 311 L1423 321 L1420 330"
                        />
                        <path
                            strokeWidth="1"
                            d="M1433 130 L1419 136 L1408 144 L1398 153 L1389 162 L1382 171 L1382 181 L1377 191 L1378 201"
                        />
                    </g>
                </g>

                {/* la foudre qui frappe */}
                <ellipse
                    cx="1220"
                    cy="770"
                    rx="260"
                    ry="170"
                    fill="url(#flash)"
                />
                <g fill="none" strokeLinecap="round" strokeLinejoin="round">
                    <g
                        stroke="#5aa8ff"
                        strokeWidth="22"
                        opacity="0.5"
                        filter="url(#bigGlow)"
                    >
                        <path d="M1180 -10 L1179 2 L1184 15 L1181 28 L1185 39 L1196 51 L1200 61 L1205 75 L1209 88 L1208 97 L1211 107 L1208 119 L1213 131 L1219 145 L1224 158 L1231 171 L1237 183 L1239 196 L1244 210 L1240 221 L1240 234 L1238 245 L1230 255 L1225 267 L1213 277 L1213 289 L1206 301 L1194 313 L1188 325 L1188 338 L1195 351 L1197 365 L1193 379 L1189 389 L1189 398 L1183 408 L1183 417 L1171 428 L1163 439 L1163 451 L1155 461 L1156 475 L1151 487 L1150 502 L1155 514 L1157 525 L1156 536 L1159 547 L1165 560 L1167 573 L1162 586 L1162 599 L1161 612 L1165 624 L1162 635 L1166 647 L1173 661 L1182 676 L1186 690 L1197 701 L1208 714 L1208 727 L1212 739 L1219 750 L1230 760" />
                    </g>
                    <g stroke="#ffffff" filter="url(#glow)">
                        <path
                            strokeWidth="5"
                            d="M1180 -10 L1179 2 L1184 15 L1181 28 L1185 39 L1196 51 L1200 61 L1205 75 L1209 88 L1208 97 L1211 107 L1208 119 L1213 131 L1219 145 L1224 158 L1231 171 L1237 183 L1239 196 L1244 210 L1240 221 L1240 234 L1238 245 L1230 255 L1225 267 L1213 277 L1213 289 L1206 301 L1194 313 L1188 325 L1188 338 L1195 351 L1197 365 L1193 379 L1189 389 L1189 398 L1183 408 L1183 417 L1171 428 L1163 439 L1163 451 L1155 461 L1156 475 L1151 487 L1150 502 L1155 514 L1157 525 L1156 536 L1159 547 L1165 560 L1167 573 L1162 586 L1162 599 L1161 612 L1165 624 L1162 635 L1166 647 L1173 661 L1182 676 L1186 690 L1197 701 L1208 714 L1208 727 L1212 739 L1219 750 L1230 760"
                        />
                        <path
                            strokeWidth="2"
                            d="M1165 560 L1162 568 L1163 574 L1152 579 L1147 587 L1141 593 L1144 600 L1146 607 L1150 613 L1145 624 L1138 633 L1125 640 L1110 648 L1107 657 L1095 664 L1102 674 L1099 687"
                        />
                        <path
                            strokeWidth="2"
                            d="M1224 158 L1214 167 L1199 177 L1201 183 L1193 190 L1178 200 L1171 207 L1173 214 L1165 225 L1162 232 L1169 243 L1163 251 L1165 262 L1161 275 L1160 286 L1165 299 L1165 311"
                        />
                        <path
                            strokeWidth="2"
                            d="M1163 451 L1162 461 L1158 471 L1167 479 L1169 488 L1176 496 L1183 502 L1196 509 L1212 514 L1223 524 L1230 532 L1244 541 L1252 553 L1264 562 L1267 573 L1276 585 L1292 597"
                        />
                        <path
                            strokeWidth="1.5"
                            d="M1213 131 L1214 136 L1216 141 L1223 147 L1222 150 L1224 157 L1235 161 L1238 169 L1250 177 L1262 180 L1280 184 L1284 192 L1298 198 L1302 204 L1314 209 L1327 217 L1341 224"
                        />
                        <path
                            strokeWidth="1.5"
                            d="M1189 389 L1190 393 L1196 397 L1197 404 L1195 410 L1206 417 L1219 422 L1234 426 L1240 432 L1248 441 L1255 447 L1268 456 L1284 464 L1295 471 L1297 479 L1297 484 L1295 490"
                        />
                    </g>
                </g>
            </svg>
            <svg className="hero-layer hero-layer--zeus" {...layerProps}>
                <g fill="none" strokeLinecap="round" strokeLinejoin="round">
                    <path
                        d="M618 46 L590 22 L606 12 L566 -12"
                        stroke="#5aa8ff"
                        strokeWidth="18"
                        opacity="0.55"
                        filter="url(#bigGlow)"
                    />
                    <path
                        d="M618 46 L590 22 L606 12 L566 -12"
                        stroke="#ffffff"
                        strokeWidth="5"
                        filter="url(#glow)"
                    />
                    <path
                        d="M606 12 L640 4 L652 -12"
                        stroke="#eef6ff"
                        strokeWidth="2"
                        filter="url(#glow)"
                    />
                    <path
                        d="M590 22 L560 26 L540 12"
                        stroke="#eef6ff"
                        strokeWidth="1.5"
                        filter="url(#glow)"
                    />
                </g>

                {/* Zeus */}
                <image
                    href={zeus}
                    x="478"
                    y="28"
                    width="483"
                    height="588"
                    preserveAspectRatio="xMidYMid meet"
                />
            </svg>
            <svg className="hero-layer hero-layer--near" {...layerProps}>
                {/* ruines */}
                <g transform="rotate(-7 125 900)">
                    <path
                        d="M70 940 L70 520 L95 498 L110 512 L130 485 L150 505 L180 492 L180 940 Z"
                        fill="#111112"
                    />
                    <g stroke="#232325" strokeWidth="3">
                        <path d="M92 520 V940" />
                        <path d="M114 515 V940" />
                        <path d="M136 505 V940" />
                        <path d="M158 505 V940" />
                    </g>
                    <path
                        d="M180 492 L180 940"
                        stroke="#b8862f"
                        strokeWidth="2.5"
                        opacity="0.7"
                    />
                </g>
                <path
                    d="M1290 940 L1290 640 L1310 618 L1326 640 L1348 606 L1380 630 L1380 940 Z"
                    fill="#0f0f10"
                />
                <g stroke="#222224" strokeWidth="3">
                    <path d="M1312 640 V940" />
                    <path d="M1335 630 V940" />
                    <path d="M1358 625 V940" />
                </g>
                <path
                    d="M1290 640 L1310 618 L1326 640 L1348 606"
                    fill="none"
                    stroke="#7fc4ff"
                    strokeWidth="2"
                    opacity="0.8"
                    filter="url(#glow)"
                />
                <g transform="rotate(18 1080 830)">
                    <rect
                        x="1010"
                        y="790"
                        width="150"
                        height="80"
                        rx="6"
                        fill="#121213"
                    />
                    <path
                        d="M1010 795 H1160"
                        stroke="#c9d2e6"
                        strokeWidth="2"
                        opacity="0.6"
                    />
                </g>
                <path
                    d="M0 900 L0 860 L60 842 L110 858 L170 836 L260 852 L330 840 L420 866 L520 850 L640 872 L760 858 L880 870 L1000 848 L1090 866 L1180 846 L1260 862 L1350 844 L1440 858 L1440 900 Z"
                    fill="#09090a"
                />

                {/* débris projetés */}
                <g fill="#151516" stroke="#9aa3b5" strokeWidth="1.2">
                    <polygon points="1120,700 1136,694 1140,708 1126,714" />
                    <polygon points="1060,660 1078,652 1082,670 1066,676" />
                    <polygon points="1300,690 1318,700 1310,716 1294,708" />
                    <polygon points="1340,640 1352,634 1358,648 1346,652" />
                    <polygon points="1170,620 1180,614 1186,626 1176,630" />
                    <polygon points="1260,600 1272,596 1276,608 1264,612" />
                    <polygon points="1020,720 1034,716 1036,728 1024,732" />
                    <polygon points="1390,700 1404,694 1408,708 1396,712" />
                    <polygon points="1100,580 1108,576 1112,586 1104,588" />
                    <polygon points="1230,560 1238,556 1242,566 1234,568" />
                </g>

                {/* cendres */}
                <g fill="#f3c76a">
                    <circle cx="980" cy="420" r="1.8" opacity="0.7" />
                    <circle cx="1040" cy="380" r="1.4" opacity="0.6" />
                    <circle cx="1100" cy="460" r="2" opacity="0.5" />
                    <circle cx="920" cy="520" r="1.5" opacity="0.6" />
                    <circle cx="1010" cy="560" r="2.2" opacity="0.5" />
                    <circle cx="1330" cy="420" r="1.6" opacity="0.6" />
                    <circle cx="1280" cy="520" r="2" opacity="0.7" />
                    <circle cx="460" cy="560" r="1.6" opacity="0.5" />
                    <circle cx="380" cy="620" r="2" opacity="0.4" />
                    <circle cx="200" cy="520" r="1.4" opacity="0.6" />
                    <circle cx="140" cy="640" r="2" opacity="0.5" />
                    <circle cx="1180" cy="340" r="1.3" opacity="0.5" />
                    <circle cx="1390" cy="560" r="1.8" opacity="0.6" />
                    <circle cx="300" cy="720" r="1.5" opacity="0.5" />
                    <circle cx="1060" cy="620" r="1.6" opacity="0.6" />
                </g>
            </svg>
            <svg className="hero-layer hero-layer--haze" {...layerProps}>
                {/* fumée, fondu, grain, vignette */}
                <g opacity="0.75" mask="url(#smokeMask)">
                    <rect
                        x="0"
                        y="540"
                        width="1440"
                        height="360"
                        filter="url(#smoke)"
                    />
                </g>
                <rect
                    x="0"
                    y="480"
                    width="1440"
                    height="420"
                    fill="url(#bottomFade)"
                />
                <rect
                    width="1440"
                    height="900"
                    filter="url(#grain)"
                    opacity="0.13"
                    style={{ mixBlendMode: "overlay" }}
                />
                <rect width="1440" height="900" fill="url(#vignette)" />
            </svg>
        </div>
    );
}
