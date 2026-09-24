import React from 'react';
import { useForm, Head } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        identity: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="flex min-h-screen flex-col justify-center bg-slate-50 py-12 sm:px-6 lg:px-8 font-sans">
            <Head title="Masuk — Si Bantek" />

            <div className="sm:mx-auto sm:w-full sm:max-w-md">
                <div className="flex justify-center">
                    <div className="flex h-20 w-20 items-center justify-center rounded-2xl bg-white p-2 border border-slate-200 shadow-sm">
                        <img
                            src="/logo.png"
                            alt="Logo Si Bantek"
                            className="h-full w-full object-contain"
                        />
                    </div>
                </div>
                <h2 className="mt-4 text-center text-2xl font-bold text-slate-900">
                    Si Bantek
                </h2>
                <p className="mt-1 text-center text-sm text-slate-500">
                    Sistem Bantuan Peralatan TIK SMP — Kemendikdasmen RI
                </p>
            </div>

            <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div className="rounded-xl border border-slate-200 bg-white px-8 py-8 shadow-sm">
                    <form className="space-y-5" onSubmit={handleSubmit}>
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Identitas Login
                            </label>
                            <input
                                type="text"
                                required
                                value={data.identity}
                                onChange={(e) => setData('identity', e.target.value)}
                                className="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                                placeholder="Masukkan Username / NIP / NPSN"
                            />
                            {errors.identity && (
                                <p className="mt-1 text-xs text-red-600">{errors.identity}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Kata Sandi
                            </label>
                            <input
                                type="password"
                                required
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-[#1e2d5a] focus:outline-none focus:ring-1 focus:ring-[#1e2d5a]"
                            />
                            {errors.password && (
                                <p className="mt-1 text-xs text-red-600">{errors.password}</p>
                            )}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="mt-2 w-full rounded-lg bg-[#1e2d5a] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#162247] focus:outline-none focus:ring-2 focus:ring-[#1e2d5a] focus:ring-offset-2 disabled:opacity-70 transition-colors"
                        >
                            {processing ? 'Memproses...' : 'Masuk'}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
