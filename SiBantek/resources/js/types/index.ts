import type React from 'react';

export type Role = 'admin' | 'verifikator' | 'sekolah';

export interface User {
    id: number;
    name: string;
    email?: string;
    role: Role;
}

export interface PageProps {
    auth?: {
        user?: User;
    };
}

export interface NavigationItem {
    label: string;
    href: string;
    icon: React.ComponentType<{
        size?: number;
        strokeWidth?: number;
    }>;
}

export interface NavigationGroup {
    label: string;
    items: NavigationItem[];
}

export interface ModulePageProps {
    role: Role;
    title: string;
    description: string;
    section?: string;
}