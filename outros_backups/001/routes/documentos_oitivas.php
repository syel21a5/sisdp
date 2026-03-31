<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\OitivasController\GerarDeclaracaoController;
use App\Http\Controllers\Documentos\OitivasController\GerarDepoimentoController;
use App\Http\Controllers\Documentos\OitivasController\GerarInterrogatorioController;
use App\Http\Controllers\Documentos\intimacao\GerarIntimacaoEditorController;

Route::middleware(['auth'])->group(function () {

    // =============================================
    // ROTAS POST PARA GERAR PDFs
    // =============================================
    Route::post('/termo-de-declaracao', [GerarDeclaracaoController::class, 'gerarPdfDeclaracao'])->name('termo.declaracao.pdf');
    Route::post('/termo-de-depoimento', [GerarDepoimentoController::class, 'gerarPdfDepoimento'])->name('gerar.pdf.depoimento');
    Route::post('/termo-de-interrogatorio', [GerarInterrogatorioController::class, 'gerarPdfInterrogatorio'])->name('gerar.interrogatorio');


    // ✅✅✅ ROTA CORRIGIDA PARA INTIMAÇÃO PDF (SEM /pdf)
    Route::post('/intimacao', [GerarIntimacaoEditorController::class, 'gerarPdfIntimacao'])->name('intimacao.pdf');

    // =============================================
    // ROTAS GET PARA VISUALIZAÇÃO/EDIÇÃO
    // =============================================
    Route::get('/interrogatorio/{dados?}', function($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('oitivas.Termo_de_Interrogatorio', compact('dadosArray'));
    })->name('interrogatorio');


    Route::get('/declaracao/{dados?}', function($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('oitivas.Termo_de_Declaracao', compact('dadosArray'));
    })->name('declaracao');


    Route::get('/depoimento/{dados?}', function($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('oitivas.Termo_de_Depoimento', compact('dadosArray'));
    })->name('depoimento');


    // ✅ ROTA GET PARA EDITOR DE INTIMAÇÃO
    Route::get('/intimacao/{dados?}', function($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('intimacao.intimacao_editor', compact('dadosArray'));
    })->name('intimacao.editor');

});
