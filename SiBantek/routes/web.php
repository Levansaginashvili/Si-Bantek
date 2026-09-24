<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\VerifikatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/' . Auth::user()->role);
    }
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::post('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{id}/status', [AdminController::class, 'toggleStatusUser'])->name('admin.users.status');
    });

    Route::middleware('role:verifikator')->prefix('verifikator')->group(function () {
        Route::get('/', [VerifikatorController::class, 'dashboard'])->name('verifikator.dashboard');
        Route::get('/sekolah/{id}', [VerifikatorController::class, 'showSekolah'])->name('verifikator.sekolah.show');
        Route::post('/sekolah/{id}/dokumen', [VerifikatorController::class, 'verifyDokumen'])->name('verifikator.dokumen.verify');
        Route::post('/sekolah/{id}/rab', [VerifikatorController::class, 'verifyRab'])->name('verifikator.rab.verify');
        Route::post('/sekolah/{id}/status-dana', [VerifikatorController::class, 'updateStatusDana'])->name('verifikator.status-dana.update');
    });

    Route::middleware('role:sekolah')->prefix('sekolah')->group(function () {
        Route::get('/', [SekolahController::class, 'dashboard'])->name('sekolah.dashboard');
        Route::post('/profil', [SekolahController::class, 'updateProfil'])->name('sekolah.profil.update');
        Route::post('/rab', [SekolahController::class, 'updateRab'])->name('sekolah.rab.update');
        Route::post('/dokumen/upload', [SekolahController::class, 'uploadDokumen'])->name('sekolah.dokumen.upload');
        Route::get('/dokumen/download/{type}', [SekolahController::class, 'downloadPdf'])->name('sekolah.dokumen.download');
        Route::get('/inventaris/label-pdf', [SekolahController::class, 'downloadLabels'])->name('sekolah.inventaris.labels');
    });
});