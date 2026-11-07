<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\GbfCharacterController;
use App\Http\Controllers\Admin\GbfCharacterStageController;
use App\Http\Controllers\Admin\GbfMaterialController;
use App\Http\Controllers\Admin\GbfProgressionController;
use App\Http\Controllers\Admin\DatabaseStructureController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/database-structure', [DatabaseStructureController::class, 'index'])
            ->name('database-structure.index');

        Route::resource('characters', GbfCharacterController::class)
            ->except(['show']);

        Route::resource('stages', GbfCharacterStageController::class)
            ->except(['show']);

        Route::resource('materials', GbfMaterialController::class)
            ->except(['show']);

        Route::get('/progression', [GbfProgressionController::class, 'index'])
            ->name('progression.index');
        Route::get('/progression/create', [GbfProgressionController::class, 'create'])
            ->name('progression.create');
        Route::post('/progression', [GbfProgressionController::class, 'store'])
            ->name('progression.store');
        Route::get('/progression/{character}', [GbfProgressionController::class, 'show'])
            ->name('progression.show');
        Route::get('/progression/{character}/edit', [GbfProgressionController::class, 'edit'])
            ->name('progression.edit');
        Route::put('/progression/{character}', [GbfProgressionController::class, 'update'])
            ->name('progression.update');
    });

Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';