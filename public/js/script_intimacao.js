// script_intimacao.js - VERSÃO COMPLETA COM PREENCHIMENTO AUTOMÁTICO E PADRONIZAÇÃO

window.initIntimacaoIfPresent = function () {
    // === VERIFICA SE O FORMULÁRIO EXISTE NA PÁGINA ===
    if (!$('#formIntimacao').length) return;

    console.log('✅ Script Intimação inicializado (initIntimacaoIfPresent)');

    // Remover listeners antigos para evitar duplicação
    $('a[data-bs-toggle="tab"][href="#intimacao-tab"], a[data-bs-toggle="tab"][href="#aba-intimacao-din"]').off('shown.bs.tab');
    $(document).off('dadosGeralAlterados');
    $(document).off('dadosCondutorAlterados');

    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: (function () {
            var token = $('meta[name="csrf-token"]').attr('content');
            if (!token) {
                var m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                if (m) { try { token = decodeURIComponent(m[1]); } catch (e) { } }
            }
            return { 'X-CSRF-TOKEN': token };
        })()
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    try {
        if ($.fn.mask) {
            $('#inputTelefoneIntimacao').mask('(00) 00000-0000');
        }
    } catch (e) { }

    // === VARIÁVEL GLOBAL ===
    let currentIntimacaoId = null;

    // === FUNÇÕES PARA RECEBER DADOS AUTOMATICAMENTE DE OUTROS FORMULÁRIOS ===

    // Receber dados do formulário GERAL (Delegado, Escrivão, Delegacia, Cidade, BOE, DATA)
    function receberDadosGeral(dados) {
        console.log('📥 Dados recebidos do GERAL:', dados);

        if (dados.delegado && !$('#inputDelegadoIntimacao').val()) {
            $('#inputDelegadoIntimacao').val(dados.delegado);
            console.log('✅ Preencheu Delegado:', dados.delegado);
        }
        if (dados.escrivao && !$('#inputEscrivaoIntimacao').val()) {
            $('#inputEscrivaoIntimacao').val(dados.escrivao);
            console.log('✅ Preencheu Escrivão:', dados.escrivao);
        }
        if (dados.delegacia && !$('#inputDelegaciaIntimacao').val()) {
            $('#inputDelegaciaIntimacao').val(dados.delegacia);
            console.log('✅ Preencheu Delegacia:', dados.delegacia);
        }
        if (dados.cidade && !$('#inputCidadeIntimacao').val()) {
            $('#inputCidadeIntimacao').val(dados.cidade);
            console.log('✅ Preencheu Cidade:', dados.cidade);
        }
        if (dados.boe && !$('#inputReferenciaIntimacao').val()) {
            $('#inputReferenciaIntimacao').val(dados.boe);
            console.log('✅ Preencheu BOE:', dados.boe);
        }
        // ✅ NOVO: Preencher data da intimação com a data do cadastro administrativo
        if (dados.data_cadastro) {
            $('#inputDataIntimacao').val(dados.data_cadastro);
            console.log('✅ Preencheu DATA da intimação:', dados.data_cadastro);
            // Disparar evento change para preencher data completa
            $('#inputDataIntimacao').trigger('change');
        }
    }

    // === SINCRONIZAÇÃO AUTOMÁTICA AO ABRIR A ABA ===
    $('a[data-bs-toggle="tab"][href="#intimacao-tab"], a[data-bs-toggle="tab"][href="#aba-intimacao-din"]').on('shown.bs.tab', function (e) {
        console.log('🔄 Sincronizando dados para Intimação...');

        // Header
        const boe = $('#inputBOE').val();
        if (boe) {
            $('#inputReferenciaIntimacao').val(boe);
            verificarEnvolvidosSalvos(boe); // Buscar status de salvos
        }

        if ($('#inputDelegado').val()) $('#inputDelegadoIntimacao').val($('#inputDelegado').val());
        if ($('#inputEscrivao').val()) $('#inputEscrivaoIntimacao').val($('#inputEscrivao').val());
        if ($('#inputDelegacia').val()) $('#inputDelegaciaIntimacao').val($('#inputDelegacia').val());
        if ($('#inputCidade').val()) $('#inputCidadeIntimacao').val($('#inputCidade').val());

        // Chips de Envolvidos
        if (typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos) {
            let lista = [];
            const mapeamento = {
                'vitimas': 'VITIMA',
                'autores': 'AUTOR',
                'testemunhas': 'TESTEMUNHA',
                'condutores': 'CONDUTOR',
                'outros': 'ENVOLVIDO'
            };

            for (const [key, tipo] of Object.entries(mapeamento)) {
                if (OcorrenciasApp.envolvidos[key] && Array.isArray(OcorrenciasApp.envolvidos[key])) {
                    OcorrenciasApp.envolvidos[key].forEach(nome => {
                        lista.push({ nome: nome, tipo: tipo });
                    });
                }
            }

            // Sempre re-renderiza para garantir atualização
            if (lista.length > 0) {
                renderizarChipsIntimacao(lista);
            }
        }
    });

    // Receber dados do formulário CONDUTOR (Nome, Endereço, Telefone)
    function receberDadosCondutor(dados) {
        console.log('📥 Dados recebidos do CONDUTOR:', dados);

        if (dados.nome && !$('#inputNomeIntimacao').val()) {
            $('#inputNomeIntimacao').val(dados.nome);
            console.log('✅ Preencheu Nome:', dados.nome);
        }
        if (dados.endereco && !$('#inputEnderecoIntimacao').val()) {
            $('#inputEnderecoIntimacao').val(dados.endereco);
            console.log('✅ Preencheu Endereço:', dados.endereco);
        }
        if (dados.telefone && !$('#inputTelefoneIntimacao').val()) {
            $('#inputTelefoneIntimacao').val(dados.telefone);
            console.log('✅ Preencheu Telefone:', dados.telefone);
        }
    }

    // === SISTEMA DE COMUNICAÇÃO ENTRE ABAS ===
    function configurarComunicacaoEntreAbas() {
        // Ouvir eventos de mudança nos outros formulários
        $(document).on('dadosGeralAlterados', function (event, dados) {
            console.log('🎯 Evento dadosGeralAlterados recebido na INTIMAÇÃO');
            if ($('#aba-intimacao-din').is(':visible')) {
                receberDadosGeral(dados);
            } else {
                console.log('ℹ️  Aba intimaçao não está visível, dados serão aplicados quando abrir');
            }
        });

        $(document).on('dadosCondutorAlterados', function (event, dados) {
            console.log('🎯 Evento dadosCondutorAlterados recebido na INTIMAÇÃO');
            if ($('#aba-intimacao-din').is(':visible')) {
                receberDadosCondutor(dados);
            } else {
                console.log('ℹ️  Aba intimaçao não está visível, dados serão aplicados quando abrir');
            }
        });

        // Também detectar quando a aba de intimação é aberta e buscar dados atuais
        $('a[data-bs-toggle="tab"][href="#aba-intimacao-din"]').on('shown.bs.tab', function () {
            console.log('🔍 Aba INTIMAÇÃO aberta - buscando dados atuais...');

            // Buscar dados atuais do formulário geral
            const dadosGeral = {
                delegado: $('#inputDelegado').val(),
                escrivao: $('#inputEscrivao').val(),
                delegacia: $('#inputDelegacia').val(),
                cidade: $('#inputCidade').val(),
                boe: $('#inputBOE').val(),
                data_cadastro: $('#inputDataCadastro').val() // ✅ NOVO: Buscar data do cadastro
            };

            // Buscar dados atuais do formulário condutor
            const dadosCondutor = {
                nome: $('#inputNomeCondutor').val(),
                endereco: $('#inputEndereco').val(),
                telefone: $('#inputTelefone').val()
            };

            console.log('📋 Dados atuais do GERAL:', dadosGeral);
            console.log('📋 Dados atuais do CONDUTOR:', dadosCondutor);

            // Aplicar os dados se os campos da intimação estiverem vazios
            receberDadosGeral(dadosGeral);
            receberDadosCondutor(dadosCondutor);
        });
    }

    // === FUNÇÕES AUXILIARES ===
    function preencherDataCompleta(dataInput) {
        let dataObj;
        if (dataInput) {
            const partes = dataInput.split('/');
            if (partes.length === 3 && partes[0] && partes[1] && partes[2]) {
                const dia = parseInt(partes[0]), mes = parseInt(partes[1]), ano = parseInt(partes[2]);
                if (!isNaN(dia) && !isNaN(mes) && !isNaN(ano)) {
                    dataObj = new Date(ano, mes - 1, dia);
                }
            }
        }

        if (!dataObj || isNaN(dataObj.getTime())) {
            return '';
        }

        // Usar toLocaleDateString para garantir formatação correta em português
        return dataObj.toLocaleDateString('pt-BR', {
            day: 'numeric', month: 'long', year: 'numeric'
        });
    }

    function formatarDataParaInput(dataString) {
        if (!dataString) return '';
        if (dataString.includes('-')) {
            const partes = dataString.split('-');
            if (partes.length === 3) {
                return `${partes[2]}/${partes[1]}/${partes[0]}`;
            }
        }
        return dataString;
    }

    function converterDataParaObjeto(dataString) {
        if (!dataString) return null;

        const partes = dataString.split('/');
        if (partes.length !== 3) return null;

        // ✅ CORREÇÃO: Usar construtor (ano, mes-1, dia) para evitar problemas de fuso horário
        return new Date(partes[2], partes[1] - 1, partes[0]);
    }

    function converterDataParaMySQL(dataString) {
        if (!dataString) return '';
        const partes = dataString.split('/');
        if (partes.length !== 3) return '';
        return `${partes[2]}-${partes[1]}-${partes[0]}`;
    }

    // === FUNÇÕES DE MODAL ===
    // Adaptado para usar funções globais (Toast)
    function mostrarModalSucesso(mensagem) {
        window.mostrarSucesso(mensagem);
    }

    function mostrarModalErro(mensagem) {
        window.mostrarErro(mensagem);
    }

    // === FUNÇÃO PARA IMPRIMIR INTIMAÇÃO - ABRIR EDITOR ===
    function imprimirIntimacao() {
        // Coletar dados do formulário de intimação
        const dadosIntimacao = {
            data: $('#inputDataIntimacao').val(),
            data_comp: $('#inputDataCompIntimacao').val(),
            dataoitiva: $('#inputDataOitivaIntimacao').val(),
            hora: $('#inputHorarioIntimacao').val(),
            delegado: $('#inputDelegadoIntimacao').val(),
            escrivao: $('#inputEscrivaoIntimacao').val(),
            delegacia: $('#inputDelegaciaIntimacao').val(),
            cidade: $('#inputCidadeIntimacao').val(),
            BOE: $('#inputReferenciaIntimacao').val(),
            Nome: $('#inputNomeIntimacao').val(),
            Endereco: $('#inputEnderecoIntimacao').val(),
            Telefone: $('#inputTelefoneIntimacao').val()
        };

        // Validação básica
        if (!dadosIntimacao.Nome) {
            mostrarModalErro('Por favor, preencha o nome do intimado.');
            return;
        }

        if (!dadosIntimacao.data) {
            mostrarModalErro('Por favor, preencha a data da intimação.');
            return;
        }

        // Converter dados para base64
        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dadosIntimacao))));

        // ✅ USAR ROTA GET PARA ABRIR EDITOR
        const url = `/intimacao/${dadosCodificados}`;
        window.open(url, '_blank');
    }

    // === IMPORTAÇÃO DE ENVOLVIDOS VIA BOE (SISTEMA DE CHIPS) ===
    // === IMPORTAÇÃO DE ENVOLVIDOS VIA BOE (SISTEMA DE CHIPS) ===
    $('#btnImportarBoeIntimacao').click(function () {
        // Limpa a área de texto
        $('#textoBoeIntimacao').val('');
        // Abre o modal
        var modal = new bootstrap.Modal(document.getElementById('modalImportarBoeIntimacao'));
        modal.show();
    });

    // Ao pressionar Enter no campo BOE
    $('#inputReferenciaIntimacao').keypress(function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnImportarBoeIntimacao').click();
        }
    });

    // Processar BOE via IA (texto ou PDF)
    $('#btnProcessarBoeIntimacao').click(function () {
        const $btn = $(this);
        const originalHtml = $btn.html();
        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const fileInput = document.getElementById('pdfBoeIntimacao');
        const pdfAtivo = fileInput && fileInput.files.length > 0;

        // Helper: progresso suave Intimacao
        let _intProgressInterval = null;
        function iniciarProgressoInt(label) {
            let pct = 0;
            $('#boeProgressWrapperIntimacao').show();
            $('#boeProgressLabelIntimacao').text(label);
            $('#boeProgressBarIntimacao').css('width', '0%').attr('aria-valuenow', 0);
            $('#boeProgressPercentIntimacao').text('0%');
            _intProgressInterval = setInterval(function () {
                const acelerador = pct < 40 ? 3 : pct < 70 ? 1.5 : 0.4;
                pct = Math.min(pct + acelerador, 85);
                $('#boeProgressBarIntimacao').css('width', pct + '%').attr('aria-valuenow', Math.round(pct));
                $('#boeProgressPercentIntimacao').text(Math.round(pct) + '%');
            }, 500);
        }
        function finalizarProgressoInt(sucesso) {
            clearInterval(_intProgressInterval);
            const cor = sucesso ? 'bg-success' : 'bg-danger';
            const label = sucesso ? '✅ Dados extraídos com sucesso!' : '❌ Erro na extração.';
            $('#boeProgressBarIntimacao').removeClass('progress-bar-animated progress-bar-striped bg-primary bg-danger bg-success').addClass(cor).css('width', '100%').attr('aria-valuenow', 100);
            $('#boeProgressLabelIntimacao').text(label);
            $('#boeProgressPercentIntimacao').text('100%');
            setTimeout(function () { $('#boeProgressWrapperIntimacao').hide(); }, 2500);
        }

        if (pdfAtivo) {
            if (!fileInput || fileInput.files.length === 0) {
                mostrarModalErro('Selecione um arquivo PDF antes de processar.');
                return;
            }
            formData.append('pdfBOE', fileInput.files[0]);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando PDF...');
            iniciarProgressoInt('🤖 O sistema está lendo o documento...');
        } else {
            var texto = $('#textoBoeIntimacao').val();
            if (!texto || texto.trim() === '') {
                mostrarModalErro('Cole o texto do BOE primeiro.');
                return;
            }
            formData.append('textoBOE', texto);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando texto...');
            iniciarProgressoInt('🤖 O sistema está analisando o texto do BOE...');
        }

        $.ajax({
            url: '/intimacao/importar-boe-texto',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                finalizarProgressoInt(response.success && !!response.dados);
                $btn.prop('disabled', false).html(originalHtml);

                if (response.success && response.dados) {
                    const dados = response.dados;

                    // ✅ Fecha o modal de forma ROBUSTA (remove backdrop que pode travar a tela)
                    const modalEl = document.getElementById('modalImportarBoeIntimacao');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }

                    // ✅ FORÇA remoção do backdrop (camada escura) que pode ficar travando a tela
                    setTimeout(() => {
                        document.body.classList.remove('modal-open');
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => backdrop.remove());
                        console.log('✅ Modal fechado e backdrop removido');
                    }, 300);

                    mostrarModalSucesso('Análise concluída com sucesso! Verifique os envolvidos na lista.');

                    // Preenche BOE se disponível
                    if (dados.boe) {
                        $('#inputReferenciaIntimacao').val(dados.boe);
                    } else if (response.boe_extraido) {
                        $('#inputReferenciaIntimacao').val(response.boe_extraido);
                    }

                    // Os campos Delegado, Escrivão, Delegacia e Cidade não são auto-preenchidos nesta etapa
                    // conforme solicitação do usuário, pois são preenchidos manualmente ou via form Geral.

                    // Unifica todos os envolvidos em uma lista única para chips
                    let todosEnvolvidos = [];

                    if (dados.vitimas) {
                        dados.vitimas.forEach(nome => todosEnvolvidos.push({ nome: nome, tipo: 'VITIMA' }));
                    }
                    if (dados.autores) {
                        dados.autores.forEach(nome => todosEnvolvidos.push({ nome: nome, tipo: 'AUTOR' }));
                    }
                    if (dados.testemunhas) {
                        dados.testemunhas.forEach(nome => todosEnvolvidos.push({ nome: nome, tipo: 'TESTEMUNHA' }));
                    }
                    if (dados.condutor) {
                        dados.condutor.forEach(nome => todosEnvolvidos.push({ nome: nome, tipo: 'CONDUTOR' }));
                    }
                    if (dados.outros) {
                        dados.outros.forEach(nome => todosEnvolvidos.push({ nome: nome, tipo: 'ENVOLVIDO' }));
                    }

                    // MERGE COM DETALHES vindos do backend (IA já fez a extração completa)
                    if (dados.envolvidos_detalhes) {
                        console.log('🔍 Aplicando detalhes extraídos pelo backend...');

                        todosEnvolvidos = todosEnvolvidos.map(pessoa => {
                            const detalhe = dados.envolvidos_detalhes[pessoa.nome] || null;

                            if (detalhe) {
                                return {
                                    ...pessoa,
                                    endereco:     detalhe.endereco     || '',
                                    telefone:     detalhe.telefone     || '',
                                    rg:           detalhe.rg           || '',
                                    cpf:          detalhe.cpf          || '',
                                    pai:          detalhe.pai          || '',
                                    mae:          detalhe.mae          || '',
                                    nascimento:   detalhe.nascimento   || '',
                                    naturalidade: detalhe.naturalidade || ''
                                };
                            }
                            return pessoa;
                        });
                    }

                    if (todosEnvolvidos.length > 0) {
                        console.log('✅ Renderizando', todosEnvolvidos.length, 'chips...');
                        renderizarChipsIntimacao(todosEnvolvidos);

                        // Verifica imediatamente se já existem salvos no banco
                        const boeAtual = dados.boe || $('#inputReferenciaIntimacao').val();
                        if (boeAtual) {
                            if (!$('#inputReferenciaIntimacao').val()) $('#inputReferenciaIntimacao').val(boeAtual);
                            verificarEnvolvidosSalvos(boeAtual);
                        }

                    } else {
                        mostrarModalErro('Nenhum envolvido identificado no texto.');
                    }

                } else {
                    mostrarModalErro('Não foi possível processar o texto do BOE.');
                }
            },
            error: function (xhr) {
                finalizarProgressoInt(false);
                $btn.prop('disabled', false).html(originalHtml);
                let msgErro = 'Ocorreu um erro ao processar os dados. Tente novamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msgErro = xhr.responseJSON.message;
                }
                window.mostrarErro(msgErro);
            },
            complete: function () {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function buscarEnvolvidosBoe(boe) {
        // Função mantida vazia se não for mais usada ou pode ser removida
    }

    // Variável global para armazenar nomes salvos
    let nomesSalvosPorBoe = [];

    function verificarEnvolvidosSalvos(boe) {
        if (!boe) return;

        $.ajax({
            url: rotasIntimacao.buscarBoe + '/' + boe,
            method: "GET",
            success: function (response) {
                // Extrair nomes, tipos e IDs
                nomesSalvosPorBoe = response.data.map(item => {
                    let nome = (item.Nome || '').toUpperCase().trim(); // ✅ Proteção contra null
                    let tipo = normalizarTipo(item.Tipo); // Normaliza logo na saída do banco

                    // 🔍 Fallback: Se o tipo no banco for nulo/vazio, tenta buscar no app principal
                    if (!tipo && typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos) {
                        const mapeamento = {
                            'vitimas': 'VITIMA',
                            'autores': 'AUTOR',
                            'testemunhas': 'TESTEMUNHA',
                            'condutores': 'CONDUTOR',
                            'outros': 'ENVOLVIDO'
                        };
                        for (const [key, t] of Object.entries(mapeamento)) {
                            if (OcorrenciasApp.envolvidos[key] && OcorrenciasApp.envolvidos[key].includes(nome)) {
                                tipo = t;
                                console.log(`ℹ️ Tipo corrigido via App para "${nome}": ${tipo}`);
                                break;
                            }
                        }
                    }

                    return {
                        id: item.id, // Armazena o ID do registro no banco
                        nome: nome,
                        tipo: tipo || 'ENVOLVIDO'
                    };
                });

                // Se não há chips no DOM e temos dados, renderizamos!
                if ($('#chipsIntimacaoContainer .chip-selecao').length === 0 && nomesSalvosPorBoe.length > 0) {
                    console.log('✨ Renderizando chips a partir de dados salvos no banco...');
                    renderizarChipsIntimacao(nomesSalvosPorBoe);
                } else {
                    // Se já temos chips renderizados, atualize-os
                    atualizarCoresChips();
                }
            },
            error: function (xhr) {
                console.error('Erro ao verificar envolvidos salvos:', xhr);
            }
        });
    }

    let isUpdatingChips = false; // Guard contra loop infinito

    function atualizarCoresChips() {
        if (isUpdatingChips) return;
        isUpdatingChips = true;

        try {
            console.log('🎨 Iniciando atualização de cores dos chips...');
            const nomesSimples = nomesSalvosPorBoe.map(n => typeof n === 'object' ? n.nome : n);
            console.log('📌 Lista de nomes salvos detectados:', nomesSimples);

            const chipsNoDom = $('#chipsIntimacaoContainer .chip-selecao');

            if (chipsNoDom.length === 0) {
                console.log('⚠️ Nenhum chip encontrado no contêiner #chipsIntimacaoContainer.');
            } else {
                console.log(`📍 Atualizando ${chipsNoDom.length} chips no DOM...`);
                chipsNoDom.each(function () {
                    const $spanNome = $(this).find('.nome-pessoa');
                    const nomeChip = $spanNome.text().toUpperCase().trim();

                    // Busca o registro salvo para pegar o TIPO correto do banco
                    const registroSalvo = nomesSalvosPorBoe.find(n => n.nome === nomeChip);
                    const estaSalvo = !!registroSalvo;
                    const tipoFinal = estaSalvo ? registroSalvo.tipo : ($(this).attr('data-tipo') || 'ENVOLVIDO');

                    console.log(`   - Verificando chip: "${nomeChip}" [Salvo: ${estaSalvo}, Tipo: ${tipoFinal}]`);

                    // Atualiza o data-tipo e a sigla no DOM se estiver salvo
                    if (estaSalvo) {
                        $(this).attr('data-tipo', tipoFinal);
                        $(this).find('.sigla-tipo').text(`[${getSiglaTipo(tipoFinal)}]`);
                    }

                    aplicarCorChip($(this), tipoFinal, nomeChip, estaSalvo);
                });
            }

            // Se estiver integrado com o formulário principal
            if ($('#chipsIntimacaoContainer .chip-selecao').length === 0 && typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos) {
                console.log('🔄 Sincronizando with OcorrenciasApp (Tab Refresh)...');
                $('a[data-bs-toggle="tab"][href="#aba-intimacao-din"]').first().trigger('shown.bs.tab');
            }
        } finally {
            isUpdatingChips = false;
        }
    }

    function aplicarCorChip(element, tipo, nome, isSalvoInternal = null) {
        const nomesSimples = nomesSalvosPorBoe.map(n => typeof n === 'object' ? n.nome : n);
        const isSalvo = isSalvoInternal !== null ? isSalvoInternal : nomesSimples.includes(nome.toUpperCase().trim());

        const tipoNormal = normalizarTipo(tipo);
        element.removeClass('bg-danger bg-warning bg-info bg-success bg-secondary bg-light text-dark border border-secondary text-white');
        element.removeClass('bg-chip-autor bg-chip-vitima bg-chip-testemunha bg-chip-envolvido');

        if (isSalvo) {
            switch (tipoNormal) {
                case 'AUTOR': element.addClass('bg-chip-autor'); break;
                case 'VITIMA': element.addClass('bg-chip-vitima'); break;
                case 'TESTEMUNHA': element.addClass('bg-chip-testemunha'); break;
                default: element.addClass('bg-chip-envolvido');
            }
        } else {
            element.addClass('bg-light text-dark border border-secondary');
        }
    }

    function renderizarChipsIntimacao(envolvidos) {
        const container = $('#chipsIntimacaoContainer');
        container.empty();

        const roles = ['AUTOR', 'VITIMA', 'TESTEMUNHA', 'ENVOLVIDO'];

        envolvidos.forEach(pessoa => {
            let tipoAtual = normalizarTipo(pessoa.tipo || 'ENVOLVIDO');

            // Cria o elemento do chip
            const $chip = $(`
                <div class="badge d-flex align-items-center p-0 cursor-pointer chip-selecao m-1 overflow-hidden" 
                     data-tipo="${tipoAtual}"
                     style="cursor: pointer; transition: all 0.2s; border: 1px solid rgba(0,0,0,0.1);"
                     title="Clique para selecionar">
                    <div class="d-flex align-items-center p-2 flex-grow-1 btn-label-chip">
                        <span class="me-2 fw-bold sigla-tipo">[${getSiglaTipo(tipoAtual)}]</span>
                        <span class="nome-pessoa">${pessoa.nome}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-cycle-tipo border-start px-2 py-2" 
                            style="border-radius: 0; background: rgba(0,0,0,0.05);"
                            title="Alternar Papel (Autor, Vítima...)">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                </div>
            `);

            // Aplica a cor inicial
            aplicarCorChip($chip, tipoAtual, pessoa.nome);

            // Evento de clique no Label (Selecionar)
            $chip.find('.btn-label-chip').click(function (e) {
                console.log('🖱️ CLIQUE NO CHIP DETECTADO:', pessoa.nome);
                e.stopPropagation();
                $('.chip-selecao').removeClass('border-dark shadow-sm').css('transform', 'scale(1)');
                $chip.addClass('border-dark shadow-sm').css('transform', 'scale(1.05)');

                // ✅ NOVO: Se o chip já tem um ID de registro no banco, carrega os dados completos
                const savedData = nomesSalvosPorBoe.find(n => n.nome === (pessoa.nome ? pessoa.nome.toUpperCase().trim() : ''));
                if (savedData && savedData.id) {
                    console.log(`🔍 Chip com cadastro encontrado (ID: ${savedData.id}), carregando dados completos...`);
                    // Limpar chips atuais antes de carregar o registro completo para evitar duplicação ou confusão visual
                    $('#chipsIntimacaoContainer').empty();
                    nomesSalvosPorBoe = [];
                    todosEnvolvidos = [];

                    carregarDadosIntimacao(savedData.id);
                } else {
                    // Caso contrário (importação de texto), apenas preenche o que já tinha no chip
                    const dadosParaForm = { ...pessoa, tipo: $chip.attr('data-tipo') };
                    preencherFormularioComChip(dadosParaForm);
                }
            });

            // Evento de clique no botão de ciclo
            $chip.find('.btn-cycle-tipo').click(function (e) {
                e.stopPropagation();

                // Encontrar o próximo tipo
                let currentIndex = roles.indexOf(tipoAtual);
                let nextIndex = (currentIndex + 1) % roles.length;
                tipoAtual = roles[nextIndex];

                // Atualizar o chip no DOM
                $chip.attr('data-tipo', tipoAtual);
                $chip.find('.sigla-tipo').text(`[${getSiglaTipo(tipoAtual)}]`);
                aplicarCorChip($chip, tipoAtual, pessoa.nome);

                // ✅ SEMPRE atualiza o campo oculto se o nome for o mesmo do formulário
                const nomeNoForm = $('#inputNomeIntimacao').val().toUpperCase().trim();
                const nomeDoChip = pessoa.nome.toUpperCase().trim();

                if (nomeNoForm === nomeDoChip) {
                    console.log(`🎯 Chip ciclado e sincronizado: ${nomeDoChip} -> ${tipoAtual}`);
                    $('#inputTipoEnvolvidoIntimacao').val(tipoAtual);
                }

                console.log(`🔄 Tipo alterado para "${pessoa.nome}": ${tipoAtual}`);
            });

            container.append($chip);
        });
    }

    function getSiglaTipo(tipo) {
        const t = normalizarTipo(tipo);
        const map = { 'VITIMA': 'V', 'AUTOR': 'A', 'TESTEMUNHA': 'T', 'ENVOLVIDO': 'E' };
        return map[t] || '?';
    }

    function normalizarTipo(tipo) {
        if (!tipo) return '';
        const t = tipo.toUpperCase().trim();
        const map = {
            'A': 'AUTOR', 'AUTOR': 'AUTOR',
            'V': 'VITIMA', 'VITIMA': 'VITIMA',
            'T': 'TESTEMUNHA', 'TESTEMUNHA': 'TESTEMUNHA',
            'E': 'ENVOLVIDO', 'ENVOLVIDO': 'ENVOLVIDO'
        };
        return map[t] || t;
    }

    function preencherFormularioComChip(pessoa) {
        // Preenche Nome
        $('#inputNomeIntimacao').val(pessoa.nome || '');

        // Se tiver endereço vindo da pessoa ou do vínculo
        const endereco = pessoa.endereco || pessoa.Endereco || '';
        if (endereco) $('#inputEnderecoIntimacao').val(endereco);

        // Se tiver telefone
        let telefone = pessoa.telefone || pessoa.Telefone || '';

        // Limpeza básica: se for traço ou invalido, coloca ZEROS para a máscara exibir (00) 00000-0000
        // O usuário solicitou que ficassem os zeros "como mostrado"
        if (!telefone || telefone === '-' || telefone === '0' || /^0+$/.test(telefone.replace(/\D/g, ''))) {
            telefone = '00000000000'; // 11 dígitos para preencher a máscara
        }

        $('#inputTelefoneIntimacao').val(telefone).trigger('input'); // Trigger para atualizar máscara se necessário

        // Se tiver outros campos mapeáveis, adicione aqui

        // Feedback visual (opcional)
        // inputNomeIntimacao pisca para indicar preenchimento
        $('#inputNomeIntimacao').fadeOut(100).fadeIn(100);

        // Preencher o TIPO oculto para salvar corretamente
        $('#inputTipoEnvolvidoIntimacao').val(normalizarTipo(pessoa.tipo) || '');

        console.log('✅ Dados preenchidos via chip:', pessoa);
    }

    // === FUNÇÕES DE PESQUISA ===
    function pesquisarIntimacoes() {
        const filtro = $('#filtroIntimacao').val();
        const termo = $('#termoPesquisaIntimacao').val().trim();

        if (!termo) {
            // Se não há termo, carregar últimos registros
            carregarUltimasIntimacoes();
            return;
        }

        // Mostrar loading
        const $tbody = $('#tabelaResultadosIntimacao tbody');
        $tbody.html('<tr><td colspan="5" class="text-center">Pesquisando...</td></tr>');

        $.ajax({
            url: rotasIntimacao.pesquisar,
            method: "GET",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                } else {
                    $tbody.html('<tr><td colspan="5" class="text-center">Nenhum resultado encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="5" class="text-center">Erro ao pesquisar</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa');
            }
        });
    }

    function carregarUltimasIntimacoes() {
        console.log('🔄 Carregando últimas intimações...');

        const $tbody = $('#tabelaResultadosIntimacao tbody');
        $tbody.html('<tr><td colspan="5" class="text-center">Carregando...</td></tr>');

        $.ajax({
            url: rotasIntimacao.ultimos,
            method: "GET",
            success: function (response) {
                console.log('📦 Resposta da API:', response);

                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                    console.log('✅ ' + response.data.length + ' intimações carregadas');
                } else {
                    $tbody.html('<tr><td colspan="5" class="text-center">Nenhuma intimação cadastrada</td></tr>');
                    console.log('ℹ️  Nenhuma intimação encontrada');
                }
            },
            error: function (xhr) {
                console.error('❌ Erro ao carregar últimas:', xhr);
                $tbody.html('<tr><td colspan="5" class="text-center">Erro ao carregar dados</td></tr>');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        const $tbody = $('#tabelaResultadosIntimacao tbody');
        $tbody.empty();

        dados.forEach(function (item) {
            const dataFormatada = formatarDataParaInput(item.data) || '-';
            const dataOitivaFormatada = formatarDataParaInput(item.dataoitiva) || '-';
            
            let badgeSituacao = '';
            const sit = (item.situacao || 'PENDENTE').toUpperCase();
            
            if (sit === 'PENDENTE') badgeSituacao = '<span class="badge bg-warning text-dark">PENDENTE</span>';
            else if (sit === 'COMPARECEU') badgeSituacao = '<span class="badge bg-success">COMPARECEU</span>';
            else if (sit === 'NÃO COMPARECEU') badgeSituacao = '<span class="badge bg-danger">NÃO COMPARECEU</span>';
            else if (sit === 'REMARCADO') badgeSituacao = '<span class="badge bg-info text-dark">REMARCADO</span>';
            else badgeSituacao = `<span class="badge bg-secondary">${sit}</span>`;

            const $linha = $(`
                <tr>
                    <td>${dataFormatada}</td>
                    <td>${item.BOE || '-'}</td>
                    <td>${item.Nome || '-'}</td>
                    <td>${dataOitivaFormatada}</td>
                    <td class="text-center">${badgeSituacao}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-selecionar-intimacao" data-id="${item.id}">
                            <i class="bi bi-check-lg me-1"></i> Selecionar
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });
    }

    // === FUNÇÕES PARA SELECIONAR E CARREGAR DADOS ===
    function carregarDadosIntimacao(id) {
        $.ajax({
            url: rotasIntimacao.buscar + '/' + id,
            method: "GET",
            success: function (response) {
                if (response.success && response.data) {
                    // ✅ Limpar chips antigos antes de carregar o novo registro
                    $('#chipsIntimacaoContainer').empty();
                    nomesSalvosPorBoe = [];
                    todosEnvolvidos = [];

                    preencherFormularioIntimacao(response.data);

                    // Habilitar botões de edição/exclusão e manter salvar habilitado (para permitir clonagem/novo com base neste)
                    $('#btnEditarIntimacao, #btnExcluirIntimacao').prop('disabled', false);
                    $('#btnSalvarIntimacao').prop('disabled', false);

                    // ✅ Atualiza a cor dos chips (pois o registro selecionado obviamente está salvo)
                    if (response.data.BOE) {
                        verificarEnvolvidosSalvos(response.data.BOE);
                    }


                } else {
                    mostrarModalErro('Erro ao carregar intimação');
                }
            },
            error: function (xhr) {
                console.error('Erro ao buscar intimação:', xhr);
                mostrarModalErro('Erro ao carregar intimação');
            }
        });
    }

    function preencherFormularioIntimacao(dados) {
        // Preencher campos do formulário
        $('#intimacao_id').val(dados.id);
        $('#inputDataIntimacao').val(formatarDataParaInput(dados.data));
        $('#inputDataCompIntimacao').val(dados.data_comp || '');
        $('#inputDataOitivaIntimacao').val(formatarDataParaInput(dados.dataoitiva));
        $('#inputHorarioIntimacao').val(dados.hora || '');
        $('#inputDelegadoIntimacao').val(dados.delegado || '');
        $('#inputEscrivaoIntimacao').val(dados.escrivao || '');
        $('#inputDelegaciaIntimacao').val(dados.delegacia || '');
        $('#inputCidadeIntimacao').val(dados.cidade || '');
        $('#inputReferenciaIntimacao').val(dados.BOE || '');
        $('#inputNomeIntimacao').val(dados.Nome || '');
        $('#inputEnderecoIntimacao').val(dados.Endereco || '');
        $('#inputTelefoneIntimacao').val(dados.Telefone || '');
        $('#inputSituacaoIntimacao').val(dados.situacao || 'PENDENTE');
        $('#inputObservacoesIntimacao').val(dados.observacoes || '');

        // Armazenar o TIPO no formulário
        $('#inputTipoEnvolvidoIntimacao').val(normalizarTipo(dados.Tipo) || '');

        // Atualizar current ID
        currentIntimacaoId = dados.id;
    }

    // === FUNÇÕES CRUD ===
    function salvarIntimacao() {
        const $btn = $('#btnSalvarIntimacao');
        const form = document.getElementById('formIntimacao');

        // ✅ VALIDAÇÃO FRONTEND CUSTOMIZADA (Substitui HTML5 native)
        const requiredFields = [
            { id: '#inputDataIntimacao', nome: 'Data Cadastro' },
            { id: '#inputDelegadoIntimacao', nome: 'Delegado(a)' },
            { id: '#inputDelegaciaIntimacao', nome: 'Delegacia' },
            { id: '#inputNomeIntimacao', nome: 'Nome do Envolvido' }
        ];

        const camposVazios = requiredFields.filter(f => !$(f.id).val().trim());

        if (camposVazios.length > 0) {
            const nomesFaltantes = camposVazios.map(f => f.nome).join(', ');
            
            if (typeof window.mostrarErro === 'function') {
                window.mostrarErro(`Preencha os campos obrigatórios: ${nomesFaltantes}`);
            } else {
                alert(`Preencha os campos obrigatórios: ${nomesFaltantes}`);
            }

            // Realçar os campos
            camposVazios.forEach(f => {
                $(f.id).addClass('is-invalid');
                $(f.id).off('input._val').on('input._val', function () {
                    $(this).removeClass('is-invalid');
                });
            });
            $(camposVazios[0].id).focus();
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        const formData = new FormData(document.getElementById('formIntimacao'));

        $.ajax({
            url: rotasIntimacao.salvar,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    // limparFormularioIntimacao(); // ❌ REMOVIDO: Não limpar após salvar

                    // ✅ Mudar estado para EDIÇÃO para evitar duplicatas
                    if (response.id) {
                        $('#intimacao_id').val(response.id);
                        currentIntimacaoId = response.id;

                        // Ajustar botões - Salvar continua habilitado para permitir novos cadastros em sequência
                        $('#btnEditarIntimacao, #btnExcluirIntimacao').prop('disabled', false);
                        $('#btnSalvarIntimacao').prop('disabled', false);
                    }

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function () {
                        carregarUltimasIntimacoes();
                        console.log('✅ Lista atualizada após salvar nova intimação');

                        // Atualizar status dos chips (marcar como salvo)
                        const boe = $('#inputReferenciaIntimacao').val();
                        if (boe) verificarEnvolvidosSalvos(boe);
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function (xhr) {
                console.error('Erro ao salvar:', xhr);
                mostrarModalErro('Erro ao salvar intimação');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Salvar');
            }
        });
    }

    function editarIntimacao() {
        if (!currentIntimacaoId) {
            mostrarModalErro('Nenhuma intimação selecionada para edição');
            return;
        }

        const $btn = $('#btnEditarIntimacao');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Editando...');

        const formData = new FormData(document.getElementById('formIntimacao'));
        formData.append('_method', 'PUT'); // Adicionar método PUT para Laravel

        $.ajax({
            url: rotasIntimacao.atualizar + '/' + currentIntimacaoId,
            method: "POST", // Laravel usa POST para requisições com _method
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);

                    // ✅ ATUALIZAR LISTA E CORES APÓS EDITAR
                    setTimeout(function () {
                        carregarUltimasIntimacoes();
                        const boe = $('#inputReferenciaIntimacao').val();
                        if (boe) verificarEnvolvidosSalvos(boe);
                        console.log('✅ Lista e cores atualizadas após editar intimação');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function (xhr) {
                console.error('Erro ao editar:', xhr);
                mostrarModalErro('Erro ao editar intimação');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Editar');
            }
        });
    }

    function excluirIntimacao() {
        if (!currentIntimacaoId) {
            mostrarModalErro('Nenhuma intimação selecionada para exclusão');
            return;
        }

        var el = document.getElementById('modalConfirmacaoIntimacao');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    function confirmarExclusaoIntimacao() {
        $.ajax({
            url: rotasIntimacao.excluir + '/' + currentIntimacaoId,
            method: "POST",
            data: {
                _method: 'DELETE'
            },
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    // Limpa o formulário e recarrega a lista
                    // $('#formIntimacao')[0].reset(); // Comentado para facilitar cadastro em sequência
                    if (response.success) {
                        const boe = $('#inputReferenciaIntimacao').val();
                        if (boe) verificarEnvolvidosSalvos(boe);
                    }
                    limparFormularioIntimacao();

                    // ✅ ATUALIZAR LISTA APÓS EXCLUIR
                    setTimeout(function () {
                        carregarUltimasIntimacoes();
                        console.log('✅ Lista atualizada após excluir intimação');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
                (function () { var el = document.getElementById('modalConfirmacaoIntimacao'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            },
            error: function (xhr) {
                console.error('Erro ao excluir:', xhr);
                mostrarModalErro('Erro ao excluir intimação');
                (function () { var el = document.getElementById('modalConfirmacaoIntimacao'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            }
        });
    }

    function limparFormularioIntimacao() {
        document.getElementById('formIntimacao').reset();
        $('#intimacao_id').val('');
        $('#inputDataCompIntimacao').val('');
        currentIntimacaoId = null;

        // ✅ Limpar chips e lista de salvos
        $('#chipsIntimacaoContainer').empty();
        nomesSalvosPorBoe = [];
        todosEnvolvidos = []; // Também limpa para evitar dados órfãos

        // ✅ PREENCHER DATA ATUAL quando limpar o formulário
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataIntimacao').val(dataFormatada);
        $('#inputDataIntimacao').trigger('change');

        // Desabilitar botões de edição/exclusão e reativar salvar
        $('#btnEditarIntimacao, #btnExcluirIntimacao').prop('disabled', true);
        $('#btnSalvarIntimacao').prop('disabled', false);

        console.log('✅ Formulário intimação limpo (incluindo chips) e data atual preenchida');
    }

    function novoIntimacao() {
        limparFormularioIntimacao();
    }

    // === FUNÇÕES PARA O CONTROLE DE INTIMAÇÕES ===
    function carregarControleIntimacoes(dataInicio = null, dataFim = null) {
        // Mostrar loading
        const $corpoTabela = $('#corpoTabelaControle').html('<tr><td colspan="4" class="text-center">Carregando...</td></tr>');

        // Preparar dados para a requisição
        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasIntimacao.controlePeriodo,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDadosControle(response.data);
                } else {
                    $corpoTabela.html('<tr><td colspan="4" class="text-center">Nenhuma intimação encontrada</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro ao carregar controle';
                $corpoTabela.html('<tr><td colspan="4" class="text-center">' + erro + '</td></tr>');
                console.error('Erro no controle:', xhr);
            }
        });
    }

    function processarDadosControle(intimacoes) {
        const hoje = new Date();
        const amanha = new Date(hoje);
        amanha.setDate(amanha.getDate() + 1);
        const umaSemana = new Date(hoje);
        umaSemana.setDate(umaSemana.getDate() + 7);

        // Formatar datas para comparação
        const hojeFormatada = hoje.toLocaleDateString('pt-BR');
        const amanhaFormatada = amanha.toLocaleDateString('pt-BR');

        // Contadores
        let contadorHoje = 0;
        let contadorAmanha = 0;
        let contadorSemana = 0;
        let contadorFaltas = 0;
        let contadorTotal = 0;

        // Agrupar por período
        const intimacoesHoje = [];
        const intimacoesAmanha = [];
        const intimacoesSemana = [];
        const intimacoesFaltas = [];
        const intimacoesTodas = intimacoes; // Todas as intimações do banco

        // Processar cada intimação do banco
        intimacoes.forEach(function (intimacao) {
            const dataOitivaTexto = formatarDataParaInput(intimacao.dataoitiva);
            
            // Dados da intimação
            const intimacaoData = {
                id: intimacao.id,
                nome: intimacao.Nome,
                boe: intimacao.BOE,
                dataOitiva: dataOitivaTexto || '-',
                dataIntimacao: formatarDataParaInput(intimacao.data),
                hora: intimacao.hora || '-',
                situacao: (intimacao.situacao || 'PENDENTE').toUpperCase()
            };

            contadorTotal++;

            // Verificar se é FALTA (Não Compareceu) - Independente de data
            if (intimacaoData.situacao === 'NÃO COMPARECEU') {
                contadorFaltas++;
                intimacoesFaltas.push(intimacaoData);
            }

            if (dataOitivaTexto) {
                const dataOitiva = converterDataParaObjeto(dataOitivaTexto);
                if (dataOitiva) {
                    const diffDias = Math.ceil((dataOitiva - hoje) / (1000 * 60 * 60 * 24));

                    // Verificar se é para hoje (DATA OITIVA)
                    if (dataOitivaTexto === hojeFormatada) {
                        contadorHoje++;
                        intimacoesHoje.push(intimacaoData);
                    }

                    // Verificar se é para amanhã (DATA OITIVA)
                    if (dataOitivaTexto === amanhaFormatada) {
                        contadorAmanha++;
                        intimacoesAmanha.push(intimacaoData);
                    }

                    // Verificar se é para os próximos 7 dias (DATA OITIVA)
                    if (diffDias >= 0 && diffDias <= 7) {
                        contadorSemana++;
                        intimacoesSemana.push(intimacaoData);
                    }
                }
            }
        });

        // Atualizar contadores
        $('#contador-hoje').text(contadorHoje);
        $('#contador-amanha').text(contadorAmanha);
        $('#contador-semana').text(contadorSemana);
        $('#contador-faltas').text(contadorFaltas);
        $('#contador-total').text(contadorTotal);

        // Preencher tabela de controle
        const $corpoTabela = $('#corpoTabelaControle').empty();

        // Adicionar linha para HOJE
        if (intimacoesHoje.length > 0) {
            const $linhaHoje = $(`
                <tr>
                    <td><strong>HOJE</strong> (${hojeFormatada})</td>
                    <td><span class="badge bg-danger">${contadorHoje}</span></td>
                    <td>Intimações com oitiva para hoje</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-periodo" data-periodo="hoje">
                            <i class="bi bi-eye me-1"></i> Ver Detalhes
                        </button>
                    </td>
                </tr>
            `);
            $corpoTabela.append($linhaHoje);
        }

        // Adicionar linha para AMANHÃ
        if (intimacoesAmanha.length > 0) {
            const $linhaAmanha = $(`
                <tr>
                    <td><strong>AMANHÃ</strong> (${amanhaFormatada})</td>
                    <td><span class="badge bg-warning">${contadorAmanha}</span></td>
                    <td>Intimações com oitiva para amanhã</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-periodo" data-periodo="amanha">
                            <i class="bi bi-eye me-1"></i> Ver Detalhes
                        </button>
                    </td>
                </tr>
            `);
            $corpoTabela.append($linhaAmanha);
        }

        // Adicionar linha para PRÓXIMOS 7 DIAS
        if (intimacoesSemana.length > 0) {
            const $linhaSemana = $(`
                <tr>
                    <td><strong>PRÓXIMOS 7 DIAS</strong></td>
                    <td><span class="badge bg-primary">${contadorSemana}</span></td>
                    <td>Intimações com oitiva na próxima semana</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-periodo" data-periodo="semana">
                            <i class="bi bi-eye me-1"></i> Ver Detalhes
                        </button>
                    </td>
                </tr>
            `);
            $corpoTabela.append($linhaSemana);
        }

        // Adicionar linha para TODAS
        if (intimacoesTodas.length > 0) {
            const $linhaTotal = $(`
                <tr>
                    <td><strong>TODAS AS INTIMAÇÕES</strong></td>
                    <td><span class="badge bg-secondary">${contadorTotal}</span></td>
                    <td>Total de intimações no período</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-periodo" data-periodo="todas">
                            <i class="bi bi-eye me-1"></i> Ver Detalhes
                        </button>
                    </td>
                </tr>
            `);
            $corpoTabela.append($linhaTotal);
        }

        // Adicionar linha para FALTAS
        if (intimacoesFaltas.length > 0) {
            const $linhaFaltas = $(`
                <tr class="table-danger">
                    <td><strong>NÃO COMPARECERAM</strong></td>
                    <td><span class="badge bg-danger">${contadorFaltas}</span></td>
                    <td>Pessoas que não compareceram às oitivas</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-ver-detalhes-periodo" data-periodo="faltas">
                            <i class="bi bi-eye me-1"></i> Ver Faltas
                        </button>
                    </td>
                </tr>
            `);
            $corpoTabela.append($linhaFaltas);
        }

        // Se não houver intimações
        if ($corpoTabela.children().length === 0) {
            $corpoTabela.append('<tr><td colspan="4" class="text-center">Nenhuma intimação com data de oitiva encontrada</td></tr>');
        }
    }

    function mostrarDetalhesPeriodo(periodo) {
        // Buscar dados atualizados do banco
        const dataInicio = $('#filtroDataInicio').val();
        const dataFim = $('#filtroDataFim').val();

        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasIntimacao.controlePeriodo,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDetalhesPeriodo(periodo, response.data);
                } else {
                    $('#conteudoDetalhesData').html('<p class="text-center">Nenhuma intimação encontrada para este período.</p>');
                }
            },
            error: function (xhr) {
                $('#conteudoDetalhesData').html('<p class="text-center">Erro ao carregar detalhes.</p>');
                console.error('Erro nos detalhes:', xhr);
            }
        });
    }

    function processarDetalhesPeriodo(periodo, intimacoes) {
        const hoje = new Date();
        const amanha = new Date(hoje);
        amanha.setDate(amanha.getDate() + 1);

        const hojeFormatada = hoje.toLocaleDateString('pt-BR');
        const amanhaFormatada = amanha.toLocaleDateString('pt-BR');

        // Coletar intimações do período selecionado
        const intimacoesDoPeriodo = [];

        intimacoes.forEach(function (intimacao) {
            const dataOitivaTexto = formatarDataParaInput(intimacao.dataoitiva);
            if (dataOitivaTexto) {
                const dataOitiva = converterDataParaObjeto(dataOitivaTexto);
                if (dataOitiva) {
                    const diffDias = Math.ceil((dataOitiva - hoje) / (1000 * 60 * 60 * 24));

                    const intimacaoData = {
                        id: intimacao.id,
                        nome: intimacao.Nome,
                        boe: intimacao.BOE,
                        dataOitiva: dataOitivaTexto,
                        dataIntimacao: formatarDataParaInput(intimacao.data),
                        hora: intimacao.hora || '-'
                    };

                    let incluir = false;

                    switch (periodo) {
                        case 'hoje':
                            incluir = (dataOitivaTexto === hojeFormatada);
                            break;
                        case 'amanha':
                            incluir = (dataOitivaTexto === amanhaFormatada);
                            break;
                        case 'semana':
                            incluir = (diffDias >= 0 && diffDias <= 7);
                            break;
                        case 'todas':
                            incluir = true;
                            break;
                        case 'faltas':
                            incluir = (intimacao.situacao === 'NÃO COMPARECEU');
                            break;
                    }

                    if (incluir) {
                        intimacoesDoPeriodo.push(intimacaoData);
                    }
                }
            }
        });

        // Definir título do modal
        let titulo = '';
        switch (periodo) {
            case 'hoje':
                titulo = `Intimações com Oitiva para Hoje (${hojeFormatada})`;
                break;
            case 'amanha':
                titulo = `Intimações com Oitiva para Amanhã (${amanhaFormatada})`;
                break;
            case 'semana':
                titulo = 'Intimações com Oitiva para os Próximos 7 Dias';
                break;
            case 'todas':
                titulo = 'Todas as Intimações no Período';
                break;
        }

        // Preencher modal
        $('#modalDetalhesDataTitulo').text(titulo);
        const $conteudo = $('#conteudoDetalhesData').empty();

        if (intimacoesDoPeriodo.length === 0) {
            $conteudo.append('<p class="text-center">Nenhuma intimação encontrada para este período.</p>');
        } else {
            // Ordenar por data da oitiva
            intimacoesDoPeriodo.sort((a, b) => {
                return new Date(converterDataParaMySQL(a.dataOitiva)) - new Date(converterDataParaMySQL(b.dataOitiva));
            });

            intimacoesDoPeriodo.forEach(intimacao => {
                let badgeSituacao = '';
                const sit = (intimacao.situacao || 'PENDENTE').toUpperCase();
                
                if (sit === 'PENDENTE') badgeSituacao = '<span class="badge bg-warning text-dark">PENDENTE</span>';
                else if (sit === 'COMPARECEU') badgeSituacao = '<span class="badge bg-success">COMPARECEU</span>';
                else if (sit === 'NÃO COMPARECEU') badgeSituacao = '<span class="badge bg-danger">FALTOU</span>';
                else if (sit === 'REMARCADO') badgeSituacao = '<span class="badge bg-info text-dark">REMARCADO</span>';

                const $card = $(`
                    <div class="card mb-2 border-0 shadow-sm ${sit === 'NÃO COMPARECEU' ? 'border-start border-danger border-4' : ''}">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-truncate">
                                    <small class="text-muted">BOE:</small><br>
                                    <strong class="small">${intimacao.boe || 'N/A'}</strong>
                                </div>
                                <div class="col-md-3 text-truncate">
                                    <small class="text-muted">Nome:</small><br>
                                    <strong class="small">${intimacao.nome || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate">
                                    <small class="text-muted">Oitiva:</small><br>
                                    <strong class="small">${intimacao.dataOitiva} ${intimacao.hora}</strong>
                                </div>
                                <div class="col-md-2 text-center">
                                    ${badgeSituacao}
                                </div>
                                <div class="col-md-3 text-end">
                                    <button type="button" class="btn btn-xs btn-primary btn-selecionar-intimacao" data-id="${intimacao.id}" onclick="$('#modalDetalhesData').modal('hide')">
                                        <i class="bi bi-pencil"></i> Abrir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $conteudo.append($card);
            });
        }

        // Aumentar tamanho do modal para extra large e adicionar classe compact
        $('#modalDetalhesData .modal-dialog').addClass('modal-xl');
        $('#modalDetalhesData .modal-body').addClass('compact-modal');
        $('#modalDetalhesData').modal('show');
    }

    // === EVENTOS ===

    // Evento para preencher data completa quando data for alterada
    $('#inputDataIntimacao').off('change').on('change', function () {
        const dataCompleta = preencherDataCompleta($(this).val());
        $('#inputDataCompIntimacao').val(dataCompleta);
    });

    // Eventos dos botões principais
    $('#btnNovoIntimacao').off('click').click(novoIntimacao);
    $('#btnSalvarIntimacao').off('click').on('click', salvarIntimacao);
    $('#btnEditarIntimacao').off('click').click(editarIntimacao);
    $('#btnExcluirIntimacao').off('click').click(excluirIntimacao);
    $('#btnLimparIntimacao').off('click').click(limparFormularioIntimacao);
    $('#btnFecharIntimacao').off('click').click(function () {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'close-subtab', id: 'aba-intimacoes' }, '*');
            }
        } catch (e) { }
    });

    // ✅✅✅ EVENTO DO BOTÃO DE IMPRESSÃO - ADICIONADO
    $('#btnImprimirIntimacao').off('click').click(imprimirIntimacao);

    // Evento do botão pesquisar
    $('#btnPesquisarIntimacao').off('click').click(pesquisarIntimacoes);

    // Evento para pesquisa ao pressionar Enter no campo de pesquisa
    $('#termoPesquisaIntimacao').off('keypress').on('keypress', function (e) {
        if (e.which === 13) {
            pesquisarIntimacoes();
        }
    });

    // Evento para selecionar intimação da tabela
    $(document).off('click', '.btn-selecionar-intimacao').on('click', '.btn-selecionar-intimacao', function () {
        const id = $(this).data('id');
        carregarDadosIntimacao(id);
    });

    // Evento de confirmação de exclusão
    $('#btnConfirmarExclusaoIntimacao').off('click').click(confirmarExclusaoIntimacao);

    // === EVENTOS PARA A NOVA ABA DE CONTROLE ===

    // Quando a aba de controle for mostrada
    $('a[href="#controle-intimacao"]').off('shown.bs.tab').on('shown.bs.tab', function () {
        carregarControleIntimacoes();
    });

    // Botão filtrar
    $('#btnFiltrarIntimacoes').off('click').click(function () {
        const dataInicio = $('#filtroDataInicio').val();
        const dataFim = $('#filtroDataFim').val();

        carregarControleIntimacoes(dataInicio, dataFim);
    });

    // Ver detalhes de um período
    $(document).off('click', '.btn-ver-detalhes-periodo').on('click', '.btn-ver-detalhes-periodo', function () {
        const periodo = $(this).data('periodo');
        mostrarDetalhesPeriodo(periodo);
    });

    // === INICIALIZAÇÃO AUTOMÁTICA ===
    function inicializarIntimacao() {
        // ✅ CORREÇÃO: Preencher data atual no formato correto (DD/MM/AAAA)
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataIntimacao').val(dataFormatada);
        $('#inputDataIntimacao').trigger('change');

        // Inicializar Flatpickr nos campos data (igual ao administrativo)
        const localePt = (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default';

        flatpickr("#inputDataIntimacao", {
            dateFormat: "d/m/Y",
            allowInput: true,
            locale: localePt
        });

        flatpickr("#inputDataOitivaIntimacao", {
            dateFormat: "d/m/Y",
            allowInput: true,
            locale: localePt
        });

        flatpickr("#inputHorarioIntimacao", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
            locale: localePt
        });

        // Desabilitar botões inicialmente
        $('#btnEditarIntimacao, #btnExcluirIntimacao').prop('disabled', true);

        // Preencher datas padrão nos filtros
        const hoje = new Date();
        const umaSemana = new Date(hoje);
        umaSemana.setDate(umaSemana.getDate() + 30); // 30 dias para ver mais dados

        $('#filtroDataInicio').val(hoje.toISOString().split('T')[0]);
        $('#filtroDataFim').val(umaSemana.toISOString().split('T')[0]);

        // ✅ CARREGAR ÚLTIMAS INTIMAÇÕES AUTOMATICAMENTE AO INICIAR
        carregarUltimasIntimacoes();

        // Configurar comunicação entre abas
        configurarComunicacaoEntreAbas();

        // ✅ NOVO: Configurar autocomplete para campos
        setupAutocompleteIntimacao();

        console.log('✅ IntimacaoApp inicializada com sucesso!');
        console.log('✅ Sistema de preenchimento automático ativado!');
    }

    // === FUNÇÃO PARA CONFIGURAR AUTOCOMPLETE ===
    function setupAutocompleteIntimacao() {
        const dadosAutocomplete = {
            delegacias: ["167ª Circunscrição", "Delegacia de Plantão", "Delegacia da Mulher"],
            cidades: ["Afogados da Ingazeira", "Recife", "Caruaru", "Petrolina", "Garanhuns"],
            delegados: ["Leandro Miranda Mai", "Israel Lima Braga Rubis", "Joedna Maria Soares Gomes", "Antonio Junior de Lima e Silva", "Vicktor de Araújo Melo"],
            escrivoes: ["Vandeleys da Silva Lima", "Marcos Antonio da Silva"]
        };

        // Helper para configurar
        const configAutocomplete = (selector, source) => {
            $(selector).autocomplete({
                source: source,
                minLength: 0,
                delay: 300
            }).focus(function () {
                $(this).autocomplete("search", "");
            });
        };

        configAutocomplete("#inputDelegadoIntimacao", dadosAutocomplete.delegados);
        configAutocomplete("#inputEscrivaoIntimacao", dadosAutocomplete.escrivoes);
        configAutocomplete("#inputDelegaciaIntimacao", dadosAutocomplete.delegacias);
        configAutocomplete("#inputCidadeIntimacao", dadosAutocomplete.cidades);

        console.log('✅ Autocomplete configurado para Intimação');
    }

    // Inicializar quando documento estiver pronto
    inicializarIntimacao();
};

$(document).ready(function () {
    if (window.initIntimacaoIfPresent) {
        window.initIntimacaoIfPresent();
    }
});

// ✅ Função IMPORTADA do script.js para parsing robusto no Frontend
// Colocada no escopo global para garantir acesso
function extrairDetalhesDoTexto(nome, texto) {
    try {
        if (!texto || !nome) return null;

        // Normaliza para busca no texto (remove espaços extras)
        const escapeRegExp = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        // 1. Encontra o início do bloco da pessoa
        // Tenta encontrar o nome seguido de "(presente" ou "(não presente"
        const nomeRegexStr = escapeRegExp(nome) + '\\s*\\n\\s*\\((?:não\\s+)?presente';
        let nomeRe = new RegExp(nomeRegexStr, 'i');
        let m = nomeRe.exec(texto);

        // Fallback: Tenta só o nome
        if (!m) {
            nomeRe = new RegExp(escapeRegExp(nome), 'i');
            m = nomeRe.exec(texto);
        }

        if (!m) return null;

        const start = m.index;

        // 2. Define o fim do bloco
        const restoTexto = texto.substring(start + 1);
        const fimRe = /\n[A-ZÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ\s]+\s*\n\s*\((?:não\s+)?presente|Objetos|Complemento|Condutor da ocorrência/i;
        const mFim = fimRe.exec(restoTexto);

        const end = mFim ? (start + 1 + mFim.index) : Math.min(texto.length, start + 3000);
        const bloco = texto.substring(start, end);

        console.log(`📝 Parsing Front para "${nome}": Bloco encontrado (${bloco.length} chars)`);

        // Helper
        const cap = (regex) => {
            const m = regex.exec(bloco);
            return m ? m[1].trim() : '';
        };

        // Extração de Telefone Aprimorada (Multi-linha e ignora traços)
        let telefone = cap(/(?:Telefones?\s+Celulares?|Telefone|Celular).*:\s*([^\n]+)/i);
        // Se falhar ou estiver vazio, tenta olhar na próxima linha explicitamente
        if (!telefone || telefone.trim() === '-' || telefone.length < 5) {
            const mTel = /(?:Telefones?\s+Celulares?|Telefone|Celular).*:\s*(?:\r\n|\n|\r)\s*(?:-\s*)?([0-9\(\)\s-]{8,})/i.exec(bloco);
            if (mTel) telefone = mTel[1].trim();
        }
        // Limpeza de bullet point (ex: "- (87)...")
        if (telefone) telefone = telefone.replace(/^-\s*/, '');

        console.log(`   📞 Telefone extraído: "${telefone}"`);

        return {
            endereco: cap(/(?:Endere[çc]o\s+(?:Residencial|Comercial)|Endere[çc]o|Resid[êe]ncia|Morada).*:\s*([^\n]+)/i),
            telefone: telefone,
            rg: cap(/RG:\s*([0-9A-Za-z\.\-\/]+)/i),
            cpf: cap(/CPF:\s*([0-9\.\-]+)/i),
            mae: cap(/M[ãa]e:\s*([^;\n]+)/i),
            pai: cap(/Pai:\s*([^;\n]+)/i),
            nascimento: cap(/(?:Data de Nascimento|Nascimento):\s*(\d{1,2}\/\d{1,2}\/\d{4})/i)
        };
    } catch (e) {
        console.error('Erro no parsing frontend:', e);
        return null;
    }
}
