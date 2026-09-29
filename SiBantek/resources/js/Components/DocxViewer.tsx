import React, { useEffect, useRef, useState } from 'react';
import { renderAsync } from 'docx-preview';
import { Loader2, AlertCircle, FileText, Download } from 'lucide-react';

interface DocxViewerProps {
    url: string;
    downloadUrl?: string;
    className?: string;
}

export default function DocxViewer({ url, downloadUrl, className = '' }: DocxViewerProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let isMounted = true;
        setLoading(true);
        setError(null);

        fetch(url)
            .then((res) => {
                if (!res.ok) {
                    throw new Error(`Gagal memuat dokumen (HTTP ${res.status})`);
                }
                return res.blob();
            })
            .then(async (blob) => {
                if (!isMounted || !containerRef.current) return;
                containerRef.current.innerHTML = '';

                await renderAsync(blob, containerRef.current, undefined, {
                    className: 'docx',
                    inWrapper: true,
                    ignoreWidth: false,
                    ignoreHeight: false,
                    ignoreFonts: false,
                    breakPages: true,
                    experimental: true,
                    useBase64URL: true,
                    trimXmlDeclaration: true,
                });

                if (isMounted) {
                    setLoading(false);
                }
            })
            .catch((err) => {
                if (isMounted) {
                    console.error('Docx render error:', err);
                    setError(err.message || 'Gagal memproses tampilan dokumen Word.');
                    setLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
    }, [url]);

    return (
        <div className={`relative w-full flex flex-col items-center min-h-[500px] ${className}`}>
            {loading && (
                <div className="absolute inset-0 z-20 flex flex-col items-center justify-center bg-slate-50/90 backdrop-blur-xs p-6">
                    <Loader2 className="h-9 w-9 animate-spin text-[#1e2d5a] mb-3" />
                    <p className="text-sm font-semibold text-slate-800">Menyiapkan Pratinjau Dokumen Word...</p>
                    <p className="text-xs text-slate-500 mt-1">Merender tata letak, tabel, dan format naskah</p>
                </div>
            )}

            {error ? (
                <div className="my-10 flex flex-col items-center text-center max-w-md p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
                    <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-3">
                        <FileText size={24} />
                    </div>
                    <h4 className="text-sm font-bold text-slate-800">Pratinjau Langsung Tidak Tersedia</h4>
                    <p className="text-xs text-slate-500 mt-1 mb-4 leading-relaxed">
                        Dokumen ini dapat langsung diunduh dan dibuka dengan aplikasi Microsoft Word / WPS Office di perangkat Anda.
                    </p>
                    {downloadUrl && (
                        <a
                            href={downloadUrl}
                            download
                            className="inline-flex items-center gap-2 rounded-lg bg-[#1e2d5a] px-4 py-2 text-xs font-semibold text-white hover:bg-[#162247] transition-colors shadow-sm"
                        >
                            <Download size={14} />
                            Unduh Dokumen Word (.docx)
                        </a>
                    )}
                </div>
            ) : (
                <div
                    ref={containerRef}
                    className="w-full flex flex-col items-center py-6 px-2 sm:px-6 bg-slate-200/70 overflow-auto rounded-lg shadow-inner"
                    style={{ minHeight: '65vh' }}
                />
            )}
        </div>
    );
}
