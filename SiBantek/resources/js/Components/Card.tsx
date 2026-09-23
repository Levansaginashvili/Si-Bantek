interface CardProps {
    children: React.ReactNode;
    className?: string;
}

export function Card({ children, className = '' }: CardProps) {
    return (
        <div
            className={`rounded-lg border border-slate-200 bg-white shadow-sm ${className}`}
        >
            {children}
        </div>
    );
}

export function CardHeader({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <div className="border-b border-slate-200 px-5 py-4">
            <h2 className="text-sm font-semibold text-slate-900">
                {title}
            </h2>

            {description && (
                <p className="mt-1 text-sm text-slate-500">
                    {description}
                </p>
            )}
        </div>
    );
}

export function CardContent({
    children,
    className = '',
}: {
    children: React.ReactNode;
    className?: string;
}) {
    return (
        <div className={`p-5 ${className}`}>
            {children}
        </div>
    );
}