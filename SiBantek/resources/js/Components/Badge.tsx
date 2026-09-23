type Variant = 'default' | 'success' | 'warning' | 'danger' | 'info';

interface BadgeProps {
    children: React.ReactNode;
    variant?: Variant;
}

const styles: Record<Variant, string> = {
    default: 'bg-slate-100 text-slate-700 border-slate-200',
    success: 'bg-green-50 text-green-700 border-green-200',
    warning: 'bg-amber-50 text-amber-800 border-amber-200',
    danger: 'bg-red-50 text-red-700 border-red-200',
    info: 'bg-blue-50 text-blue-700 border-blue-200',
};

export function Badge({
    children,
    variant = 'default',
}: BadgeProps) {
    return (
        <span
            className={`inline-flex items-center rounded-md border px-2.5 py-1 text-xs font-medium ${styles[variant]}`}
        >
            {children}
        </span>
    );
}