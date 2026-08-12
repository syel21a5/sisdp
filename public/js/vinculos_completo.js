/**
 * VÍNCULOS COMPLETOS BOE - TODOS OS ENVOLVIDOS
 * VERSÃO CORRIGIDA - COM MENSAGENS DE SUCESSO PARA TODOS OS ENVOLVIDOS
 *
 * ✅ PONTOS IMPORTANTES APRENDIDOS:
 * 1. Cada tipo (vitima1, vitima2, testemunha1, etc) precisa de sua própria função de mensagem
 * 2. As mensagens devem ser chamadas tanto no sucesso quanto em casos de não encontrado
 * 3. Sempre usar classes CSS únicas para cada tipo (alert-vitima1, alert-vitima2, etc)
 * 4. Timing de 5 segundos para auto-fechar as mensagens
 * 5. Sempre remover mensagens anteriores antes de mostrar novas
 */

console.log('🚀 SISTEMA DE VÍNCULOS COMPLETOS CARREGADO!');

// ✅ ROTAS FIXAS PARA TODOS OS VÍNCULOS
const rotasVinculosCompletos = {
    buscarCondutor: "/boe/vinculos/buscar-condutor/",
    buscarVitima1: "/boe/vinculos/buscar-vitima1/",
    buscarTestemunha1: "/boe/vinculos/buscar-testemunha1/",
    buscarAutor1: "/boe/vinculos/buscar-autor1/",
    salvar: "/boe/vinculos/salvar"
};

// ✅ FUNÇÃO PADRÃO PARA TRATAR ERROS DE VÍNCULOS
function tratarErroVinculoCompleto(xhr, tipo, funcaoLimpar, funcaoMensagem) {
    console.log(`🔍 ERRO ${tipo.toUpperCase()} - Status: ${xhr.status}`);

    if (xhr.status === 404) {
        console.log(`ℹ️ Nenhum(a) ${tipo} vinculado(a) - situação normal`);
        funcaoLimpar();
        funcaoMensagem(`Nenhum(a) ${tipo} vinculado(a) a este BOE`, 'info');
    } else if (xhr.status === 500) {
        console.error(`❌ Erro interno no servidor ao buscar ${tipo}`);
        funcaoLimpar();
        funcaoMensagem(`Erro interno ao carregar ${tipo}`, 'warning');
    } else {
        console.error(`❌ Erro ${xhr.status} ao buscar ${tipo}:`, xhr);
        funcaoLimpar();
        funcaoMensagem(`Erro ao carregar ${tipo}`, 'error');
    }
}

// =============================================
// ✅ VÍTIMA 1 - CORRIGIDO (JÁ FUNCIONAVA)
// =============================================

window.carregarVitima1Vinculada = function (boe) {
    if (!boe || boe === 'N/A') {
        console.log('ℹ️ BOE vazio ou inválido, ignorando carregamento da vítima1');
        return;
    }

    console.log('🔍 BUSCANDO VITIMA1 VINCULADA AO BOE:', boe);
    $('#aba-vitima').addClass('loading');

    $.ajax({
        url: rotasVinculosCompletos.buscarVitima1 + encodeURIComponent(boe),
        method: 'GET',
        success: function (response) {
            console.log('📥 RESPOSTA VITIMA1:', response);

            if (response.success && response.data) {
                console.log('✅ VITIMA1 ENCONTRADA:', response.data.Nome);
                // ✅ USA A FUNÇÃO DO script_vitima1.js
                if (window.preencherVitima1Vinculada) {
                    window.preencherVitima1Vinculada(response.data);
                }
                mostrarMensagemVitima1('Vítima1 carregada automaticamente: ' + response.data.Nome, 'success');
            } else {
                console.log('ℹ️ Nenhuma vítima1 vinculada encontrada');
                mostrarMensagemVitima1('Nenhuma vítima1 vinculada a este BOE', 'info');
            }
        },
        error: function (xhr) {
            tratarErroVinculoCompleto(xhr, 'vítima1', function () {
                // Não limpa o formulário - deixa para o script_vitima1.js
            }, mostrarMensagemVitima1);
        },
        complete: function () {
            $('#aba-vitima').removeClass('loading');
        }
    });
}

// ✅ FUNÇÃO AUXILIAR APENAS PARA MENSAGENS DA VITIMA1
function mostrarMensagemVitima1(mensagem, tipo = 'info') {
    const cores = { success: 'alert-success', error: 'alert-danger', info: 'alert-info', warning: 'alert-warning' };
    $('.alert-vitima1').remove();

    const alerta = $(`
        <div class="alert ${cores[tipo]} alert-dismissible fade show alert-vitima1 mt-3" role="alert">
            <strong>${tipo.toUpperCase()}:</strong> ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('#formVitima1').prepend(alerta);
    setTimeout(() => { alerta.alert('close'); }, 1000);
}

// =============================================
// ✅ VÍTIMA 2 & 3 - REMOVIDO
// =============================================




// =============================================
// ✅ TESTEMUNHA 1 - CORRIGIDO (AGORA COM MENSAGENS)
// =============================================

window.carregarTestemunha1Vinculada = function (boe) {
    if (!boe || boe === 'N/A') {
        console.log('ℹ️ BOE vazio ou inválido, ignorando carregamento da testemunha1');
        return;
    }

    console.log('🔍 BUSCANDO TESTEMUNHA1 VINCULADA AO BOE:', boe);

    $.ajax({
        url: rotasVinculosCompletos.buscarTestemunha1 + encodeURIComponent(boe),
        method: 'GET',
        success: function (response) {
            if (response.success && response.data) {
                console.log('✅ TESTEMUNHA1 ENCONTRADA:', response.data.Nome);
                if (window.preencherTestemunha1Vinculada) {
                    window.preencherTestemunha1Vinculada(response.data);
                }
                // ✅ AGORA COM MENSAGEM DE SUCESSO
                mostrarMensagemTestemunha1('Testemunha1 carregada automaticamente: ' + response.data.Nome, 'success');
            } else {
                console.log('ℹ️ Nenhuma testemunha1 vinculada encontrada');
                // ✅ MENSAGEM INFORMATIVA QUANDO NÃO ENCONTRADA
                mostrarMensagemTestemunha1('Nenhuma testemunha1 vinculada a este BOE', 'info');
            }
        },
        error: function (xhr) {
            console.log('ℹ️ Nenhuma testemunha1 vinculada');
            // ✅ MENSAGEM INFORMATIVA EM CASO DE ERRO
            mostrarMensagemTestemunha1('Nenhuma testemunha1 vinculada a este BOE', 'info');
        }
    });
}

// ✅ FUNÇÃO AUXILIAR PARA MENSAGENS DA TESTEMUNHA1 (NOVA)
function mostrarMensagemTestemunha1(mensagem, tipo = 'info') {
    const cores = { success: 'alert-success', error: 'alert-danger', info: 'alert-info', warning: 'alert-warning' };
    $('.alert-testemunha1').remove();

    const alerta = $(`
        <div class="alert ${cores[tipo]} alert-dismissible fade show alert-testemunha1 mt-3" role="alert">
            <strong>${tipo.toUpperCase()}:</strong> ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('#formTestemunha1').prepend(alerta);
    setTimeout(() => { alerta.alert('close'); }, 1000);
}


// =============================================
// ✅ TESTEMUNHA 2 & 3 - REMOVIDO
// =============================================





// =============================================
// ✅ AUTOR 1 - CORRIGIDO (AGORA COM MENSAGENS)
// =============================================

window.carregarAutor1Vinculado = function (boe) {
    if (!boe || boe === 'N/A') {
        console.log('ℹ️ BOE vazio ou inválido, ignorando carregamento do autor1');
        return;
    }

    console.log('🔍 BUSCANDO AUTOR1 VINCULADO AO BOE:', boe);

    $.ajax({
        url: rotasVinculosCompletos.buscarAutor1 + encodeURIComponent(boe),
        method: 'GET',
        success: function (response) {
            if (response.success && response.data) {
                console.log('✅ AUTOR1 ENCONTRADO:', response.data.Nome);
                if (window.preencherAutor1Vinculada) {
                    window.preencherAutor1Vinculada(response.data);
                }
                // ✅ AGORA COM MENSAGEM DE SUCESSO
                mostrarMensagemAutor1('Autor1 carregado automaticamente: ' + response.data.Nome, 'success');
            } else {
                console.log('ℹ️ Nenhum autor1 vinculado encontrado');
                // ✅ MENSAGEM INFORMATIVA QUANDO NÃO ENCONTRADO
                mostrarMensagemAutor1('Nenhum autor1 vinculado a este BOE', 'info');
            }
        },
        error: function (xhr) {
            console.log('ℹ️ Nenhum autor1 vinculado');
            // ✅ MENSAGEM INFORMATIVA EM CASO DE ERRO
            mostrarMensagemAutor1('Nenhum autor1 vinculado a este BOE', 'info');
        }
    });
}

// ✅ FUNÇÃO AUXILIAR PARA MENSAGENS DO AUTOR1 (NOVA)
function mostrarMensagemAutor1(mensagem, tipo = 'info') {
    const cores = { success: 'alert-success', error: 'alert-danger', info: 'alert-info', warning: 'alert-warning' };
    $('.alert-autor1').remove();

    const alerta = $(`
        <div class="alert ${cores[tipo]} alert-dismissible fade show alert-autor1 mt-3" role="alert">
            <strong>${tipo.toUpperCase()}:</strong> ${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('#formAutor1').prepend(alerta);
    setTimeout(() => { alerta.alert('close'); }, 1000);
}


// =============================================
// ✅ AUTOR 2 & 3 - REMOVIDO
// =============================================




// =============================================
// ✅ FUNÇÕES DE SALVAR VÍNCULOS
// =============================================

function salvarVinculoBOE(tipo, id) {
    const boe = $('#inputBOE').val().trim();

    if (!boe || boe === 'N/A' || !id) {
        console.log('⚠️ Dados insuficientes para salvar vínculo ' + tipo);
        return;
    }

    console.log('💾 SALVANDO VÍNCULO BOE-' + tipo.toUpperCase() + ':', { boe, id });

    const data = { boe: boe, _token: $('meta[name="csrf-token"]').attr('content') };
    data[tipo + '_id'] = id;

    $.ajax({
        url: rotasVinculosCompletos.salvar,
        method: 'POST',
        data: data,
        success: function (response) {
            if (response.success) {
                console.log('✅ VÍNCULO ' + tipo.toUpperCase() + ' SALVO COM SUCESSO!');
            } else {
                console.error('❌ ERRO AO SALVAR VÍNCULO ' + tipo.toUpperCase() + ':', response.message);
            }
        },
        error: function (xhr) {
            console.error('❌ ERRO AJAX AO SALVAR VÍNCULO ' + tipo.toUpperCase() + ':', xhr.responseJSON);
        }
    });
}

// =============================================
// ✅ CONFIGURAÇÃO DE EVENT LISTENERS
// =============================================

$(document).ready(function () {
    console.log('🎯 SISTEMA DE VÍNCULOS COMPLETOS CONFIGURADO!');

    // Event listeners para salvar vínculos ao salvar formulários
    $(document).on('click', '#btnSalvarTestemunha1', function () {
        setTimeout(() => salvarVinculoBOE('testemunha1', $('#testemunha1_id').val()), 500);
    });

    $(document).on('click', '#btnSalvarAutor1', function () {
        setTimeout(() => salvarVinculoBOE('autor1', $('#autor1_id').val()), 500);
    });

    // Event listeners para limpar IDs ao limpar formulários
    $(document).on('click', '#btnLimparTestemunha1', function () { $('#testemunha1_id').val(''); });
    $(document).on('click', '#btnLimparAutor1', function () { $('#autor1_id').val(''); });
});

// =============================================
// ✅ CSS PARA LOADING
// =============================================

$('<style>')
    .prop('type', 'text/css')
    .html(`
        #aba-vitima.loading,
        #aba-testemunha.loading,
        #aba-autor.loading {
            position: relative;
            pointer-events: none;
        }
        #aba-vitima.loading::after {
            content: "Carregando vítimas...";
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
        #aba-testemunha.loading::after {
            content: "Carregando testemunhas...";
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
        #aba-autor.loading::after {
            content: "Carregando autores...";
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
