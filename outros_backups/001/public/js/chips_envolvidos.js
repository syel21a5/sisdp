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
 * Adiciona uma vítima como chip
 */
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

    // Sincroniza com OcorrenciasApp e delega a renderização para evitar conflitos
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        if (!window.OcorrenciasApp.envolvidos.vitimas.includes(vitima.nome)) {
            window.OcorrenciasApp.envolvidos.vitimas.push(vitima.nome);
            // Sincroniza vínculos
            window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
            window.OcorrenciasApp.vinculos.vitimas = window.OcorrenciasApp.vinculos.vitimas || [];
            // Adiciona vínculo preservando ID se existir
            while (window.OcorrenciasApp.vinculos.vitimas.length < window.OcorrenciasApp.envolvidos.vitimas.length - 1) {
                window.OcorrenciasApp.vinculos.vitimas.push(null);
            }
            window.OcorrenciasApp.vinculos.vitimas.push({ nome: vitima.nome, pessoa_id: id });
        }
        window.OcorrenciasApp.atualizarChips('vitimas');
    } else {
        // Fallback
        criarChip('vitima', vitima.nome, vitima.id);
    }

    // Limpa o formulário
    $('#btnLimparVitima1').click();

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

    // Sincroniza com OcorrenciasApp e delega a renderização
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        if (!window.OcorrenciasApp.envolvidos.autores.includes(autor.nome)) {
            window.OcorrenciasApp.envolvidos.autores.push(autor.nome);
            window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
            window.OcorrenciasApp.vinculos.autores = window.OcorrenciasApp.vinculos.autores || [];
            while (window.OcorrenciasApp.vinculos.autores.length < window.OcorrenciasApp.envolvidos.autores.length - 1) {
                window.OcorrenciasApp.vinculos.autores.push(null);
            }
            window.OcorrenciasApp.vinculos.autores.push({ nome: autor.nome, pessoa_id: id });
        }
        window.OcorrenciasApp.atualizarChips('autores');
    } else {
        criarChip('autor', autor.nome, autor.id);
    }

    // Limpa o formulário
    $('#btnLimparAutor1').click();

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

    // Sincroniza com OcorrenciasApp e delega a renderização
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        if (!window.OcorrenciasApp.envolvidos.testemunhas.includes(testemunha.nome)) {
            window.OcorrenciasApp.envolvidos.testemunhas.push(testemunha.nome);
            window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
            window.OcorrenciasApp.vinculos.testemunhas = window.OcorrenciasApp.vinculos.testemunhas || [];
            while (window.OcorrenciasApp.vinculos.testemunhas.length < window.OcorrenciasApp.envolvidos.testemunhas.length - 1) {
                window.OcorrenciasApp.vinculos.testemunhas.push(null);
            }
            window.OcorrenciasApp.vinculos.testemunhas.push({ nome: testemunha.nome, pessoa_id: id });
        }
        window.OcorrenciasApp.atualizarChips('testemunhas');
    } else {
        // Cria o chip
        criarChip('testemunha', testemunha.nome, testemunha.id);
    }

    // Limpa o formulário
    $('#btnLimparTestemunha1').click();

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

    // Sincroniza com OcorrenciasApp e delega a renderização
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        if (!window.OcorrenciasApp.envolvidos.condutores.includes(condutor.nome)) {
            window.OcorrenciasApp.envolvidos.condutores.push(condutor.nome);
            window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
            window.OcorrenciasApp.vinculos.condutores = window.OcorrenciasApp.vinculos.condutores || [];
            while (window.OcorrenciasApp.vinculos.condutores.length < window.OcorrenciasApp.envolvidos.condutores.length - 1) {
                window.OcorrenciasApp.vinculos.condutores.push(null);
            }
            window.OcorrenciasApp.vinculos.condutores.push({ nome: condutor.nome, pessoa_id: id });
        }
        window.OcorrenciasApp.atualizarChips('condutores');
    } else {
        // Cria o chip
        criarChip('condutor', condutor.nome, condutor.id);
    }

    // Limpa o formulário
    $('#btnLimparCondutor').click();

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

    // Sincroniza com OcorrenciasApp e delega a renderização
    if (window.OcorrenciasApp && window.OcorrenciasApp.envolvidos) {
        if (!window.OcorrenciasApp.envolvidos.outros.includes(outro.nome)) {
            window.OcorrenciasApp.envolvidos.outros.push(outro.nome);
            window.OcorrenciasApp.vinculos = window.OcorrenciasApp.vinculos || {};
            window.OcorrenciasApp.vinculos.outros = window.OcorrenciasApp.vinculos.outros || [];
            while (window.OcorrenciasApp.vinculos.outros.length < window.OcorrenciasApp.envolvidos.outros.length - 1) {
                window.OcorrenciasApp.vinculos.outros.push(null);
            }
            window.OcorrenciasApp.vinculos.outros.push({ nome: outro.nome, pessoa_id: id });
        }
        window.OcorrenciasApp.atualizarChips('outros');
    } else {
        criarChip('outro', outro.nome, outro.id);
    }

    // Limpa o formulário
    $('#btnLimparOutro').click();

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
    const chip = $(`
        <div class="badge bg-primary d-inline-flex align-items-center gap-1 px-2 py-1 me-1 mb-1 shadow-sm" 
             data-tipo="${tipo}" data-id="${id}" style="font-size: 0.8rem; font-weight: 500;">
            <span>${nome.toUpperCase()}</span>
            <button type="button" class="btn-close btn-close-white" 
                    style="font-size: 0.6rem; opacity: 0.8;" 
                    onclick="removerChip('${tipo}', ${id})"></button>
            <button type="button" class="btn btn-sm btn-light border-0" 
                    style="padding: 0px 4px; font-size: 0.6rem; line-height: 1.2; opacity: 0.9;" 
                    onclick="editarChip('${tipo}', ${id})" 
                    title="Editar">
                <i class="bi bi-pencil"></i>
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
            $(`#formVitima1 [name="${field.name}"]`).val(field.value);
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-vitima', 'Vítima', 'tabLinkVitima');
        }
    } else if (tipo === 'autor') {
        item.dados.forEach(field => {
            $(`#formAutor1 [name="${field.name}"]`).val(field.value);
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-autor', 'Autor', 'tabLinkAutor');
        }
    } else if (tipo === 'testemunha') {
        item.dados.forEach(field => {
            $(`#formTestemunha1 [name="${field.name}"]`).val(field.value);
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-testemunha', 'Testemunha', 'tabLinkTestemunha');
        }
    } else if (tipo === 'condutor') {
        item.dados.forEach(field => {
            $(`#formCondutor [name="${field.name}"]`).val(field.value);
        });
        if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
            window.ocTabs.ensureTab('tab-condutor', 'Condutor', 'tabLinkCondutor');
        }
    } else if (tipo === 'outro') {
        item.dados.forEach(field => {
            $(`#formOutro [name="${field.name}"]`).val(field.value);
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
