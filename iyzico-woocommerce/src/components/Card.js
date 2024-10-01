const Card = ({ children, className }) => (
    <div className={`bg-white shadow rounded-lg p-4 ${className}`}>
        {children}
    </div>
);

const CardHeader = ({ children, className }) => (
    <div className={`font-bold text-lg mb-2 ${className}`}>{children}</div>
);

const CardTitle = ({ children, className }) => (
    <h3 className={`text-2xl font-semibold leading-none tracking-tight text-center ${className}`}>{children}</h3>
);

const CardContent = ({ children, className }) => (
    <div className={className}>{children}</div>
);

export { Card, CardHeader, CardContent, CardTitle };