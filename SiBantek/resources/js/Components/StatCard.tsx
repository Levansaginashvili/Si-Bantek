import type { LucideIcon } from 'lucide-react';

interface StatCardProps {
    title: string;
    value: string;
    description: string;
    icon: LucideIcon;
}

export function StatCard({
    title,
    value,
    description,
    icon: Icon,
}: StatCardProps) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">
                        {title}
                    </p>

                    <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">
                        {value}
                    </p>

                    <p className="mt-1 text-xs text-slate-500">
                        {description}
                    </p>
                </div>

                <div className="rounded-md bg-blue-50 p-2.5 text-blue-700">
                    <Icon size={20} strokeWidth={1.8} />
                </div>
            </div>
        </div>
    );
}