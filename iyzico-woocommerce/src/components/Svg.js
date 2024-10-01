import * as React from "react";

const Svg = ({ icon }) => {
    switch (icon) {
        case "DollarSign":
            return (
                <svg
                    id="Layer_1"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlnsXlink="http://www.w3.org/1999/xlink"
                    width="32px"
                    height="32px"
                    viewBox="0 0 64 64"
                    enableBackground="new 0 0 64 64"
                    xmlSpace="preserve"
                >
                    <circle
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        cx={44}
                        cy={38}
                        r={19}
                    />
                    <circle
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        cx={44}
                        cy={38}
                        r={13}
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="30,51 1,51 1,57 38,57 38,56 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="27,45 3,45 3,51 30,51 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="26,39 5,39 5,45 27,45 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="26,33 1,33 1,39 26,39 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="29,27 3,27 3,33 26,33 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="35,21 1,21 1,27 29,27 "
                    />
                    <polyline
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        points="40,20 40,15 3,15 3,21 35,21 "
                    />
                    <rect
                        x={1}
                        y={9}
                        fill="none"
                        stroke="#000000"
                        strokeWidth={2}
                        strokeMiterlimit={10}
                        width={37}
                        height={6}
                    />
                </svg>
            );
        case "TrendingUp":
            return (
                <svg
                    width="32px"
                    height="32px"
                    viewBox="0 0 16 16"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlnsXlink="http://www.w3.org/1999/xlink"
                >
                    <path
                        fill="#444"
                        d="M16 2h-4l1.29 1.29-4.29 4.3-3-3-6 6v2.82l6-6 3 3 5.71-5.7 1.28 1.29 0.010-4z"
                    />
                </svg>
            );
        case "ShoppingCart":
            return (
                <svg
                    fill="#000000"
                    xmlns="http://www.w3.org/2000/svg"
                    width="32px"
                    height="32px"
                    viewBox="0 0 52 52"
                    enableBackground="new 0 0 52 52"
                    xmlSpace="preserve"
                >
                    <g>
                        <path d="M20.1,26H44c0.7,0,1.4-0.5,1.5-1.2l4.4-15.4c0.3-1.1-0.5-2-1.5-2H11.5l-0.6-2.3c-0.3-1.1-1.3-1.8-2.3-1.8 H4.6c-1.3,0-2.5,1-2.6,2.3C1.9,7,3.1,8.2,4.4,8.2h2.3l7.6,25.7c0.3,1.1,1.2,1.8,2.3,1.8h28.2c1.3,0,2.5-1,2.6-2.3 c0.1-1.4-1.1-2.6-2.4-2.6H20.2c-1.1,0-2-0.7-2.3-1.7v-0.1C17.4,27.5,18.6,26,20.1,26z" />
                        <circle cx={20.6} cy={44.6} r={4} />
                        <circle cx={40.1} cy={44.6} r={4} />
                    </g>
                </svg>
            );
        default:
            return null; // Default case to return null or any other default icon
    }
};

export default Svg