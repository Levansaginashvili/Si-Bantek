import React, { useState, useEffect } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    Users,
    ShieldCheck,
    Building2,
    LogOut,
    Menu,
    X,
    User,
    ChevronRight,
    Laptop,
} from 'lucide-react';

interface AppLayoutProps {
    children: React.ReactNode;
    title?: string;
}

interface NavItem {
    label: string;
    href: string;
    icon: React.ReactNode;
}

export default function AppLayout({ children, title }: AppLayoutProps) {
    const { auth, flash } = usePage().props as any;
    const user = auth?.user;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentPath = window.location.pathname;

    useEffect(() => {
        setSidebarOpen(false);
    }, [currentPath]);

    const navByRole: Record<string, NavItem[]> = {
        admin: [
            { label: 'Dashboard', href: '/admin', icon: <LayoutDashboard size={18} /> },
            { label: 'Kelola Akun', href: '/admin/users', icon: <Users size={18} /> },
        ],
        verifikator: [
            { label: 'Dashboard', href: '/verifikator', icon: <LayoutDashboard size={18} /> },
        ],
        sekolah: [
            { label: 'Dashboard', href: '/sekolah', icon: <LayoutDashboard size={18} /> },
            { label: 'Profil Sekolah', href: '/sekolah?tab=profil', icon: <Building2 size={18} /> },
            { label: 'RAB Laptop', href: '/sekolah?tab=rab', icon: <Laptop size={18} /> },
            { label: 'Dokumen Saya', href: '/sekolah?tab=dokumen', icon: <ShieldCheck size={18} /> },
        ],
    };

    const navItems: NavItem[] = user ? (navByRole[user.role] ?? []) : [];

    const handleLogout = (e: React.FormEvent) => {
        e.preventDefault();
        router.post('/logout');
    };

    const Sidebar = ({ mobile = false }: { mobile?: boolean }) => (
        <div className={`flex h-full flex-col bg-[#1e2d5a] text-white ${mobile ? '' : ''}`}>
            <div className="flex h-16 items-center gap-3 border-b border-white/10 px-5">
                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10">
                    <Laptop size={16} className="text-white" />
                </div>
                <div>
                    <p className="text-sm font-bold tracking-wide">SI BANTEK 2026</p>
                    <p className="text-[10px] text-white/50 leading-none">Kemendikdasmen RI</p>
                </div>
                {mobile && (
                    <button
                        onClick={() => setSidebarOpen(false)}
                        className="ml-auto text-white/60 hover:text-white"
                    >
                        <X size={18} />
                    </button>
                )}
            </div>

            <div className="flex-1 overflow-y-auto px-3 py-5 space-y-1">
                {navItems.map((item) => {
                    const isActive = currentPath === item.href || currentPath.startsWith(item.href + '/');
                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${
                                isActive
                                    ? 'bg-white/15 text-white'
                                    : 'text-white/60 hover:bg-white/10 hover:text-white'
                            }`}
                        >
                            {item.icon}
                            <span>{item.label}</span>
                            {isActive && <ChevronRight size={14} className="ml-auto text-white/40" />}
                        </Link>
                    );
                })}
            </div>

            <div className="border-t border-white/10 p-4 space-y-3">
                <div className="flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white flex-shrink-0">
                        <User size={14} />
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-xs font-semibold text-white">{user?.name}</p>
                        <p className="text-[10px] uppercase tracking-wider text-white/50">{user?.role}</p>
                    </div>
                </div>

                <button
                    onClick={handleLogout}
                    className="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-white/60 hover:bg-white/10 hover:text-white transition-colors"
                >
                    <LogOut size={16} />
                    <span>Keluar</span>
                </button>
            </div>
        </div>
    );

    return (
        <div className="flex h-screen overflow-hidden bg-slate-50">
            {/* Sidebar desktop */}
            <aside className="hidden w-60 flex-shrink-0 lg:flex lg:flex-col">
                <Sidebar />
            </aside>

            {/* Sidebar mobile overlay */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-40 lg:hidden">
                    <div
                        className="absolute inset-0 bg-black/50"
                        onClick={() => setSidebarOpen(false)}
                    />
                    <aside className="relative z-50 h-full w-60">
                        <Sidebar mobile />
                    </aside>
                </div>
            )}

            {/* Main area */}
            <div className="flex flex-1 flex-col overflow-hidden">
                {/* Topbar */}
                <header className="flex h-16 flex-shrink-0 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="text-slate-500 hover:text-slate-700 lg:hidden"
                    >
                        <Menu size={20} />
                    </button>

                    <div className="min-w-0 flex-1">
                        {title && (
                            <h1 className="truncate text-base font-semibold text-slate-800">
                                {title}
                            </h1>
                        )}
                    </div>

                    {user?.sekolah && (
                        <div className="hidden sm:block text-right">
                            <p className="text-xs font-semibold text-slate-700 truncate max-w-[200px]">
                                {user.sekolah.nama_sekolah}
                            </p>
                            <p className="text-[11px] text-slate-400">NPSN: {user.sekolah.npsn}</p>
                        </div>
                    )}
                </header>

                {/* Content */}
                <main className="flex-1 overflow-y-auto">
                    <div className="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                        {flash?.success && (
                            <div className="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                {flash.success}
                            </div>
                        )}
                        {flash?.error && (
                            <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {flash.error}
                            </div>
                        )}
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}