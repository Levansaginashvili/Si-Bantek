import { Link } from '@inertiajs/react';
import { ArrowLeft, FileText } from 'lucide-react';

import AppLayout from '../../Layouts/AppLayout';
import { Badge } from '../../Components/Badge';
import { Card, CardContent } from '../../Components/Card';
import { PageHeader } from '../../Components/PageHeader';

import type { ModulePageProps } from '../../types';

export default function Index({
    role,
    title,
    description,
    section,
}: ModulePageProps) {
    return (
        <AppLayout role={role}>
            <PageHeader
                eyebrow={section ?? 'Modul'}
                title={title}
                description={description}
            />

            <Card>
                <CardContent>
                    <div className="flex flex-col items-center justify-center py-16 text-center">
                        <div className="rounded-full bg-blue-50 p-4 text-blue-700">
                            <FileText size={28} strokeWidth={1.7} />
                        </div>

                        <h2 className="mt-5 text-base font-semibold text-slate-900">
                            Modul Si Bantek
                        </h2>

                        <p className="mt-2 max-w-lg text-sm leading-6 text-slate-500">
                            Struktur halaman sudah tersedia. Form,
                            tabel, dokumen, verifikasi, dan workflow
                            modul ini akan menggunakan struktur data
                            Si Bantek.
                        </p>

                        <div className="mt-5">
                            <Badge variant="info">
                                Modul siap dikembangkan
                            </Badge>
                        </div>

                        <Link
                            href={`/${role}`}
                            className="mt-6 inline-flex items-center gap-2 rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            <ArrowLeft size={16} />
                            Kembali ke Dashboard
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}