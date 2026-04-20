/**
 * SISTEMA DE CHIPS PARA MÚLTIPLOS ENVOLVIDOS
 * Permite adicionar várias vítimas, autores, testemunhas e condutores
 * usando chips azuis estilo Bootstrap
 */

console.log('🎯 Sistema de Chips de Envolvidos carregado!');

// Arrays para armazenar os envolvidos
window.envolvidosChips = {
    vitimas: [],
    autores: [],
    testemunhas: [],
    condutores: [],
    outros: []
};

// =============================================
// FUNÇÕES PARA ADICIONAR CHIPS
// =============================================

/**
 * ✅ COLABORAÇÃO EM TEMPO REAL:
 * Quando o usuário NÃO é dono, envia automaticamente uma sugestão ao servidor.
 * O chip aparece em LARANJA (pendente) para o dono validar.
 */
function sugerirVinculoEmTempoReal(nome, tipoPlural) {
    const app = window.OcorrenciasApp;
    if (!app || app.isOwner !== false) return; // Só para colaboradores

    const boe = $('#inputBOE').val();
    if (!boe) return;

    const tipoMap = {
        vitimas: 'VITIMA', autores: 'AUTOR',
        testemunhas: 'TESTEMUNHA', condutores: 'CONDUTOR', outros: 'OUTRO'
    };
    const tipo = tipoMap[tipoPlural] || 'OUTRO';

    $.ajax({
        url: '/boe/vinculos/sugerir',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { boe, nome, tipo },
        success: (resp) => {
            if (resp.success) {
                const idx = (app.envolvidos[tipoPlural] || []).indexOf(nome);
                if (idx >= 0) {
                    app.vinculos[tipoPlural] = app.vinculos[tipoPlural] || [];
                    app.vinculos[tipoPlural][idx] = {
                        nome: nome,
                        pessoa_id: resp.pessoa_id,
                        vinculo_id: resp.vinculo_id,
                        status_aprovacao: 'pendente',
                        criado_por_nome: null
                    };
                    app.atualizarChips(tipoPlural);
                }
                console.log(`✅ [Colaboração] Sugestão enviada: ${nome} (${tipo}) no BOE ${boe}`);
            } else {
                console.warn('[Colaboração] Sugestão rejeitada pelo servidor:', resp.message);
            }
        },
        error: (xhr) => {
            console.error('[Colaboração] Erro ao sugerir:', xhr.responseJSON?.message || 'Erro desconhecido');
        }
    });
}

// ✅ NOVA FUNÇÃO: Limpar campos do formulário sem disparar eventos destrutivos
window.limparCamposEnvolvido = function(tipo) {
    console.log(`🧹 Limpando campos de: ${tipo}`);
    const seletores = {
        vitimas: ['#inputNomeVitima1', '#vitima1_id', '#inputAlcunhaVitima1', '#inputRGVitima1', '#inputCPFVitima1', '#formVitima1'],
        autores: ['#inputNomeAutor1', '#autor1_id', '#inputAlcunhaAutor1', '#inputRGAutor1', '#inputCPFAutor1', '#formAutor1'],
        testemunhas: ['#inputNomeTestemunha1', '#testemunha1_id', '#inputAlcunhaTestemunha1', '#inputRGTestemunha1', '#inputCPFTestemunha1', '#formTestemunha1'],
        condutores: ['#inputNomeCondutor', '#condutor_id', '#inputAlcunha', '#inputRG', '#inputCPF', '#inputDataNascimento', '#inputIdade', '#formCondutor'],
        outros: ['#inputNomeOutro', '#outro_id', '#inputDescricaoOutro', '#formOutro']
    };

    const ids = seletores[tipo];
    if (ids) {
        ids.forEach(s => {
            const el = $(s);
            if (el.is('form')) {
                try { el[0].reset(); } catch(e) {}
            } else {
                el.val('');
            }
        });
    }
    
    // Remove classes de validação se existirem
    $(`.is-valid, .is-invalid`).removeClass('is-valid is-invalid');
};

/**
 * Adiciona uma vítima como chip

 */
window.salvarVinculoAutomatico = function(tipoEnum, tipoArray, pessoa_id, nome) {
    const boeSelecionado = $('#inputBOE').val() ? $('#inputBOE').val().trim() : null;

    if (!window.OcorrenciasApp.envolvidos[tipoArray].includes(nome)) {
        window.OcorrenciasApp.envolvidos[tipoArray].push(nome);
        window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
        window.OcorrenciasApp.vinculos[tipoArray] = window.OcorrenciasApp.vinculos[tipoArray] || [];
        
        // Inserção temporária (otimista) no frontend
        const statusInicial = window.OcorrenciasApp.isOwner === false ? 'pendente' : 'aprovado';
        window.OcorrenciasApp.vinculos[tipoArray].push({ 
            nome: nome, 
            pessoa_id: pessoa_id,
            status_aprovacao: statusInicial 
        });

        // SALVAMENTO IMEDIATO no banco de dados!
        if (boeSelecionado && boeSelecionado !== 'N/A' && pessoa_id && parseInt(pessoa_id) < 1000000000000) {
            console.log('🔄 Salvando vínculo automaticamente do botão + Add...', tipoEnum);
            $.ajax({
                url: '/boe/vinculos/adicionar',
                method: 'POST',
                data: {
                    boe: boeSelecionado,
                    pessoa_id: pessoa_id,
                    tipo: tipoEnum,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        console.log('✅ Vínculo salvo automático. Recarregando vinculações...');
                        window.OcorrenciasApp.carregarVinculosDoBoe(boeSelecionado);
                    }
                }
            });
        }
    }
    window.OcorrenciasApp.atualizarChips(tipoArray);
    sugerirVinculoEmTempoReal(nome, tipoArray);
};

window.adicionarVitimaChip = function () {
    const nome = $('#inputNomeVitima1').val().trim();
    const id = $('#vitima1_id').val();

    if (!nome) {
        window.mostrarAlerta('Por favor, preencha o nome da vítima');
        return;
    }

    // Verifica se já existe
    if (window.envolvidosChips.vitimas.find(v => v.nome === nome)) {
        window.mostrarAlerta('Esta vítima já foi adicionada');
        return;
    }

    // Adiciona ao array
    const vitima = {
        id: id || Date.now(),
        nome: nome,
        alcunha: $('#inputAlcunhaVitima1').val(),
        rg: $('#inputRGVitima1').val(),
        cpf: $('#inputCPFVitima1').val(),
        dados: $('#formVitima1').serializeArray()
    };

    window.envolvidosChips.vitimas.push(vitima);

    // Sincroniza com OcorrenciasApp e salva vínculo automaticamente se tiver ID de banco
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        window.salvarVinculoAutomatico('VITIMA', 'vitimas', id, vitima.nome);
    } else {
        // Fallback
        criarChip('vitima', vitima.nome, vitima.id);
    }

        // ✅ CORREÇÃO: Limpa o formulário sem disparar o botão de exclusão legado
        window.limparCamposEnvolvido('vitimas');

    // Mensagem de sucesso
    window.mostrarSucesso('Vítima adicionada com sucesso!');
    if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
        window.ocTabs.closeTab('tab-vitima', 'tabLinkVitima');
    }
};

/**
 * Adiciona um autor como chip
 */
window.adicionarAutorChip = function () {
    const nome = $('#inputNomeAutor1').val().trim();
    const id = $('#autor1_id').val();

    if (!nome) {
        window.mostrarAlerta('Por favor, preencha o nome do autor');
        return;
    }

    // Verifica se já existe
    if (window.envolvidosChips.autores.find(a => a.nome === nome)) {
        window.mostrarAlerta('Este autor já foi adicionado');
        return;
    }

    // Adiciona ao array
    const autor = {
        id: id || Date.now(),
        nome: nome,
        alcunha: $('#inputAlcunhaAutor1').val(),
        rg: $('#inputRGAutor1').val(),
        cpf: $('#inputCPFAutor1').val(),
        dados: $('#formAutor1').serializeArray()
    };

    window.envolvidosChips.autores.push(autor);

    // Sincroniza com OcorrenciasApp e salva vínculo automaticamente se tiver ID de banco
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        window.salvarVinculoAutomatico('AUTOR', 'autores', id, autor.nome);
    } else {
        // Fallback
        criarChip('autor', autor.nome, autor.id);
    }

    // ✅ CORREÇÃO: Limpeza não-destrutiva
    window.limparCamposEnvolvido('autores');

    // Mensagem de sucesso
    window.mostrarSucesso('Autor adicionado com sucesso!');
    if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
        window.ocTabs.closeTab('tab-autor', 'tabLinkAutor');
    }
};

/**
 * Adiciona uma testemunha como chip
 */
window.adicionarTestemunhaChip = function () {
    const nome = $('#inputNomeTestemunha1').val().trim();
    const id = $('#testemunha1_id').val();

    if (!nome) {
        window.mostrarAlerta('Por favor, preencha o nome da testemunha');
        return;
    }

    // Verifica se já existe
    if (window.envolvidosChips.testemunhas.find(t => t.nome === nome)) {
        window.mostrarAlerta('Esta testemunha já foi adicionada');
        return;
    }

    // Adiciona ao array
    const testemunha = {
        id: id || Date.now(),
        nome: nome,
        alcunha: $('#inputAlcunhaTestemunha1').val(),
        rg: $('#inputRGTestemunha1').val(),
        cpf: $('#inputCPFTestemunha1').val(),
        dados: $('#formTestemunha1').serializeArray()
    };

    // Sincroniza com OcorrenciasApp e salva vínculo automaticamente se tiver ID de banco
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        window.salvarVinculoAutomatico('TESTEMUNHA', 'testemunhas', id, testemunha.nome);
    } else {
        // Fallback
        criarChip('testemunha', testemunha.nome, testemunha.id);
    }

    // ✅ CORREÇÃO: Limpeza não-destrutiva
    window.limparCamposEnvolvido('testemunhas');

    // Mensagem de sucesso
    window.mostrarSucesso('Testemunha adicionada com sucesso!');
    if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
        window.ocTabs.closeTab('tab-testemunha', 'tabLinkTestemunha');
    }
};

/**
 * Adiciona um condutor como chip
 */
window.adicionarCondutorChip = function () {
    const nome = $('#inputNomeCondutor').val().trim();
    const id = $('#condutor_id').val();

    if (!nome) {
        window.mostrarAlerta('Por favor, preencha o nome do condutor');
        return;
    }

    // Verifica se já existe
    if (window.envolvidosChips.condutores.find(c => c.nome === nome)) {
        window.mostrarAlerta('Este condutor já foi adicionado');
        return;
    }

    // Adiciona ao array
    const condutor = {
        id: id || Date.now(),
        nome: nome,
        alcunha: $('#inputAlcunha').val(),
        rg: $('#inputRG').val(),
        cpf: $('#inputCPF').val(),
        dados: $('#formCondutor').serializeArray()
    };

    // Sincroniza com OcorrenciasApp e salva vínculo automaticamente se tiver ID de banco
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        window.salvarVinculoAutomatico('CONDUTOR', 'condutores', id, condutor.nome);
    } else {
        // Fallback
        criarChip('condutor', condutor.nome, condutor.id);
    }

    // ✅ CORREÇÃO: Limpeza não-destrutiva (substituindo o #btnLimparCondutor.click())
    window.limparCamposEnvolvido('condutores');

    // Mensagem de sucesso
    window.mostrarSucesso('Condutor adicionado com sucesso!');
    if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
        window.ocTabs.closeTab('tab-condutor', 'tabLinkCondutor');
    }
};

/**
 * Adiciona um OUTRO como chip
 */
window.adicionarOutroChip = function () {
    const nome = $('#inputNomeOutro').val().trim();
    const id = $('#outro_id').val();

    if (!nome) {
        window.mostrarAlerta('Por favor, preencha o nome do envolvido');
        return;
    }

    // Verifica se já existe
    if (window.envolvidosChips.outros.find(o => o.nome === nome)) {
        window.mostrarAlerta('Este envolvido já foi adicionado');
        return;
    }

    // Adiciona ao array
    const outro = {
        id: id || Date.now(),
        nome: nome,
        alcunha: $('#inputAlcunhaOutro').val(),
        rg: $('#inputRGOutro').val(),
        cpf: $('#inputCPFOutro').val(),
        dados: $('#formOutro').serializeArray()
    };

    window.envolvidosChips.outros.push(outro);

    // Sincroniza com OcorrenciasApp e salva vínculo automaticamente se tiver ID de banco
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        window.salvarVinculoAutomatico('OUTRO', 'outros', id, outro.nome);
    } else {
        // Fallback
        criarChip('outro', outro.nome, outro.id);
    }

    // ✅ CORREÇÃO: Limpeza não-destrutiva
    window.limparCamposEnvolvido('outros');

    // Mensagem de sucesso
    window.mostrarSucesso('Envolvido adicionado com sucesso!');
    if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
        window.ocTabs.closeTab('tab-outro', 'tabLinkOutro');
    }
};

// =============================================
// FUNÇÕES AUXILIARES
// =============================================

/**
 * Cria um chip visual
 */
function criarChip(tipo, nome, id) {
    // Verifica se o ID é temporário gerado pelo Date.now() (maior que 13 dígitos numéricos)
    const isTemp = (id && !isNaN(id) && parseInt(id) > 1000000000000);
    
    const bgClass = isTemp ? 'bg-warning text-dark' : 'bg-primary text-white';
    const alertIcon = isTemp ? '<i class="fas fa-exclamation-triangle text-danger me-1"></i>' : '';
    const spanText = isTemp ? `${nome.toUpperCase()} <span style="font-size:0.65rem; font-weight:bold; color: #d32f2f;">(NÃO SALVO)</span>` : nome.toUpperCase();
    const closeBtnClass = isTemp ? 'btn-close' : 'btn-close btn-close-white';
    const tooltipText = isTemp ? 'title="Este envolvido ainda não foi salvo. Clique para editar e salvá-lo no banco."' : '';

    const chip = $(`
        <div class="badge ${bgClass} d-inline-flex align-items-center gap-1 px-2 py-1 me-1 mb-1 shadow-sm border border-secondary" 
             data-tipo="${tipo}" data-id="${id}" style="font-size: 0.8rem; font-weight: 500;" ${tooltipText}>
            ${alertIcon}
            <span>${spanText}</span>
            <button type="button" class="${closeBtnClass}" 
                    style="font-size: 0.6rem; opacity: 0.8; margin-left: 5px;" 
                    onclick="removerChip('${tipo}', ${id})"></button>
            <button type="button" class="btn btn-sm btn-light border-0" 
                    style="padding: 0px 4px; font-size: 0.6rem; line-height: 1.2; opacity: 0.9;" 
                    onclick="editarChip('${tipo}', ${id})" 
                    title="Editar e Salvar">
                <i class="fas fa-pencil-alt"></i>
            </button>
        </div>
    `);

    // Adiciona no container apropriado
    const containers = {
        'vitima': '#chipsVitimas',
        'autor': '#chipsAutores',
        'testemunha': '#chipsTestemunhas',
        'condutor': '#chipsCondutores',
        'outro': '#chipsOutros'
    };

    $(containers[tipo]).append(chip);
}

window.ocChipConfirm = window.ocChipConfirm || null;

/**
 * Solicita confirmação para remover um chip
 */
window.removerChip = function (tipo, id) {
    window.ocChipConfirm = { source: 'chips', tipo, id };
    $('#modalConfirmacaoChip').modal('show');
};

/**
 * Edita um chip (abre o modal com os dados)
 */
window.editarChip = function (tipo, id) {
    // Busca os dados
    const arrays = {
        'vitima': window.envolvidosChips.vitimas,
        'autor': window.envolvidosChips.autores,
        'testemunha': window.envolvidosChips.testemunhas,
        'condutor': window.envolvidosChips.condutores,
        'outro': window.envolvidosChips.outros
    };

    const item = arrays[tipo].find(i => i.id == id);
    if (!item) return;

    // Preenche o formulário correspondente
    if (tipo === 'vitima') {
        item.dados.forEach(field => {
            let val = field.value;
            if (field.name.toLowerCase().includes('telefone') && (!val || val.trim() === '')) {
                val = '(00) 00000-0000';
            }
            const $el = $(`#formVitima1 [name="${field.name}"]`);
            $el.val(val);
            if (field.name.toLowerCase().includes('cpf') || field.name.toLowerCase().includes('telefone')) {
                $el.trigger('input');
            }
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-vitima', 'Vítima', 'tabLinkVitima');
        }
    } else if (tipo === 'autor') {
        item.dados.forEach(field => {
            let val = field.value;
            if (field.name.toLowerCase().includes('telefone') && (!val || val.trim() === '')) {
                val = '(00) 00000-0000';
            }
            const $el = $(`#formAutor1 [name="${field.name}"]`);
            $el.val(val);
            if (field.name.toLowerCase().includes('cpf') || field.name.toLowerCase().includes('telefone')) {
                $el.trigger('input');
            }
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-autor', 'Autor', 'tabLinkAutor');
        }
    } else if (tipo === 'testemunha') {
        item.dados.forEach(field => {
            let val = field.value;
            if (field.name.toLowerCase().includes('telefone') && (!val || val.trim() === '')) {
                val = '(00) 00000-0000';
            }
            const $el = $(`#formTestemunha1 [name="${field.name}"]`);
            $el.val(val);
            if (field.name.toLowerCase().includes('cpf') || field.name.toLowerCase().includes('telefone')) {
                $el.trigger('input');
            }
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-testemunha', 'Testemunha', 'tabLinkTestemunha');
        }
    } else if (tipo === 'condutor') {
        item.dados.forEach(field => {
            let val = field.value;
            if (field.name.toLowerCase().includes('telefone') && (!val || val.trim() === '')) {
                val = '(00) 00000-0000';
            }
            const $el = $(`#formCondutor [name="${field.name}"]`);
            $el.val(val);
            if (field.name.toLowerCase().includes('cpf') || field.name.toLowerCase().includes('telefone')) {
                $el.trigger('input');
            }
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-condutor', 'Condutor', 'tabLinkCondutor');
        }
    } else if (tipo === 'outro') {
        item.dados.forEach(field => {
            let val = field.value;
            if (field.name.toLowerCase().includes('telefone') && (!val || val.trim() === '')) {
                val = '(00) 00000-0000';
            }
            const $el = $(`#formOutro [name="${field.name}"]`);
            $el.val(val);
            if (field.name.toLowerCase().includes('cpf') || field.name.toLowerCase().includes('telefone')) {
                $el.trigger('input');
            }
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-outro', 'Outros', 'tabLinkOutro');
        }
    }
}


// =============================================
// EVENT LISTENERS
// =============================================

$(document).ready(function () {
    console.log('🎯 Configurando event listeners dos botões Add...');

    // Botão Add Vítima
    $(document).on('click', '#btnAddVitima1ToChip', function () {
        console.log('✅ Botão Add Vítima clicado');
        window.adicionarVitimaChip();
    });

    // Botão Add Autor
    $(document).on('click', '#btnAddAutor1ToChip', function () {
        console.log('✅ Botão Add Autor clicado');
        window.adicionarAutorChip();
    });

    // Botão Add Testemunha
    $(document).on('click', '#btnAddTestemunha1ToChip', function () {
        console.log('✅ Botão Add Testemunha clicado');
        window.adicionarTestemunhaChip();
    });

    // Botão Add Condutor
    $(document).on('click', '#btnAddCondutorToChip', function () {
        console.log('✅ Botão Add Condutor clicado');
        window.adicionarCondutorChip();
    });

    // Botão Add Outro
    $(document).on('click', '#btnAddOutroToChip', function () {
        console.log('✅ Botão Add Outro clicado');
        window.adicionarOutroChip();
    });

    console.log('✅ Event listeners configurados com sucesso!');
});

// =============================================
// FUNÇÕES PARA SALVAR OS DADOS
// =============================================

/**
 * Retorna todos os envolvidos para salvar no banco
 */
window.obterEnvolvidosParaSalvar = function () {
    return {
        vitimas: window.envolvidosChips.vitimas,
        autores: window.envolvidosChips.autores,
        testemunhas: window.envolvidosChips.testemunhas,
        condutores: window.envolvidosChips.condutores,
        outros: window.envolvidosChips.outros
    };
};
