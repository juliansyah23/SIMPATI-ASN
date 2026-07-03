<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\TentangController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KuisionerController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Public routes ──────────────────────────────────────────────────────────
Route::get('/',        [DashboardController::class,  'index'])->name('dashboard');
Route::get('/data',    [DataController::class,       'index'])->name('data');

// ── Export Data: Performa per Pusat Riset (publik, sama seperti halaman /data) ──
Route::get('/data/export/pusat-riset/excel', [DataController::class, 'exportPusatRisetExcel'])->name('data.export.pusatRiset.excel');
Route::get('/data/export/pusat-riset/pdf',   [DataController::class, 'exportPusatRisetPdf'])->name('data.export.pusatRiset.pdf');

// ── Export Data: Data Detail Responden (khusus admin) ────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/data/export/responden/excel', [DataController::class, 'exportRespondenExcel'])->name('data.export.responden.excel');
    Route::get('/data/export/responden/pdf',   [DataController::class, 'exportRespondenPdf'])->name('data.export.responden.pdf');
});
Route::get('/tentang', [TentangController::class,    'index'])->name('tentang');

// ── Kuisioner routes (wajib login) ──────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/kuisioner',              [KuisionerController::class, 'index'])->name('kuisioner');
    Route::get('/kuisioner/riwayat/{id}', [KuisionerController::class, 'riwayatDetail'])->name('kuisioner.riwayat.detail');
    Route::get('/kuisioner/{id}',         [KuisionerController::class, 'show'])->name('kuisioner.show');
    Route::post('/kuisioner/{id}/step',   [KuisionerController::class, 'step'])->name('kuisioner.step');
    Route::post('/kuisioner/{id}/submit', [KuisionerController::class, 'submit'])->name('kuisioner.submit');
});

// ── Admin routes (wajib login + role admin) ─────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/',                        [AdminController::class, 'index'])->name('admin.index');
    Route::get('/kuisioner/create',         [KuisionerController::class, 'create'])->name('admin.kuisioner.create');
    Route::get('/kuisioner/{id}/edit',      [KuisionerController::class, 'edit'])->name('admin.kuisioner.edit');
    Route::post('/kuisioner',               [AdminController::class, 'storeKuisioner'])->name('admin.kuisioner.store');
    Route::put('/kuisioner/{id}',           [AdminController::class, 'updateKuisioner'])->name('admin.kuisioner.update');
    Route::post('/kuisioner/{id}/toggle',   [AdminController::class, 'toggleKuisioner'])->name('admin.kuisioner.toggle');
    Route::get('/kuisioner/{id}/respons',   [AdminController::class, 'kuisionerRespons'])->name('admin.kuisioner.respons');
    Route::get('/kuisioner/{id}/respons/export/excel', [AdminController::class, 'exportKuisionerResponsExcel'])->name('admin.kuisioner.respons.export.excel');
    Route::get('/kuisioner/{id}/respons/export/pdf',   [AdminController::class, 'exportKuisionerResponsPdf'])->name('admin.kuisioner.respons.export.pdf');
    Route::delete('/kuisioner/{id}',        [AdminController::class, 'deleteKuisioner'])->name('admin.kuisioner.delete');
    Route::get('/user/{id}',                [AdminController::class, 'showUser'])->name('admin.user.show');
    Route::get('/user/{id}/edit',           [AdminController::class, 'editUser'])->name('admin.user.edit');
    Route::put('/user/{id}',                [AdminController::class, 'updateUser'])->name('admin.user.update');
});

// ── Auth routes (hanya untuk guest / belum login) ───────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login',   [AuthController::class, 'loginSubmit'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register',[AuthController::class, 'registerSubmit'])->name('register.submit');

    Route::get('/forgot-password',        [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password',       [AuthController::class, 'forgotPasswordSubmit'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/reset-password',        [AuthController::class, 'resetPasswordSubmit'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');