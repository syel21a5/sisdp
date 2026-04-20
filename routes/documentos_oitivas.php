<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\OitivasController\GerarDeclaracaoController;
use App\Http\Controllers\Documentos\OitivasController\GerarDepoimentoController;
use App\Http\Controllers\Documentos\OitivasController\GerarInterrogatorioController;
use App\Http\Controllers\Documentos\intimacao\GerarIntimacaoEditorController;

Route::middleware(['auth', 'permission:oitivas'])->group(function () {

    // =============================================
    // ROTAS POST PARA GERAR PDFs
    // =============================================
    Route::post('/termo-de-declaracao', [GerarDeclaracaoController::class, 'gerarPdfDeclaracao'])->name('termo.declaracao.pdf');
    Route::post('/termo-de-depoimento', [GerarDepoimentoController::class, 'gerarPdfDepoimento'])->name('gerar.pdf.depoimento');
    Route::post('/termo-de-interrogatorio', [GerarInterrogatorioController::class, 'gerarPdfInterrogatorio'])->name('gerar.interrogatorio');


    // ✅✅✅ ROTA CORRIGIDA PARA INTIMAÇÃO PDF (SEM /pdf)
    Route::post('/intimacao', [GerarIntimacaoEditorController::class, 'gerarPdfIntimacao'])->name('intimacao.pdf');

    // =============================================
    // ROTAS GET PARA VISUALIZAÇÃO/EDIÇÃO - PADRONIZADAS
    // =============================================
    
    // Função auxiliar para processar dados de Oitivas
    $processarOitiva = function($dados, $view) {
        $dadosArray = [];
        if ($dados) {
            // Suporte a UUID (Cache) ou Base64 (Antigo)
            if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){3}-[a-f\d]{12}$/i', $dados)) {
                $dadosArray = \Illuminate\Support\Facades\Cache::get('doc_sessao_' . $dados, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true) ?? [];
                } catch (\Exception $e) { $dadosArray = []; }
            }
        }
        return view($view, compact('dadosArray'));
    };

    Route::get('/interrogatorio/{dados?}', function($dados = null) use ($processarOitiva) {
        return $processarOitiva($dados, 'oitivas.Termo_de_Interrogatorio');
    })->name('interrogatorio');

    Route::get('/declaracao/{dados?}', function($dados = null) use ($processarOitiva) {
        return $processarOitiva($dados, 'oitivas.Termo_de_Declaracao');
    })->name('declaracao');

    Route::get('/depoimento/{dados?}', function($dados = null) use ($processarOitiva) {
        return $processarOitiva($dados, 'oitivas.Termo_de_Depoimento');
    })->name('depoimento');

    // ✅ ROTA GET PARA EDITOR DE INTIMAÇÃO PADRONIZADA
    Route::get('/intimacao/{dados?}', function($dados = null) use ($processarOitiva) {
        return $processarOitiva($dados, 'intimacao.intimacao_editor');
    })->name('intimacao.editor');

});
