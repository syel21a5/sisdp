/**
 * SISTEMA DE VÍNCULOS DINÂMICOS (APFD)
 * 
 * Este script substitui a lógica antiga de colunas fixas (vitima1, vitima2...)
 * por uma lógica dinâmica baseada em linhas na tabela boe_pessoas_vinculos.
 * 
 * Funcionalidade:
 * 1. Carrega todos os vínculos do BOE.
 * 2. Preenche os formulários visuais existentes (vitima1, vitima2...) sequencialmente.
 * 3. Ao salvar, usa a API de adicionarVinculo.
 * 4. Ao excluir, remove o vínculo específico.
 */

console.log('🚀 SISTEMA DE VÍNCULOS DINÂMICOS INICIADO!');

const API_VINCULOS = {
    listar: '/boe/vinculos/listar/', // + boe
    adicionar: '/boe/vinculos/adicionar',
    remover: '/boe/vinculos/remover/' // + id
};

// Estado global dos vínculos carregados
window.estadoVinculos = {
    condutor: [],
    vitimas: [],
    autores: [],
    testemunhas: []
};

/**
 * Carrega todos os vínculos do BOE e distribui na tela
 */
window.carregarVinculosDinamicamente = function (boe) {
    if (!boe || boe === 'N/A') return;

    console.log('🔄 Carregando vínculos dinâmicos para BOE:', boe);

    $.ajax({
        url: API_VINCULOS.listar + encodeURIComponent(boe),
        method: 'GET',
        success: function (response) {
            if (response.success) {
                window.estadoVinculos = response.data;
                distribuirDadosNaTela();
                mostrarMensagemGeral('Vínculos carregados com sucesso!', 'success');
            }
        },
        error: function (xhr) {
            console.error('Erro ao listar vínculos:', xhr);
            mostrarMensagemGeral('Erro ao carregar vínculos.', 'error');
        }
    });
};

/**
 * Pega os dados do estado global e preenche os formulários legados (1, 2, 3...)
 */
function distribuirDadosNaTela() {
    // Limpa os extras antes de redistribuir
    $('#listaVinculosExtras').empty();
    $('#containerVinculosExtras').remove(); // Remove o container se estiver vazio ou para recriar

    // Condutor
    if (window.estadoVinculos.condutor.length > 0) {
        preencherFormularioLegado('condutor', null, window.estadoVinculos.condutor[0]);
    }

    // Vítimas
    window.estadoVinculos.vitimas.forEach((pessoa, index) => {
        const slot = index + 1;
        if (slot <= 3) { // Limite visual atual
            preencherFormularioLegado('vitima', slot, pessoa);
        } else {
            console.warn('⚠️ Mais vítimas do que slots visuais:', pessoa);
            adicionarAvisoExtra('Vítima', pessoa);
        }
    });

    // Autores
    window.estadoVinculos.autores.forEach((pessoa, index) => {
        const slot = index + 1;
        if (slot <= 3) {
            preencherFormularioLegado('autor', slot, pessoa);
        } else {
            adicionarAvisoExtra('Autor', pessoa);
        }
    });

    // Testemunhas
    window.estadoVinculos.testemunhas.forEach((pessoa, index) => {
        const slot = index + 1;
        if (slot <= 3) {
            preencherFormularioLegado('testemunha', slot, pessoa);
        } else {
            adicionarAvisoExtra('Testemunha', pessoa);
        }
    });
}

/**
 * Preenche um formulário específico (ex: vitima2) com dados
 */
function preencherFormularioLegado(tipo, numero, dados) {
    const sufixo = numero ? numero : '';
    const nomeTipo = tipo + sufixo; // ex: vitima1

    console.log(`📝 Preenchendo ${nomeTipo} com:`, dados.Nome);

    // Armazena o ID do vínculo no botão de excluir ou em um hidden, se existir
    // Como o HTML é legado, vamos tentar achar um lugar ou usar atributo data
    $(`#btnLimpar${capitalize(nomeTipo)}`).data('vinculo-id', dados.vinculo_id);

    // Chama a função global de preenchimento existente (do script original de cada tipo)
    // Ex: window.preencherVitima1Vinculada
    const funcNome = `preencher${capitalize(tipo)}${sufixo}Vinculada`;
    if (window[funcNome]) {
        window[funcNome](dados);
    } else {
        console.warn(`Função ${funcNome} não encontrada.`);
    }
}

/**
 * Salva um vínculo (chamado pelos botões de salvar)
 */
window.salvarVinculoDinamico = function (tipo, numero, pessoaId) {
    const boe = $('#inputBOE').val();
    if (!boe || !pessoaId) return;

    const payload = {
        boe: boe,
        pessoa_id: pessoaId,
        tipo: tipo.toUpperCase() // VITIMA, AUTOR...
    };

    $.ajax({
        url: API_VINCULOS.adicionar,
        method: 'POST',
        data: { ...payload, _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            if (response.success) {
                mostrarMensagemGeral('Vínculo salvo com sucesso!', 'success');
                // Recarrega para atualizar IDs de vínculo
                carregarVinculosDinamicamente(boe);
            } else {
                mostrarMensagemGeral(response.message, 'warning');
            }
        },
        error: function () {
            mostrarMensagemGeral('Erro ao salvar vínculo.', 'error');
        }
    });
};

/**
 * Remove um vínculo
 */
window.removerVinculoDinamico = function (vinculoId) {
    if (!vinculoId) return;

    if (!confirm('Deseja remover este vínculo extra?')) return;

    $.ajax({
        url: API_VINCULOS.remover + vinculoId,
        method: 'DELETE',
        data: { _token: $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            if (response.success) {
                mostrarMensagemGeral('Vínculo removido.', 'success');
                // Recarrega para atualizar a lista
                const boe = $('#inputBOE').val();
                carregarVinculosDinamicamente(boe);
            }
        },
        error: function () {
            mostrarMensagemGeral('Erro ao remover vínculo.', 'error');
        }
    });
};

// Utilitários
function capitalize(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function mostrarMensagemGeral(msg, tipo) {
    // Implementação simples de alert ou toast
    // alert(`${tipo.toUpperCase()}: ${msg}`);
    console.log(`[${tipo.toUpperCase()}] ${msg}`);
}

// Inicialização
$(document).ready(function () {
    console.log('✅ Script de vínculos dinâmicos pronto.');
    // Não adicionamos listeners de salvar aqui para evitar conflito com scripts legados.
    // Os scripts legados (script_vitima1.js, etc) chamam /boe/vinculos/salvar
    // que o backend já trata corretamente salvando na nova tabela.
});

function adicionarAvisoExtra(tipo, pessoa) {
    let container = $('#containerVinculosExtras');
    if (container.length === 0) {
        // Cria container se não existir
        $('.tab-content').first().after(`
            <div id="containerVinculosExtras" class="container mt-4">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Outros Envolvidos (Excedente Visual)</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="listaVinculosExtras"></ul>
                    </div>
                </div>
            </div>
        `);
        container = $('#containerVinculosExtras');
    }

    $('#listaVinculosExtras').append(`
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong>${tipo}:</strong> ${pessoa.Nome} (CPF: ${pessoa.CPF || 'N/A'})
            </div>
            <button class="btn btn-sm btn-danger" onclick="removerVinculoDinamico(${pessoa.vinculo_id})">
                <i class="bi bi-trash"></i> Remover
            </button>
        </li>
    `);
}
