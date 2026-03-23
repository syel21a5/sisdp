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

    // Rota ESPECÍFICA para Auto de Apreensão
    Route::get('/auto-apreensao/{dados?}', [NumOficioController::class, 'gerarAutoApreensao'])->name('auto.apreensao');

    // Rota para pegar apenas o número (AJAX)
    Route::get('/numero-oficio/gerar', [NumOficioController::class, 'gerarNumero'])->name('numoficio.apenas.numero');

    // Rotas para Documentos (Agrupadas)
    Route::prefix('documentos')->group(function () {
        Route::get('/termo-restituicao/{dados?}', [NumOficioController::class, 'gerarTermoRestituicao'])->name('termo.restituicao');
        Route::get('/termo-renuncia-representacao/{dados?}', [NumOficioController::class, 'gerarTermoRenuncia'])->name('termo.renuncia.representacao');
        Route::get('/termo-representacao/{dados?}', [NumOficioController::class, 'gerarTermoRepresentacao'])->name('termo.representacao');
        Route::get('/termo-compromisso-juizo/{dados?}', [NumOficioController::class, 'gerarTermoCompromisso'])->name('termo.compromisso.juizo');
    });

    // Rota universal para documentos
    Route::get('/documento/{tipo}', [NumOficioController::class, 'gerarDocumento'])->name('numoficio.gerar');

    // 🔽 ROTA ESPECÍFICA PARA LIBERAÇÃO DO INFRATOR (SEM NUMERO OFÍCIO)
    Route::get('/liberacao-infrator/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('pecas.Termo_Liberacao_Infrator', compact('dadosArray'));
    })->name('liberacao.infrator');

    // ✅ ROTA GET PARA DESPACHO DE CONCLUSÃO (EDITOR)
    Route::get('/despacho-conclusao/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('pecas.despachoconclusao', compact('dadosArray'));
    })->where('dados', '.*')->name('despacho.conclusao');

    // ✅ ROTA GET PARA CERTIDÃO DE ASSINATURAS INDIVIDUAL
    Route::get('/certidao-assinaturas-individual/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('certidaoassinaturas.Certidao_Assinaturas_Individual', compact('dadosArray'));
    })->name('certidao.assinaturas.individual');

    // ✅ ROTA GET PARA CERTIDÃO DE ASSINATURAS APFD
    Route::get('/certidao-assinaturas-apfd/{dados?}', function ($dados = null) {
        $dadosArray = [];
        if ($dados) {
            try {
                $dadosArray = json_decode(base64_decode($dados), true);
            } catch (\Exception $e) {
                $dadosArray = [];
            }
        }
        return view('certidaoassinaturas.Certidao_Assinaturas_Apfd', compact('dadosArray'));
    })->name('certidao.assinaturas.apfd');

    // ✅ ROTA GET PARA ROL DE TESTEMUNHAS
    Route::get('/rol-de-testemunhas/{dados?}', function ($dados = null) {
        $dadosArray = [];

        if ($dados) {
            // Se os dados começarem com 'session_', buscamos na sessão (evita URI Too Long e Resubmission Error)
            if (strpos($dados, 'session_') === 0) {
                $key = str_replace('session_', '', $dados);
                $dadosArray = session()->get('rol_data_' . $key, []);
            } else {
                try {
                    $dadosArray = json_decode(base64_decode($dados), true);
                } catch (\Exception $e) {
                    $dadosArray = [];
                }
            }
        }

        return view('pecas.roldetestemunhas', compact('dadosArray'));
    })->name('rol.testemunhas');

    // ✅ ROTA POST PARA ROL DE TESTEMUNHAS (PRG Pattern para evitar erro de reenvio)
    Route::post('/rol-de-testemunhas-gerar', function (\Illuminate\Http\Request $request) {
        $dadosRaw = $request->input('dados', []);
        $dadosArray = is_string($dadosRaw) ? json_decode($dadosRaw, true) : $dadosRaw;

        $key = substr(md5(json_encode($dadosArray)), 0, 10);
        session()->put('rol_data_' . $key, $dadosArray);
        session()->save();

        return redirect()->to('/rol-de-testemunhas/session_' . $key);
    })->name('rol.testemunhas.view.post');

});
