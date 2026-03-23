<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\Pericias\GerarTraumatologicoIMLController;
use App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaEmVeiculo_Controller;
use App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaEmLocalDeCrime_Controller;
use App\Http\Controllers\NumOficioController;

Route::middleware(['auth', 'permission:pericias'])->group(function () {

    // ✅ ROTA GET PARA VISUALIZAR O FORMULÁRIO
    Route::get('/termo-traumatologico-iml/{dados?}', [NumOficioController::class, 'gerarTermoTraumatologicoIML'])->name('termo.traumatologico.iml');

    // ✅ ROTA POST PARA GERAR O PDF
    Route::post('/termo-traumatologico-iml', [GerarTraumatologicoIMLController::class, 'gerarPdfTraumatologicoIML'])->name('termo.traumatologico.iml.pdf');

    // ==========================================
    // ✅ PERÍCIA EM VEÍCULO (NOVO MÓDULO)
    // ==========================================

    // Rota GET para visualizar o formulário (AGORA COM GERAÇÃO DE OFÍCIO AUTOMÁTICO)
    Route::get('/pericia-em-veiculo/{dados?}', [NumOficioController::class, 'gerarPericiaEmVeiculo'])->name('pericia.veiculo');

    // Rota POST para gerar o PDF
    Route::post('/pericia-em-veiculo-pdf', [GerarPdf_PericiaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.veiculo.pdf');

    // ==========================================
    // ✅ PERÍCIA EM LOCAL DE CRIME (NOVO MÓDULO)
    // ==========================================

    // Rota GET para visualizar o formulário
    Route::get('/pericia-local-de-crime/{dados?}', [NumOficioController::class, 'gerarPericiaEmLocalDeCrime'])->name('pericia.local.crime');

    // Rota POST para gerar o PDF
    Route::post('/pericia-local-de-crime-pdf', [GerarPdf_PericiaEmLocalDeCrime_Controller::class, 'gerarPdf'])->name('pericia.local.crime.pdf');

});
