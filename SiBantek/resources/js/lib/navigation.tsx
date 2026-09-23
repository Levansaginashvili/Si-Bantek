import {
    Archive,
    BarChart3,
    BookOpenCheck,
    Building2,
    ClipboardCheck,
    ClipboardList,
    Database,
    FileCheck2,
    FileClock,
    FileSearch,
    FileText,
    FolderCheck,
    History,
    Home,
    Laptop,
    Landmark,
    ListChecks,
    PackageCheck,
    ReceiptText,
    Settings,
    ShieldCheck,
    UserCheck,
    UserCog,
    Users,
    WalletCards,
} from 'lucide-react';

import type { NavigationGroup, Role } from '../types';

export const navigationByRole: Record<Role, NavigationGroup[]> = {
    admin: [
        {
            label: '',
            items: [
                {
                    label: 'Dashboard',
                    href: '/admin',
                    icon: Home,
                },
            ],
        },
        {
            label: 'SEKOLAH',
            items: [
                {
                    label: 'Daftar Sekolah',
                    href: '/admin/sekolah',
                    icon: Building2,
                },
                {
                    label: 'Detail Sekolah',
                    href: '/admin/sekolah',
                    icon: FileSearch,
                },
            ],
        },
        {
            label: 'PENGGUNA',
            items: [
                {
                    label: 'Akun Sekolah',
                    href: '/admin/akun-sekolah',
                    icon: Users,
                },
                {
                    label: 'Akun Verifikator',
                    href: '/admin/akun-verifikator',
                    icon: UserCheck,
                },
            ],
        },
        {
            label: 'MONITORING',
            items: [
                {
                    label: 'Pengajuan',
                    href: '/admin/pengajuan',
                    icon: ClipboardList,
                },
                {
                    label: 'Dokumen',
                    href: '/admin/dokumen',
                    icon: FileText,
                },
                {
                    label: 'Progres Tahapan',
                    href: '/admin/progres',
                    icon: BarChart3,
                },
                {
                    label: 'Rekap Program',
                    href: '/admin/rekap',
                    icon: Database,
                },
            ],
        },
        {
            label: 'SISTEM',
            items: [
                {
                    label: 'Audit Trail',
                    href: '/admin/audit-trail',
                    icon: History,
                },
            ],
        },
    ],

    verifikator: [
        {
            label: '',
            items: [
                {
                    label: 'Dashboard',
                    href: '/verifikator',
                    icon: Home,
                },
            ],
        },
        {
            label: 'VERIFIKASI',
            items: [
                {
                    label: 'Administrasi',
                    href: '/verifikator/administrasi',
                    icon: FileCheck2,
                },
                {
                    label: 'RAB',
                    href: '/verifikator/rab',
                    icon: ReceiptText,
                },
                {
                    label: 'Pengadaan',
                    href: '/verifikator/pengadaan',
                    icon: ClipboardCheck,
                },
                {
                    label: 'Penerimaan',
                    href: '/verifikator/penerimaan',
                    icon: PackageCheck,
                },
                {
                    label: 'Inventarisasi',
                    href: '/verifikator/inventarisasi',
                    icon: Archive,
                },
                {
                    label: 'Pemanfaatan',
                    href: '/verifikator/pemanfaatan',
                    icon: Laptop,
                },
                {
                    label: 'Pelaporan / LPJ',
                    href: '/verifikator/lpj',
                    icon: FolderCheck,
                },
            ],
        },
        {
            label: 'PENYALURAN',
            items: [
                {
                    label: 'Status Dana',
                    href: '/verifikator/status-dana',
                    icon: WalletCards,
                },
            ],
        },
        {
            label: 'MONITORING',
            items: [
                {
                    label: 'Semua Sekolah',
                    href: '/verifikator/sekolah',
                    icon: Building2,
                },
                {
                    label: 'Progres Tahapan',
                    href: '/verifikator/progres',
                    icon: ListChecks,
                },
            ],
        },
        {
            label: 'AKUN',
            items: [
                {
                    label: 'Profil',
                    href: '/verifikator/profil',
                    icon: UserCog,
                },
            ],
        },
    ],

    sekolah: [
        {
            label: '',
            items: [
                {
                    label: 'Dashboard',
                    href: '/sekolah',
                    icon: Home,
                },
            ],
        },
        {
            label: 'PROFIL SEKOLAH',
            items: [
                {
                    label: 'Profil',
                    href: '/sekolah/profil',
                    icon: Building2,
                },
            ],
        },
        {
            label: 'BANTUAN',
            items: [
                {
                    label: 'Administrasi Awal',
                    href: '/sekolah/administrasi',
                    icon: FileText,
                },
                {
                    label: 'RAB',
                    href: '/sekolah/rab',
                    icon: ReceiptText,
                },
                {
                    label: 'Pengadaan SIPLah',
                    href: '/sekolah/pengadaan',
                    icon: ShoppingBagIcon,
                },
                {
                    label: 'Penerimaan Barang',
                    href: '/sekolah/penerimaan',
                    icon: PackageCheck,
                },
                {
                    label: 'Inventarisasi',
                    href: '/sekolah/inventarisasi',
                    icon: Archive,
                },
                {
                    label: 'Pemanfaatan',
                    href: '/sekolah/pemanfaatan',
                    icon: Laptop,
                },
            ],
        },
        {
            label: 'KEUANGAN',
            items: [
                {
                    label: 'Dana Masuk',
                    href: '/sekolah/dana-masuk',
                    icon: Landmark,
                },
                {
                    label: 'Pengeluaran',
                    href: '/sekolah/pengeluaran',
                    icon: WalletCards,
                },
                {
                    label: 'Saldo & Sisa Dana',
                    href: '/sekolah/saldo',
                    icon: BarChart3,
                },
                {
                    label: 'Pengembalian Sisa Dana',
                    href: '/sekolah/pengembalian',
                    icon: ReceiptText,
                },
            ],
        },
        {
            label: 'PELAPORAN',
            items: [
                {
                    label: 'Laporan Akhir',
                    href: '/sekolah/laporan-akhir',
                    icon: FileText,
                },
                {
                    label: 'LPJ',
                    href: '/sekolah/lpj',
                    icon: BookOpenCheck,
                },
            ],
        },
        {
            label: 'STATUS',
            items: [
                {
                    label: 'Progres Bantuan',
                    href: '/sekolah/progres',
                    icon: ClipboardCheck,
                },
            ],
        },
    ],
};

function ShoppingBagIcon(props: { size?: number; strokeWidth?: number }) {
    return <ShoppingBag {...props} />;
}

function ShoppingBag(props: { size?: number; strokeWidth?: number }) {
    return <PackageCheck {...props} />;
}