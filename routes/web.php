<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\JournalEntryController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('categories', CategoryController::class)
        ->except(['show']);

    Route::resource('transactions', TransactionController::class)
        ->except(['show']);

    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])
        ->name('transactions.receipt');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto'])
        ->name('profile.remove-photo');

    Route::get('/transactions/export/pdf', [TransactionController::class, 'exportPdf'])
        ->name('transactions.export.pdf');

    Route::get('/transactions/export/excel', [TransactionController::class, 'exportExcel'])
    ->name('transactions.export.excel');
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/download', [ReportController::class, 'download'])
        ->name('reports.download');

    Route::get('/transactions/export-excel', [ExcelController::class, 'export'])
        ->name('transactions.export');

    Route::get('/transactions/excel-template', [ExcelController::class, 'template'])
        ->name('transactions.template');

    Route::post('/transactions/import-excel', [ExcelController::class, 'import'])
        ->name('transactions.import');

    Route::resource('journal', JournalEntryController::class)
        ->parameters(['journal' => 'journal'])
        ->names('journal');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

