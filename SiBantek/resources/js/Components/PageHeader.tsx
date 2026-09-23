interface PageHeaderProps {
    title: string;
    description?: string;
    eyebrow?: string;
}

export function PageHeader({
    title,
    description,
    eyebrow,
}: PageHeaderProps) {
    return (
        <div className="mb-6">
            {eyebrow && (
                <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-blue-700">
                    {eyebrow}
                </p>
            )}

            <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                {title}
            </h1>

            {description && (
                <p className="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                    {description}
                </p>
            )}
        </div>
    );
}