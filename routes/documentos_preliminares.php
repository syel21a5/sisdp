<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_Avaliacao_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_AvaliacaoIndireta_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_ExameConstatacaoDanos_Controller;

use App\Http\Controllers\Documentos\PecasController\GerarPdf_ConstatacaoDanosIndireta_Controller;
use App\Http\Controllers\Documentos\PecasController\GerarPdf_EficienciaArmaFogo_Controller;

Route::middleware(['auth', 'permission:preliminares'])->group(function () {

    // Função auxiliar para processar dados de exames preliminares (Cache ou Base64)
    $processarPrelim = function ($dados, $view) {
        $dadosArray = [];
        if ($dados) {
            // 1. Verificar se é UUID (Cache)
            if (strlen($dados) === 36 || strpos($dados, 'prelim_') === 0 || strpos($dados, 'session_') === 0) {
                $key = str_replace(['session_', 'prelim_'], '', $dados);
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
        return view($view, compact('dadosArray'));
    };

    // Helper para gerar rota POST de visualização (PRG Pattern)
    $gerarRotaPost = function ($dados) {
        $dadosArray = is_string($dados) ? json_decode($dados, true) : $dados;
        $key = 'prelim_' . substr(md5(json_encode($dadosArray)), 0, 10);
        \Illuminate\Support\Facades\Cache::put($key, $dadosArray, 3600);
        return $key;
    };

    // =============================================
    // ROTAS POST PARA GERAR PDFs (DOWNLOAD)
    // =============================================
    Route::post('/termo-de-avaliacao-portaria', [GerarPdf_Avaliacao_Controller::class, 'gerarPdfAvaliacaoPortaria'])->name('termo.avaliacao.portaria.pdf');
    Route::post('/termo-de-avaliacao-termo', [GerarPdf_Avaliacao_Controller::class, 'gerarPdfAvaliacaoTermo'])->name('termo.avaliacao.termo.pdf');
    Route::post('/exame-danos-portaria', [GerarPdf_ExameConstatacaoDanos_Controller::class, 'gerarPdfExameDanosPortaria'])->name('exame.danos.portaria.pdf');
    Route::post('/exame-danos-termo', [GerarPdf_ExameConstatacaoDanos_Controller::class, 'gerarPdfExameDanosTermo'])->name('exame.danos.termo.pdf');
    Route::post('/constatacao-indireta-portaria', [GerarPdf_ConstatacaoDanosIndireta_Controller::class, 'gerarPdfConstatacaoIndiretaPortaria'])->name('constatacao.indireta.portaria.pdf');
    Route::post('/constatacao-indireta-termo', [GerarPdf_ConstatacaoDanosIndireta_Controller::class, 'gerarPdfConstatacaoIndiretaTermo'])->name('constatacao.indireta.termo.pdf');
    Route::post('/eficiencia-arma-portaria', [GerarPdf_EficienciaArmaFogo_Controller::class, 'gerarPdfEficienciaArmaPortaria'])->name('constatacao.eficiencia.arma.portaria.pdf');
    Route::post('/eficiencia-arma-termo', [GerarPdf_EficienciaArmaFogo_Controller::class, 'gerarPdfEficienciaArmaTermo'])->name('constatacao.eficiencia.arma.termo.pdf');

    // =============================================
    // ROTAS POST PARA VISUALIZAÇÃO (PRG PATTERN)
    // =============================================
    Route::post('/avaliacao-portaria-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/avaliacao-portaria/' . $gerarRotaPost($request->input('dados')));
    })->name('avaliacao.portaria.view.post');

    Route::post('/avaliacao-termo-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/avaliacao-termo/' . $gerarRotaPost($request->input('dados')));
    })->name('avaliacao.termo.view.post');

    Route::post('/avaliacao-indireta-portaria-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/avaliacao-indireta-portaria/' . $gerarRotaPost($request->input('dados')));
    })->name('avaliacao.indireta.portaria.view.post');

    Route::post('/avaliacao-indireta-termo-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/avaliacao-indireta-termo/' . $gerarRotaPost($request->input('dados')));
    })->name('avaliacao.indireta.termo.view.post');

    Route::post('/exame-danos-portaria-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/exame-danos-portaria/' . $gerarRotaPost($request->input('dados')));
    })->name('exame.danos.portaria.view.post');

    Route::post('/exame-danos-termo-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/exame-danos-termo/' . $gerarRotaPost($request->input('dados')));
    })->name('exame.danos.termo.view.post');

    Route::post('/constatacao-indireta-portaria-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/constatacao-indireta-portaria/' . $gerarRotaPost($request->input('dados')));
    })->name('constatacao.indireta.portaria.view.post');

    Route::post('/constatacao-indireta-termo-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/constatacao-indireta-termo/' . $gerarRotaPost($request->input('dados')));
    })->name('constatacao.indireta.termo.view.post');

    Route::post('/eficiencia-arma-portaria-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/eficiencia-arma-portaria/' . $gerarRotaPost($request->input('dados')));
    })->name('constatacao.eficiencia.arma.portaria.view.post');

    Route::post('/eficiencia-arma-termo-gerar', function (Illuminate\Http\Request $request) use ($gerarRotaPost) {
        return redirect()->to('/eficiencia-arma-termo/' . $gerarRotaPost($request->input('dados')));
    })->name('constatacao.eficiencia.arma.termo.view.post');

    // =============================================
    // ROTAS GET PARA VISUALIZAÇÃO (EDITOR)
    // =============================================
    Route::get('/avaliacao-portaria/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Avaliacao_Portaria');
    })->name('avaliacao.portaria');

    Route::get('/avaliacao-termo/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Avaliacao_Termo');
    })->name('avaliacao.termo');

    Route::get('/avaliacao-indireta-portaria/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Avaliacao_Indireta_Portaria');
    })->name('avaliacao.indireta.portaria');

    Route::get('/avaliacao-indireta-termo/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Avaliacao_Indireta_Termo');
    })->name('avaliacao.indireta.termo');

    Route::get('/exame-danos-portaria/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Exame_Danos_Portaria');
    })->name('exame.danos.portaria.view');

    Route::get('/exame-danos-termo/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Exame_Danos_Termo');
    })->name('exame.danos.termo.view');

    Route::get('/constatacao-indireta-portaria/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Constatacao_Indireta_Portaria');
    })->name('constatacao.indireta.portaria.view');

    Route::get('/constatacao-indireta-termo/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Constatacao_Indireta_Termo');
    })->name('constatacao.indireta.termo.view');

    Route::get('/eficiencia-arma-portaria/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Eficiencia_Arma_Portaria');
    })->name('constatacao.eficiencia.arma.portaria.view');

    Route::get('/eficiencia-arma-termo/{dados?}', function ($dados = null) use ($processarPrelim) {
        return $processarPrelim($dados, 'pecas.exames_preliminares.Auto_Eficiencia_Arma_Termo');
    })->name('constatacao.eficiencia.arma.termo.view');

});

