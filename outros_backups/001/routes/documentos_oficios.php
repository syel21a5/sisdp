<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NumOficioController;

// ✅ CORRIGIDO (mp minúsculo)

// ✅ NOVO: Controller MP para Ofícios
use App\Http\Controllers\Documentos\mp\GerarPdfOficiosMpController;
use App\Http\Controllers\Documentos\mp\GerarPdfOficioFamiliaMpController;
use App\Http\Controllers\Documentos\mp\GerarPdfautocircunstanciadoController;

// ✅ CORRIGIDO: APFD → apfd (minúsculo)
use App\Http\Controllers\Documentos\apfd\oficios\GerarPdfOficios_1AutorController;
use App\Http\Controllers\Documentos\apfd\oficios\GerarPdfOficioFamiliaApfdController;

Route::middleware(['auth'])->group(function () {

    // 🔽 ROTAS MP OFÍCIOS (unificado)

    // ✅ NOVAS ROTAS MP OFÍCIOS
    Route::post('/oficios-mp', [GerarPdfOficiosMpController::class, 'gerarPdfOficiosMp'])->name('oficio.mp.pdf');
    Route::get('/oficios-mp/{dados?}', [NumOficioController::class, 'gerarTermoOficiosMp'])->name('numoficio.mp');

    Route::post('/autocircunstanciado', [GerarPdfautocircunstanciadoController::class, 'GerarPdfautocircunstanciado_1Autor'])->name('autocircunstanciado.mp.pdf');
    Route::get('/autocircunstanciado/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('mp.autocircunstanciado', compact('dadosArray'));
    })->name('mp.autocircunstanciado');

    // ✅ NOVA ROTA MP OFÍCIO FAMÍLIA
    Route::post('/oficiofamilia-mp', [GerarPdfOficioFamiliaMpController::class, 'gerarPdfOficioFamiliaMp'])->name('oficiofamilia.mp.pdf');
    Route::get('/oficiofamilia-mp/{dados?}', [NumOficioController::class, 'gerarTermoOficioFamiliaMp'])->name('numoficio.mp.familia');

    // ✅ OFÍCIO FAMÍLIA APFD (EDITOR E PDF)
    Route::post('/oficiofamilia-apfd', [GerarPdfOficioFamiliaApfdController::class, 'gerarPdfOficioFamiliaApfd'])->name('oficiofamilia.apfd.pdf');
    Route::get('/oficiofamilia-apfd/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('apfd.oficios.oficio_familia_apfd_dinamico', compact('dadosArray'));
    })->name('oficiofamilia.apfd.editor');

    Route::post('/recolhimento-mp', [GerarPdfOficioFamiliaMpController::class, 'gerarPdfOficioFamiliaMp'])->name('recolhimento.mp.pdf');
    Route::get('/recolhimento-mp/{dados?}', [NumOficioController::class, 'gerarTermoRecolhimentoMp'])->name('numoficio.mp.recolhimento');




    // 🔽 ROTAS APFD (DINÂMICO) - ROTA POST
    Route::post('/oficios-apfd-dinamico', [GerarPdfOficios_1AutorController::class, 'gerarPdfOficios1Autor'])->name('oficio.apfd.dinamico.pdf');

    // 🔽 ROTAS APFD (DINÂMICO) - ROTA GET
    Route::get('/oficios-apfd-dinamico/{dados?}', [NumOficioController::class, 'gerarTermoOficiosDinamico'])->name('numoficio.apfd.dinamico');

    // ✅ NOVA ROTA: OFÍCIO ÚNICO
    Route::get('/oficios-apfd-unico/{dados?}', [NumOficioController::class, 'gerarTermoOficiosUnico'])->name('numoficio.apfd.unico');

});
