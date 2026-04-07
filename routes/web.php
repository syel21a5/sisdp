<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeralController;
use App\Http\Controllers\IntimacaoController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProcedimentosController;
use App\Http\Controllers\ConsultaPessoaController;
use App\Http\Controllers\PessoaController;

// Rotas Públicas (acessíveis sem login)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Rotas Protegidas (requerem autenticação)
Route::middleware(['auth'])->group(function () {
    Route::get('/', [GeralController::class, 'index'])->name('geral');
    Route::get('/wf-geral', [GeralController::class, 'index']);

    // ✅ ROTAS DO ADMINISTRATIVO
    Route::get('/wf-administrativo', [AdministrativoController::class, 'index'])->name('administrativo.index');
    Route::get('/administrativo', [AdministrativoController::class, 'index'])->name('administrativo');
    Route::get('/administrativo/auditoria', [\App\Http\Controllers\Administrativo\AuditController::class, 'index'])->name('administrativo.auditoria');
    Route::get('/administrativo/auditoria-chips', [App\Http\Controllers\AdministrativoController::class, 'relatorioSemChips'])->name('administrativo.auditoria_chips');

    // ✅ ROTA DO VEÍCULO (ADICIONADA)
    Route::get('/wf-veiculo', [VeiculoController::class, 'index'])->name('veiculo');
    Route::get('/wf-celular', [App\Http\Controllers\CelularController::class, 'index'])->name('celular');
    Route::get('/wf-apreensao-outros', [App\Http\Controllers\OutrosController::class, 'index'])->name('apreensao.outros');

    // Rotas do Módulo de Relatórios
    Route::prefix('relatorios')->group(function () {
        Route::get('/procedimentos', [ProcedimentosController::class, 'index'])->name('relatorios.procedimentos');
        Route::get('/procedimentos/dados', [ProcedimentosController::class, 'buscarDados']);
        Route::get('/procedimentos/exportar', [ProcedimentosController::class, 'exportar'])->name('relatorios.procedimentos.exportar');
    });

    Route::get('/intimacao', [IntimacaoController::class, 'index'])->name('intimacao.index');


    // ✅ ROTA DE TESTE (TEMPORÁRIA)
    Route::get('/inicio/gerar-teste', [InicioController::class, 'gerarMassaTeste'])->name('inicio.gerarMassaTeste');

    // ✅ NOVAS ROTAS DE RELATÓRIOS AVANÇADOS
    Route::get('/administrativo/relatorio', [AdministrativoController::class, 'relatorio'])->name('administrativo.relatorio');
    Route::get('/administrativo/relatorio/crimes', [AdministrativoController::class, 'relatorioCrimes'])->name('administrativo.relatorio.crimes');
    Route::get('/administrativo/relatorio/pessoas', [AdministrativoController::class, 'relatorioPessoas'])->name('administrativo.relatorio.pessoas');
    Route::get('/administrativo/relatorio/apreensoes', [AdministrativoController::class, 'relatorioApreensoes'])->name('administrativo.relatorio.apreensoes');
    Route::post('/administrativo/exportar', [AdministrativoController::class, 'exportarRelatorio'])->name('administrativo.exportar');

    // ✅ ROTAS UNIFICADAS PARA VÍNCULOS (garantia de carregamento)
    Route::prefix('boe/vinculos')->group(function () {
        Route::get('/listar/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'listarVinculos']);
        Route::post('/adicionar', [\App\Http\Controllers\BoeVincularController::class, 'adicionarVinculo']);
        Route::post('/sugerir', [\App\Http\Controllers\BoeVincularController::class, 'sugerirVinculo']);
        Route::delete('/remover/{id}', [\App\Http\Controllers\BoeVincularController::class, 'removerVinculo']);
        Route::post('/salvar', [\App\Http\Controllers\BoeVincularController::class, 'salvarVinculos']);
        Route::post('/aprovar/{id}', [\App\Http\Controllers\BoeVincularController::class, 'aprovarVinculo']);
        Route::post('/rejeitar/{id}', [\App\Http\Controllers\BoeVincularController::class, 'rejeitarVinculo']);

        Route::delete('/excluir-todos/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'excluirTodosVinculos']);
        Route::delete('/excluir-condutor/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'excluirVinculoCondutor']);
        Route::delete('/excluir-vitima1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'excluirVinculoVitima1']);
        Route::delete('/excluir-testemunha1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'excluirVinculoTestemunha1']);
        Route::delete('/excluir-autor1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'excluirVinculoAutor1']);

        Route::get('/buscar-condutor/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'buscarCondutorPorBoe']);
        Route::get('/buscar-vitima1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'buscarVitima1PorBoe']);
        Route::get('/buscar-testemunha1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'buscarTestemunha1PorBoe']);
        Route::get('/buscar-autor1/{boe}', [\App\Http\Controllers\BoeVincularController::class, 'buscarAutor1PorBoe']);
    });

    // ✅ ROTAS DE CONSULTA DE ANTECEDENTES (PESSOA)
    Route::get('/consulta-pessoa', [ConsultaPessoaController::class, 'index'])->name('consulta.pessoa.index');
    Route::get('/consulta-pessoa/detalhes/{id}', [ConsultaPessoaController::class, 'detalhes'])->name('consulta.pessoa.detalhes');
    Route::get('/pessoas/search', [PessoaController::class, 'search'])->name('pessoa.search');
    Route::get('/pessoas/search-fuzzy', [PessoaController::class, 'searchFuzzy'])->name('pessoa.searchFuzzy');

    // ✅ ROTAS PARA O SINCRONIZADOR SDS (INFOPOL)
    Route::get('/infopol/sincronizar', [App\Http\Controllers\InfopolController::class, 'index'])->name('infopol.index');
    Route::post('/infopol/conectar', [App\Http\Controllers\InfopolController::class, 'conectar'])->name('infopol.conectar');
    Route::post('/infopol/buscar', [App\Http\Controllers\InfopolController::class, 'buscar'])->name('infopol.buscar');
    Route::post('/infopol/baixar-selecionados', [App\Http\Controllers\InfopolController::class, 'baixarSelecionados'])->name('infopol.baixarSelecionados');
    Route::get('/infopol/download/{jobId}', [App\Http\Controllers\InfopolController::class, 'download'])->name('infopol.download');
    Route::get('/infopol/screenshot/{jobId}/{filename}', [App\Http\Controllers\InfopolController::class, 'screenshot'])->name('infopol.screenshot');

    Route::get('/sei/verificar', [App\Http\Controllers\SeiController::class, 'index'])->name('sei.index');
    Route::post('/sei/conectar', [App\Http\Controllers\SeiController::class, 'conectar'])->name('sei.conectar');
    Route::get('/sei/listar-seis', [App\Http\Controllers\SeiController::class, 'listarSeis'])->name('sei.listarSeis');
    Route::post('/sei/verificar', [App\Http\Controllers\SeiController::class, 'verificar'])->name('sei.verificar');
    Route::post('/sei/parar', [App\Http\Controllers\SeiController::class, 'parar'])->name('sei.parar');
    Route::get('/sei/screenshot/{jobId}/{filename}', [App\Http\Controllers\SeiController::class, 'screenshot'])->name('sei.screenshot');

});
