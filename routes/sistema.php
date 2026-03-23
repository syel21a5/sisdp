<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeralController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\CondutorController;
use App\Http\Controllers\CondutorAPFDController;
use App\Http\Controllers\Vitima1Controller;
use App\Http\Controllers\Testemunha1Controller;
use App\Http\Controllers\OutrosController;
use App\Http\Controllers\Autor1Controller;
use App\Http\Controllers\CelularController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\BoeVincularController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\IntimacaoController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\PessoaController; // Adicionado

use Illuminate\Http\Request;

Route::middleware(['auth'])->group(function () {

    Route::get('/pesquisar-pessoa', [PessoaController::class, 'search']); // Nova rota
    Route::post('/pessoas/store', [PessoaController::class, 'store']); // Rota para salvar nova pessoa

    Route::post('/apfd/importar-boe-texto', 'App\Http\Controllers\InicioController@importarBoeTexto'); // Rota para importar texto do BOE

    // ✅ REMOVIDO: Estas rotas já estão no web.php
    // Route::get('/', [GeralController::class, 'index'])->name('geral');
    // Route::get('/wf-geral', [GeralController::class, 'index'])->name('geral');

    // ✅ Página Inicio com URL limpa e alias legado
    Route::get('/ip-apfd', [InicioController::class, 'index'])->name('inicio');
    Route::get('/wf-inicio', [InicioController::class, 'index']);

    // ✅ ROTAS PARA INTIMAÇÃO (COMPLETAS E CORRETAS)
    Route::prefix('intimacao')->group(function () {
        Route::get('/', [IntimacaoController::class, 'index'])->name('intimacao.index');
        Route::get('/ultimos', [IntimacaoController::class, 'ultimos'])->name('intimacao.ultimos');
        Route::get('/pesquisar', [IntimacaoController::class, 'pesquisar'])->name('intimacao.pesquisar');
        Route::get('/buscar/{id}', [IntimacaoController::class, 'buscar'])->name('intimacao.buscar');
        Route::get('/buscar-boe/{boe}', [IntimacaoController::class, 'buscarPorBoe'])->name('intimacao.buscarBoe');
        Route::post('/salvar', [IntimacaoController::class, 'salvar'])->name('intimacao.salvar');
        Route::put('/atualizar/{id}', [IntimacaoController::class, 'atualizar'])->name('intimacao.atualizar');
        Route::delete('/excluir/{id}', [IntimacaoController::class, 'excluir'])->name('intimacao.excluir');
        Route::get('/controle-periodo', [IntimacaoController::class, 'controlePorPeriodo'])->name('intimacao.controle.periodo');
    });

    // ✅✅✅ ROTAS PARA ADMINISTRATIVO (ADICIONADAS)
    Route::prefix('administrativo')->group(function () {
        Route::get('/ultimos', [AdministrativoController::class, 'ultimos'])->name('administrativo.ultimos');
        Route::get('/pesquisar', [AdministrativoController::class, 'pesquisar'])->name('administrativo.pesquisar');
        Route::get('/buscar/{id}', [AdministrativoController::class, 'buscar'])->name('administrativo.buscar');
        Route::post('/salvar', [AdministrativoController::class, 'salvar'])->name('administrativo.salvar');
        Route::put('/atualizar/{id}', [AdministrativoController::class, 'atualizar'])->name('administrativo.atualizar');
        Route::delete('/excluir/{id}', [AdministrativoController::class, 'excluir'])->name('administrativo.excluir');
        Route::post('/importar-boe-texto', [AdministrativoController::class, 'importarBoeTexto'])->name('administrativo.importar_boe_texto');
    });

    // Rotas para GERAL
    Route::prefix('geral')->group(function () {
        Route::get('/pesquisar', [GeralController::class, 'pesquisar'])->name('geral.pesquisar');
        Route::get('/buscar/{id}', [GeralController::class, 'buscar'])->name('geral.buscar');
        Route::post('/salvar', [GeralController::class, 'salvar'])->name('geral.salvar');
        Route::put('/atualizar/{id}', [GeralController::class, 'atualizar'])->name('geral.atualizar');
        Route::delete('/excluir/{id}', [GeralController::class, 'excluir'])->name('geral.excluir');
    });

    // Rotas para INICIO
    Route::prefix('inicio')->group(function () {
        Route::get('/pesquisar', [InicioController::class, 'pesquisar'])->name('inicio.pesquisar');
        Route::get('/buscar-pendencias', [InicioController::class, 'buscarPendencias'])->name('inicio.buscarPendencias');
        Route::get('/buscar/{id}', [InicioController::class, 'buscar'])->name('inicio.buscar');
        Route::post('/salvar', [InicioController::class, 'salvar'])->name('inicio.salvar');
        Route::put('/atualizar/{id}', [InicioController::class, 'atualizar'])->name('inicio.atualizar');
        Route::delete('/excluir/{id}', [InicioController::class, 'excluir'])->name('inicio.excluir');
    });

    // Rotas para CONDUTOR
    Route::prefix('condutor')->group(function () {
        Route::get('/ultimos', [CondutorController::class, 'ultimos'])->name('condutor.ultimos');
        Route::get('/pesquisar', [CondutorController::class, 'pesquisar'])->name('condutor.pesquisar');
        Route::get('/buscar/{id}', [CondutorController::class, 'buscar'])->name('condutor.buscar');
        Route::post('/salvar', [CondutorController::class, 'salvar'])->name('condutor.salvar');
        Route::put('/atualizar/{id}', [CondutorController::class, 'atualizar'])->name('condutor.atualizar');
        Route::delete('/excluir/{id}', [CondutorController::class, 'excluir'])->name('condutor.excluir');
        Route::post('/documentos/salvar', [CondutorController::class, 'salvarDocumentos'])->name('condutor.documentos.salvar');
    });

    // Rotas para CONDUTOR APFD
    Route::prefix('condutor-apfd')->group(function () {
        Route::get('/ultimos', [CondutorAPFDController::class, 'ultimos'])->name('condutor.apfd.ultimos');
        Route::get('/pesquisar', [CondutorAPFDController::class, 'pesquisar'])->name('condutor.apfd.pesquisar');
        Route::get('/buscar/{id}', [CondutorAPFDController::class, 'buscar'])->name('condutor.apfd.buscar');
        Route::post('/salvar', [CondutorAPFDController::class, 'salvar'])->name('condutor.apfd.salvar');
        Route::put('/atualizar/{id}', [CondutorAPFDController::class, 'atualizar'])->name('condutor.apfd.atualizar');
        Route::delete('/excluir/{id}', [CondutorAPFDController::class, 'excluir'])->name('condutor.apfd.excluir');
        Route::post('/documentos/salvar', [CondutorAPFDController::class, 'salvarDocumentos'])->name('condutor.apfd.documentos.salvar');
    });

    // Rotas para VÍTIMA 1, 2, 3
    Route::prefix('vitima1')->group(function () {
        Route::post('/pesquisar', [Vitima1Controller::class, 'pesquisar'])->name('vitima1.pesquisar');
        Route::get('/buscar/{id}', [Vitima1Controller::class, 'buscar'])->name('vitima1.buscar');
        Route::post('/salvar', [Vitima1Controller::class, 'salvar'])->name('vitima1.salvar');
        Route::put('/atualizar/{id}', [Vitima1Controller::class, 'atualizar'])->name('vitima1.atualizar');
        Route::delete('/excluir/{id}', [Vitima1Controller::class, 'excluir'])->name('vitima1.excluir');
    });


    // Rotas para TESTEMUNHA 1, 2, 3
    Route::prefix('testemunha1')->group(function () {
        Route::post('/pesquisar', [Testemunha1Controller::class, 'pesquisar'])->name('testemunha1.pesquisar');
        Route::get('/buscar/{id}', [Testemunha1Controller::class, 'buscar'])->name('testemunha1.buscar');
        Route::post('/salvar', [Testemunha1Controller::class, 'salvar'])->name('testemunha1.salvar');
        Route::put('/atualizar/{id}', [Testemunha1Controller::class, 'atualizar'])->name('testemunha1.atualizar');
        Route::delete('/excluir/{id}', [Testemunha1Controller::class, 'excluir'])->name('testemunha1.excluir');
    });

    Route::prefix('outro')->group(function () {
        Route::post('/pesquisar', [OutrosController::class, 'pesquisar'])->name('outro.pesquisar');
        Route::get('/buscar/{id}', [OutrosController::class, 'buscar'])->name('outro.buscar');
        Route::post('/salvar', [OutrosController::class, 'salvar'])->name('outro.salvar');
        Route::put('/atualizar/{id}', [OutrosController::class, 'atualizar'])->name('outro.atualizar');
        Route::delete('/excluir/{id}', [OutrosController::class, 'excluir'])->name('outro.excluir');
    });


    // Rotas para AUTOR 1, 2, 3
    Route::prefix('autor1')->group(function () {
        Route::post('/pesquisar', [Autor1Controller::class, 'pesquisar'])->name('autor1.pesquisar');
        Route::get('/buscar/{id}', [Autor1Controller::class, 'buscar'])->name('autor1.buscar');
        Route::post('/salvar', [Autor1Controller::class, 'salvar'])->name('autor1.salvar');
        Route::put('/atualizar/{id}', [Autor1Controller::class, 'atualizar'])->name('autor1.atualizar');
        Route::delete('/excluir/{id}', [Autor1Controller::class, 'excluir'])->name('autor1.excluir');
    });


    // Rotas para Celular
    Route::prefix('celular')->group(function () {
        Route::get('/', [CelularController::class, 'index'])->name('celular.index');
        Route::get('/pesquisar', [CelularController::class, 'pesquisar'])->name('celular.pesquisar');
        Route::get('/buscar/{id}', [CelularController::class, 'buscar'])->name('celular.buscar');
        Route::post('/salvar', [CelularController::class, 'salvar'])->name('celular.salvar');
        Route::put('/atualizar/{id}', [CelularController::class, 'atualizar'])->name('celular.atualizar');
        Route::delete('/excluir/{id}', [CelularController::class, 'excluir'])->name('celular.excluir');
        Route::get('/ultimos', [CelularController::class, 'ultimos'])->name('celular.ultimos');
        Route::get('/controle-status', [CelularController::class, 'controlePorStatus'])->name('celular.controle.status');
        Route::get('/exportar-excel', [CelularController::class, 'exportarExcel'])->name('celular.exportar.excel');
        Route::get('/exportar-pdf',   [CelularController::class, 'exportarPdf'])->name('celular.exportar.pdf');
    });

    // ✅✅✅ ROTAS PARA VEÍCULO (CORRIGIDAS)
    Route::prefix('veiculo')->group(function () {
        Route::get('/', [VeiculoController::class, 'index'])->name('veiculo.index');
        Route::get('/pesquisar', [VeiculoController::class, 'pesquisar'])->name('veiculo.pesquisar');
        Route::get('/buscar/{id}', [VeiculoController::class, 'buscar'])->name('veiculo.buscar');
        Route::post('/salvar', [VeiculoController::class, 'salvar'])->name('veiculo.salvar');
        Route::post('/atualizar/{id}', [VeiculoController::class, 'atualizar'])->name('veiculo.atualizar'); // ✅ CORRIGIDO: PUT → POST
        Route::delete('/excluir/{id}', [VeiculoController::class, 'excluir'])->name('veiculo.excluir');
        Route::get('/ultimos', [VeiculoController::class, 'ultimos'])->name('veiculo.ultimos');
        Route::get('/controle-status', [VeiculoController::class, 'controlePorStatus'])->name('veiculo.controle.status');
        Route::get('/exportar-excel', [VeiculoController::class, 'exportarExcel'])->name('veiculo.exportar.excel');
        Route::get('/exportar-pdf',   [VeiculoController::class, 'exportarPdf'])->name('veiculo.exportar.pdf');
    });

    // ✅ ROTAS UNIFICADAS PARA VÍNCULOS (COMPLETAS)
    Route::prefix('boe/vinculos')->group(function () {
        // ✅ NOVAS ROTAS DINÂMICAS
        Route::get('/listar/{boe}', [BoeVincularController::class, 'listarVinculos']);
        Route::post('/adicionar', [BoeVincularController::class, 'adicionarVinculo']);
        Route::delete('/remover/{id}', [BoeVincularController::class, 'removerVinculo']);

        Route::post('/salvar', [BoeVincularController::class, 'salvarVinculos']);

        // ROTAS DE EXCLUSÃO ESPECÍFICAS
        Route::delete('/excluir-todos/{boe}', [BoeVincularController::class, 'excluirTodosVinculos'])->name('boe.vinculos.excluir-todos');
        Route::delete('/excluir-condutor/{boe}', [BoeVincularController::class, 'excluirVinculoCondutor'])->name('boe.vinculos.excluir-condutor');
        Route::delete('/excluir-vitima1/{boe}', [BoeVincularController::class, 'excluirVinculoVitima1'])->name('boe.vinculos.excluir-vitima1');
        Route::delete('/excluir-vitima2/{boe}', [BoeVincularController::class, 'excluirVinculoVitima2'])->name('boe.vinculos.excluir-vitima2');
        Route::delete('/excluir-vitima3/{boe}', [BoeVincularController::class, 'excluirVinculoVitima3'])->name('boe.vinculos.excluir-vitima3');
        Route::delete('/excluir-testemunha1/{boe}', [BoeVincularController::class, 'excluirVinculoTestemunha1'])->name('boe.vinculos.excluir-testemunha1');
        Route::delete('/excluir-outro/{boe}', [BoeVincularController::class, 'excluirVinculoOutro'])->name('boe.vinculos.excluir-outro');
        Route::delete('/excluir-testemunha2/{boe}', [BoeVincularController::class, 'excluirVinculoTestemunha2'])->name('boe.vinculos.excluir-testemunha2');
        Route::delete('/excluir-testemunha3/{boe}', [BoeVincularController::class, 'excluirVinculoTestemunha3'])->name('boe.vinculos.excluir-testemunha3');
        Route::delete('/excluir-autor1/{boe}', [BoeVincularController::class, 'excluirVinculoAutor1'])->name('boe.vinculos.excluir-autor1');
        Route::delete('/excluir-autor2/{boe}', [BoeVincularController::class, 'excluirVinculoAutor2'])->name('boe.vinculos.excluir-autor2');
        Route::delete('/excluir-autor3/{boe}', [BoeVincularController::class, 'excluirVinculoAutor3'])->name('boe.vinculos.excluir-autor3');

        // Rotas de busca
        Route::get('/buscar-condutor/{boe}', [BoeVincularController::class, 'buscarCondutorPorBoe']);
        Route::get('/buscar-vitima1/{boe}', [BoeVincularController::class, 'buscarVitima1PorBoe']);
        Route::get('/buscar-vitima2/{boe}', [BoeVincularController::class, 'buscarVitima2PorBoe']);
        Route::get('/buscar-vitima3/{boe}', [BoeVincularController::class, 'buscarVitima3PorBoe']);
        Route::get('/buscar-testemunha1/{boe}', [BoeVincularController::class, 'buscarTestemunha1PorBoe']);
        Route::get('/buscar-testemunha2/{boe}', [BoeVincularController::class, 'buscarTestemunha2PorBoe']);
        Route::get('/buscar-testemunha3/{boe}', [BoeVincularController::class, 'buscarTestemunha3PorBoe']);
        Route::get('/buscar-autor1/{boe}', [BoeVincularController::class, 'buscarAutor1PorBoe']);
        Route::get('/buscar-autor2/{boe}', [BoeVincularController::class, 'buscarAutor2PorBoe']);
        Route::get('/buscar-autor3/{boe}', [BoeVincularController::class, 'buscarAutor3PorBoe']);
    });

    // Rotas para detalhes APFD por pessoa
    Route::prefix('apfd/detalhes')->group(function () {
        Route::post('/salvar', [\App\Http\Controllers\ApfdPessoaDetalheController::class, 'salvar']);
        Route::get('/buscar/{cadprincipal_id}/{pessoa_id}/{papel}', [\App\Http\Controllers\ApfdPessoaDetalheController::class, 'buscar']);
        Route::get('/listar/{cadprincipal_id}', [\App\Http\Controllers\ApfdPessoaDetalheController::class, 'listarPorCadprincipal']);
    });

    // ✅ ROTAS DE USUÁRIOS (COMPLETAS)
    Route::middleware([\App\Http\Middleware\AdminAccess::class])->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/novo', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{id}/toggle', [UsuarioController::class, 'toggleAtivo'])->name('usuarios.toggle');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    // Rota para nova janela
    Route::get('/nova-janela', function () {
        return view('textenovajanela');
    })->name('textenovajanela');

    Route::post('/nova-janela', function (Request $request) {
        return 'Você enviou: ' . $request->input('texto');
    })->name('textenovajanela.salvar');
});
