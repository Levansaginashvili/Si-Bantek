import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, LogOut, X } from 'lucide-react';
import { navigationByRole } from '../lib/navigation';
import type { Role } from '../types';

interface SidebarProps {
    role: Role;
    mobileOpen: boolean;
    onClose: () => void;
}

const roleLabels: Record<Role, string> = {
    admin: 'Administrator',
    verifikator: 'Verifikator',
    sekolah: 'Sekolah',
};

export function Sidebar({
    role,
    mobileOpen,
    onClose,
}: SidebarProps) {
    const page = usePage();

    const currentUrl = page.url;

    return (
        <>
            {mobileOpen && (
                <button
                    type="button"
                    aria-label="Tutup menu"
                    onClick={onClose}
                    className="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"
                />
            )}

            <aside
                className={[
                    'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-800 bg-slate-950 text-white transition-transform duration-200',
                    mobileOpen
                        ? 'translate-x-0'
                        : '-translate-x-full lg:translate-x-0',
                ].join(' ')}
            >
                <div className="flex h-16 items-center justify-between border-b border-slate-800 px-5">
                    <div>
                        <div className="text-base font-bold tracking-tight">
                            SI BANTEK
                        </div>

                        <div className="mt-0.5 text-[10px] uppercase tracking-wider text-slate-400">
                            Bantuan Peralatan TIK
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-md p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden"
                    >
                        <X size={18} />
                    </button>
                </div>

                <div className="border-b border-slate-800 px-5 py-4">
                    <p className="text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                        Akses
                    </p>

                    <p className="mt-1 text-sm font-medium text-slate-200">
                        {roleLabels[role]}
                    </p>
                </div>

                <nav className="flex-1 overflow-y-auto px-3 py-4">
                    {navigationByRole[role].map((group, index) => (
                        <div
                            key={`${group.label}-${index}`}
                            className="mb-5"
                        >
                            {group.label && (
                                <p className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
                                    {group.label}
                                </p>
                            )}

                            <div className="space-y-0.5">
                                {group.items.map((item) => {
                                    const Icon = item.icon;

                                    const active =
                                        item.href === currentUrl ||
                                        (item.href !== `/${role}` &&
                                            currentUrl.startsWith(
                                                item.href,
                                            ));

                                    return (
                                        <Link
                                            key={item.href + item.label}
                                            href={item.href}
                                            onClick={onClose}
                                            className={[
                                                'group flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition-colors',
                                                active
                                                    ? 'bg-blue-700 text-white'
                                                    : 'text-slate-300 hover:bg-slate-800 hover:text-white',
                                            ].join(' ')}
                                        >
                                            <Icon
                                                size={17}
                                                strokeWidth={1.8}
                                            />

                                            <span className="flex-1">
                                                {item.label}
                                            </span>

                                            {active && (
                                                <ChevronRight
                                                    size={14}
                                                    className="opacity-80"
                                                />
                                            )}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </nav>

                <div className="border-t border-slate-800 p-3">
                    <button
                        type="button"
                        className="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm text-slate-400 hover:bg-slate-800 hover:text-white"
                    >
                        <LogOut size={17} strokeWidth={1.8} />
                        Keluar
                    </button>
                </div>
            </aside>
        </>
    );
}