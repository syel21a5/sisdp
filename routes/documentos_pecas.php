<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GerarPdfController;
use App\Http\Controllers\Documentos\PecasController\GerarPdfRestituicaoController;
use App\Http\Controllers\Documentos\PecasController\GerarPdfRepresentacaoController;
use App\Http\Controllers\Documentos\PecasController\GerarPdfRenunciaRepresentacaoController;
use App\Http\Controllers\Documentos\PecasController\GerarPdfApreensaoController;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_LiberacaoInfratorController;
use App\Http\Controllers\Documentos\CertidaoAssinaturas\GerarPdf_AssinaturasCompartilhadas;
use App\Http\Controllers\Documentos\CertidaoAssinaturas\GerarPdf_AssinaturasIndividual;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_DespachoDinamico_Controller;
use App\Http\Controllers\NumOficioController;

Route::middleware(['auth', 'permission:pecas'])->group(function () {

    // Função auxiliar para processar dados de peças (Cache ou Base64)
    $processarPeca = function ($dados, $view, $isOficio = false) {
        $dadosArray = [];
        if ($dados) {
            // 1. Verificar se é UUID (Cache)
            if (strlen($dados) === 36 || strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = \Illuminate\Support\Facades\Cache::get('doc_sessao_' . $key, []);
            } else {
                // 2. Fallback Base64
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }

        if ($isOficio) {
            $numeroOficio = app(\App\Services\NumeroOficioService::class)->gerarProximo();
            return view($view, compact('dadosArray', 'numeroOficio'));
        }

        return view($view, compact('dadosArray'));
    };

    // 🔽 ROTA DO PDF PEÇAS (POST)
    Route::post('/termo-de-compromisso', [GerarPdfController::class, 'gerarPdfCompromisso'])->name('termo.compromisso.pdf');
    Route::post('/termo-de-restituicao', [GerarPdfRestituicaoController::class, 'gerarPdfRestituicao'])->name('termo.restituicao.pdf');
    Route::post('/termo-de-representacao', [GerarPdfRepresentacaoController::class, 'gerarPdfRepresentacao'])->name('termo.representacao.pdf');
    Route::post('/termo-de-renuncia-representacao', [GerarPdfRenunciaRepresentacaoController::class, 'gerarPdfRenunciaRepresentacao'])->name('termo.renuncia.representacao.pdf');
    Route::post('/termo-de-apreensao', [GerarPdfApreensaoController::class, 'gerarPdfApreensao'])->name('termo.apreensao.pdf');
    Route::post('/termo-de-liberacao-infrator', [GerarPdf_LiberacaoInfratorController::class, 'gerarPdfLiberacaoInfrator'])->name('termo.liberacao.infrator.pdf');
    Route::post('/despacho-de-conclusao-', [GerarPdf_DespachoDinamico_Controller::class, 'gerarPdfDespachoConclusao'])->name('despacho.conclusao.pdf');

    // 🔽 ROTA POST DO PDF (CERTIDÃO DE ASSINATURAS)
    Route::post('/certidao-assinaturas-compartilhadas', [GerarPdf_AssinaturasCompartilhadas::class, 'gerarPdfAssinaturasCompartilhadas'])->name('certidao.assinaturas.compartilhadas.pdf');
    Route::post('/certidao-assinaturas-individual', [GerarPdf_AssinaturasIndividual::class, 'gerarPdfAssinaturasIndividual'])->name('certidao.assinaturas.individual.pdf');

    // ✅ ROTA POST PARA GERAR PDF (ROL DE TESTEMUNHAS)
    Route::post('/rol-de-testemunhas-', [App\Http\Controllers\Documentos\PecasController\GerarPdf_RoldeTestemunhas_Controller::class, 'gerarPdfRoldeTestemunhas'])->name('rol.testemunhas.pdf');

    // =======================================================
    // 🔥 ROTAS PARA DOCUMENTOS COM NÚMERO DE OFÍCIO (GET)
    // =======================================================

    Route::get('/auto-apreensao/{dados?}', function($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'pecas.auto_de_apreensao', true);
    })->name('auto.apreensao');

    Route::get('/numero-oficio/gerar', [NumOficioController::class, 'gerarNumero'])->name('numoficio.apenas.numero');

    Route::prefix('documentos')->group(function () use ($processarPeca) {
        Route::get('/termo-restituicao/{dados?}', function($dados = null) use ($processarPeca) {
            return $processarPeca($dados, 'pecas.termo_de_restituicao', true);
        })->name('termo.restituicao');

        Route::get('/termo-renuncia-representacao/{dados?}', function($dados = null) use ($processarPeca) {
            return $processarPeca($dados, 'pecas.termo_de_renuncia_representacao', true);
        })->name('termo.renuncia.representacao');

        Route::get('/termo-representacao/{dados?}', function($dados = null) use ($processarPeca) {
            return $processarPeca($dados, 'pecas.termo_de_representacao', true);
        })->name('termo.representacao');

        Route::get('/termo-compromisso-juizo/{dados?}', function($dados = null) use ($processarPeca) {
            return $processarPeca($dados, 'pecas.termo_de_compromisso_juizo', true);
        })->name('termo.compromisso.juizo');
    });

    Route::get('/liberacao-infrator/{dados?}', function ($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'pecas.Termo_Liberacao_Infrator');
    })->name('liberacao.infrator');

    Route::get('/despacho-conclusao/{dados?}', function ($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'pecas.despachoconclusao');
    })->where('dados', '.*')->name('despacho.conclusao');

    Route::get('/certidao-assinaturas-individual/{dados?}', function ($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'certidaoassinaturas.Certidao_Assinaturas_Individual');
    })->name('certidao.assinaturas.individual');

    Route::get('/certidao-assinaturas-apfd/{dados?}', function ($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'certidaoassinaturas.Certidao_Assinaturas_Apfd');
    })->name('certidao.assinaturas.apfd');

    Route::get('/rol-de-testemunhas/{dados?}', function ($dados = null) use ($processarPeca) {
        return $processarPeca($dados, 'pecas.roldetestemunhas');
    })->name('rol.testemunhas');

    // ✅ ROTA POST PARA ROL DE TESTEMUNHAS (PRG Pattern)
    Route::post('/rol-de-testemunhas-gerar', function (\Illuminate\Http\Request $request) {
        $dadosArray = $request->input('dados', []);
        $dadosArray = is_string($dadosArray) ? json_decode($dadosArray, true) : $dadosArray;
        $key = 'rol_' . substr(md5(json_encode($dadosArray)), 0, 10);
        \Illuminate\Support\Facades\Cache::put($key, $dadosArray, 3600);
        return redirect()->to('/rol-de-testemunhas/' . $key);
    })->name('rol.testemunhas.view.post');

});

