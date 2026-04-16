// script_ip.js - Código reorganizado COM FALLBACK PARA VÍNCULOS E AUTOCOMPLETE DE DOCUMENTOS
// Objeto principal para encapsular toda a funcionalidade
window.ocTabs = window.ocTabs || {};
window.ocTabs.ensureTab = function (id, label, linkId) {
    if (!document.getElementById(linkId)) {
        $('#subAbasInicio').append(
            `<li class="nav-item"><a class="nav-link" id="${linkId}" data-bs-toggle="tab" href="#${id}" role="tab">${label}</a></li>`
        );
    }
    const el = document.getElementById(linkId);
    if (el) new bootstrap.Tab(el).show();
};
window.ocTabs.closeTab = function (id, linkId) {
    const link = document.getElementById(linkId);
    if (link && link.parentElement) link.parentElement.remove();
    const pane = document.getElementById(id);
    if (pane) {
        pane.classList.remove('show', 'active');
    }
    const apfdLink = document.querySelector('#subAbasInicio a[href="#dados"]');
    if (apfdLink) new bootstrap.Tab(apfdLink).show();
};

// Global Modal Helpers removidos. Agora residentes em public/js/core.js

window.OcorrenciasApp = {
    init: function () {
        this.currentId = null;
        this.setupMasks();
        this.setupAutocomplete();
        this.setupDocumentosAutocomplete();
        this.setupEnvolvidos(); // ✅ NOVO: Inicializar sistema de envolvidos
        this.bindEvents();
        this.resetForms();
        this.preencherDataAtual();

        // Resetar estados dos botões
        $('#btnSalvar').prop('disabled', false).html('<i class="bi bi-save"></i> Salvar');
        $('#btnEditar').prop('disabled', true).html('<i class="bi bi-pencil-square"></i> Editar');
        $('#btnExcluir').prop('disabled', true);

        // Atualizar badges de itens pendentes (celulares/veículos) removido
    },

    setupMasks: function () {
        $('#inputData').mask('00/00/0000');
        $('#inputIP').mask('0000.0000.000000-00', {
            placeholder: "____.____.______-__",
            reverse: true
        });
    },

    setupAutocomplete: function () {
        const dadosAutocomplete = {
            delegacias: ["167ª Circunscrição", "Delegacia de Plantão", "Delegacia da Mulher"],
            cidades: ["Afogados da Ingazeira", "Recife", "Caruaru", "Petrolina", "Garanhuns"],
            delegados: ["Leandro Miranda Mai", "Israel Lima Braga Rubis", "Joedna Maria Soares Gomes", "Antonio Junior de Lima e Silva", "Vicktor de Araújo Melo"],
            escrivoes: ["Vandeleys da Silva Lima", "Marcos Antonio da Silva"],
            policiais: ["Vandeleys da Silva Lima", "Marcos Antonio da Silva", "José Carlos", "Maria da Silva"]
        };
        $("#inputDelegado").autocomplete({
            source: dadosAutocomplete.delegados,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
        $("#inputEscrivao").autocomplete({
            source: dadosAutocomplete.escrivoes,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
        $("#inputPolicial1").autocomplete({
            source: dadosAutocomplete.policiais,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
        $("#inputPolicial2").autocomplete({
            source: dadosAutocomplete.policiais,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
        $("#inputDelegacia").autocomplete({
            source: dadosAutocomplete.delegacias,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
        $("#inputCidade").autocomplete({
            source: dadosAutocomplete.cidades,
            minLength: 0,
            delay: 300
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
    },

    // ✅ NOVO: Sistema de envolvidos
    setupEnvolvidos: function () {
        this.envolvidos = {
            vitimas: [], autores: [], testemunhas: [], condutores: [], outros: []
        };
        this.vinculos = {
            vitimas: [], autores: [], testemunhas: [], condutores: [], outros: []
        };
        this.isOwner = true;
        this.ownerName = null;
        this.pollingSugestoes = null;
        this.detalhes = { AUTOR: {}, VITIMA: {}, TESTEMUNHA: {} };

        this.bindEnvolvidosEvents();
    },

    normalizarNome: function (nome) {
        return (nome || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toUpperCase()
            .replace(/\s+/g, ' ')
            .trim();
    },

    obterDadosImportadosPorNome: function (nome) {
        const base = this.dadosImportados || {};
        if (!nome) return null;
        if (base[nome]) return base[nome];
        const alvo = this.normalizarNome(nome);
        for (const k in base) {
            if (this.normalizarNome(k) === alvo) return base[k];
        }
        return null;
    },

    extrairDetalhesDoTexto: function (nome) {
        try {
            const texto = this.textoBoeImportado || '';
            if (!texto || !nome) return null;

            // Normaliza para busca no texto (remove espaços extras)
            const nomeBusca = nome.trim().replace(/\s+/g, '\\s+');
            const escape = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            // Encontra o início do bloco da pessoa
            // Procura pelo nome seguido de algum separador comum ou fim de linha,
            // mas tenta garantir que é o cabeçalho da pessoa (ex: "NOME (situação)")
            // 1. Tenta encontrar o nome seguido de "(presente" ou "(não presente" para garantir que é o bloco de detalhes
            // Regex: Nome + quebra de linha + ( + (não)? presente
            const escapeRegExp = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const nomeRegexStr = escapeRegExp(nome) + '\\s*\\n\\s*\\((?:não\\s+)?presente';
            let nomeRe = new RegExp(nomeRegexStr, 'i');
            let m = nomeRe.exec(texto);

            // Fallback: Se não achar com o status, tenta só o nome (mas é arriscado pegar o resumo)
            if (!m) {
                console.log('⚠️ Bloco detalhado não encontrado com status, tentando apenas nome...');
                nomeRe = new RegExp(escapeRegExp(nome), 'i');
                m = nomeRe.exec(texto);
            }

            if (!m) return null;

            const start = m.index;

            // 2. Define o fim do bloco para não invadir os dados de outra pessoa
            // Procura o próximo "padrão de pessoa" (Nome em caps + \n + (presente...) ou palavras chave de fim de seção
            const restoTexto = texto.substring(start + 1); // +1 para não achar a própria pessoa

            // Regex para identificar o início da PRÓXIMA pessoa: Linha com letras maiúsculas seguida de (presente...)
            // Ou seções que encerram a lista de pessoas: "Objetos", "Complemento", "Condutor da ocorrência"
            const fimRe = /\n[A-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s]+\s*\n\s*\((?:não\s+)?presente|Objetos|Complemento|Condutor da ocorrência/i;
            const mFim = fimRe.exec(restoTexto);

            const end = mFim ? (start + 1 + mFim.index) : Math.min(texto.length, start + 3000);

            const bloco = texto.substring(start, end);
            console.log('📝 Bloco de texto analisado (Escopado):', bloco);

            // Helper para capturar grupo 1 de regex
            const cap = (regex, returnMatch = false) => {
                const m = regex.exec(bloco);
                if (returnMatch) return m;
                return m ? m[1].trim() : '';
            };

            // Regexes robustas baseadas no BOE real
            const detalhes = {
                nome: nome,
                alcunha: cap(/(?:Alcunha|Apelido):\s*([^;\n]+)/i),

                nascimento: (function () {
                    const m = cap(/(?:Data de Nascimento|Nascimento):\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/i, true);
                    if (m) {
                        return `${m[1].padStart(2, '0')}/${m[2].padStart(2, '0')}/${m[3]}`;
                    }
                    return cap(/(?:Data de Nascimento|Nascimento):\s*([^;\n]+)/i);
                })(),

                idade: cap(/Idade(?:\s+aparente)?:\s*(\d+)/i),
                estado_civil: cap(/Estado\s+Civil:\s*([^;\n]+)/i),

                // Naturalidade: Pega apenas a cidade (parte antes da primeira barra)
                naturalidade: (function () {
                    // Regex mais permissiva: aceita : ou . e quebras de linha
                    const m = cap(/Naturalidade\s*[:\.]\s*([^;\n]+)/i, true);
                    console.log('🌍 Debug Naturalidade Match:', m);
                    if (m && m[1]) {
                        return m[1].split('/')[0].trim();
                    }
                    return '';
                })(),

                instrucao: cap(/(?:Grau\s+de\s+Instru[çc][ãa]o|Escolaridade):\s*([^;\n]+)/i),
                profissao: cap(/(?:Profiss[ãa]o|Ocupa[çc][ãa]o):\s*([^;\n]+)/i),

                // RG: Pega de "Documentos: ... (RG)" ou "RG: ..."
                rg: (function () {
                    // Tenta pegar direto do bloco o padrão "... (RG)"
                    const mm = /([0-9A-Za-z\.\-\/]+)\s*\(RG\)/i.exec(bloco);
                    if (mm) return mm[1].trim();

                    // Fallback: Tenta pegar da linha "Documentos:" (caso esteja na mesma linha)
                    const d = cap(/Documentos:\s*([^\n]+)/i);
                    const mm2 = /([0-9A-Za-z\.\-\/]+)\s*\(RG\)/i.exec(d);
                    return mm2 ? mm2[1].trim() : cap(/RG:\s*([0-9A-Za-z\.\-\/]+)/i);
                })(),

                // CPF: Pega de "Documentos: ... (CPF)" ou "CPF: ..."
                cpf: (function () {
                    // Tenta pegar direto do bloco o padrão "... (CPF)"
                    // Aceita pontos e traços opcionais
                    const mm = /([0-9]{3}\.?[0-9]{3}\.?[0-9]{3}-?[0-9]{2})\s*\(CPF\)/i.exec(bloco);
                    let cpfRaw = mm ? mm[1].trim() : cap(/CPF:\s*([0-9\.\-]+)/i);

                    if (cpfRaw) {
                        // Remove tudo que não é dígito
                        const soNumeros = cpfRaw.replace(/\D/g, '');
                        if (soNumeros.length === 11) {
                            return soNumeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
                        }
                        return cpfRaw; // Retorna como achou se não tiver 11 dígitos
                    }
                    return '';
                })(),

                telefone: cap(/(?:Telefones?\s+Celulares?|Telefone|Celular):\s*([^;\n]+)/i),
                mae: cap(/M[ãa]e:\s*([^;\n]+)/i),
                pai: cap(/Pai:\s*([^;\n]+)/i),

                // Endereço: Pode conter ponto e vírgula, então pegamos até o final da linha (\n)
                endereco: cap(/(?:Endere[çc]o\s+(?:Residencial|Comercial)|Endere[çc]o|Resid[êe]ncia|Morada):\s*([^\n]+)/i)
            };

            // Limpeza final: remove campos vazios e "NÃO INFORMADO"
            Object.keys(detalhes).forEach(k => {
                if (!detalhes[k] || detalhes[k].toUpperCase().includes('NÃO INFORMADO')) {
                    delete detalhes[k];
                }
            });

            return Object.keys(detalhes).length > 1 ? detalhes : null; // > 1 porque 'nome' sempre tem
        } catch (e) {
            console.error('Erro na extração de detalhes:', e);
            return null;
        }
    },

    bindEnvolvidosEvents: function () {
        const ensureTab = window.ocTabs.ensureTab;
        const limpar = (tipo) => {
            if (typeof window.limparCamposEnvolvido === 'function') {
                window.limparCamposEnvolvido(tipo);
            } else {
                // Fallback caso o módulo de chips não esteja carregado
                const btnMap = { vitimas: '#btnNovaVitima1', autores: '#btnNovoAutor1', testemunhas: '#btnNovaTestemunha1', condutores: '#btnNovoCondutor', outros: '#btnNovoOutro' };
                try { $(btnMap[tipo]).trigger('click'); } catch (e) { }
            }
        };

        // Abrir sub-aba Vítima
        $('#btnAddVitima').off('click').on('click', () => {
            limpar('vitimas');
            ensureTab('tab-vitima', 'Vítima', 'tabLinkVitima');
        });
        $('#inputVitima').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                limpar('vitimas');
                const el = document.querySelector('#tabLinkVitima');
                if (el) new bootstrap.Tab(el).show();
            }
        });

        // Abrir sub-aba Autor
        $('#btnAddAutor').off('click').on('click', () => {
            limpar('autores');
            ensureTab('tab-autor', 'Autor', 'tabLinkAutor');
        });
        $('#inputAutor').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                limpar('autores');
                const el = document.querySelector('#tabLinkAutor');
                if (el) new bootstrap.Tab(el).show();
            }
        });

        // Abrir sub-aba Testemunha
        $('#btnAddTestemunha').off('click').on('click', () => {
            limpar('testemunhas');
            ensureTab('tab-testemunha', 'Testemunha', 'tabLinkTestemunha');
        });
        $('#inputTestemunha').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                limpar('testemunhas');
                const el = document.querySelector('#tabLinkTestemunha');
                if (el) new bootstrap.Tab(el).show();
            }
        });

        // Abrir sub-aba Condutor
        $('#btnAddCondutor').off('click').on('click', () => {
            ensureTab('tab-condutor', 'Condutor', 'tabLinkCondutor');
            setTimeout(() => { limpar('condutores'); }, 50);
        });

        $(document).off('click', '#btnFecharVitima1').on('click', '#btnFecharVitima1', () => {
            window.ocTabs.closeTab('tab-vitima', 'tabLinkVitima');
        });
        $(document).off('click', '#btnFecharAutor1').on('click', '#btnFecharAutor1', () => {
            window.ocTabs.closeTab('tab-autor', 'tabLinkAutor');
        });
        $(document).off('click', '#btnFecharTestemunha1').on('click', '#btnFecharTestemunha1', () => {
            window.ocTabs.closeTab('tab-testemunha', 'tabLinkTestemunha');
        });
        $(document).off('click', '#btnFecharCondutor').on('click', '#btnFecharCondutor', () => {
            window.ocTabs.closeTab('tab-condutor', 'tabLinkCondutor');
        });
        $('#inputCondutor').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                const el = document.querySelector('#tabLinkCondutor');
                if (el) new bootstrap.Tab(el).show();
                setTimeout(() => { limpar('condutores'); }, 50);
            }
        });

        $('#btnConfirmarTrocaPapel').off('click').on('click', () => {
            const novoTipo = $('#selectNovoPapel').val();
            this.moverEnvolvido(novoTipo);
        });


        // ✅ NOVO: Adicionar Outros (Simples, sem abas)
        $('#btnAddOutro').off('click').on('click', () => {
            limpar('outros');
            window.ocTabs.ensureTab('tab-outro', 'Outros', 'tabLinkOutro');
        });
        $('#inputOutro').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                limpar('outros');
                window.ocTabs.ensureTab('tab-outro', 'Outros', 'tabLinkOutro');
            }
        });
    },

    adicionarEnvolvido: function (tipo) {
        const input = $(`#input${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
        const nome = input.val().trim();

        if (!nome) {
            this.mostrarErro(`Digite o nome da ${tipo}`);
            return;
        }

        this.envolvidos[tipo + 's'].push(nome);
        this.atualizarChips(tipo + 's');
        input.val('').focus();
    },

    removerEnvolvido: function (tipo, index) {
        const nomeRemovido = this.envolvidos[tipo][index];
        
        // 1. Remove dos arrays principais
        this.envolvidos[tipo].splice(index, 1);
        if (this.vinculos[tipo]) {
            this.vinculos[tipo].splice(index, 1);
        }

        // 2. Sincroniza com window.envolvidosChips (Persistência)
        if (window.envolvidosChips && window.envolvidosChips[tipo]) {
            const idxChip = window.envolvidosChips[tipo].findIndex(c => c.nome === nomeRemovido);
            if (idxChip > -1) {
                window.envolvidosChips[tipo].splice(idxChip, 1);
            }
        }
        
        this.atualizarChips(tipo);
        this.atualizarEstadoBotoes();
    },

    abrirModalTrocaPapel: function (tipo, index) {
        this.pendingSwitch = { tipo, index };
        // Prepara o select para não mostrar a opção ataul
        $('#selectNovoPapel option').prop('disabled', false);
        $('#selectNovoPapel option[value="' + tipo + '"]').prop('disabled', true);

        // Seleciona o primeiro não desabilitado
        const firstValid = $('#selectNovoPapel option:not(:disabled)').first().val();
        $('#selectNovoPapel').val(firstValid);

        $('#modalTrocarPapel').modal('show');
    },

    moverEnvolvido: function (novoTipo) {
        if (!this.pendingSwitch) return;

        const oldTipo = this.pendingSwitch.tipo;
        const index = this.pendingSwitch.index;

        // Recupera os dados
        const nome = this.envolvidos[oldTipo][index];
        const vinculo = (this.vinculos[oldTipo] && this.vinculos[oldTipo][index]) ? this.vinculos[oldTipo][index] : null;

        // Remove do antigo
        this.envolvidos[oldTipo].splice(index, 1);
        if (this.vinculos[oldTipo]) this.vinculos[oldTipo].splice(index, 1);

        // ✅ FIX: Se saiu de condutores, limpa os campos ocultos do formulário
        if (oldTipo === 'condutores') {
            $('#condutor_id').val('');
            $('#inputNomeCondutor').val('');
            if ($('#formCondutor').length) $('#formCondutor')[0].reset();
        }

        // Adiciona no novo
        this.envolvidos[novoTipo] = this.envolvidos[novoTipo] || [];
        this.envolvidos[novoTipo].push(nome);

        // ✅ FIX: Sincronizar com window.envolvidosChips (Persistência)
        if (window.envolvidosChips) {
            const arrOld = window.envolvidosChips[oldTipo];
            const arrNew = window.envolvidosChips[novoTipo];
            if (arrOld && arrNew) {
                const idxChip = arrOld.findIndex(c => c.nome === nome);
                if (idxChip > -1) {
                    const chipObj = arrOld.splice(idxChip, 1)[0];
                    arrNew.push(chipObj);
                } else {
                    // Se não achou (caso raro), cria um novo
                    arrNew.push({ nome: nome, id: null });
                }
            }
        }

        this.vinculos[novoTipo] = this.vinculos[novoTipo] || [];
        // Se tinha vínculo, tenta preservar a pessoa_id, mas reseta o vinculo_id pois é nova relação
        if (vinculo) {
            this.vinculos[novoTipo].push({
                nome: vinculo.nome,
                pessoa_id: vinculo.pessoa_id,
                vinculo_id: null
            });

            // ✅ FIX: Se moveu PARA condutores, preenche o ID oculto
            if (novoTipo === 'condutores' && vinculo.pessoa_id) {
                $('#condutor_id').val(vinculo.pessoa_id);
                $('#inputNomeCondutor').val(vinculo.nome || nome);
            }
        } else {
            this.vinculos[novoTipo].push(null);
            if (novoTipo === 'condutores') {
                $('#inputNomeCondutor').val(nome);
            }
        }

        // Atualiza UI
        this.atualizarChips(oldTipo);
        this.atualizarChips(novoTipo);
        this.atualizarEstadoBotoes();

        $('#modalTrocarPapel').modal('hide');
        this.pendingSwitch = null;
    },

    atualizarChips: function (tipo) {
        const container = $(`#chips${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
        container.empty();

        const currentUserName = $('.system-user').text().replace('Usuário: ', '').trim();
        const isOwner = this.isOwner !== undefined ? this.isOwner : true;

        this.envolvidos[tipo].forEach((nome, index) => {
            const vinculo = (this.vinculos[tipo] && this.vinculos[tipo][index]) ? this.vinculos[tipo][index] : null;
            const temVinculo = !!(vinculo && (vinculo.pessoa_id || vinculo.vinculo_id));
            
            // ✅ FIX: Se a sugestão foi feita pelo próprio usuário ou se ele é o dono adicionando agora, não é "pendente" para ele
            const statusOriginal = vinculo ? (vinculo.status_aprovacao || 'aprovado') : 'aprovado';
            const criadoPorNome = vinculo ? vinculo.criado_por_nome : null;
            const isMinhaSugestao = criadoPorNome && currentUserName && this.normalizarNome(criadoPorNome) === this.normalizarNome(currentUserName);
            
            // Para efeitos de botões e cor orange, ignoramos se for o dono ou se for minha própria sugestão
            const isPendenteParaMim = statusOriginal === 'pendente' && !isOwner && !isMinhaSugestao;
            
            // Para o label "Sugestão de", mostramos sempre que não for minha própria
            const mostrarLabelSugestao = statusOriginal === 'pendente' && !isMinhaSugestao;

            if (!$('#premium-chip-styles').length) {
                $('head').append(`
                    <style id="premium-chip-styles">
                        .chip-premium {
                            border-radius: 6px;
                            padding: 0.4rem 0.65rem !important;
                            font-size: 0.82rem !important;
                            font-weight: 600;
                            letter-spacing: 0.2px;
                            border: 1px solid rgba(0,0,0,0.05);
                            transition: all 0.2s ease;
                            display: inline-flex;
                            align-items: center;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                            color: white !important;
                        }
                        .chip-premium:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 4px 6px rgba(0,0,0,0.12);
                        }
                        .chip-blue { background: linear-gradient(135deg, #0d6efd, #084298); border-color: #0b5ed7; }
                        .chip-red { background: linear-gradient(135deg, #dc3545, #9c2532); border-color: #b02a37; }
                        .chip-orange { background: linear-gradient(135deg, #fd7e14, #ca6510); border-color: #d9620b; color: white !important;}
                        .chip-orange .badge.bg-light { color: #000 !important; background: rgba(255,255,255,0.85) !important; }
                        
                        .chip-premium .btn-link {
                            color: rgba(255,255,255,0.6) !important;
                            transition: all 0.2s;
                            padding: 2px 4px !important;
                        }
                        .chip-premium .btn-link:hover {
                            color: #ffffff !important;
                            transform: scale(1.15);
                        }
                        .chip-premium .btn-close-white {
                            filter: invert(1) grayscale(100%) brightness(200%);
                            opacity: 0.6;
                            transition: all 0.2s;
                            margin-left: 0.5rem !important;
                        }
                        .chip-premium .btn-close-white:hover {
                            opacity: 1;
                            transform: scale(1.15);
                        }
                    </style>
                `);
            }

            // ✅ Cores baseadas no status
            let classeBadge = 'chip-premium ';
            if (isPendenteParaMim) {
                classeBadge += 'chip-orange'; // Laranja para pendente (para não-donos)
            } else if (temVinculo) {
                classeBadge += 'chip-blue'; // Azul para aprovado com vínculo
            } else {
                classeBadge += 'chip-red'; // Vermelho para sem vínculo no banco
            }

            // Botões de ação normais
            const btnEdit = `<button type="button" class="btn btn-sm btn-link text-white ms-2 btn-edit-chip p-0" data-tipo="${tipo}" data-index="${index}" title="Editar Detalhes"><i class="bi bi-pencil-square"></i></button>`;
            
            // Renderiza apenas se a permissão estiver ativa (ou não testada para fallback)
            const btnPrompt = (window._userPerms && window._userPerms.gerar_prompts === false) ? '' : `<button type="button" class="btn btn-sm btn-link text-white ms-1 btn-prompt-chip p-0" data-tipo="${tipo}" data-index="${index}" title="Gerar Prompt" style="font-size: 0.9rem;">📋</button>`;
            
            const btnRefresh = `<button type="button" class="btn btn-sm btn-link text-white ms-1 btn-refresh-chip p-0" data-tipo="${tipo}" data-index="${index}" title="Atualizar do Banco"><i class="bi bi-arrow-repeat"></i></button>`;
            const btnSwitch = `<button type="button" class="btn btn-sm btn-link text-white ms-1 btn-switch-chip p-0" data-tipo="${tipo}" data-index="${index}" title="Trocar Papel"><i class="bi bi-arrow-left-right"></i></button>`;

            // ✅ Botões de aprovação/rejeição (apenas para o dono, em chips pendentes reais)
            let btnAprovar = '';
            let btnRejeitar = '';
            if (statusOriginal === 'pendente' && isOwner && !isMinhaSugestao && vinculo && vinculo.vinculo_id) {
                btnAprovar = `<button type="button" class="btn btn-sm btn-success ms-2 btn-aprovar-chip px-2" data-vinculo-id="${vinculo.vinculo_id}" title="Aprovar Sugestão"><i class="bi bi-check-lg"></i></button>`;
                btnRejeitar = `<button type="button" class="btn btn-sm btn-danger ms-1 btn-rejeitar-chip px-2" data-vinculo-id="${vinculo.vinculo_id}" title="Recusar Sugestão"><i class="bi bi-x-lg"></i></button>`;
            }

            // ✅ Botão de fechar: não-donos NÃO podem excluir chips aprovados
            let btnClose = '';
            if (isOwner || isPendenteParaMim) {
                btnClose = `<button type="button" class="btn-close btn-close-white ms-2" data-tipo="${tipo}" data-index="${index}" ${vinculo && vinculo.vinculo_id ? `data-vinculo-id="${vinculo.vinculo_id}"` : ''} style="font-size: 0.65rem;"></button>`;
            }

            // Botões extras (edit/switch/refresh) - non-owners still see edit but not switch
            let extraBtn = '';
            if (isOwner) {
                extraBtn = btnEdit + btnPrompt + btnSwitch + btnRefresh;
            } else {
                extraBtn = btnEdit + btnPrompt;
            }

            // ✅ Layout flexível para acomodar o label embaixo do nome quando for sugestão
            let nomeArea = `<span class="lh-1" style="margin-top:2px;">${nome.toUpperCase()}</span>`;
            if (mostrarLabelSugestao) {
                const quem = criadoPorNome || 'Colega';
                const labelSugestao = `<span class="badge bg-light text-dark fw-normal mt-1" style="font-size: 0.62rem; padding: 2px 5px; width: fit-content; text-transform:none;">Sugestão de: <b>${quem}</b></span>`;
                nomeArea = `
                    <div class="d-flex flex-column justify-content-center">
                        <span class="lh-1" style="margin-top:2px;">${nome.toUpperCase()}</span>
                        ${labelSugestao}
                    </div>
                `;
            }

            const chip = $(`
                <div class="chip-envolvido ${classeBadge} mb-1 me-1 d-inline-flex align-items-center" title="${nome}">
                    <div class="ms-1 d-flex flex-column justify-content-center h-100">
                        ${nomeArea}
                    </div>
                    <div class="d-flex align-items-center ms-2 h-100" style="gap:2px;">
                        ${extraBtn}
                        ${btnAprovar}${btnRejeitar}
                        ${btnClose}
                    </div>
                </div>
            `);
            container.append(chip);
        });

        container.find('.chip-envolvido').on('click', (e) => {
            const target = $(e.target);
            if (target.hasClass('btn-close') || target.closest('.btn-edit-chip').length || target.closest('.btn-refresh-chip').length || target.closest('.btn-switch-chip').length || target.closest('.btn-prompt-chip').length || target.closest('.btn-gerar-intimacao').length || target.closest('.btn-aprovar-chip').length || target.closest('.btn-rejeitar-chip').length) return;
            const chip = $(e.currentTarget);
            const pencil = chip.find('.btn-edit-chip');
            if (pencil.length) pencil.trigger('click');
        });

        // ✅ NOVO: Handlers de aprovação/rejeição de chips pendentes
        container.find('.btn-aprovar-chip').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const vinculoId = $(e.currentTarget).data('vinculo-id');
            const boe = $('#inputBOE').val();
            $.ajax({
                url: `/boe/vinculos/aprovar/${vinculoId}`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: (resp) => {
                    if (resp.success) {
                        window.mostrarSucesso('Envolvido aprovado com sucesso!');
                        this.carregarVinculosDoBoe(boe);
                    } else {
                        window.mostrarErro(resp.message || 'Erro ao aprovar');
                    }
                },
                error: (xhr) => {
                    window.mostrarErro(xhr.responseJSON?.message || 'Erro ao aprovar o vínculo.');
                }
            });
        });

        container.find('.btn-rejeitar-chip').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const vinculoId = $(e.currentTarget).data('vinculo-id');
            const boe = $('#inputBOE').val();
            $.ajax({
                url: `/boe/vinculos/rejeitar/${vinculoId}`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: (resp) => {
                    if (resp.success) {
                        window.mostrarSucesso('Sugestão rejeitada.');
                        this.carregarVinculosDoBoe(boe);
                    } else {
                        window.mostrarErro(resp.message || 'Erro ao rejeitar');
                    }
                },
                error: (xhr) => {
                    window.mostrarErro(xhr.responseJSON?.message || 'Erro ao rejeitar o vínculo.');
                }
            });
        });



        container.find('.btn-switch-chip').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const btn = $(e.currentTarget);
            const tipo = btn.data('tipo');
            const index = btn.data('index');
            this.abrirModalTrocaPapel(tipo, index);
        });

        container.find('.btn-close').on('click', (e) => {
            const btn = $(e.currentTarget);
            const tipo = btn.data('tipo');
            const index = btn.data('index');
            const vinculoId = btn.data('vinculo-id');
            this.pendingChip = { tipo, index, vinculoId };
            $('#modalConfirmacaoChip').modal('show');
        });

        container.find('.btn-refresh-chip').on('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const btn = $(e.currentTarget);
            const tipo = btn.data('tipo');
            const index = btn.data('index');
            this.atualizarChipDoBanco(tipo, index);
        });

        container.find('.btn-edit-chip').on('click', (e) => {
            const btn = $(e.currentTarget);
            const tipo = btn.data('tipo');
            const index = btn.data('index');

            if (tipo === 'outros' || tipo === 'outro') {
                const nome = this.envolvidos[tipo][index];

                // Verifica se tem vínculo com ID real
                const vinculo = (this.vinculos[tipo] && this.vinculos[tipo][index]) ? this.vinculos[tipo][index] : null;

                if (vinculo && vinculo.pessoa_id && typeof window.buscarOutroPorId === 'function') {
                    window.buscarOutroPorId(vinculo.pessoa_id);
                } else {
                    $('#inputNomeOutro').val(nome);
                    // Tenta recuperar detalhes armazenados (se houver)
                    if (this.dadosImportados && this.dadosImportados[nome]) {
                        const d = this.dadosImportados[nome];
                        $('#inputRGOutro').val(d.rg || '');
                        $('#inputCPFOutro').val(d.cpf || '');
                        if (typeof window.preencherOutroVinculado === 'function') {
                            window.preencherOutroVinculado({ ...d, Nome: nome });
                        }
                    }
                }

                if (window.ocTabs && typeof window.ocTabs.ensureTab === 'function') {
                    window.ocTabs.ensureTab('tab-outro', 'Outros', 'tabLinkOutro');
                }
                return;
            }

            // ... (rest of the handler) ...
            // Note: I cannot easily replace just the end of the function without including the handler content which is long.
            // Actually, I can just append it after the handlers are attached.
            // Let's look at where the function ends.
            // It seems the view_file didn't show the end of atualizarChips.
            // I need to find the closing brace of atualizarChips.


            // Helper para limpar e popular formulário
            const popularFormulario = (prefixo, dados) => {
                // ✅ FIX: Remover acentos/caracteres especiais de todos os valores antes de preencher
                const semAcento = (s) => typeof s === 'string' ? s.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : (s || '');
                Object.keys(dados).forEach(k => { if (typeof dados[k] === 'string') dados[k] = semAcento(dados[k]); });

                console.log(`📝 Populating Form ${prefixo}:`, dados);
                console.log(`🎯 Target Naturalidade: #inputNaturalidade${prefixo}`, $(`#inputNaturalidade${prefixo}`).length);

                $(`#inputNome${prefixo}`).val(dados.nome || dados.Nome || '');
                $(`#inputAlcunha${prefixo}`).val(dados.alcunha || dados.Alcunha || dados.apelido || dados.Apelido || '');
                let dataNasc = dados.nascimento || dados.Nascimento || '';
                // Se vier no formato YYYY-MM-DD, converte para DD/MM/YYYY
                if (dataNasc && /^\d{4}-\d{2}-\d{2}$/.test(dataNasc)) {
                    const partes = dataNasc.split('-');
                    dataNasc = `${partes[2]}/${partes[1]}/${partes[0]}`;
                } else if (dataNasc && dataNasc.includes('/')) {
                    // Força padding de 2 dígitos na data extraída do chip (se IA jogar 2/5 em vez de 02/05)
                    const partes = dataNasc.split('/');
                    if (partes.length === 3) {
                       let d = partes[0].trim();
                       let m = partes[1].trim();
                       if (d.length === 1) d = '0' + d;
                       if (m.length === 1) m = '0' + m;
                       dataNasc = `${d}/${m}/${partes[2].trim()}`;
                    }
                }
                $(`#inputDataNascimento${prefixo}`).val(dataNasc);

                // Calcular idade automaticamente
                if (dataNasc) {
                    const partes = dataNasc.split('/');
                    if (partes.length === 3) {
                        const nascip = new Date(partes[2], partes[1] - 1, partes[0]);
                        if (!isNaN(nascip.getTime())) {
                            const hoje = new Date();
                            let idade = hoje.getFullYear() - nascip.getFullYear();
                            const m = hoje.getMonth() - nascip.getMonth();
                            if (m < 0 || (m === 0 && hoje.getDate() < nascip.getDate())) {
                                idade--;
                            }
                            $(`#inputIdade${prefixo}`).val(idade);
                        } else {
                            $(`#inputIdade${prefixo}`).val(dados.idade || dados.Idade || '');
                        }
                    } else {
                        $(`#inputIdade${prefixo}`).val(dados.idade || dados.Idade || '');
                    }
                } else {
                    $(`#inputIdade${prefixo}`).val(dados.idade || dados.Idade || '');
                }
                $(`#inputEstadoCivil${prefixo}`).val(dados.estado_civil || dados.EstCivil || dados.EstadoCivil || '');

                const nat = dados.naturalidade || dados.Naturalidade || '';
                console.log(`🌍 Setting Naturalidade to: '${nat}'`);
                $(`#inputNaturalidade${prefixo}`).val(nat);

                $(`#inputInstrucao${prefixo}`).val(dados.instrucao || dados.Instrucao || dados.escolaridade || dados.Escolaridade || '');
                $(`#inputRG${prefixo}`).val(dados.rg || dados.RG || '');
                $(`#inputCPF${prefixo}`).val(dados.cpf || dados.CPF || '').trigger('input');
                let tel = dados.telefone || dados.Telefone || dados.telefones || dados.Telefones || '';
                if (!tel || tel.trim() === '') tel = '(00) 00000-0000';
                $(`#inputTelefone${prefixo}`).val(tel).trigger('input');
                $(`#inputProfissao${prefixo}`).val(dados.profissao || dados.Profissao || '');
                $(`#inputMae${prefixo}`).val(dados.mae || dados.Mae || '');
                $(`#inputPai${prefixo}`).val(dados.pai || dados.Pai || '');
                $(`#inputEndereco${prefixo}`).val(dados.endereco || dados.Endereco || '');

                // ✅ NOVO: Campos Complementares (Autor)
                // Usamos verificações de existência para não quebrar outros formulários (vítima/testemunha)
                const setVal = (id, val) => { if ($(id).length) $(id).val(val); };

                setVal(`#inputTipoPenal${prefixo}`, dados.TipoPenal || dados.tipopenal || '');
                setVal(`#inputNmandado${prefixo}`, dados.Nmandado || dados.nmandado || '');
                setVal(`#inputDataMandado${prefixo}`, dados.DataMandado || dados.datamandado || '');

                // Fiança
                if ($(`#inputFianca${prefixo}`).length) {
                    let valFianca = dados.Fianca || dados.fianca || '';
                    if (valFianca && !isNaN(valFianca)) {
                        // Se for número, formata para string monetária se necessário (ex: 2 casas)
                        // Mas o input mask espera texto formatado (ex: 1.500,00) dependendo da config.
                        // Assumindo que vem do BD float, convertemos para formato BR se for o caso
                        valFianca = parseFloat(valFianca).toFixed(2).replace('.', ',');
                        // A mask pode exigir pontos de milhar, mas o .replace('.', ',') é o base para decimais.
                        // Se precisar de formatação completa (1.000,00), seria mais complexo,
                        // mas geralmente o toFixed(2) com replace basta p/ começar ou o usuário ajusta.
                        // Ajuste simples para ponto flutuante BR:
                        valFianca = valFianca.replace(/\d(?=(\d{3})+\,)/g, '$&.');
                    }
                    $(`#inputFianca${prefixo}`).val(valFianca);
                    $(`#inputFianca${prefixo}`).trigger('input'); // Dispara conversão extenso se existir
                }

                setVal(`#inputFiancaExt${prefixo}`, dados.FiancaExt || dados.fianca_ext || '');

                if ($(`#inputFiancaPago${prefixo}`).length) {
                    const isPago = (dados.FiancaPago == 1 || dados.FiancaPago === true || dados.fianca_pago == 1 || dados.fianca_pago === true);
                    $(`#inputFiancaPago${prefixo}`).prop('checked', isPago);
                    // Atualiza badge se a função existir no escopo global ou tiver trigger
                    $(`#inputFiancaPago${prefixo}`).trigger('change');
                }

                setVal(`#inputParente${prefixo}`, dados.Parente || dados.parente || '');
                setVal(`#inputFamilia${prefixo}`, dados.Familia || dados.familia || '');
                setVal(`#inputAdvogado${prefixo}`, dados.Advogado || dados.advogado || '');
                setVal(`#inputJuizMandado${prefixo}`, dados.JuizMandado || dados.juiz_mandado || '');
                setVal(`#inputComarcaMandado${prefixo}`, dados.ComarcaMandado || dados.comarca_mandado || '');
                setVal(`#inputRecolher${prefixo}`, dados.Recolher || dados.recolher || '');
                setVal(`#inputOfJuiz${prefixo}`, dados.OfJuiz || dados.of_juiz || '');
                setVal(`#inputOfPromotor${prefixo}`, dados.OfPromotor || dados.of_promotor || '');
                setVal(`#inputOfDefensor${prefixo}`, dados.OfDefensor || dados.of_defensor || '');
                setVal(`#inputOfCustodia${prefixo}`, dados.OfCustodia || dados.of_custodia || '');
            };

            // Helper para limpar formulário
            const limparFormulario = (prefixo) => {
                $(`#inputNome${prefixo}`).val('');
                $(`#inputAlcunha${prefixo}`).val('');
                $(`#inputDataNascimento${prefixo}`).val('');
                $(`#inputIdade${prefixo}`).val('');
                $(`#inputEstadoCivil${prefixo}`).val('');
                $(`#inputNaturalidade${prefixo}`).val('');
                $(`#inputInstrucao${prefixo}`).val('');
                $(`#inputRG${prefixo}`).val('');
                $(`#inputCPF${prefixo}`).val('');
                $(`#inputTelefone${prefixo}`).val('');
                $(`#inputProfissao${prefixo}`).val('');
                $(`#inputMae${prefixo}`).val('');
                $(`#inputPai${prefixo}`).val('');
                $(`#inputEndereco${prefixo}`).val('');

                // ✅ NOVO: Limpeza de Campos Complementares (Autor)
                const setEmpty = (id) => { if ($(id).length) $(id).val(''); };

                setEmpty(`#inputTipoPenal${prefixo}`);
                setEmpty(`#inputNmandado${prefixo}`);
                setEmpty(`#inputDataMandado${prefixo}`);
                setEmpty(`#inputFianca${prefixo}`);
                setEmpty(`#inputFiancaExt${prefixo}`);
                setEmpty(`#inputParente${prefixo}`);
                setEmpty(`#inputFamilia${prefixo}`);
                setEmpty(`#inputAdvogado${prefixo}`);
                setEmpty(`#inputJuizMandado${prefixo}`);
                setEmpty(`#inputComarcaMandado${prefixo}`);
                setEmpty(`#inputRecolher${prefixo}`);
                setEmpty(`#inputOfJuiz${prefixo}`);
                setEmpty(`#inputOfPromotor${prefixo}`);
                setEmpty(`#inputOfDefensor${prefixo}`);
                setEmpty(`#inputOfCustodia${prefixo}`);

                if ($(`#inputFiancaPago${prefixo}`).length) {
                    $(`#inputFiancaPago${prefixo}`).prop('checked', false).trigger('change');
                }
            };

            if (tipo === 'autores') {
                const meta = (this.vinculos.autores && this.vinculos.autores[index]) ? this.vinculos.autores[index] : null;
                // ✅ FIX: Prioritize ID from meta (link), then search result if any
                const pessoaId = meta ? (meta.pessoa_id || meta.id_cad) : null;
                const nomePessoa = this.envolvidos.autores[index];
                const open = () => { window.ocTabs.ensureTab('tab-autor', 'Autor', 'tabLinkAutor'); };
                const prefixo = 'Autor1';

                limparFormulario(prefixo);
                $('#autor1_id').val(pessoaId || '');

                if (pessoaId) {
                    // ✅ FORCE FETCH: Always fetch if we have an ID
                    const btn = $(`button[data-tipo="${tipo}"][data-index="${index}"].btn-edit-chip`);
                    if (btn.prop('disabled')) return; // Prevenir cliques duplos/loop infinito
                    const originalIcon = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

                    const rota = `/autor1/buscar/${pessoaId}`;
                    $.get(rota)
                        .done((resp) => {
                            const dados = resp.data || resp;
                            popularFormulario(prefixo, dados);
                            open();
                        })
                        .fail(() => {
                            console.warn('Falha ao buscar dados frescos. Usando dados locais.');
                            // Fallback logic
                            const det = (meta && meta.detalhes) || {};
                            if (det && Object.keys(det).length > 0) popularFormulario(prefixo, det);
                            else $(`#inputNome${prefixo}`).val(nomePessoa);
                            open();
                        })
                        .always(() => {
                            btn.html(originalIcon).prop('disabled', false);
                        });
                } else {
                    // Legacy/Local logic
                    const meta = this.vinculos.autores ? this.vinculos.autores[index] : null;
                    let det = (meta && meta.detalhes) || (OcorrenciasApp.obterDadosImportadosPorNome ? OcorrenciasApp.obterDadosImportadosPorNome(nomePessoa) : null) || {};

                    if (typeof OcorrenciasApp.extrairDetalhesDoTexto === 'function') {
                        const detTexto = OcorrenciasApp.extrairDetalhesDoTexto(nomePessoa);
                        if (detTexto) {
                            for (const key in detTexto) {
                                if (!det[key] || det[key] === 'NÃO INFORMADO') {
                                    det[key] = detTexto[key];
                                }
                            }
                        }
                    }
                    if (det && Object.keys(det).length > 0) { popularFormulario(prefixo, det); } else { $(`#inputNome${prefixo}`).val(nomePessoa); }
                    open();
                }
            } else if (tipo === 'vitimas') {
                const meta = (this.vinculos.vitimas && this.vinculos.vitimas[index]) ? this.vinculos.vitimas[index] : null;
                const pessoaId = meta ? (meta.pessoa_id || meta.id_cad) : null;
                const nomePessoa = this.envolvidos.vitimas[index];
                const open = () => { window.ocTabs.ensureTab('tab-vitima', 'Vítima', 'tabLinkVitima'); };
                const prefixo = 'Vitima1';

                limparFormulario(prefixo);
                $('#vitima1_id').val(pessoaId || '');

                if (pessoaId) {
                    const btn = $(`button[data-tipo="${tipo}"][data-index="${index}"].btn-edit-chip`);
                    if (btn.prop('disabled')) return;
                    const originalIcon = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

                    const rota = `/vitima1/buscar/${pessoaId}`;
                    $.get(rota)
                        .done((resp) => {
                            const dados = resp.data || resp;
                            popularFormulario(prefixo, dados);
                            open();
                        })
                        .fail(() => {
                            // Fallback
                            const det = (meta && meta.detalhes) || {};
                            if (det && Object.keys(det).length > 0) popularFormulario(prefixo, det);
                            else $(`#inputNome${prefixo}`).val(nomePessoa);
                            open();
                        })
                        .always(() => { btn.html(originalIcon).prop('disabled', false); });
                } else {
                    const meta = this.vinculos.vitimas ? this.vinculos.vitimas[index] : null;
                    let det = (meta && meta.detalhes) || (OcorrenciasApp.obterDadosImportadosPorNome ? OcorrenciasApp.obterDadosImportadosPorNome(nomePessoa) : null) || {};

                    if (typeof OcorrenciasApp.extrairDetalhesDoTexto === 'function') {
                        const detTexto = OcorrenciasApp.extrairDetalhesDoTexto(nomePessoa);
                        if (detTexto) {
                            for (const key in detTexto) {
                                if (!det[key] || det[key] === 'NÃO INFORMADO') {
                                    det[key] = detTexto[key];
                                }
                            }
                        }
                    }
                    if (det && Object.keys(det).length > 0) { popularFormulario(prefixo, det); } else { $(`#inputNome${prefixo}`).val(nomePessoa); }
                    open();
                }
            } else if (tipo === 'testemunhas') {
                const meta = (this.vinculos.testemunhas && this.vinculos.testemunhas[index]) ? this.vinculos.testemunhas[index] : null;
                const pessoaId = meta ? (meta.pessoa_id || meta.id_cad) : null;
                const nomePessoa = this.envolvidos.testemunhas[index];
                const open = () => { window.ocTabs.ensureTab('tab-testemunha', 'Testemunha', 'tabLinkTestemunha'); };
                const prefixo = 'Testemunha1';

                limparFormulario(prefixo);
                $('#testemunha1_id').val(pessoaId || '');

                if (pessoaId) {
                    const btn = $(`button[data-tipo="${tipo}"][data-index="${index}"].btn-edit-chip`);
                    if (btn.prop('disabled')) return;
                    const originalIcon = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

                    const rota = `/testemunha1/buscar/${pessoaId}`;
                    $.get(rota).done((resp) => {
                        const dados = resp.data || resp;
                        popularFormulario(prefixo, dados);
                        open();
                    }).fail(() => {
                        const det = (meta && meta.detalhes) || {};
                        if (det && Object.keys(det).length > 0) popularFormulario(prefixo, det);
                        else $(`#inputNome${prefixo}`).val(nomePessoa);
                        open();
                    }).always(() => { btn.html(originalIcon).prop('disabled', false); });
                } else {
                    const meta = this.vinculos.testemunhas ? this.vinculos.testemunhas[index] : null;

                    // 1. Obtém o que já existe (do backend ou importação)
                    let det = (meta && meta.detalhes) || (OcorrenciasApp.obterDadosImportadosPorNome ? OcorrenciasApp.obterDadosImportadosPorNome(nomePessoa) : null) || {};

                    // 2. Sempre tenta extrair do texto para enriquecer (pois o backend pode trazer campos vazios)
                    if (typeof OcorrenciasApp.extrairDetalhesDoTexto === 'function') {
                        const detTexto = OcorrenciasApp.extrairDetalhesDoTexto(nomePessoa);
                        if (detTexto) {
                            console.log('🧩 Mesclando dados do texto com dados existentes:', { det, detTexto });
                            // Mescla: prioriza o que veio do texto se o original estiver vazio/nulo
                            for (const key in detTexto) {
                                if (!det[key] || det[key] === 'NÃO INFORMADO') {
                                    det[key] = detTexto[key];
                                }
                            }
                        }
                    }

                    console.log('📋 Detalhes finais (Mesclados):', det);

                    if (det && Object.keys(det).length > 0) {
                        popularFormulario(prefixo, det);
                    } else {
                        $(`#inputNome${prefixo}`).val(nomePessoa);
                    }
                    open();
                }
            } else if (tipo === 'condutores') {
                const meta = (this.vinculos.condutores && this.vinculos.condutores[index]) ? this.vinculos.condutores[index] : null;
                const pessoaId = meta ? meta.pessoa_id : null;
                const nomePessoa = this.envolvidos.condutores[index];
                const open = () => { window.ocTabs.ensureTab('tab-condutor', 'Condutor', 'tabLinkCondutor'); };

                const prefixo = '';
                // Limpa campos do condutor
                $('#condutor_id').val(pessoaId || '');
                $('#formCondutor')[0]?.reset?.();

                if (pessoaId) {
                    const btn = $(`button[data-tipo="${tipo}"][data-index="${index}"].btn-edit-chip`);
                    if (btn.prop('disabled')) return;
                    const originalIcon = btn.html();
                    btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

                    const rota = `/condutor-apfd/buscar/${pessoaId}`;
                    $.get(rota).done((resp) => {
                        const dados = resp.data || resp;
                        // ✅ FIX: Remover acentos de todos os campos
                        const semAcento = (s) => typeof s === 'string' ? s.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : (s || '');
                        Object.keys(dados).forEach(k => { if (typeof dados[k] === 'string') dados[k] = semAcento(dados[k]); });
                        $('#inputNomeCondutor').val(dados.Nome || nomePessoa || '');
                        $('#inputAlcunha').val(dados.Alcunha || '');
                        let dataNasc = dados.Nascimento || '';
                        if (dataNasc && /^\d{4}-\d{2}-\d{2}$/.test(dataNasc)) {
                            const partes = dataNasc.split('-');
                            dataNasc = `${partes[2]}/${partes[1]}/${partes[0]}`;
                        }
                        $('#inputDataNascimento').val(dataNasc);

                        // Calcular idade automaticamente
                        let idadeCalculada = dados.Idade || '';
                        if (dataNasc) {
                            const partes = dataNasc.split('/');
                            if (partes.length === 3) {
                                const nascip = new Date(partes[2], partes[1] - 1, partes[0]);
                                if (!isNaN(nascip.getTime())) {
                                    const hoje = new Date();
                                    let idade = hoje.getFullYear() - nascip.getFullYear();
                                    const m = hoje.getMonth() - nascip.getMonth();
                                    if (m < 0 || (m === 0 && hoje.getDate() < nascip.getDate())) {
                                        idade--;
                                    }
                                    idadeCalculada = idade;
                                }
                            }
                        }
                        $('#inputIdade').val(idadeCalculada);
                        $('#inputEstadoCivil').val(dados.EstCivil || '');
                        $('#inputNaturalidade').val(dados.Naturalidade || '');
                        $('#inputInstrucao').val(dados.Instrucao || '');
                        $('#inputRG').val(dados.RG || '');
                        $('#inputCPF').val(dados.CPF || '');
                        $('#inputTelefone').val(dados.Telefone || '');
                        $('#inputProfissao').val(dados.Profissao || '');
                        $('#inputMae').val(dados.Mae || '');
                        $('#inputPai').val(dados.Pai || '');
                        $('#inputEndereco').val(dados.Endereco || '');
                        open();
                    }).fail(() => {
                        // Fallback minimal
                        $('#inputNomeCondutor').val(nomePessoa || '');
                        open();
                    }).always(() => { btn.html(originalIcon).prop('disabled', false); });
                } else {
                    // Tenta preencher com dados importados (chip vermelho sem vinculo)
                    const metaCond = this.vinculos.condutores ? this.vinculos.condutores[index] : null;
                    let detCond = (metaCond && metaCond.detalhes) || (OcorrenciasApp.obterDadosImportadosPorNome ? OcorrenciasApp.obterDadosImportadosPorNome(nomePessoa) : null) || {};
                    
                    if (typeof OcorrenciasApp.extrairDetalhesDoTexto === 'function') {
                        const detTexto = OcorrenciasApp.extrairDetalhesDoTexto(nomePessoa);
                        if (detTexto) {
                            for (const key in detTexto) {
                                if (!detCond[key] || detCond[key] === 'NÃO INFORMADO') {
                                    detCond[key] = detTexto[key];
                                }
                            }
                        }
                    }
                    
                    if (detCond && Object.keys(detCond).length > 0) {
                        $('#inputNomeCondutor').val(detCond.nome || nomePessoa || '');
                        $('#inputAlcunha').val(detCond.alcunha || '');
                        let dataNasc = detCond.nascimento || '';
                        if (dataNasc && dataNasc.includes('/')) {
                            const partes = dataNasc.split('/');
                            if (partes.length === 3) {
                                let d = partes[0].trim(); let m = partes[1].trim();
                                if (d.length === 1) d = '0' + d;
                                if (m.length === 1) m = '0' + m;
                                dataNasc = `${d}/${m}/${partes[2].trim()}`;
                            }
                        }
                        $('#inputDataNascimento').val(dataNasc);
                        if (dataNasc) {
                            const partes = dataNasc.split('/');
                            if (partes.length === 3) {
                                const nascip = new Date(partes[2], partes[1] - 1, partes[0]);
                                if (!isNaN(nascip.getTime())) {
                                    const hoje = new Date();
                                    let idade = hoje.getFullYear() - nascip.getFullYear();
                                    const mc = hoje.getMonth() - nascip.getMonth();
                                    if (mc < 0 || (mc === 0 && hoje.getDate() < nascip.getDate())) idade--;
                                    $('#inputIdade').val(idade);
                                }
                            }
                        }
                        $('#inputEstadoCivil').val(detCond.estado_civil || '');
                        $('#inputNaturalidade').val(detCond.naturalidade || '');
                        $('#inputInstrucao').val(detCond.instrucao || detCond.escolaridade || '');
                        $('#inputRG').val(detCond.rg || '');
                        $('#inputCPF').val(detCond.cpf || '');
                        let tel = detCond.telefone || ''; if (!tel || tel.trim() === '') tel = '(00) 00000-0000';
                        $('#inputTelefone').val(tel);
                        $('#inputProfissao').val(detCond.profissao || '');
                        $('#inputMae').val(detCond.mae || '');
                        $('#inputPai').val(detCond.pai || '');
                        $('#inputEndereco').val(detCond.endereco || '');
                    } else {
                        $('#inputNomeCondutor').val(nomePessoa || '');
                    }
                    open();
                }
            }
        });
        this.atualizarEstadoBotoes();
    },

    atualizarChipDoBanco: function (tipo, index) {
        const nome = (this.envolvidos[tipo] || [])[index];
        if (!nome) return;

        // ✅ FIX: Usar a mesma normalização do restante do app (UPPERCASE)
        const normaliza = (s) => this.normalizarNome(s);
        console.log('🔄 Atualizando chip do banco:', { tipo, index, nome });

        $.ajax({ url: '/pesquisar-pessoa', method: 'GET', dataType: 'json', data: { term: nome } })
            .done((lista) => {
                const alvo = normaliza(nome);

                // Tenta obter detalhes importados (CPF) para validação cruzada
                let cpfImportado = null;
                if (typeof this.extrairDetalhesDoTexto === 'function') {
                    const detalhes = this.extrairDetalhesDoTexto(nome);
                    if (detalhes && detalhes.cpf) {
                        cpfImportado = detalhes.cpf.replace(/\D/g, '');
                    }
                }

                // 1. Tenta encontrar correspondência EXATA de nome
                let item = (lista || []).find((x) => {
                    const nomeItem = x.Nome || x.nome || '';
                    return normaliza(nomeItem) === alvo;
                });

                // 2. Se não achou exato, mas temos CPF importado, verifica se algum candidato bate o CPF
                if (!item && cpfImportado) {
                    item = (lista || []).find((x) => {
                        const cpfCand = (x.CPF || x.cpf || '').replace(/\D/g, '');
                        return cpfCand === cpfImportado;
                    });
                    if (item) {
                        console.log('✅ Match por CPF encontrado para:', nome);
                    }
                }

                // 🚨 FALLBACK CEGO REMOVIDO: || (lista || [])[0]
                // Isso causava falsos positivos (ex: "TEREZE" match "TEREZE CRISTINA CAVALCANTE")

                const id = item ? (item.id || item.IdCad) : null;
                const nomeEncontrado = item ? (item.Nome || item.nome) : null;

                this.vinculos[tipo] = this.vinculos[tipo] || [];

                if (id) {
                    // ✅ FIX: Atualiza o nome no array de envolvidos se achou um nome oficial
                    if (nomeEncontrado && nomeEncontrado !== nome) {
                        console.log(`📝 Corrigindo nome do chip: "${nome}" -> "${nomeEncontrado}"`);
                        this.envolvidos[tipo][index] = nomeEncontrado;
                    }

                    this.vinculos[tipo][index] = { nome: nomeEncontrado || nome, pessoa_id: id };
                    console.log('✅ Vínculo aplicado ao chip:', { tipo, index, id });

                    // ✅ DESATIVADO: A persistência automática agora é centralizada em sugerirVinculoEmTempoReal
                    // ou no "Salvar" geral para evitar conflitos de status (pendente vs aprovado).
                    /*
                    const boe = ($('#inputBOE').val() || '').trim();
                    const tipoMap = { vitimas: 'VITIMA', autores: 'AUTOR', testemunhas: 'TESTEMUNHA', condutores: 'CONDUTOR' };
                    const tipoVinc = tipoMap[tipo];
                    if (boe && boe !== 'N/A' && tipoVinc) {
                        $.ajax({
                            url: '/boe/vinculos/adicionar',
                            method: 'POST',
                            data: { boe, pessoa_id: id, tipo: tipoVinc, _token: $('meta[name="csrf-token"]').attr('content') }
                        }).done((resp) => {
                            console.log('💾 Vínculo BOE persistido:', resp);
                        }).fail((xhr) => {
                            console.warn('⚠️ Falha ao persistir vínculo BOE:', xhr.responseJSON || xhr.status);
                        });
                    }
                    */
                } else {
                    // ✅ FIX: Se não encontrou, remove o vínculo para o chip ficar vermelho
                    console.warn('❌ Pessoa não encontrada (Nome exato ou CPF). Removendo vínculo do chip:', nome);
                    this.vinculos[tipo][index] = { nome: nome, pessoa_id: null };

                    // Não mostrar erro intrusivo para cada chip não encontrado, apenas logar e deixar vermelho
                    // if (this.mostrarErro) this.mostrarErro('Pessoa não encontrada no cadastro.');
                }
            })
            .fail((xhr) => {
                console.error('❌ Erro ao pesquisar pessoa:', xhr.responseJSON || xhr.status);
                if (this.mostrarErro) this.mostrarErro('Erro ao consultar o cadastro de pessoas.');
            })
            .always(() => {
                // ✅ FIX: Usar 'this' pois OcorrenciasApp pode não estar no window (const)
                console.log('🔄 Finalizando atualização do chip, chamando renderização...');
                this.deduplicarChips(tipo);
                this.atualizarChips(tipo);
                if (typeof this.atualizarEstadoBotoes === 'function') {
                    this.atualizarEstadoBotoes();
                }
            });
    },



    atualizarEstadoBotoes: function () {
        const tipos = ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'];
        let temChipVermelho = false;

        tipos.forEach(tipo => {
            const nomes = this.envolvidos[tipo] || [];
            const vinculos = this.vinculos[tipo] || [];

            nomes.forEach((nome, index) => {
                const vinculo = vinculos[index];
                const estaVinculado = !!(vinculo && (vinculo.pessoa_id || vinculo.vinculo_id));
                if (!estaVinculado) {
                    temChipVermelho = true;
                }
            });
        });

        const $btnSalvar = $('#btnSalvar');
        const $btnEditar = $('#btnEditar');

        if (temChipVermelho) {
            $btnSalvar.prop('disabled', true);
            $btnEditar.prop('disabled', true);

            // Opcional: Adicionar tooltip ou aviso visual
            if (!$('#avisoChipVermelho').length) {
                $('#acoes-botoes').append('<div id="avisoChipVermelho" class="text-danger mt-2 small"><i class="bi bi-exclamation-triangle"></i> Resolva os itens em vermelho antes de salvar.</div>');
            }
        } else {
            // ✅ CORREÇÃO: Salvar só habilita para NOVOS registros (sem ID válido)
            const isNovo = !this.currentId || this.currentId === '' || this.currentId === 'null' || this.currentId === 0;
            
            if (isNovo && !$btnSalvar.html().includes('spinner')) {
                $btnSalvar.prop('disabled', false);
            } else {
                $btnSalvar.prop('disabled', true);
            }

            // Editar só habilita se tiver ID selecionado e for o dono
            const isOwner = this.isOwner !== undefined ? this.isOwner : true;
            if (!isNovo && isOwner && !$btnEditar.html().includes('spinner')) {
                $btnEditar.prop('disabled', false);
            } else {
                $btnEditar.prop('disabled', true);
            }
            $('#avisoChipVermelho').remove();
        }
    },

    normalizarNome: function (s) {
        return (s || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toUpperCase()
            .replace(/\s+/g, ' ')
            .trim();
    },

    addOrLinkChip: function (tipoPlural, nome, pessoaId) {
        const tiposValidos = ['vitimas', 'autores', 'testemunhas', 'condutores'];
        if (!tiposValidos.includes(tipoPlural)) return;
        const nomes = this.envolvidos[tipoPlural] || [];
        const vincs = this.vinculos[tipoPlural] || [];
        const alvo = this.normalizarNome(nome);

        let idx = -1;
        for (let i = 0; i < nomes.length; i++) {
            if (this.normalizarNome(nomes[i]) === alvo) { idx = i; break; }
        }

        if (idx >= 0) {
            this.vinculos[tipoPlural] = this.vinculos[tipoPlural] || [];
            const reg = this.vinculos[tipoPlural][idx] || { nome: nomes[idx] };
            reg.pessoa_id = pessoaId || reg.pessoa_id || null;
            this.vinculos[tipoPlural][idx] = reg;
        } else {
            nomes.push(nome);
            this.envolvidos[tipoPlural] = nomes;
            this.vinculos[tipoPlural] = vincs;
            this.vinculos[tipoPlural].push({ nome, pessoa_id: pessoaId || null });
        }

        this.deduplicarChips(tipoPlural);
        this.atualizarChips(tipoPlural);
        this.atualizarEstadoBotoes();
    },

    deduplicarChips: function (tipoPlural) {
        const nomes = this.envolvidos[tipoPlural] || [];
        const vincs = this.vinculos[tipoPlural] || [];
        const mapa = {};
        for (let i = 0; i < nomes.length; i++) {
            const key = this.normalizarNome(nomes[i]);
            const vinc = vincs[i] || { nome: nomes[i] };
            if (!mapa[key]) {
                mapa[key] = { nome: nomes[i], vinculo: vinc };
            } else {
                const atual = mapa[key];
                const atualTem = !!(atual.vinculo && (atual.vinculo.pessoa_id || atual.vinculo.vinculo_id));
                const novoTem = !!(vinc && (vinc.pessoa_id || vinc.vinculo_id));
                if (novoTem && !atualTem) {
                    mapa[key] = { nome: nomes[i], vinculo: vinc };
                }
            }
        }
        const novosNomes = [];
        const novosVinculos = [];
        Object.keys(mapa).forEach(k => {
            novosNomes.push(mapa[k].nome);
            novosVinculos.push(mapa[k].vinculo);
        });
        this.envolvidos[tipoPlural] = novosNomes;
        this.vinculos[tipoPlural] = novosVinculos;
    },

    deduplicarTodosChips: function () {
        ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(t => this.deduplicarChips(t));
        this.atualizarChips('vitimas');
        this.atualizarChips('autores');
        this.atualizarChips('testemunhas');
        this.atualizarChips('condutores');
        this.atualizarChips('outros');
        this.atualizarEstadoBotoes();
    },

    // ✅ NOVO: Preparar dados dos envolvidos para envio
    prepararDadosEnvolvidos: function () {
        // Remover campos anteriores
        $('[name^="vitimas"], [name^="autores"], [name^="testemunhas"], [name^="condutores"], [name^="outros"], [name="envolvidos_json"]').remove();

        const hasChipsModule = (typeof window.obterEnvolvidosParaSalvar === 'function') && window.envolvidosChips;

        // ✅ FIX: Se o módulo de chips está ativo, ele é a ÚNICA fonte de verdade.
        // NÃO fazer fallback para arrays internos (que podem conter dados "sujos" de um BOE anterior).
        let nomesEscolhidos;
        if (hasChipsModule) {
            nomesEscolhidos = {
                vitimas: (window.envolvidosChips.vitimas || []).map(v => v.nome).filter(Boolean),
                autores: (window.envolvidosChips.autores || []).map(a => a.nome).filter(Boolean),
                testemunhas: (window.envolvidosChips.testemunhas || []).map(t => t.nome).filter(Boolean),
                condutores: (window.envolvidosChips.condutores || []).map(c => c.nome).filter(Boolean),
                outros: (window.envolvidosChips.outros || []).map(o => o.nome).filter(Boolean)
            };
        } else {
            // Fallback: sem módulo de chips, usar arrays internos do app
            nomesEscolhidos = {
                vitimas: (this.envolvidos.vitimas || []).slice(),
                autores: (this.envolvidos.autores || []).slice(),
                testemunhas: (this.envolvidos.testemunhas || []).slice(),
                condutores: (this.envolvidos.condutores || []).slice(),
                outros: (this.envolvidos.outros || []).slice()
            };
        }

        console.log('📋 [prepararDadosEnvolvidos] Fonte:', hasChipsModule ? 'chips' : 'app', '| Dados:', JSON.stringify(nomesEscolhidos));

        nomesEscolhidos.vitimas.forEach((nome, index) => {
            $('#formInicio').append(`<input type="hidden" name="vitimas[${index}]" value="${nome}">`);
        });
        nomesEscolhidos.autores.forEach((nome, index) => {
            $('#formInicio').append(`<input type="hidden" name="autores[${index}]" value="${nome}">`);
        });
        nomesEscolhidos.testemunhas.forEach((nome, index) => {
            $('#formInicio').append(`<input type="hidden" name="testemunhas[${index}]" value="${nome}">`);
        });
        nomesEscolhidos.condutores.forEach((nome, index) => {
            $('#formInicio').append(`<input type="hidden" name="condutores[${index}]" value="${nome}">`);
        });
        nomesEscolhidos.outros.forEach((nome, index) => {
            $('#formInicio').append(`<input type="hidden" name="outros[${index}]" value="${nome}">`);
        });

        if (hasChipsModule) {
            const dadosChips = window.obterEnvolvidosParaSalvar();
            const jsonDados = JSON.stringify(dadosChips);
            $('#formInicio').append(`<input type="hidden" name="envolvidos_json" value='${jsonDados}'>`);
            console.log('✅ Dados dos chips preparados para envio (módulo chips):', dadosChips);
        } else {
            console.log('✅ Dados dos chips preparados para envio (OcorrenciasApp):', nomesEscolhidos);
        }
    },

    // ✅ NOVO: Carregar envolvidos do registro
    carregarEnvolvidos: function (dados) {
        // Limpar arrays internos antes de atribuir novos
        this.envolvidos = {
            vitimas: dados.vitimas ? dados.vitimas.slice() : [],
            autores: dados.autores ? dados.autores.slice() : [],
            testemunhas: dados.testemunhas ? dados.testemunhas.slice() : [],
            condutores: dados.condutores ? dados.condutores.slice() : [],
            outros: dados.outros ? dados.outros.slice() : []
        };

        // Reseta vínculos ao carregar novos dados básicos
        this.vinculos = { vitimas: [], autores: [], testemunhas: [], condutores: [], outros: [] };

        // Atualizar chips
        this.atualizarChips('vitimas');
        this.atualizarChips('autores');
        this.atualizarChips('testemunhas');
        this.atualizarChips('condutores');
        this.atualizarChips('outros');
    },

    // ✅ Novo: Carregar vínculos do BOE e hidratar chips com vinculo_id
    carregarVinculosDoBoe: function (boe) {
        if (!boe || boe === 'N/A') return;
        $.ajax({
            url: "/boe/vinculos/listar/" + encodeURIComponent(boe),
            method: 'GET',
            success: (response) => {
                if (response.success && response.data) {
                    const data = response.data;

                    // ✅ NOVO: Capturar flag de propriedade
                    this.isOwner = response.is_owner !== undefined ? response.is_owner : true;
                    this.ownerName = response.owner_name || null;

                    // ✅ BACKUP: Preservando os chips que o usuário adicionou manualmente (sem vinculo_id no banco)
                    const backupManuais = { vitimas: [], autores: [], testemunhas: [], condutores: [], outros: [] };
                    ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(tipo => {
                        (this.vinculos[tipo] || []).forEach((v, index) => {
                            if (v && !v.vinculo_id) {
                                backupManuais[tipo].push({
                                    nome: this.envolvidos[tipo] && this.envolvidos[tipo][index] ? this.envolvidos[tipo][index] : v.nome,
                                    vinculo: v,
                                    chipCompleto: (window.envolvidosChips && window.envolvidosChips[tipo]) ? window.envolvidosChips[tipo].find(c => c.nome === (this.envolvidos[tipo][index] || v.nome)) : null
                                });
                            }
                        });
                    });

                    this.envolvidos.vitimas = (data.vitimas || []).map(p => p.Nome || p.nome || '');
                    this.envolvidos.autores = (data.autores || []).map(p => p.Nome || p.nome || '');
                    this.envolvidos.testemunhas = (data.testemunhas || []).map(p => p.Nome || p.nome || '');
                    this.envolvidos.condutores = (data.condutor || []).map(p => p.Nome || p.nome || '');
                    this.envolvidos.outros = (data.outros || []).map(p => p.Nome || p.nome || '');

                    // ✅ NOVO: Incluir status_aprovacao, criado_por_nome e vinculo_id nos vínculos
                    const mapVinculo = (p) => ({
                        nome: p.Nome || p.nome || '',
                        vinculo_id: p.vinculo_id,
                        pessoa_id: p.IdCad || p.id,
                        status_aprovacao: p.status_aprovacao || 'aprovado',
                        criado_por_nome: p.criado_por_nome || null
                    });

                    this.vinculos.vitimas = (data.vitimas || []).map(mapVinculo);
                    this.vinculos.autores = (data.autores || []).map(mapVinculo);
                    this.vinculos.testemunhas = (data.testemunhas || []).map(mapVinculo);
                    this.vinculos.condutores = (data.condutor || []).map(mapVinculo);
                    this.vinculos.outros = (data.outros || []).map(mapVinculo);

                    // ✅ MERGE: Restaurar os envolvidos manuais que não vieram do banco
                    ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(tipo => {
                        backupManuais[tipo].forEach(itemManual => {
                            if (itemManual.nome && !this.envolvidos[tipo].includes(itemManual.nome)) {
                                this.envolvidos[tipo].push(itemManual.nome);
                                this.vinculos[tipo].push(itemManual.vinculo);
                            }
                        });
                    });

                    this.atualizarChips('vitimas');
                    this.atualizarChips('autores');
                    this.atualizarChips('testemunhas');
                    this.atualizarChips('condutores');
                    this.atualizarChips('outros');

                    // ✅ NOVO: Se for o dono, inicia monitoramento de novas sugestões
                    if (this.isOwner) {
                        this.iniciarPollingSugestoes(boe);
                    } else {
                        this.pararPollingSugestoes();
                    }

                    // ✅ NOVO: Mostrar banner de propriedade se não for dono
                    this.mostrarBannerPropriedade();

                    // Sincronizar com window.envolvidosChips para garantir salvamento correto
                    if (window.envolvidosChips) {
                        window.envolvidosChips.vitimas = (data.vitimas || []).map(p => ({
                            id: p.IdCad || p.id || Date.now(),
                            nome: p.Nome || p.nome || '',
                            dados: p
                        }));
                        window.envolvidosChips.autores = (data.autores || []).map(p => ({
                            id: p.IdCad || p.id || Date.now(),
                            nome: p.Nome || p.nome || '',
                            dados: p
                        }));
                        window.envolvidosChips.testemunhas = (data.testemunhas || []).map(p => ({
                            id: p.IdCad || p.id || Date.now(),
                            nome: p.Nome || p.nome || '',
                            dados: p,
                            rg: p.RG || p.rg || '',
                            cpf: p.CPF || p.cpf || ''
                        }));
                        window.envolvidosChips.condutores = (data.condutor || []).map(p => ({
                            id: p.IdCad || p.id || Date.now(),
                            nome: p.Nome || p.nome || '',
                            dados: p
                        }));
                        window.envolvidosChips.outros = (data.outros || []).map(p => ({
                            id: p.IdCad || p.id || Date.now(),
                            nome: p.Nome || p.nome || '',
                            dados: p
                        }));

                        // ✅ MERGE CHIPS LAB/FORMS: Restaurar arrays inteiros das abas manuais sem perder os dados dos inputs
                        ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(tipo => {
                            backupManuais[tipo].forEach(itemManual => {
                                if (itemManual.chipCompleto && itemManual.nome && !window.envolvidosChips[tipo].find(c => c.nome === itemManual.nome)) {
                                    window.envolvidosChips[tipo].push(itemManual.chipCompleto);
                                }
                            });
                        });

                        console.log('✅ Chips sincronizados com dados do banco (preservados manuais combinados):', window.envolvidosChips);
                    }

                    if (this.currentId) {
                        $.ajax({
                            url: `/apfd/detalhes/listar/${this.currentId}`,
                            method: 'GET',
                            success: (det) => {
                                if (det.success && det.data) {
                                    const d = det.data;
                                    this.detalhes = { AUTOR: {}, VITIMA: {}, TESTEMUNHA: {} };
                                    (d.AUTOR || []).forEach(x => { this.detalhes.AUTOR[x.pessoa_id] = x; });
                                    (d.VITIMA || []).forEach(x => { this.detalhes.VITIMA[x.pessoa_id] = x; });
                                    (d.TESTEMUNHA || []).forEach(x => { this.detalhes.TESTEMUNHA[x.pessoa_id] = x; });
                                }
                            }
                        });
                    }
                }
            },
            error: (xhr) => {
                console.error('Erro ao listar vínculos do BOE:', xhr);
            }
        });
    },

    conciliarEnvolvidosBD: function (tipos = ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros']) {
        const normaliza = (s) => this.normalizarNome(s);
        const buscar = (termo) => $.ajax({ url: '/pesquisar-pessoa', method: 'GET', dataType: 'json', data: { term: termo } });

        const tarefas = [];
        const self = this;

        tipos.forEach((tipo) => {
            // ✅ FIX: Preservar detalhes já armazenados (vindos da extração do BOE)
            // Em vez de zerar self.vinculos[tipo] = [], salvamos os detalhes existentes
            // para re-aplicar após a conciliação, evitando a race condition.
            const detalhesPreservados = {};
            (self.vinculos[tipo] || []).forEach((vinc, idx) => {
                if (vinc && vinc.detalhes) {
                    const nome = self.envolvidos[tipo][idx] || vinc.nome;
                    if (nome) detalhesPreservados[nome] = vinc.detalhes;
                }
            });
            self.vinculos[tipo] = [];

            (self.envolvidos[tipo] || []).forEach((nome, index) => {
                // Tenta pegar CPF e Nascimento dos detalhes importados
                let detImportado = null;
                if (self.dadosImportados && self.dadosImportados[nome]) {
                    detImportado = self.dadosImportados[nome];
                }

                let cpfImportado = detImportado ? detImportado.cpf : null;
                const nascImportado = detImportado ? detImportado.nascimento : null;

                // Limpa o CPF para ver se realmente tem números (evita lixo da IA como "NÃO INFORMADO")
                const cpfNumeros = cpfImportado ? cpfImportado.replace(/[^\d]/g, '') : '';
                if (cpfNumeros.length < 3) {
                    cpfImportado = null; // Invalida se for lixo de texto
                }

                // BUSCA PELO NOME!
                // Por que não pelo CPF? Porque se a IA extrair um CPF, mas o banco não tiver esse CPF salvo (campo vazio), 
                // a query do backend vai falhar e retornar 0 pessoas.
                // Buscando pelo NOME, o backend traz a pessoa (mesmo sem CPF) e os filtros Nível 1/2/3 conferem o resto.
                const termoBusca = nome;

                tarefas.push(
                    buscar(termoBusca).done((lista) => {
                        const alvoNome = normaliza(nome);
                        const alvoCpf = cpfImportado ? cpfImportado.replace(/[^\d]/g, '') : null;
                        const alvoNasc = nascImportado ? nascImportado.trim() : null;

                        let item = null;

                        // --- TRIPLE CHECK LOGIC ---

                        // Nível 1: Match por CPF (Absolute)
                        if (alvoCpf) {
                            item = (lista || []).find(x => {
                                const cpfBanco = (x.cpf || '').replace(/[^\d]/g, '');
                                return cpfBanco === alvoCpf;
                            });
                        }

                        // Nível 2: Match por Nome + Data de Nascimento (High Certainty)
                        if (!item && alvoNasc) {
                            item = (lista || []).find(x => {
                                const nomeMatch = normaliza(x.nome) === alvoNome;
                                // Converte data do banco (Y-m-d) para d/m/Y se necessário para comparar com o BOE
                                let nascBanco = x.nascimento || '';
                                if (nascBanco.includes('-')) {
                                    const partes = nascBanco.split('-');
                                    nascBanco = `${partes[2]}/${partes[1]}/${partes[0]}`;
                                }
                                const nascMatch = nascBanco.trim() === alvoNasc;
                                return nomeMatch && nascMatch;
                            });
                        }

                        // Nível 3: Match por Nome apenas (Strict)
                        if (!item) {
                            item = (lista || []).find(x => {
                                const nomeMatch = normaliza(x.nome) === alvoNome;
                                if (!nomeMatch) return false;

                                // Se o nome bate, mas a data de nascimento no banco é DIFERENTE da do BOE, NÃO vincula.
                                let nascBanco = x.nascimento || '';
                                if (nascBanco.includes('-')) {
                                    const partes = nascBanco.split('-');
                                    nascBanco = `${partes[2]}/${partes[1]}/${partes[0]}`;
                                }

                                if (alvoNasc && nascBanco && nascBanco.trim() !== alvoNasc) {
                                    return false; // Conflito de data de nascimento -> ignora
                                }

                                return true; // Sem data de nascimento para comparar ou data bateu
                            });
                        }

                        const id = item ? (item.id || item.IdCad) : null;
                        
                        // ✅ FIX: Restaurar detalhes preservados
                        const detalhes = detalhesPreservados[nome] || null;
                        const vincAtual = self.vinculos[tipo][index] || {};
                        
                        if (id) {
                            self.vinculos[tipo][index] = Object.assign({}, vincAtual, { nome: nome, pessoa_id: id, detalhes: detalhes });
                        } else {
                            // Mantém o objeto de detalhes, só crava que pessoa_id é null
                            self.vinculos[tipo][index] = Object.assign({}, vincAtual, { nome: nome, pessoa_id: null, detalhes: detalhes });
                        }
                    })
                );
            });
        });

        $.when.apply($, tarefas).always(() => {
            self.atualizarChips('vitimas');
            self.atualizarChips('autores');
            self.atualizarChips('testemunhas');
            self.atualizarChips('condutores');
            self.atualizarChips('outros');
            // ✅ Garante que o estado dos botões seja atualizado após carregar todos os chips
            self.atualizarEstadoBotoes();
        });
    },

    // ✅ CORREÇÃO COMPLETA: Sistema de autocomplete para documentos (IGUAL AO DO CONDUTOR)
    setupDocumentosAutocomplete: function () {
        const documentos = [
            'DESPACHO DE CONCLUSAO',
            'ROL DE TESTEMUNHAS',
            // NOVOS DOCUMENTOS
            'AVALIACAO DE OBJETOS - COMPLETO',
            'AVALIACAO DE OBJETOS - PORTARIA',
            'AVALIACAO DE OBJETOS - AUTO',
            // AVALIAÇÃO INDIRETA
            'AVALIACAO INDIRETA DE OBJETOS - COMPLETO',
            'AVALIACAO INDIRETA DE OBJETOS - PORTARIA',
            'AVALIACAO INDIRETA DE OBJETOS - AUTO',
            // EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO
            'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - COMPLETO',
            'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - PORTARIA',
            'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - AUTO',
            // EXAME DE CONSTATAÇÃO DE DANOS INDIRETA
            'EXAME DE CONSTATACAO DE DANOS INDIRETA - COMPLETO',
            'EXAME DE CONSTATACAO DE DANOS INDIRETA - PORTARIA',
            'EXAME DE CONSTATACAO DE DANOS INDIRETA - AUTO',
            // EXAME DE EFICIÊNCIA DE ARMA DE FOGO
            'EXAME DE EFICIENCIA DE ARMA DE FOGO - COMPLETO',
            'EXAME DE EFICIENCIA DE ARMA DE FOGO - PORTARIA',
            'EXAME DE EFICIENCIA DE ARMA DE FOGO - AUTO',
            'PERICIA EM VEICULO',
            'PERICIA EM LOCAL DE CRIME',
        ];

        let selectedIndex = -1;
        let sugestoesAtuais = [];

        const $inputDocumento = $('#termoDocumentoInicio');
        const $sugestoesContainer = $('#sugestoesDocumentosInicio');

        // ✅ CORREÇÃO: Verificar se os elementos existem
        if (!$inputDocumento.length || !$sugestoesContainer.length) {
            console.error('❌ Elementos do autocomplete não encontrados!');
            return;
        }

        console.log('✅ Inicializando autocomplete para documentos...');

        function filtrarSugestoes(termo) {
            if (!termo) return [];
            termo = termo.toUpperCase();
            return documentos.filter(doc => doc.toUpperCase().includes(termo));
        }

        function mostrarSugestoes(sugestoes) {
            $sugestoesContainer.empty();
            selectedIndex = -1;
            sugestoesAtuais = sugestoes;

            if (!sugestoes.length) {
                $sugestoesContainer.hide();
                return;
            }

            sugestoes.forEach((sugestao, index) => {
                const $item = $('<a>', {
                    class: 'list-group-item list-group-item-action',
                    href: '#',
                    text: sugestao,
                    'data-index': index
                });

                $item.on('mouseenter', function () {
                    $sugestoesContainer.find('a').removeClass('active');
                    $(this).addClass('active');
                    selectedIndex = index;
                });

                $item.on('click', function (e) {
                    e.preventDefault();
                    $inputDocumento.val(sugestao);
                    $sugestoesContainer.hide();
                    $inputDocumento.focus();
                    console.log('✅ Documento selecionado:', sugestao);
                });

                $sugestoesContainer.append($item);
            });

            $sugestoesContainer.show();
        }

        // ✅ CORREÇÃO: Eventos do autocomplete
        $inputDocumento.off('input').on('input', function () {
            const termo = $(this).val().trim();
            console.log('🔍 Pesquisando:', termo);
            const sugestoes = filtrarSugestoes(termo);
            console.log('📋 Sugestões encontradas:', sugestoes);
            mostrarSugestoes(sugestoes);
        });

        $inputDocumento.off('keydown').on('keydown', function (e) {
            const $items = $sugestoesContainer.find('a');
            if (!$items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + $items.length) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                $inputDocumento.val(sugestoesAtuais[selectedIndex]);
                $sugestoesContainer.hide();
            } else if (e.key === 'Escape') {
                $sugestoesContainer.hide();
            }
        });

        // ✅ CORREÇÃO: Fechar sugestões ao clicar fora
        $(document).off('click.autocomplete').on('click.autocomplete', function (e) {
            if (!$inputDocumento.is(e.target) &&
                !$sugestoesContainer.is(e.target) &&
                $sugestoesContainer.has(e.target).length === 0) {
                $sugestoesContainer.hide();
            }
        });

        $inputDocumento.off('blur').on('blur', function () {
            setTimeout(() => $sugestoesContainer.hide(), 150);
        });

        console.log('✅ Autocomplete inicializado com sucesso!');
    },

    // ✅ NOVO: Função para impressão de documentos
    imprimirDocumentoInicio: function () {
        const documentoSelecionado = $('#termoDocumentoInicio').val().trim().toUpperCase();

        // Validações básicas
        if (!documentoSelecionado) {
            this.mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        // ✅ NOVA VALIDAÇÃO: Campos obrigatórios para impressão
        const camposObrigatorios = {
            'BOE': $('#inputBOE').val().trim(),
            'Delegado': $('#inputDelegado').val().trim(),
            'Escrivão': $('#inputEscrivao').val().trim(),
            'Delegacia': $('#inputDelegacia').val().trim(),
            'Cidade': $('#inputCidade').val().trim()
        };

        // Verifica campos vazios
        const camposVazios = [];
        for (const [campo, valor] of Object.entries(camposObrigatorios)) {
            if (!valor) {
                camposVazios.push(campo);
            }
        }

        if (camposVazios.length > 0) {
            this.mostrarErro(`Para imprimir o documento, preencha os seguintes campos obrigatórios: ${camposVazios.join(', ')}`);
            return;
        }

        // Verifica se as rotas de impressão estão definidas
        if (typeof rotasImpressaoInicio === 'undefined') {
            this.mostrarErro('Rotas de impressão não configuradas. Recarregue a página.');
            return;
        }

        // Captura os valores dos campos principais
        const data = $('#inputData').val();
        const dataComp = $('#inputDataComp').val();
        const dataExtenso = $('#inputDataExt').val();
        const cidade = $('#inputCidade').val();
        const delegado = $('#inputDelegado').val();
        const escrivao = $('#inputEscrivao').val();
        const delegacia = $('#inputDelegacia').val();
        const boe = $('#inputBOE').val();
        const apreensao = $('#inputApreensao').val();

        // Cria um objeto com todos os dados necessários
        const dados = {
            // Dados do formulário principal
            data: data,
            data_comp: dataComp,
            data_ext: dataExtenso,
            cidade: cidade,
            delegado: delegado,
            escrivao: escrivao,
            delegacia: delegacia,
            boe: boe,
            apreensao: apreensao,
            // Campos que podem ser usados em documentos genéricos
            ip: $('#inputIP').val(),
            boe_pm: $('#inputBOEPM').val(),
            policial_1: $('#inputPolicial1').val(),
            policial_2: $('#inputPolicial2').val(),
            local_fato: $('#inputEndFato').val(),
            data_fato: (() => {
                const d = $('#inputDataFato').val();
                if (d && d.includes('-')) {
                    const p = d.split('-');
                    return `${p[2]}/${p[1]}/${p[0]}`;
                }
                return d;
            })()
        };

        // ✅ INSERIR DADOS DAS VÍTIMAS DO CHIP (MÚLTIPLOS CHIPS)
        // Isso permite gerar documentos com múltiplas vítimas
        if (window.envolvidosChips && window.envolvidosChips.vitimas && window.envolvidosChips.vitimas.length > 0) {
            dados.lista_vitimas = []; // Inicializa array para múltiplas vítimas

            window.envolvidosChips.vitimas.forEach((vitimaChip, index) => {
                let chipDados = {};

                // 1. Converter "dados" se for Array
                if (Array.isArray(vitimaChip.dados)) {
                    vitimaChip.dados.forEach(field => {
                        chipDados[field.name] = field.value;
                    });
                } else if (typeof vitimaChip.dados === 'object') {
                    chipDados = { ...vitimaChip.dados };
                }

                // 2. Merge com dados raiz
                Object.assign(chipDados, vitimaChip);

                // 3. Normalização
                let dadosNormalizados = {};
                const mapCampos = {
                    'nome': ['Nome', 'nome'],
                    'nascimento': ['Nascimento', 'nascimento', 'data_nascimento'],
                    'idade': ['Idade', 'idade'],
                    'rg': ['RG', 'rg'],
                    'cpf': ['CPF', 'cpf'],
                    'pai': ['Pai', 'pai'],
                    'mae': ['Mae', 'mae'],
                    'endereco': ['Endereco', 'endereco']
                };

                Object.keys(mapCampos).forEach(targetKey => {
                    const sourceKeys = mapCampos[targetKey];
                    dadosNormalizados[targetKey] = 'NÃO INFORMADO'; // Valor padrão
                    for (const source of sourceKeys) {
                        if (chipDados[source]) {
                            dadosNormalizados[targetKey] = chipDados[source];
                            break;
                        }
                    }
                });

                // Tratamento de data nascimento
                if (dadosNormalizados.nascimento && dadosNormalizados.nascimento.includes('-')) {
                    const parts = dadosNormalizados.nascimento.split('-');
                    if (parts.length === 3) {
                        dadosNormalizados.nascimento = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    }
                }

                // Adiciona à lista de vítimas
                dados.lista_vitimas.push(dadosNormalizados);

                // Se for a primeira vítima, preenche também os campos principais (fallback/legacy)
                if (index === 0) {
                    Object.keys(dadosNormalizados).forEach(key => {
                        if (!dados[key] || dados[key] === 'NÃO INFORMADO') {
                            dados[key] = dadosNormalizados[key];
                        }
                    });
                }
            });
        }

        // ✅ INSERIR TESTEMUNHAS DINAMICAMENTE
        if (window.envolvidosChips && window.envolvidosChips.testemunhas) {
            window.envolvidosChips.testemunhas.forEach((testemunhaChip, index) => {
                const key = `testemunha${index + 1}`;
                let chipDados = {};

                // 1. Converter "dados" se for Array (serializeArray de formulário)
                if (Array.isArray(testemunhaChip.dados)) {
                    testemunhaChip.dados.forEach(field => {
                        chipDados[field.name] = field.value;
                    });
                } else if (typeof testemunhaChip.dados === 'object') {
                    chipDados = { ...testemunhaChip.dados };
                }

                // 2. Merge com dados raiz do chip (para casos vindos do banco via carregarVinculosDoBoe)
                Object.assign(chipDados, testemunhaChip);

                // 3. Normalização COMPLETA de campos (Mapeamento DB -> Blade)
                const mapCampos = {
                    'nome': ['Nome', 'nome'],
                    'alcunha': ['Alcunha', 'alcunha', 'apelido'],
                    'rg': ['RG', 'rg'],
                    'cpf': ['CPF', 'cpf'],
                    'pai': ['Pai', 'pai'],
                    'mae': ['Mae', 'mae'],
                    'endereco': ['Endereco', 'endereco'],
                    'profissao': ['Profissao', 'profissao'],
                    'naturalidade': ['Naturalidade', 'naturalidade'],
                    'estcivil': ['EstCivil', 'estcivil', 'estado_civil'],
                    'instrucao': ['Instrucao', 'instrucao', 'grau_instrucao'],
                    'telefone': ['Telefone', 'telefone']
                };

                // Aplica o mapeamento
                Object.keys(mapCampos).forEach(targetKey => {
                    const sourceKeys = mapCampos[targetKey];
                    // Tenta encontrar valor em qualquer uma das chaves de origem
                    for (const source of sourceKeys) {
                        if (chipDados[source]) {
                            chipDados[targetKey] = chipDados[source];
                            break;
                        }
                    }
                    if (!chipDados[targetKey]) chipDados[targetKey] = 'NÃO INFORMADO';
                });

                // 4. Tratamento especial para Datas (Nascimento)
                if (chipDados.Nascimento || chipDados.nascimento) {
                    const rawDate = chipDados.Nascimento || chipDados.nascimento;
                    if (rawDate && rawDate.includes('-')) {
                        // Converte YYYY-MM-DD para DD/MM/YYYY
                        const parts = rawDate.split('-');
                        if (parts.length === 3) {
                            chipDados.nascimento = `${parts[2]}/${parts[1]}/${parts[0]}`;
                        }
                    } else if (rawDate) {
                        chipDados.nascimento = rawDate;
                    }
                } else {
                    chipDados.nascimento = 'NÃO INFORMADO';
                }

                // 5. Tratamento para Idade (se não tiver, calcula ou deixa vazio)
                if (!chipDados.idade && chipDados.nascimento && chipDados.nascimento !== 'NÃO INFORMADO') {
                    // Tenta calcular idade se possível (opcional, ou deixa vazio)
                    // chipDados.idade = ...
                }
                if (!chipDados.idade) chipDados.idade = 'NÃO INFORMADO';

                dados[key] = chipDados;
            });
        }


        // ✅ NOVO: Detecta se é "COMPLETO" e gera ambos os documentos
        if (documentoSelecionado === 'AVALIACAO DE OBJETOS - COMPLETO') {
            console.log('🔄 Gerando documentos completos: Portaria + Auto');

            // Gera a Portaria
            this.gerarDocumentoIndividual('AVALIACAO DE OBJETOS - PORTARIA', dados);

            // Aguarda 500ms e gera o Auto
            setTimeout(() => {
                this.gerarDocumentoIndividual('AVALIACAO DE OBJETOS - AUTO', dados);
            }, 500);

            return;
        }

        // ✅ NOVO: Detecta COMPLETO para Avaliação Indireta
        if (documentoSelecionado === 'AVALIACAO INDIRETA DE OBJETOS - COMPLETO') {
            console.log('🔄 Gerando documentos completos: Portaria Indireta + Auto Indireta');

            this.gerarDocumentoIndividual('AVALIACAO INDIRETA DE OBJETOS - PORTARIA', dados);

            setTimeout(() => {
                this.gerarDocumentoIndividual('AVALIACAO INDIRETA DE OBJETOS - AUTO', dados);
            }, 500);

            return;
        }

        // ✅ NOVO: Detecta EXAME DE CONSTATACAO DE DANOS E AVALIACAO
        if (documentoSelecionado === 'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - COMPLETO') {
            console.log('🔄 Gerando documentos completos: Portaria + Auto Exame Danos');
            this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS - PORTARIA', dados);
            setTimeout(() => {
                this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS - TERMO', dados);
            }, 500);
            return;
        }

        // ✅ NOVO: Detecta EXAME DE CONSTATACAO DE DANOS INDIRETA - COMPLETO
        if (documentoSelecionado === 'EXAME DE CONSTATACAO DE DANOS INDIRETA - COMPLETO') {
            console.log('🔄 Gerando documentos completos: Portaria Indireta + Auto Indireta');
            this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS INDIRETA - PORTARIA', dados);
            setTimeout(() => {
                this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS INDIRETA - AUTO', dados);
            }, 500);
            return;
        }

        // ✅ NOVO: Detecta EXAME DE EFICIÊNCIA DE ARMA DE FOGO - COMPLETO
        if (documentoSelecionado === 'EXAME DE EFICIENCIA DE ARMA DE FOGO - COMPLETO') {
            console.log('🔄 Gerando documentos completos: Portaria Eficiência Arma + Auto Eficiência Arma');
            this.gerarDocumentoIndividual('EXAME DE EFICIENCIA DE ARMA DE FOGO - PORTARIA', dados);
            setTimeout(() => {
                this.gerarDocumentoIndividual('EXAME DE EFICIENCIA DE ARMA DE FOGO - AUTO', dados);
            }, 500);
            return;
        }

        if (documentoSelecionado === 'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - PORTARIA') {
            this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS - PORTARIA', dados);
            return;
        } else if (documentoSelecionado === 'EXAME DE CONSTATACAO DE DANOS E AVALIACAO - AUTO') {
            this.gerarDocumentoIndividual('EXAME DE CONSTATACAO DE DANOS - TERMO', dados);
            return;
        }

        if (!rotasImpressaoInicio[documentoSelecionado]) {
            this.mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        } const rota = rotasImpressaoInicio[documentoSelecionado];

        // ✅ LÓGICA HÍBRIDA: SE NÃO TIVER '--DADOS--', USA POST (EVITA ERRO 403 URI TOO LONG)
        if (!rota.includes('--DADOS--')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rota;
            form.target = '_blank';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dados';
            input.value = JSON.stringify(dados);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            return;
        }

        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
        const url = rota.replace('--DADOS--', dadosCodificados);
        window.open(url, "_blank");
    },

    // ✅ NOVO: Função auxiliar para gerar documentos individuais (usada pelo COMPLETO)
    gerarDocumentoIndividual: function (nomeDocumento, dados) {
        console.log(`📄 Gerando documento: ${nomeDocumento}`);

        if (!rotasImpressaoInicio[nomeDocumento]) {
            console.error(`❌ Rota não encontrada para: ${nomeDocumento}`);
            return;
        }

        const rota = rotasImpressaoInicio[nomeDocumento];

        // Se não tiver '--DADOS--', usa POST
        if (!rota.includes('--DADOS--')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rota;
            form.target = '_blank';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrf);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dados';
            input.value = JSON.stringify(dados);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            return;
        }

        // Se tiver '--DADOS--', usa GET com dados codificados
        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
        const url = rota.replace('--DADOS--', dadosCodificados);
        window.open(url, "_blank");
    },


    bindEvents: function () {
        $('#inputData').on('change', () => this.preencherDatasAutomaticamente($('#inputData').val()));
        $('#btnPesquisar').click(() => this.carregarGrid());
        $('#txtPesquisa').off('keypress').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                this.carregarGrid();
            }
        });

        // Máscara automática de CPF no campo de busca de envolvidos
        $('#txtPesquisa').on('input', function() {
            if ($('#ddlFiltro').val() !== 'CPF') return;
            let val = $(this).val().replace(/\D/g, '').substring(0, 11);
            if (val.length > 0) {
                val = val
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
                $(this).val(val);
            }
        });

        // Ao trocar o filtro: ajusta maxlength, placeholder e limpa o campo
        $('#ddlFiltro').on('change', function() {
            const campo = $('#txtPesquisa');
            campo.val('');
            if ($(this).val() === 'CPF') {
                campo.attr('maxlength', 14).attr('placeholder', '000.000.000-00');
            } else {
                campo.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...');
            }
        });
        $(document).on('click', '.btn-selecionar', (e) => this.selecionarRegistro(e));
        $('#btnNovo').click(() => this.novoRegistro());
        $('#btnSalvar').click(() => this.salvarRegistro());
        $('#btnEditar').click(() => this.editarRegistro());
        $('#btnExcluir').click(() => this.confirmarExclusao());
        $('#btnConfirmarExclusao').click(() => this.excluirRegistro());
        $('#btnLimpar').click(() => this.limparFormularios());

        // ✅ CORREÇÃO: Evento de impressão corrigido
        $('#btnImprimirDocumentoInicio').click(() => {
            this.imprimirDocumentoInicio();
        });

        $(document).off('click', '#btnConfirmarRemoverChip').on('click', '#btnConfirmarRemoverChip', () => {
            $('#modalConfirmacaoChip').modal('hide');
            if (window.ocChipConfirm && window.ocChipConfirm.source === 'chips') {
                const { tipo, id } = window.ocChipConfirm;
                window.ocChipConfirm = null;
                const arrays = {
                    'vitima': window.envolvidosChips?.vitimas || [],
                    'autor': window.envolvidosChips?.autores || [],
                    'testemunha': window.envolvidosChips?.testemunhas || [],
                    'condutor': window.envolvidosChips?.condutores || [],
                    'outro': window.envolvidosChips?.outros || []
                };
                const array = arrays[tipo];
                const idx = array.findIndex(item => item.id == id);
                let nomeRemovido = null;

                if (idx > -1) {
                    nomeRemovido = array[idx].nome;
                    array.splice(idx, 1);
                }

                // Sincroniza remoção no OcorrenciasApp
                const tipoPluralMap = {
                    'vitima': 'vitimas',
                    'autor': 'autores',
                    'testemunha': 'testemunhas',
                    'condutor': 'condutores',
                    'outro': 'outros'
                };
                const tipoPlural = tipoPluralMap[tipo];

                if (nomeRemovido && this.envolvidos[tipoPlural]) {
                    const idxOA = this.envolvidos[tipoPlural].indexOf(nomeRemovido);
                    if (idxOA > -1) {
                        this.envolvidos[tipoPlural].splice(idxOA, 1);
                        if (this.vinculos[tipoPlural]) this.vinculos[tipoPlural].splice(idxOA, 1);
                    }
                    this.atualizarChips(tipoPlural);
                } else {
                    $(`[data-tipo="${tipo}"][data-id="${id}"]`).remove();
                }

                if (typeof mostrarMensagemSucesso === 'function') {
                    mostrarMensagemSucesso('Envolvido removido com sucesso!');
                } else if (typeof mostrarSucesso === 'function') {
                    mostrarSucesso('Envolvido removido com sucesso!');
                }
                return;
            }

            if (this.pendingChip) {
                const { tipo, index, vinculoId } = this.pendingChip;
                this.pendingChip = null;
                if (vinculoId) {
                    $.ajax({
                        url: `/boe/vinculos/remover/${vinculoId}`,
                        method: 'DELETE',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: (response) => {
                            const boe = $('#inputBOE').val();
                            if (boe) {
                                this.carregarVinculosDoBoe(boe);
                            } else {
                                this.removerEnvolvido(tipo, index);
                            }
                        },
                        error: (xhr) => {
                            this.mostrarErro('Erro ao remover vínculo: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                        }
                    });
                } else {
                    this.removerEnvolvido(tipo, index);
                }
            }
        });
    },

    carregarGrid: function () {
        const filtro = $('#ddlFiltro').val();
        const termo = $('#txtPesquisa').val().trim();
        if (!termo) {
            $('#gridResultados tbody').html(`
                <tr>
                    <td colspan="7" class="text-center py-3 text-muted">
                        <i class="bi bi-search display-6 d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                        Digite um termo para iniciar a pesquisa.
                    </td>
                </tr>
            `);
            return;
        }
        $.ajax({
            url: rotas.inicio.pesquisar,
            method: "GET",
            data: { filtro, termo: filtro === 'CPF' ? termo.replace(/\D/g, '') : termo },
            success: (response) => {
                const tbody = $('#gridResultados tbody');
                tbody.empty();
                if (response.data?.length > 0) {
                    response.data.forEach(item => {
                        const dono = item.owner_name && item.owner_name !== '-' 
                            ? item.owner_name.split(' ')[0] 
                            : 'Sistema';
                        tbody.append(`
                            <tr>
                                <td>${item.BOE || ''}</td>
                                <td>${item.boe_pm || '-'}</td>
                                <td>${item.IP || ''}</td>
                                <td class="text-center">${item.status || '-'}</td>
                                <td class="text-center">
                                    ${item.prioridade ?
                                `<span class="badge ${item.prioridade.includes('ALTA') ? 'bg-danger' :
                                    item.prioridade.includes('MEDIA') ? 'bg-warning text-dark' :
                                        item.prioridade.includes('BAIXA') ? 'bg-success' : 'bg-secondary'}">
                                      ${item.prioridade}</span>`
                                : '-'}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><i class="bi bi-person-fill"></i> ${dono}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success btn-selecionar" data-id="${item.id}">
                                        Selecionar
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">
                                <i class="bi bi-search display-6 d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                                Nenhum registro encontrado.
                            </td>
                        </tr>
                    `);
                }
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#gridResultados tbody').html(`<tr><td colspan="7" class="text-center text-danger py-4">${errorMsg}</td></tr>`);
            }
        });
    },

    selecionarRegistro: function (e) {
        e.preventDefault();
        const id = $(e.currentTarget).data('id');
        this.carregarDadosRegistro(id);
    },

    carregarDadosRegistro: function (id) {
        this.currentId = id;

        console.log('🔄 CARREGANDO REGISTRO ID:', id);

        // ✅ CORREÇÃO: Limpar TUDO antes de carregar o novo registro para evitar mistura de chips
        this.resetForms();
        // Limpar chips e envolvidos explicitamente
        ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(tipo => {
            this.envolvidos[tipo] = [];
            this.vinculos[tipo] = [];
            if (window.envolvidosChips && window.envolvidosChips[tipo]) {
                window.envolvidosChips[tipo] = [];
            }
            this.atualizarChips(tipo);
        });

        $.ajax({
            url: `${rotas.inicio.buscar}/${id}`,
            method: "GET",
            success: (response) => {
                if (response.success) {
                    // Dados Pessoais
                    $('#inputData').val(response.data.data_formatada || '').trigger('input');
                    $('#inputDataComp').val(response.data.data_comp || '');
                    $('#inputDataExt').val(response.data.data_ext || '');
                    $('#inputIP').val(response.data.IP || '');
                    $('#inputBOE').val(response.data.BOE || '');
                    $('#inputBOEPM').val(response.data.boe_pm || '');
                    $('#inputDelegado').val(response.data.delegado || '');
                    $('#inputEscrivao').val(response.data.escrivao || '');
                    $('#inputDelegacia').val(response.data.delegacia || '');
                    $('#inputCidade').val(response.data.cidade || '');
                    $('#inputPolicial1').val(response.data.policial_1 || '');
                    $('#inputPolicial2').val(response.data.policial_2 || '');
                    // Documentos
                    $('#inputDPResp').val(response.data.dp_resp || '');
                    $('#inputCidResp').val(response.data.cid_resp || '');
                    $('#inputBelResp').val(response.data.bel_resp || '');
                    $('#inputEscrResp').val(response.data.escr_resp || '');
                    // Dados Complementares
                    $('#inputDataFato').val(response.data.data_fato || '');
                    $('#inputDataInstauracao').val(response.data.data_instauracao || '');
                    const horaFatoSrv = response.data.hora_fato || '';
                    const horaFmt = (horaFatoSrv && horaFatoSrv.length >= 5) ? horaFatoSrv.substring(0, 5) : horaFatoSrv;
                    $('#inputHoraFato').val(horaFmt);
                    $('#inputEndFato').val(response.data.end_fato || '');
                    $('#inputMeiosEmpregados').val(response.data.meios_empregados || '');
                    $('#inputMotivacao').val(response.data.motivacao || '');
                    $('#inputIncidenciaPenal').val(response.data.incidencia_penal || '');
                    $('#inputComarca').val(response.data.comarca || '');
                    $('#inputStatus').val(response.data.status || '');
                    $('#inputPrioridade').val(response.data.prioridade || ''); // ✅ NOVO: Prioridade
                    // Apreensão
                    $('#inputApreensao').val(response.data.Apreensao || '');

                    // ✅ NOVO: Carregar envolvidos
                    this.carregarEnvolvidos(response.data);

                    // ✅ NOVO: Capturar is_owner da resposta e bloquear/desbloquear formulário
                    this.isOwner = response.data.is_owner !== undefined ? response.data.is_owner : true;
                    this.ownerName = response.data.owner_name || null;
                    if (this.isOwner) {
                        this.desbloquearCamposFormulario();
                        $('#btnSalvar').prop('disabled', true); // ✅ Desabilita Salvar (já existe, deve usar Editar)
                        $('#btnEditar').prop('disabled', false);
                        $('#btnExcluir').prop('disabled', false);
                    } else {
                        this.bloquearCamposFormulario();
                        $('#btnSalvar').prop('disabled', true);
                        $('#btnEditar').prop('disabled', true);
                        $('#btnExcluir').prop('disabled', true);
                    }

                    // ✅ CORREÇÃO: Garantir que pegamos o BOE independente de maiúscula/minúscula
                    const boeValue = response.data.BOE || response.data.boe;

                    console.log('✅ FORMULÁRIO INÍCIO PREENCHIDO - BOE:', boeValue, '| isOwner:', this.isOwner);
                    console.log('✅ ENVOLVIDOS CARREGADOS:', this.envolvidos);

                    // ✅✅✅ CORREÇÃO: USAR O SISTEMA DE VÍNCULOS COM FALLBACK
                    if (window.carregarCondutorVinculado) {
                        console.log('🎯 Sistema de vínculos disponível, carregando condutor...');
                        window.carregarCondutorVinculado(boeValue);
                    } else {
                        console.log('🔄 Sistema de vínculos não carregado, usando fallback...');
                        this.carregarVinculosFallback(boeValue);
                    }

                    // Polling de sugestões (só para o dono)
                    this.iniciarPollingSugestoes(boeValue);

                    // Auto-resize do campo Apreensão após carregar dados
                    if (typeof autoResizeApreensao === 'function') {
                        setTimeout(autoResizeApreensao, 100);
                    }
                    if (typeof atualizarContadorItens === 'function') {
                        atualizarContadorItens(response.data.Apreensao);
                    }

                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    // ✅ Carregar todos vínculos para chips (vitimas/autores/testemunhas)
                    this.carregarVinculosDoBoe(boeValue);

                    // ✅ Fallback: se não houver BOE, conciliar nomes com base de dados
                    if (!boeValue) {
                        this.conciliarEnvolvidosBD(['vitimas', 'autores', 'testemunhas', 'condutores']);
                    }

                } else {
                    this.mostrarErro(response.message);
                }
            },
            error: (xhr) => {
                this.mostrarErro('Erro ao carregar registro: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    },

    // ✅ NOVO: Mostrar banner de modo somente-leitura quando não é dono
    mostrarBannerPropriedade: function () {
        $('#bannerPropriedade').remove(); // Remove banner anterior se existir
        if (!this.isOwner && this.ownerName) {
            const banner = $(`
                <div id="bannerPropriedade" class="alert mb-2 d-flex align-items-center gap-2" 
                     style="background: linear-gradient(135deg, #f0a500 0%, #e07b00 100%); border:none; border-radius:10px; color:#fff; font-size:0.85rem; padding: 8px 14px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                    <div>
                        <strong>Modo Colaborador</strong> — Este procedimento pertence a <strong>${this.ownerName}</strong>.
                        Você pode <em>sugerir novos envolvidos</em>, mas não pode editar ou excluir dados existentes.
                    </div>
                </div>
            `);
            // Inserir no início do formulário principal
            const target = $('#formInicio').length ? $('#formInicio') : $('.card-body').first();
            target.prepend(banner);
        }
    },

    // ✅ NOVO: Bloquear todos os campos textuais do formulário para não-donos
    bloquearCamposFormulario: function () {
        const campos = [
            '#inputData','#inputDataComp','#inputDataExt','#inputBOE','#inputBOEPM',
            '#inputIP','#inputDelegado','#inputEscrivao','#inputDelegacia','#inputCidade',
            '#inputPolicial1','#inputPolicial2','#inputDPResp','#inputCidResp',
            '#inputBelResp','#inputEscrResp','#inputDataFato','#inputDataInstauracao',
            '#inputHoraFato','#inputEndFato','#inputMeiosEmpregados','#inputMotivacao',
            '#inputIncidenciaPenal','#inputComarca','#inputStatus','#inputPrioridade',
            '#inputApreensao'
        ];
        campos.forEach(id => {
            $(id).prop('readonly', true).css({ 'background': 'rgba(0,0,0,0.05)', 'cursor': 'not-allowed', 'opacity': '0.75' });
        });
        // Desabilitar botão Salvar/Editar mas manter botão de Adicionar Envolvido
        $('#btnSalvar').prop('disabled', true);
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);
        // Tooltip informativo
        $('#btnSalvar').attr('title', 'Apenas o responsável pode salvar alterações neste procedimento.');
        console.log('🔒 [Propriedade] Formulário bloqueado para não-dono.');
    },

    // ✅ NOVO: Desbloquear campos quando é o dono
    desbloquearCamposFormulario: function () {
        const campos = [
            '#inputData','#inputDataComp','#inputDataExt','#inputBOE','#inputBOEPM',
            '#inputIP','#inputDelegado','#inputEscrivao','#inputDelegacia','#inputCidade',
            '#inputPolicial1','#inputPolicial2','#inputDPResp','#inputCidResp',
            '#inputBelResp','#inputEscrResp','#inputDataFato','#inputDataInstauracao',
            '#inputHoraFato','#inputEndFato','#inputMeiosEmpregados','#inputMotivacao',
            '#inputIncidenciaPenal','#inputComarca','#inputStatus','#inputPrioridade',
            '#inputApreensao'
        ];
        campos.forEach(id => {
            $(id).prop('readonly', false).css({ 'background': '', 'cursor': '', 'opacity': '' });
        });
        $('#bannerPropriedade').remove();
        console.log('🔓 [Propriedade] Formulário desbloqueado para o dono.');
    },

    // ✅ NOVA FUNÇÃO FALLBACK PARA VÍNCULOS
    carregarVinculosFallback: function (boe) {
        if (!boe || boe === 'N/A') return;

        console.log('🔄 [FALLBACK] Carregando vínculos do BOE:', boe);

        // ✅ CARREGAR CONDUTOR VIA FALLBACK
        $.ajax({
            url: "/boe/vinculos/buscar-condutor/" + encodeURIComponent(boe),
            method: 'GET',
            success: (response) => {
                if (response.success && response.data) {
                    console.log('✅ [FALLBACK] CONDUTOR ENCONTRADO:', response.data.Nome);
                    this.preencherCondutorFallback(response.data);
                } else {
                    console.log('ℹ️ [FALLBACK] Nenhum condutor vinculado');
                }
            },
            error: (xhr) => {
                console.error('❌ [FALLBACK] Erro ao buscar condutor:', xhr);
            }
        });
    },

    // ✅ FUNÇÃO PARA PREENCHER CONDUTOR MANUALMENTE
    preencherCondutorFallback: function (dados) {
        console.log('🔄 [FALLBACK] Preenchendo condutor...');

        // Mudar para aba do condutor PRIMEIRO
        $('#abasPrincipais a[href="#aba-condutor"]').tab('show');

        // Preencher formulário
        $('#condutor_id').val(dados.IdCad || '');
        $('#inputNomeCondutor').val(dados.Nome || '');
        $('#inputAlcunha').val(dados.Alcunha || '');

        // Data de nascimento
        if (dados.Nascimento) {
            const dataNasc = new Date(dados.Nascimento);
            if (!isNaN(dataNasc.getTime())) {
                $('#inputDataNascimento').val(dataNasc.toLocaleDateString('pt-BR'));
                this.calcularIdadeCondutorFallback(dataNasc);
            }
        }

        $('#inputEstadoCivil').val(dados.EstCivil || '');
        $('#inputNaturalidade').val(dados.Naturalidade || '');
        $('#inputInstrucao').val(dados.Instrucao || '');
        $('#inputRG').val(dados.RG || '');
        $('#inputCPF').val(dados.CPF || '');
        $('#inputTelefone').val(dados.Telefone || '');
        $('#inputProfissao').val(dados.Profissao || '');
        $('#inputMae').val(dados.Mae || '');
        $('#inputPai').val(dados.Pai || '');
        $('#inputEndereco').val(dados.Endereco || '');

        // Habilitar botões
        $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

        console.log('✅ [FALLBACK] Condutor preenchido com sucesso!');

        // Voltar para aba inicial após 1 segundo
        setTimeout(() => {
            $('#abasPrincipais a[href="#aba-inicio"]').tab('show');
        }, 1000);
    },

    // ✅ CALCULAR IDADE PARA FALLBACK
    calcularIdadeCondutorFallback: function (dataNascimento) {
        const hoje = new Date();
        const idade = hoje.getFullYear() - dataNascimento.getFullYear();
        const mesAtual = hoje.getMonth();
        const mesNasc = dataNascimento.getMonth();

        if (mesAtual < mesNasc || (mesAtual === mesNasc && hoje.getDate() < dataNascimento.getDate())) {
            $('#inputIdade').val(idade - 1);
        } else {
            $('#inputIdade').val(idade);
        }
    },

    // ✅ NOVO: MONITORAMENTO DE SUGESTÕES (POLLING 30S)
    iniciarPollingSugestoes: function (boe) {
        this.pararPollingSugestoes();
        if (!boe || !this.isOwner) return;

        console.log('⏰ [Polling] Iniciado monitoramento de sugestões para BOE:', boe);
        this.pollingSugestoes = setInterval(() => {
            // Apenas atualiza se o BOE no campo ainda for o mesmo e a aba ativa for a de APFD
            const boeAtual = $('#inputBOE').val();
            if (boeAtual === boe && this.isOwner) {
                console.log('🔄 [Polling] Verificando novas sugestões...');
                // Faz o fetch silencioso (sem flags visíveis se possível, ou apenas atualiza chips)
                this.atualizarApenasVinculos(boe);
            }
        }, 30000); // 30 segundos
    },

    pararPollingSugestoes: function () {
        if (this.pollingSugestoes) {
            clearInterval(this.pollingSugestoes);
            this.pollingSugestoes = null;
            console.log('🛑 [Polling] Monitoramento parado.');
        }
    },

    atualizarApenasVinculos: function (boe) {
        $.ajax({
            url: "/boe/vinculos/listar/" + encodeURIComponent(boe),
            method: 'GET',
            success: (response) => {
                if (response.success && response.data) {
                    const data = response.data;
                    const mapVinculo = (p) => ({
                        nome: p.Nome || p.nome || '',
                        vinculo_id: p.vinculo_id,
                        pessoa_id: p.IdCad || p.id,
                        status_aprovacao: p.status_aprovacao || 'aprovado',
                        criado_por_nome: p.criado_por_nome || null
                    });

                    const mergeVinculos = (tipo, novosDb) => {
                        const localEnvolvidos = [ ...(this.envolvidos[tipo] || []) ];
                        const localVinculosMap = {};
                        (this.vinculos[tipo] || []).forEach(v => {
                            if(v && v.nome) localVinculosMap[v.nome] = v;
                        });

                        const dbNomes = new Set(novosDb.map(v => v.nome));
                        const novosVinculos = [];
                        const novosEnvolvidos = [];

                        // 1. Manter os chips locais (manuais) não salvos no BD
                        localEnvolvidos.forEach(nome => {
                            if (!dbNomes.has(nome)) {
                                novosEnvolvidos.push(nome);
                                novosVinculos.push(localVinculosMap[nome] || { nome: nome });
                            }
                        });

                        // 2. Adicionar os do BD
                        novosDb.forEach(v => {
                            novosEnvolvidos.push(v.nome);
                            novosVinculos.push(v);
                        });

                        this.envolvidos[tipo] = novosEnvolvidos;
                        this.vinculos[tipo] = novosVinculos;
                    };

                    mergeVinculos('vitimas', (data.vitimas || []).map(mapVinculo));
                    mergeVinculos('autores', (data.autores || []).map(mapVinculo));
                    mergeVinculos('testemunhas', (data.testemunhas || []).map(mapVinculo));
                    mergeVinculos('condutores', (data.condutor || []).map(mapVinculo));
                    mergeVinculos('outros', (data.outros || []).map(mapVinculo));

                    this.atualizarChips('vitimas');
                    this.atualizarChips('autores');
                    this.atualizarChips('testemunhas');
                    this.atualizarChips('condutores');
                    this.atualizarChips('outros');
                }
            }
        });
    },

    novoRegistro: function () {
        this.currentId = null;
        this.pararPollingSugestoes();
        this.resetForms();
        this.preencherDataAtual();
        $('#btnSalvar').prop('disabled', false); // ✅ Reabilita Salvar para novo registro
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);
    },

    salvarRegistro: function () {
        if (!this.validarCamposObrigatorios()) return;

        const $btn = $('#btnSalvar');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        // ✅ NOVO: Preparar dados dos envolvidos
        this.prepararDadosEnvolvidos();

        let hfSalvar = $('#inputHoraFato').val();
        if (hfSalvar && hfSalvar.length >= 5) { $('#inputHoraFato').val(hfSalvar.substring(0, 5)); }
        let hfEditar = $('#inputHoraFato').val();
        if (hfEditar && hfEditar.length >= 5) { $('#inputHoraFato').val(hfEditar.substring(0, 5)); }
        const formData = $('#formInicio').serializeArray();
        const formDocumentos = $('#documentos form').serializeArray();
        const formDadosComplementares = $('#dados-complementares form').serializeArray();
        const formApreensao = $('#apreensao form').serializeArray();
        const allData = formData.concat(formDocumentos, formDadosComplementares, formApreensao);

        $.ajax({
            url: rotas.inicio.salvar,
            method: "POST",
            data: allData,
            success: (response) => {
                if (response.success) {
                    this.currentId = response.id || this.currentId;
                    this.mostrarSucesso('Registro salvo com sucesso!');

                    this.dadosImportados = {};

                    // Atualiza botões
                    $btn.prop('disabled', true).html(originalHtml);
                    $('#btnEditar').prop('disabled', false);
                    $('#btnExcluir').prop('disabled', false);

                    // ✅ FIX: Recarregar vínculos do banco após salvar.
                    // NÃO chamar /boe/vinculos/salvar separadamente — os vínculos já foram
                    // criados pelo InicioController.salvar() via vincularNomesAoBoe().
                    // A chamada extra com condutor_id causava "condutores fantasmas".
                    const boePos = $('#inputBOE').val();
                    if (boePos) {
                        this.carregarVinculosDoBoe(boePos);
                    }

                    // === ADICIONA O NOVO REGISTRO NA GRID ===
                    const boe = $('#inputBOE').val().trim() || '';
                    const boePm = $('#inputBOEPM').val().trim() || '-';
                    const ip = $('#inputIP').val().trim() || '';
                    const status = $('#inputStatus').val().trim() || '-';
                    const prioridade = $('#inputPrioridade').val().trim() || '';
                    const id = this.currentId;

                    const tbody = $('#gridResultados tbody');

                    // Se a tabela tem mensagem de "nenhum" ou está vazia, limpa para adicionar
                    if (tbody.find('td[colspan]').length || tbody.text().includes('Nenhum')) {
                        tbody.empty();
                    }

                    // Cria a nova linha com TODAS as 7 colunas (igual ao carregarGrid)
                    const novaLinha = `
                        <tr>
                            <td>${boe}</td>
                            <td>${boePm}</td>
                            <td>${ip}</td>
                            <td class="text-center">${status}</td>
                            <td class="text-center">
                                ${prioridade ?
                            `<span class="badge ${prioridade.includes('ALTA') ? 'bg-danger' :
                                prioridade.includes('MEDIA') ? 'bg-warning text-dark' :
                                    prioridade.includes('BAIXA') ? 'bg-success' : 'bg-secondary'}">
                                       ${prioridade}</span>`
                            : '-'}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><i class="bi bi-person-fill"></i> ${$('.system-user').text().replace('Usuário: ', '').trim().split(' ')[0] || 'Sistema'}</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-success btn-selecionar" data-id="${id}">
                                    Selecionar
                                </button>
                            </td>
                        </tr>
                    `;

                    // Adiciona no topo (mais recente)
                    tbody.prepend(novaLinha);

                    // Destaque temporário (opcional)
                    tbody.find('tr:first').addClass('table-success');
                    setTimeout(() => tbody.find('tr:first').removeClass('table-success'), 2000);

                    // ==============================

                    $('#btnEditar').prop('disabled', false);
                    $('#btnExcluir').prop('disabled', false);
                } else {
                    this.mostrarErro(response.message || 'Erro ao salvar');
                    $('#btnSalvar').prop('disabled', false).html('<i class="bi bi-save"></i> Salvar');
                }
            },
            error: (xhr) => {
                const mensagem = xhr.responseJSON?.message || 'Erro ao salvar | Boletim de Ocorrência já cadastrado no sistema!';
                this.mostrarErro(mensagem);
                $('#btnSalvar').prop('disabled', false).html('<i class="bi bi-save"></i> Salvar');
            }
        });
    },

    editarRegistro: function () {
        if (!this.currentId) {
            this.mostrarErro('Nenhum registro selecionado para editar.');
            return;
        }
        if (!this.validarCamposObrigatorios()) return;

        const $btn = $('#btnEditar');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        // ✅ NOVO: Preparar dados dos envolvidos
        this.prepararDadosEnvolvidos();

        const formData = $('#formInicio').serializeArray();
        const formDocumentos = $('#documentos form').serializeArray();
        const formDadosComplementares = $('#dados-complementares form').serializeArray();
        const formApreensao = $('#apreensao form').serializeArray();
        const allData = formData.concat(formDocumentos, formDadosComplementares, formApreensao);

        $.ajax({
            url: `${rotas.inicio.atualizar}/${this.currentId}`,
            method: "PUT",
            data: allData,
            success: (response) => {
                if (response.success) {
                    this.mostrarSucesso('Registro atualizado com sucesso!');
                    this.carregarGrid();
                    $('#btnEditar').prop('disabled', false).html('<i class="bi bi-pencil-square"></i> Editar');
                } else {
                    this.mostrarErro(response.message);
                    $('#btnEditar').prop('disabled', false).html('<i class="bi bi-pencil-square"></i> Editar');
                }
            },
            error: (xhr) => {
                this.mostrarErro('Erro ao editar: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                $('#btnEditar').prop('disabled', false).html('<i class="bi bi-pencil-square"></i> Editar');
            }
        });
    },

    confirmarExclusao: function () {
        if (!this.currentId) {
            this.mostrarErro('Selecione um registro para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este registro?', () => {
            const $btn = $('#btnConfirmarExclusaoGenerico'); // Botão do modal genérico
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Excluindo...');

            // ✅ REFATORAÇÃO: Função para continuar exclusão após vínculos
            const continuarExclusao = () => {
                $.ajax({
                    url: `${rotas.inicio.excluir}/${this.currentId}`,
                    method: "DELETE",
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: (response) => {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            this.mostrarSucesso('Registro excluído com sucesso!');
                            this.resetForms();
                            this.currentId = null;
                            // Botões já são resetados em resetForms

                            // Remove a linha do registro excluído
                            // Nota: registroId não está definido aqui no escopo original, usar this.currentId se possível ou capturar antes
                            // Mas como currentId é nullado acima, precisamos capturar antes.
                            // Vamos corrigir isso capturando o ID antes.
                        } else {
                            this.mostrarErro(response.message);
                        }
                    },
                    error: (xhr) => {
                        this.mostrarErro('Erro ao excluir: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            };

            // Captura ID para uso no callback
            const registroId = this.currentId;
            const boe = $('#inputBOE').val().trim();

            // Redefinindo continuarExclusao para usar registroId capturado e corrigir lógica de UI
            const continuarExclusaoCorrigido = () => {
                $.ajax({
                    url: `${rotas.inicio.excluir}/${registroId}`,
                    method: "DELETE",
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: (response) => {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            this.mostrarSucesso('Registro excluído com sucesso!');
                            this.resetForms();
                            this.currentId = null;

                            // Remove a linha do registro excluído
                            $(`button.btn-selecionar[data-id="${registroId}"]`).closest('tr').remove();

                            // Se a tabela ficar vazia
                            if ($('#gridResultados tbody tr').length === 0) {
                                $('#gridResultados tbody').html('<tr><td colspan="7" class="text-center">Nenhum registro encontrado.</td></tr>');
                            }

                            // Recarrega apenas se houver pesquisa ativa e a grid estiver vazia
                            const termoPesquisa = $('#inputPesquisa').val(); // Assumindo seletor, ou usar variável global se existir
                            if (termoPesquisa && $('#gridResultados tbody tr td').text().includes('Nenhum')) {
                                this.carregarGrid();
                            }
                        } else {
                            this.mostrarErro(response.message);
                        }
                    },
                    error: (xhr) => {
                        this.mostrarErro('Erro ao excluir: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                    },
                    complete: () => {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            };

            // ✅ PRIMEIRO EXCLUI TODOS OS VÍNCULOS DO BOE
            if (boe && boe !== 'N/A') {
                console.log('🗑️ Excluindo todos os vínculos do BOE antes de excluir registro...');

                $.ajax({
                    url: '/boe/vinculos/excluir-todos/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (response) => {
                        if (response.success) {
                            console.log('✅ Vínculos excluídos com sucesso, agora excluindo registro...');
                        } else {
                            // ✅ 404 É NORMAL - Não havia vínculos para excluir
                            console.log('ℹ️ Nenhum vínculo encontrado para excluir (situação normal)');
                        }
                        // Continua com a exclusão normal do registro
                        continuarExclusaoCorrigido();
                    },
                    error: (xhr) => {
                        // ✅ TRATA 404 COMO SITUAÇÃO NORMAL (não havia vínculos)
                        if (xhr.status === 404) {
                            console.log('ℹ️ Nenhum vínculo encontrado para excluir (404 - situação normal)');
                        } else {
                            console.log('⚠️ Erro ao excluir vínculos, continuando exclusão do registro...');
                        }
                        // Mesmo com erro, continua com a exclusão do registro
                        continuarExclusaoCorrigido();
                    }
                });
            } else {
                continuarExclusaoCorrigido();
            }
        });
    },

    limparFormularios: function () {
        this.currentId = null;
        this.pararPollingSugestoes();
        this.resetForms();
        this.preencherDataAtual();
        $('#btnSalvar').prop('disabled', false);
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);
    },

    resetForms: function () {
        console.log('🧹 Iniciando limpeza do formulário...');

        // 1. Limpar Envolvidos (Arrays e Chips)
        try {
            if (window.envolvidosChips) {
                if (window.envolvidosChips.vitimas && Array.isArray(window.envolvidosChips.vitimas)) window.envolvidosChips.vitimas.length = 0;
                if (window.envolvidosChips.autores && Array.isArray(window.envolvidosChips.autores)) window.envolvidosChips.autores.length = 0;
                if (window.envolvidosChips.testemunhas && Array.isArray(window.envolvidosChips.testemunhas)) window.envolvidosChips.testemunhas.length = 0;
                if (window.envolvidosChips.condutores && Array.isArray(window.envolvidosChips.condutores)) window.envolvidosChips.condutores.length = 0;
                if (window.envolvidosChips.outros && Array.isArray(window.envolvidosChips.outros)) window.envolvidosChips.outros.length = 0;
            }

            // Limpar visualmente os containers
            ['chipsVitimas', 'chipsAutores', 'chipsTestemunhas', 'chipsCondutores', 'chipsOutros'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '';
            });

            // Limpar campos específicos do condutor/outro
            $('#inputCondutor').val('');
            $('#inputOutro').val('');
            // ✅ FIX: Limpar IDs ocultos para evitar "vazamento" entre registros
            $('#condutor_id').val('');

            // Limpar envolvidos internos do app
            this.envolvidos = { vitimas: [], autores: [], testemunhas: [], condutores: [], outros: [] };
            this.vinculos = { vitimas: [], autores: [], testemunhas: [], condutores: [], outros: [] };

            this.atualizarChips('vitimas');
            this.atualizarChips('autores');
            this.atualizarChips('testemunhas');
            this.atualizarChips('condutores');
            this.atualizarChips('outros');

        } catch (e) {
            console.error('Erro ao limpar envolvidos:', e);
        }

        // 2. Limpar Formulários Padrão
        try {
            const fInicio = $('#formInicio')[0];
            if (fInicio && typeof fInicio.reset === 'function') fInicio.reset();

            const fDocs = $('#formDocumentos')[0];
            if (fDocs && typeof fDocs.reset === 'function') fDocs.reset();

            // Força bruta para limpar Dados Complementares (garantia extra)
            $('#formDocumentos input, #formDocumentos select, #formDocumentos textarea').val('');

            // Limpa explicitamente campos críticos que podem ter máscaras ou plugins
            $('#inputDataFato').val('');
            $('#inputDataInstauracao').val('');
            $('#inputHoraFato').val('');
            $('#inputStatus').val('');
            $('#inputPrioridade').val('');
            $('#inputApreensao').val(''); // Textarea dentro do formDocumentos

        } catch (e) {
            console.error('Erro ao resetar formulários HTML:', e);
        }

        // 3. Resetar Data Principal (APFD | IP)
        try {
            // Limpa explicitamente antes de preencher
            $('#inputData').val('').trigger('input');

            // Preenche com a data atual
            this.preencherDataAtual();

            console.log('✅ Data atual redefinida com sucesso.');
        } catch (e) {
            console.error('Erro ao redefinir data atual:', e);
            // Fallback de emergência
            const hoje = new Date().toLocaleDateString('pt-BR');
            $('#inputData').val(hoje);
        }

        console.log('✨ Limpeza concluída.');
    },

    validarCamposObrigatorios: function () {
        const required = [
            { sel: '#inputData', label: 'Data' },
            { sel: '#inputDelegado', label: 'Delegado' },
            { sel: '#inputDelegacia', label: 'Delegacia' },
            { sel: '#inputBOE', label: 'BOE' },
            { sel: '#inputIncidenciaPenal', label: 'Incidência Penal' }
        ];
        const missing = required.filter(r => !$(r.sel).val());
        if (missing.length) {
            const nomes = missing.map(m => m.label).join(', ');
            const msg = `Preencha: ${nomes}`;
            this.mostrarErro(msg);
            missing.forEach(r => this.realcarCampoInvalido(r.sel, `Informe ${r.label}`));
            return false;
        }
        return true;
    },

    preencherDatasAutomaticamente: function (data) {
        if (!data || data.length !== 10) return; // ✅ VALIDAR DATA COMPLETA

        let dataObj;
        if (data) {
            const partes = data.split('/');
            if (partes.length === 3 && partes[0] && partes[1] && partes[2]) {
                const dia = parseInt(partes[0]), mes = parseInt(partes[1]), ano = parseInt(partes[2]);
                if (!isNaN(dia) && !isNaN(mes) && !isNaN(ano)) {
                    dataObj = new Date(ano, mes - 1, dia);
                }
            }
            if (!dataObj || isNaN(dataObj.getTime())) {
                dataObj = new Date();
                $('#inputData').val(dataInput);
            }
        } else {
            dataObj = new Date();
        }
        const dataCompleta = dataObj.toLocaleDateString('pt-BR', {
            day: 'numeric', month: 'long', year: 'numeric'
        });
        $('#inputDataComp').val(dataCompleta);
        const diasExtenso = ["", "Um", "Dois", "Três", "Quatro", "Cinco", "Seis", "Sete", "Oito", "Nove", "Dez",
            "Onze", "Doze", "Treze", "Quatorze", "Quinze", "Dezesseis", "Dezessete", "Dezoito", "Dezenove", "Vinte",
            "Vinte e Um", "Vinte e Dois", "Vinte e Três", "Vinte e Quatro", "Vinte e Cinco", "Vinte e Seis", "Vinte e Sete",
            "Vinte e Oito", "Vinte e Nove", "Trinta", "Trinta e Um"];
        const anosExtenso = {
            2025: "Dois Mil Vinte e Cinco",
            2024: "Dois Mil Vinte e Quatro",
            2023: "Dois Mil Vinte e Três"
        };
        const dia = dataObj.getDate();
        const mes = dataObj.toLocaleDateString('pt-BR', { month: 'long' });
        const ano = dataObj.getFullYear();
        const diaPorExtenso = diasExtenso[dia];
        const anoPorExtenso = anosExtenso[ano] || ano;
        const dataExtenso = `${diaPorExtenso} dias do mês de ${mes.charAt(0).toUpperCase() + mes.slice(1)} do ano de ${anoPorExtenso} (${dataObj.toLocaleDateString('pt-BR')})`;
        $('#inputDataExt').val(dataExtenso);
    },

    preencherDataAtual: function () {
        $('#inputData').val(new Date().toLocaleDateString('pt-BR'));
        this.preencherDatasAutomaticamente($('#inputData').val());
    },

    realcarCampoInvalido: function (selector, mensagem) {
        const el = $(selector);
        if (!el.length) return;
        el.addClass('is-invalid');
        if (mensagem) el.attr('title', mensagem);
        el.off('input._val').on('input._val', function () {
            $(this).removeClass('is-invalid').removeAttr('title');
        });
        const node = el.get(0);
        if (node && node.scrollIntoView) node.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.focus();
    },

    // Global Modal Helpers removidos. Agora residentes exclusivamente em public/js/core.js
    // MAS expostos como proxy para retrocompatibilidade no OcorrenciasApp para evitar silent errors
    mostrarErro: function(mensagem) {
        if (typeof window.mostrarErro === 'function') {
            window.mostrarErro(mensagem);
        } else {
            console.error(mensagem);
            alert(mensagem);
        }
    },
    
    mostrarSucesso: function(mensagem) {
        if (typeof window.mostrarSucesso === 'function') {
            window.mostrarSucesso(mensagem);
        } else {
            console.log(mensagem);
            alert(mensagem);
        }
    }
};

// Inicializa a aplicação quando o documento estiver pronto
$(document).ready(function () {
    OcorrenciasApp.init();

    // ✅ FIX: Garantir que a barra de rolagem seja restaurada após fechar modais
    $(document).on('hidden.bs.modal', '.modal', function () {
        const modalId = $(this).attr('id');

        // Remove a classe modal-open se não houver outros modais abertos
        if ($('.modal:visible').length === 0) {
            $('body').removeClass('modal-open').css({
                'overflow': 'auto',
                'overflow-y': 'auto',
                'padding-right': '0'
            });
        }
    });

    // ✅ FIX: Garantir que a rolagem funcione mesmo quando modais são abertos
    $(document).on('shown.bs.modal', '.modal', function () {
        $('body').css('overflow-y', 'auto');
    });

    // ==========================================
    // Lógica do Gerador de Prompts (Depoimentos)
    // ==========================================
    
    // Ação do botão 📋 no chip (btn-prompt-chip)
    $(document).on('click', '.btn-prompt-chip', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Previne editar o chip
        
        let $chip = $(this).closest('.chip-envolvido');
        let nome = $chip.find('span').first().text().trim(); // Pega apenas o nome
        let tipoPlural = $(this).data('tipo');
        
        // Mapear plural para papel único
        let tipoMap = {
            'vitimas': 'VITIMA',
            'autores': 'AUTOR',
            'testemunhas': 'TESTEMUNHA',
            'condutores': 'CONDUTOR',
            'outros': 'OUTRO'
        };
        let papel = tipoMap[tipoPlural] || 'OUTRO';

        let boe = $('#inputBOE').val();
        if(!boe) {
            OcorrenciasApp.mostrarErro("Você precisa importar um BOE primeiro.");
            return;
        }

        // 1. Mostrar estado de carregamento
        $('#selectTipoPrompt').html('<option value="">Carregando prompts...</option>').prop('disabled', true);
        $('#textareaPromptGerado').val('Aguarde, gerando prompt com a Inteligência do SisDP...');
        $('#badgeTipoCrimePrompt').text('Analisando...');
        $('#avisoHistoricoFaltando').hide();
        $('#btnCopiarPrompt').prop('disabled', true);

        // Armazena dados atuais pra requisições de change
        $('#modalPromptGenerator').data('current-boe', boe)
                                 .data('current-nome', nome)
                                 .data('current-papel', papel);

        $('#modalPromptGenerator').modal('show');

        // 2. Chamar a API
        gerarPromptApi(boe, nome, papel, null);
    });

    // Quando o usuário muda o tipo de prompt no seletor
    $(document).on('change', '#selectTipoPrompt', function() {
        let tipoSelecionado = $(this).val();
        if(!tipoSelecionado) return;

        let boe = $('#modalPromptGenerator').data('current-boe');
        let nome = $('#modalPromptGenerator').data('current-nome');
        let papel = $('#modalPromptGenerator').data('current-papel');

        $('#textareaPromptGerado').val('Atualizando prompt...');
        $('#btnCopiarPrompt').prop('disabled', true);
        
        gerarPromptApi(boe, nome, papel, tipoSelecionado);
    });

    // Função para chamar o endpoint
    function gerarPromptApi(boe, nome, papel, tipoPrompt) {
        $.ajax({
            url: '/prompt/gerar',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                boe: boe,
                nome: nome,
                papel: papel,
                tipo_prompt: tipoPrompt
            },
            success: function(resp) {
                if(resp.success) {
                    $('#textareaPromptGerado').val(resp.prompt);
                    $('#btnCopiarPrompt').prop('disabled', false).html('<i class="bi bi-clipboard"></i> Copiar Prompt').removeClass('btn-success').addClass('btn-primary');
                    
                    if(!resp.tem_historico) {
                        $('#avisoHistoricoFaltando').show();
                    } else {
                        $('#avisoHistoricoFaltando').hide();
                    }

                    let badges = '';
                    if(resp.is_transito) badges += '<span class="badge bg-warning text-dark me-1">CRIME DE TRÂNSITO</span>';
                    if(resp.is_pm) badges += '<span class="badge bg-primary me-1">POLICIAL MILITAR</span>';
                    $('#badgeTipoCrimePrompt').html(badges);

                    // Povoar o seletor apenas se for a primeira carga ou não tinha sido preenchido
                    if(!tipoPrompt || $('#selectTipoPrompt option').length <= 1) {
                        let options = '';
                        resp.templates_disponiveis.forEach(t => {
                            let selected = (t.id === resp.tipo_usado) ? 'selected' : '';
                            options += `<option value="${t.id}" ${selected}>${t.titulo} - ${t.descricao}</option>`;
                        });
                        $('#selectTipoPrompt').html(options).prop('disabled', false);
                    }
                } else {
                    $('#textareaPromptGerado').val("Erro retornado: " + resp.message);
                }
            },
            error: function(xhr) {
                let msg = 'Erro desconhecido';
                if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                $('#textareaPromptGerado').val("ERRO AO GERAR: " + msg);
                $('#selectTipoPrompt').prop('disabled', false);
            }
        });
    }

    // Ação de copiar o texto
    $('#btnCopiarPrompt').on('click', function() {
        let textArea = document.getElementById('textareaPromptGerado');
        textArea.select();
        textArea.setSelectionRange(0, 99999); // Para mobile
        
        try {
            document.execCommand('copy');
            $(this).removeClass('btn-primary').addClass('btn-success').html('<i class="bi bi-check-lg"></i> Copiado!');
            setTimeout(() => {
                $(this).removeClass('btn-success').addClass('btn-primary').html('<i class="bi bi-clipboard"></i> Copiar Prompt');
            }, 2500);
        } catch(err) {
            alert('Falha ao tentar copiar o texto.');
        }
    });

});

