<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Documentos\Pericias\GerarTraumatologicoIMLController;
use App\Http\Controllers\Documentos\Pericias\GerarTraumatologicoHospitalarController;
use App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaEmVeiculo_Controller;
use App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaEmLocalDeCrime_Controller;
use App\Http\Controllers\NumOficioController;

Route::middleware(['auth', 'permission:pericias'])->group(function () {

    // ✅ ROTA GET PARA VISUALIZAR O FORMULÁRIO
    Route::get('/termo-traumatologico-iml/{dados?}', [NumOficioController::class, 'gerarTermoTraumatologicoIML'])->name('termo.traumatologico.iml');
    Route::get('/pericia-traumatologico/{dados?}', [NumOficioController::class, 'gerarTermoTraumatologico'])->name('pericia.traumatologico');

    // ✅ ROTA POST PARA GERAR O PDF
    Route::post('/termo-traumatologico-iml', [GerarTraumatologicoIMLController::class, 'gerarPdfTraumatologicoIML'])->name('termo.traumatologico.iml.pdf');
    Route::post('/pericia-traumatologico', [GerarTraumatologicoHospitalarController::class, 'gerarPdf'])->name('pericia.traumatologico.pdf');

    // ==========================================
    // ✅ PERÍCIA EM VEÍCULO (NOVO MÓDULO)
    // ==========================================

    // Rota GET para visualizar o formulário (AGORA COM GERAÇÃO DE OFÍCIO AUTOMÁTICO)
    Route::match(['get', 'post'], '/pericia-em-veiculo/{dados?}', [NumOficioController::class, 'gerarPericiaEmVeiculo'])->name('pericia.veiculo');

    // Rota POST para gerar o PDF
    Route::post('/pericia-em-veiculo-pdf', [GerarPdf_PericiaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.veiculo.pdf');

    // ==========================================
    // ✅ PERÍCIA EM LOCAL DE CRIME (NOVO MÓDULO)
    // ==========================================

    // Rota GET para visualizar o formulário
    Route::get('/pericia-local-de-crime/{dados?}', [NumOficioController::class, 'gerarPericiaEmLocalDeCrime'])->name('pericia.local.crime');

    // Rota POST para gerar o PDF
    Route::post('/pericia-local-de-crime-pdf', [GerarPdf_PericiaEmLocalDeCrime_Controller::class, 'gerarPdf'])->name('pericia.local.crime.pdf');

    // ==========================================
    // ✅ NOVAS PERÍCIAS 
    // ==========================================

    Route::match(['get', 'post'], '/exame-arma-com-disparo/{dados?}', [NumOficioController::class, 'gerarExameEficienciaArmaComDisparo'])->name('exame.arma.com.disparo');
    Route::post('/exame-arma-com-disparo-pdf', [\App\Http\Controllers\Documentos\PecasController\GerarPdf_EficienciaArmaFogo_Controller::class, 'gerarPdfEficienciaArmaPortaria'])->name('exame.arma.com.disparo.pdf');
    Route::match(['get', 'post'], '/pericia-papiloscopica-veiculo/{dados?}', [NumOficioController::class, 'gerarPericiaPapiloscopicaEmVeiculo'])->name('pericia.papiloscopica.veiculo');
    Route::post('/pericia-papiloscopica-veiculo-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.papiloscopica.veiculo.pdf');

    Route::match(['get', 'post'], '/pericia-papiloscopica-local-crime/{dados?}', [NumOficioController::class, 'gerarPericiaPapiloscopicaEmLocalDeCrime'])->name('pericia.papiloscopica.local.crime');
    Route::post('/pericia-papiloscopica-local-crime-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.papiloscopica.local.crime.pdf');

    Route::match(['get', 'post'], '/pericia-papiloscopica-confrontacao/{dados?}', [NumOficioController::class, 'gerarPericiaPapiloscopicaConfrontacao'])->name('pericia.papiloscopica.confrontacao');
    Route::post('/pericia-papiloscopica-confrontacao-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.papiloscopica.confrontacao.pdf');

    Route::match(['get', 'post'], '/pericia-papiloscopica-pessoa/{dados?}', [NumOficioController::class, 'gerarPericiaPapiloscopicaEmPessoa'])->name('pericia.papiloscopica.pessoa');
    Route::post('/pericia-papiloscopica-pessoa-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.papiloscopica.pessoa.pdf');

    Route::match(['get', 'post'], '/pericia-papiloscopica-objeto/{dados?}', [NumOficioController::class, 'gerarPericiaPapiloscopicaEmObjeto'])->name('pericia.papiloscopica.objeto');
    Route::post('/pericia-papiloscopica-objeto-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.papiloscopica.objeto.pdf');

    // ==========================================
    // ✅ MÓDULO DE ENTORPECENTES 
    // ==========================================
    Route::match(['get', 'post'], '/exame-preliminar-entorpecentes/{dados?}', [NumOficioController::class, 'gerarExamePreliminarEntorpecentes'])->name('exame.preliminar.entorpecentes');
    Route::post('/exame-preliminar-entorpecentes-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('exame.preliminar.entorpecentes.pdf');

    Route::match(['get', 'post'], '/pericia-definitiva-entorpecentes/{dados?}', [NumOficioController::class, 'gerarPericiaDefinitivaEntorpecentes'])->name('pericia.definitiva.entorpecentes');
    Route::post('/pericia-definitiva-entorpecentes-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.definitiva.entorpecentes.pdf');
    // ==========================================
    // ✅ PERÍCIAS AVULSAS
    // ==========================================
    Route::match(['get', 'post'], '/pericia-arrombamento/{dados?}', [NumOficioController::class, 'gerarPericiaArrombamento'])->name('pericia.arrombamento');
    Route::post('/pericia-arrombamento-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.arrombamento.pdf');

    Route::match(['get', 'post'], '/oficio-prontuario-hospitalar/{dados?}', [NumOficioController::class, 'gerarOficioProntuarioHospitalar'])->name('oficio.prontuario.hospitalar');
    Route::post('/oficio-prontuario-hospitalar-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.prontuario.hospitalar.pdf');

    Route::match(['get', 'post'], '/pericia-documentoscopica/{dados?}', [NumOficioController::class, 'gerarPericiaDocumentoscopica'])->name('pericia.documentoscopica');
    Route::post('/pericia-documentoscopica-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.documentoscopica.pdf');

    Route::match(['get', 'post'], '/pericia-local/{dados?}', [NumOficioController::class, 'gerarPericiaLocal'])->name('pericia.local');
    Route::post('/pericia-local-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.local.pdf');

    Route::match(['get', 'post'], '/pericia-informatica/{dados?}', [NumOficioController::class, 'gerarPericiaInformatica'])->name('pericia.informatica');
    Route::post('/pericia-informatica-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('pericia.informatica.pdf');

    Route::match(['get', 'post'], '/termo-autorizacao-dados/{dados?}', [NumOficioController::class, 'gerarTermoAutorizacaoDados'])->name('termo.autorizacao.dados');
    Route::post('/termo-autorizacao-dados-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('termo.autorizacao.dados.pdf');

    Route::match(['get', 'post'], '/oficio-comunica-advogado/{dados?}', [NumOficioController::class, 'gerarOficioComunicaAdvogado'])->name('oficio.comunica.advogado');
    Route::post('/oficio-comunica-advogado-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.comunica.advogado.pdf');

    Route::match(['get', 'post'], '/oficio-encaminhar-veiculo/{dados?}', [NumOficioController::class, 'gerarOficioEncaminharVeiculo'])->name('oficio.encaminhar.veiculo');
    Route::post('/oficio-encaminhar-veiculo-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.encaminhar.veiculo.pdf');

    Route::match(['get', 'post'], '/certidao-midias-drive/{dados?}', [NumOficioController::class, 'gerarCertidaoMidiasDrive'])->name('certidao.midias.drive');
    Route::post('/certidao-midias-drive-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('certidao.midias.drive.pdf');

    Route::match(['get', 'post'], '/ci-remessa-procedimentos/{dados?}', [NumOficioController::class, 'gerarCIRemessaProcedimentos'])->name('ci.remessa.procedimentos');
    Route::post('/ci-remessa-procedimentos-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('ci.remessa.procedimentos.pdf');

    Route::match(['get', 'post'], '/ci-remessa-objetos-documentos/{dados?}', [NumOficioController::class, 'gerarCIRemessaObjetosDocumentos'])->name('ci.remessa.objetos.documentos');
    Route::post('/ci-remessa-objetos-documentos-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('ci.remessa.objetos.documentos.pdf');

    Route::match(['get', 'post'], '/ci-generica/{dados?}', [NumOficioController::class, 'gerarCIGenerica'])->name('ci.generica');
    Route::post('/ci-generica-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('ci.generica.pdf');

    Route::match(['get', 'post'], '/ci-encaminhamento-iitb/{dados?}', [NumOficioController::class, 'gerarCIEncaminhamentoIITB'])->name('ci.encaminhamento.iitb');
    Route::post('/ci-encaminhamento-iitb-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('ci.encaminhamento.iitb.pdf');

    Route::match(['get', 'post'], '/oficio-banco/{dados?}', [NumOficioController::class, 'gerarOficioBanco'])->name('oficio.banco');
    Route::post('/oficio-banco-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.banco.pdf');

    Route::match(['get', 'post'], '/oficio-certidao-obito/{dados?}', [NumOficioController::class, 'gerarOficioCertidaoObito'])->name('oficio.certidao.obito');
    Route::post('/oficio-certidao-obito-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.certidao.obito.pdf');

    Route::match(['get', 'post'], '/oficio-iml/{dados?}', [NumOficioController::class, 'gerarOficioIML'])->name('oficio.iml');
    Route::post('/oficio-iml-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.iml.pdf');

    Route::match(['get', 'post'], '/oficio-pm/{dados?}', [NumOficioController::class, 'gerarOficioPM'])->name('oficio.pm');
    Route::post('/oficio-pm-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.pm.pdf');

    Route::match(['get', 'post'], '/certidao-comparecimento/{dados?}', [NumOficioController::class, 'gerarCertidaoComparecimento'])->name('certidao.comparecimento');
    Route::post('/certidao-comparecimento-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('certidao.comparecimento.pdf');

    Route::match(['get', 'post'], '/certidao-comunica-familia-preso/{dados?}', [NumOficioController::class, 'gerarCertidaoComunicaFamiliaPreso'])->name('certidao.comunica.familia.preso');
    Route::post('/certidao-comunica-familia-preso-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('certidao.comunica.familia.preso.pdf');

    Route::match(['get', 'post'], '/recibo-preso/{dados?}', [NumOficioController::class, 'gerarReciboPreso'])->name('recibo.preso');
    Route::post('/recibo-preso-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('recibo.preso.pdf');

    Route::match(['get', 'post'], '/recibo-procedimento/{dados?}', [NumOficioController::class, 'gerarReciboProcedimento'])->name('recibo.procedimento');
    Route::post('/recibo-procedimento-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('recibo.procedimento.pdf');

    Route::match(['get', 'post'], '/auto-deposito-fiel/{dados?}', [NumOficioController::class, 'gerarAutoDepositoFiel'])->name('auto.deposito.fiel');
    Route::post('/auto-deposito-fiel-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('auto.deposito.fiel.pdf');

    Route::match(['get', 'post'], '/termo-guarda-veiculo/{dados?}', [NumOficioController::class, 'gerarTermoGuardaVeiculo'])->name('termo.guarda.veiculo');
    Route::post('/termo-guarda-veiculo-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('termo.guarda.veiculo.pdf');

    Route::match(['get', 'post'], '/termo-apreensao-novo/{dados?}', [NumOficioController::class, 'gerarTermoApreensao'])->name('termo.apreensao.novo');
    Route::post('/termo-apreensao-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('termo.apreensao.pdf');

    Route::match(['get', 'post'], '/capa-boc/{dados?}', [NumOficioController::class, 'gerarCapaBOC'])->name('capa.boc');
    Route::post('/capa-boc-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('capa.boc.pdf');

    Route::match(['get', 'post'], '/capa-tco/{dados?}', [NumOficioController::class, 'gerarCapaTCO'])->name('capa.tco');
    Route::post('/capa-tco-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('capa.tco.pdf');

    Route::match(['get', 'post'], '/ordem-servico-intimacao/{dados?}', [NumOficioController::class, 'gerarOrdemServicoIntimacao'])->name('ordem.servico.intimacao');
    Route::post('/ordem-servico-intimacao-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('ordem.servico.intimacao.pdf');

    Route::match(['get', 'post'], '/oficio-remessa/{dados?}', [NumOficioController::class, 'gerarOficioRemessa'])->name('oficio.remessa');
    Route::post('/oficio-remessa-pdf', [\App\Http\Controllers\Documentos\Pericias\GerarPdf_PericiaPapiloscopicaEmVeiculo_Controller::class, 'gerarPdf'])->name('oficio.remessa.pdf');

});
