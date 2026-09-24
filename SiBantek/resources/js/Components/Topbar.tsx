import { Menu, UserCircle } from 'lucide-react';

import type { Role } from '../types';

interface TopbarProps {
    role: Role;
    onMenuClick: () => void;
}

const roleLabels: Record<Role, string> = {
    admin: 'Administrator',
    verifikator: 'Verifikator',
    sekolah: 'Operator Sekolah',
};

export function Topbar({ role, onMenuClick }: TopbarProps) {
    return (
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
            <div className="flex items-center gap-3">
                <button
                    type="button"
                    onClick={onMenuClick}
                    className="rounded-md border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 lg:hidden"
                >
                    <Menu size={19} />
                </button>

                <div>
                    <p className="text-sm font-semibold text-slate-900">
                        Sistem Bantuan Peralatan TIK SMP
                    </p>

                    <p className="hidden text-xs text-slate-500 sm:block">
                        Kemendikdasmen RI
                    </p>
                </div>
            </div>

            <div className="flex items-center gap-3">
                <div className="hidden text-right sm:block">
                    <p className="text-sm font-medium text-slate-800">
                        Pengguna Sistem
                    </p>

                    <p className="text-xs text-slate-500">
                        {roleLabels[role]}
                    </p>
                </div>

                <UserCircle
                    size={32}
                    strokeWidth={1.5}
                    className="text-slate-500"
                />
            </div>
        </header>
    );
}