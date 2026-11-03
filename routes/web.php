<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\GbfCharacterController;
use App\Http\Controllers\Admin\GbfCharacterStageController;
use App\Http\Controllers\Admin\GbfMaterialController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('characters', GbfCharacterController::class)
            ->except(['show']);

        Route::resource('stages', GbfCharacterStageController::class)
            ->except(['show']);

        Route::resource('materials', GbfMaterialController::class)
            ->except(['show']);
    });

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';