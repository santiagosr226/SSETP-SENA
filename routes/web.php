<?php

use App\Http\Controllers\Admin\FichaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\ProgramaController;
use App\Http\Controllers\Admin\FuncionarioController;

Route::redirect('/', '/admin', 301);

route::get('/admin', [InicioController::class, 'index'])->name('admin');

Route::resource('programas', ProgramaController::class);

Route::resource('funcionarios', FuncionarioController::class);

Route::resource('fichas', FichaController::class);
Route::post('/fichas/importar-aprendices', [FichaController::class, 'importarAprendices'])->name('fichas.importar-aprendices');
Route::post('/fichas/importar-juicios-evaluativos', [FichaController::class, 'importarJuiciosEvaluativos'])->name('fichas.importar-juicios-evaluativos');
Route::delete('/fichas/{ficha}/aprendices/{aprendiz}', [FichaController::class, 'eliminarAprendiz'])->name('fichas.aprendices.destroy');
