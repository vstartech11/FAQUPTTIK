<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\gedungController;
use App\Http\Controllers\Api\dataController;
use App\Http\Controllers\Api\ChatbotController;

Route::post('/chatbot', [ChatbotController::class, 'handleMessage']);

Route::get('/data/gedung', [gedungController::class, 'getAllGedung']);
Route::get('/data/gedung/{idGedung}', [dataController::class, 'getDataByGedung']);
Route::post('/data', [DataController::class, 'store']); // Menangani pengiriman data dari ESP8266
Route::get('/data', [DataController::class, 'index']);   // Mengambil data untuk dashboard

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware( ['auth', 'verified'])->group(function (){
    Route::post('/gedung', [gedungController::class, 'insertGedung']); // Untuk menambah data
    Route::put('/gedung/{id}', [gedungController::class, 'editGedung']); // Untuk edit
    Route::delete('/gedung/{id}', [gedungController::class, 'deleteGedung']); // Untuk delete
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



});

require __DIR__.'/auth.php';