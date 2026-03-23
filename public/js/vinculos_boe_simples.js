// vinculos_boe_simples.js - VERSÃO CORRIGIDA PARA AMBIENTES
console.log('🚀 Sistema de vínculos BOE carregado!');

// ✅ ROTAS FIXAS (SEM BLADE) - SIMPLES
const rotasVinculos = {
    buscarCondutor: "/boe/vinculos/buscar-condutor/",
    buscarVitima1: "/boe/vinculos/buscar-vitima1/",
    buscarTestemunha1: "/boe/vinculos/buscar-testemunha1/",
    buscarAutor1: "/boe/vinculos/buscar-autor1/",
    salvar: "/boe/vinculos/salvar"
};

const timingVinculos = {
    condutor: 0,        // Início imediato
    vitima1: 100,       // +100ms
    testemunha1: 200,   // +200ms
    autor1: 300         // +300ms
};

// ✅ FUNÇÃO PADRÃO PARA TRATAR ERROS DE VÍNCULOS
function tratarErroVinculo(xhr, tipo, funcaoLimpar, funcaoMensagem) {
    if (xhr.status === 404) {
        console.log(`ℹ️ [SIMPLES] Nenhum(a) ${tipo} vinculado(a) encontrado(a) (404)`);
        funcaoLimpar();
        funcaoMensagem(`Nenhum(a) ${tipo} vinculado(a) a este BOE`, 'info');
    } else {
        console.error(`❌ [SIMPLES] ERRO AO BUSCAR ${tipo.toUpperCase()}:`, xhr.responseJSON);
        funcaoLimpar();
        funcaoMensagem(`Erro ao carregar ${tipo} vinculado(a)`, 'error');
    }
}

// ✅ FUNÇÃO GLOBAL SIMPLES PARA CARREGAR CONDUTOR
window.carregarCondutorVinculado = function (boe) {
    if (!boe || boe === 'N/A') {
        console.log('⚠️ BOE vazio ou inválido, ignorando carregamento do condutor');
        return;
    }

    console.log('🔍 [SIMPLES] Buscando condutor para BOE:', boe);

    // ✅ MUDAR PARA ABA DO CONDUTOR PRIMEIRO
    $('#abasPrincipais a[href="#aba-condutor"]').tab('show');

    // Mostrar loading
    $('#aba-condutor').addClass('loading');

    $.ajax({
        url: rotasVinculos.buscarCondutor + encodeURIComponent(boe),
        method: 'GET',
        success: function (response) {
            console.log('📥 [SIMPLES] Resposta condutor:', response);

            if (response.success && response.data) {
                console.log('✅ [SIMPLES] Condutor encontrado:', response.data.Nome);

                // Preencher formulário
                $('#condutor_id').val(response.data.IdCad || '');
                $('#inputNomeCondutor').val(response.data.Nome || '');
                $('#inputAlcunha').val(response.data.Alcunha || '');

                // Data de nascimento
                if (response.data.Nascimento) {
                    const dataNasc = new Date(response.data.Nascimento);
                    if (!isNaN(dataNasc.getTime())) {
                        $('#inputDataNascimento').val(dataNasc.toLocaleDateString('pt-BR'));

                        // Calcular idade
                        const hoje = new Date();
                        const idade = hoje.getFullYear() - dataNasc.getFullYear();
                        const mesAtual = hoje.getMonth();
                        const mesNasc = dataNasc.getMonth();

                        if (mesAtual < mesNasc || (mesAtual === mesNasc && hoje.getDate() < dataNasc.getDate())) {
                            $('#inputIdade').val(idade - 1);
                        } else {
                            $('#inputIdade').val(idade);
                        }
                    }
                }

                $('#inputEstadoCivil').val(response.data.EstCivil || '');
                $('#inputNaturalidade').val(response.data.Naturalidade || '');
                $('#inputInstrucao').val(response.data.Instrucao || '');
                $('#inputRG').val(response.data.RG || '');
                $('#inputCPF').val(response.data.CPF || '');
                $('#inputTelefone').val(response.data.Telefone || '');
                $('#inputProfissao').val(response.data.Profissao || '');
                $('#inputMae').val(response.data.Mae || '');
                $('#inputPai').val(response.data.Pai || '');
                $('#inputEndereco').val(response.data.Endereco || '');

                // Habilitar botões
                $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

                console.log('✅ [SIMPLES] Formulário do condutor preenchido!');

                // Mostrar mensagem de sucesso
                mostrarMensagemCondutor('Condutor carregado automaticamente: ' + response.data.Nome, 'success');

                // ✅ SALVAR VÍNCULO (se necessário)
                salvarVinculoCondutor(boe, response.data.IdCad);
            } else {
                console.log('ℹ️ [SIMPLES] Nenhum condutor vinculado encontrado');
                limparFormularioCondutor();
                mostrarMensagemCondutor('Nenhum condutor vinculado a este BOE', 'info');
            }
        },
        error: function (xhr) {
            // ✅ TRATAMENTO CORRETO PARA AMBOS OS AMBIENTES
            tratarErroVinculo(xhr, 'condutor', limparFormularioCondutor, mostrarMensagemCondutor);
        },
        complete: function () {
            $('#aba-condutor').removeClass('loading');

            // ✅ VOLTAR PARA ABA INICIAL APÓS 1 SEGUNDO
            setTimeout(() => {
                $('#abasPrincipais a[href="#aba-inicio"]').tab('show');
                console.log('🔄 [SIMPLES] Voltando para aba inicial');
            }, 1000);
        }
    });
};

// ✅ FUNÇÃO PARA SALVAR VÍNCULO
function salvarVinculoCondutor(boe, condutorId) {
    if (!boe || boe === 'N/A' || !condutorId) {
        console.log('⚠️ Dados insuficientes para salvar vínculo condutor');
        return;
    }

    console.log('💾 [SIMPLES] Salvando vínculo condutor:', { boe, condutorId });

    $.ajax({
        url: rotasVinculos.salvar,
        method: 'POST',
        data: {
            boe: boe,
            condutor_id: condutorId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success) {
                console.log('✅ [SIMPLES] Vínculo condutor salvo com sucesso!');
            } else {
                console.error('❌ [SIMPLES] Erro ao salvar vínculo condutor:', response.message);
            }
        },
        error: function (xhr) {
            console.error('❌ [SIMPLES] Erro AJAX ao salvar vínculo condutor:', xhr.responseJSON);
        }
    });
}

// ✅ FUNÇÃO PARA LIMPAR FORMULÁRIO DO CONDUTOR
function limparFormularioCondutor() {
    console.log('🔄 [SIMPLES] Limpando formulário do condutor');
    $('#condutor_id').val('');
    const form = $('#formCondutor')[0];
    if (form && typeof form.reset === 'function') {
        form.reset();
    } else {
        $('#inputNomeCondutor').val('');
        $('#inputAlcunha').val('');
        $('#inputDataNascimento').val('');
        $('#inputNaturalidade').val('');
        $('#inputInstrucao').val('');
        $('#inputRG').val('');
        $('#inputCPF').val('');
        $('#inputTelefone').val('');
        $('#inputProfissao').val('');
        $('#inputMae').val('');
        $('#inputPai').val('');
        $('#inputEndereco').val('');
    }
    $('#inputIdade').val('');
    $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', true);
}

// ✅ FUNÇÃO PARA MOSTRAR MENSAGENS (CONDUTOR)
function mostrarMensagemCondutor(mensagem, tipo = 'info') {
    const cores = {
        success: 'alert-success',
        error: 'alert-danger',
        info: 'alert-info',
        warning: 'alert-warning'
    };

    // Remover mensagens anteriores
    $('.alert-condutor').remove();

    // Criar nova mensagem
    const alerta = $(`
        <div class="alert ${cores[tipo]} alert-dismissible fade show alert-condutor mt-3" role="alert">
            <strong>${tipo.toUpperCase()}:</strong> ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('#formCondutor').prepend(alerta);

    // Auto-remover após 5 segundos
    setTimeout(() => {
        alerta.alert('close');
    }, 1000);
}

// ✅ ATUALIZAR A FUNÇÃO DE INTERCEPTAÇÃO COM TIMING PADRONIZADO
$(document).ready(function () {
    console.log('🎯 [SIMPLES] Sistema de vínculos configurado e pronto!');


});

// ✅ CSS para loading
$('<style>')
    .prop('type', 'text/css')
    .html(`
        #aba-condutor.loading {
            position: relative;
            pointer-events: none;
        }
        #aba-condutor.loading::after {
            content: "Carregando condutor...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            font-weight: bold;
            color: #000;
            border: 1px solid #ccc;
        }
    `)
    .appendTo('head');
