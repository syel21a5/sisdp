$(document).ready(function () {
    // Funções de feedback visual (modais)
    // Usam window.mostrarSucesso e window.mostrarErro definidos em script.js

    // Função de Auto-resize para o campo Apreensão
    const autoResizeApreensao = function() {
        const el = document.getElementById('inputApreensao');
        if (el) {
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight + 5) + 'px';
        }
    };

    // Função para atualizar o contador de itens apreendidos
    const atualizarContadorItens = function(texto) {
        if (!texto) {
            $('#badgeContadorItens').text('0 itens detectados').removeClass('bg-success').addClass('bg-primary');
            return;
        }
        // Conta linhas que começam com "-"
        const lines = texto.split('\n').filter(line => line.trim().startsWith('-'));
        const total = lines.length;
        $('#badgeContadorItens').text(`${total} item(ns) detectado(s)`)
            .removeClass('bg-primary').addClass(total > 0 ? 'bg-success' : 'bg-primary');
    };

    $(document).on('input', '#inputApreensao', function() {
        autoResizeApreensao();
        atualizarContadorItens($(this).val());
    });



    // Variável para rastrear o tipo de pessoa sendo adicionada no modal
    let tipoPessoaAtual = null;

    // Função para abrir o modal de pessoas
    function abrirModalPessoa(tipo) {
        tipoPessoaAtual = tipo;
        $('#modalPessoaLabel').text(`Adicionar/Buscar ${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`);
        // Limpar o modal antes de exibir
        $('#inputBuscaPessoaModal').val('');
        $('#resultadosBuscaPessoa').empty();
        $('#formCadPessoaModal')[0].reset();
        // Abrir o modal
        const modal = new bootstrap.Modal($('#modalPessoa'));
        modal.show();
    }

    // Lógica de busca de pessoas dentro do modal
    $('#inputBuscaPessoaModal').on('keyup', function () {
        const term = $(this).val();
        if (term.length < 2) {
            $('#resultadosBuscaPessoa').empty();
            return;
        }

        $.ajax({
            url: '/pesquisar-pessoa',
            dataType: 'json',
            data: { term: term },
            success: function (data) {
                const $resultados = $('#resultadosBuscaPessoa');
                $resultados.empty();
                if (data.length > 0) {
                    data.forEach(pessoa => {
                        const cpfLink = pessoa.cpf ? ` - CPF: ${pessoa.cpf}` : '';
                        const maeLink = pessoa.mae ? ` - Mãe: ${pessoa.mae}` : '';
                        const nasclink = pessoa.nascimento ? ` - Nasc: ${pessoa.nascimento.split('-').reverse().join('/')}` : '';
                        const $item = $(`<a href="#" class="list-group-item list-group-item-action" data-id="${pessoa.id}" data-nome="${pessoa.nome}">${pessoa.nome}${cpfLink}${maeLink}${nasclink}</a>`);
                        $resultados.append($item);
                    });
                } else {
                    $resultados.append('<span class="list-group-item">Nenhuma pessoa encontrada.</span>');
                }
            }
        });
    });

    // Lógica para selecionar uma pessoa da lista de resultados
    $('#resultadosBuscaPessoa').on('click', '.list-group-item', function (e) {
        e.preventDefault();
        const nome = $(this).data('nome');
        const id = $(this).data('id');
        const boe = $('#inputBOE').val().trim();
        const papelMap = { vitimas: 'VITIMA', autores: 'AUTOR', testemunhas: 'TESTEMUNHA' };

        if (!tipoPessoaAtual || !nome) return;

        if (!boe) {
            // Sem BOE: apenas atualiza UI local e chips globais
            if (!OcorrenciasApp.envolvidos[tipoPessoaAtual].some(p => p === nome)) {
                OcorrenciasApp.envolvidos[tipoPessoaAtual].push(nome);
                if (window.envolvidosChips && window.envolvidosChips[tipoPessoaAtual] && !window.envolvidosChips[tipoPessoaAtual].some(c => c.nome === nome)) {
                    window.envolvidosChips[tipoPessoaAtual].push({ nome: nome, id: id });
                }
                OcorrenciasApp.atualizarChips(tipoPessoaAtual);
            }
            bootstrap.Modal.getInstance($('#modalPessoa')).hide();
            return;
        }

        // Com BOE: cria vínculo persistente
        $.ajax({
            url: '/boe/vinculos/adicionar',
            method: 'POST',
            data: {
                boe: boe,
                pessoa_id: id,
                tipo: papelMap[tipoPessoaAtual]
            },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (resp) {
                if (!OcorrenciasApp.envolvidos[tipoPessoaAtual].some(p => p === nome)) {
                    OcorrenciasApp.envolvidos[tipoPessoaAtual].push(nome);
                    OcorrenciasApp.vinculos[tipoPessoaAtual] = OcorrenciasApp.vinculos[tipoPessoaAtual] || [];
                    OcorrenciasApp.vinculos[tipoPessoaAtual].push({
                        nome: nome,
                        pessoa_id: id,
                        vinculo_id: resp.vinculo_id || null,
                        status_aprovacao: 'aprovado',
                        criado_por_nome: null
                    });
                    if (window.envolvidosChips && window.envolvidosChips[tipoPessoaAtual] && !window.envolvidosChips[tipoPessoaAtual].some(c => c.nome === nome)) {
                        window.envolvidosChips[tipoPessoaAtual].push({ nome: nome, id: id, boevinculo_id: resp.vinculo_id || null });
                    }
                    OcorrenciasApp.atualizarChips(tipoPessoaAtual);
                }
                bootstrap.Modal.getInstance($('#modalPessoa')).hide();
            },
            error: function (xhr) {
                window.mostrarErro('Erro ao vincular pessoa: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // Abertura do modal de Condutor é controlada no script central (script.js)

    // Lógica para o modal de DADOS COMPLEMENTARES DO AUTOR
    let autorIndexAtual = null;
    let vitimaIndexAtual = null;
    let testemunhaIndexAtual = null;

    $('.card-body').on('click', '[data-bs-target="#modalDadosAutor"]', function () {
        autorIndexAtual = $(this).data('index');
        const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.autores) ? OcorrenciasApp.vinculos.autores[autorIndexAtual] : null;
        $('#formDadosAutorModal')[0].reset();
        const cadId = OcorrenciasApp.currentId;
        const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
        if (cadId && pessoaId) {
            const cache = OcorrenciasApp.detalhes && OcorrenciasApp.detalhes.AUTOR ? OcorrenciasApp.detalhes.AUTOR[pessoaId] : null;
            if (cache) {
                $('#inputInterrogatorio').val(cache.interrogatorio || '');
                $('#inputNotaCulpa').val(cache.nota_culpa || '');
            } else {
                $.ajax({
                    url: `/apfd/detalhes/buscar/${cadId}/${pessoaId}/AUTOR`,
                    method: 'GET',
                    success: function (resp) {
                        if (resp.success && resp.data) {
                            $('#inputInterrogatorio').val(resp.data.interrogatorio || '');
                            $('#inputNotaCulpa').val(resp.data.nota_culpa || '');
                        }
                    }
                });
            }
        }
    });

    $('#btnSalvarDadosAutorModal').on('click', function () {
        if (autorIndexAtual !== null) {
            const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.autores) ? OcorrenciasApp.vinculos.autores[autorIndexAtual] : null;
            const cadId = OcorrenciasApp.currentId;
            const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
            const interrogatorio = $('#inputInterrogatorio').val();
            const notaCulpa = $('#inputNotaCulpa').val();
            if (cadId && pessoaId) {
                $.ajax({
                    url: '/apfd/detalhes/salvar',
                    method: 'POST',
                    data: {
                        cadprincipal_id: cadId,
                        pessoa_id: pessoaId,
                        papel: 'AUTOR',
                        interrogatorio: interrogatorio,
                        nota_culpa: notaCulpa,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        bootstrap.Modal.getInstance($('#modalDadosAutor')).hide();
                        autorIndexAtual = null;
                        if (OcorrenciasApp.detalhes && pessoaId) {
                            OcorrenciasApp.detalhes.AUTOR[pessoaId] = {
                                pessoa_id: pessoaId,
                                interrogatorio: interrogatorio,
                                nota_culpa: notaCulpa
                            };
                        }
                    },
                    error: function () {
                        window.mostrarErro('Erro ao salvar dados do autor');
                    }
                });
            } else {
                bootstrap.Modal.getInstance($('#modalDadosAutor')).hide();
                autorIndexAtual = null;
            }
        }
    });

    $('.card-body').on('click', '[data-bs-target="#modalDadosVitima"]', function () {
        vitimaIndexAtual = $(this).data('index');
        const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.vitimas) ? OcorrenciasApp.vinculos.vitimas[vitimaIndexAtual] : null;
        $('#formDadosVitimaModal')[0].reset();
        const cadId = OcorrenciasApp.currentId;
        const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
        if (cadId && pessoaId) {
            const cache = OcorrenciasApp.detalhes && OcorrenciasApp.detalhes.VITIMA ? OcorrenciasApp.detalhes.VITIMA[pessoaId] : null;
            if (cache) {
                $('#inputInterrogatorioVitima').val(cache.interrogatorio || '');
                $('#inputNotaVitima').val(cache.nota_culpa || '');
            } else {
                $.ajax({
                    url: `/apfd/detalhes/buscar/${cadId}/${pessoaId}/VITIMA`,
                    method: 'GET',
                    success: function (resp) {
                        if (resp.success && resp.data) {
                            $('#inputInterrogatorioVitima').val(resp.data.interrogatorio || '');
                            $('#inputNotaVitima').val(resp.data.nota_culpa || '');
                        }
                    }
                });
            }
        }
    });

    $('#btnSalvarDadosVitimaModal').on('click', function () {
        if (vitimaIndexAtual !== null) {
            const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.vitimas) ? OcorrenciasApp.vinculos.vitimas[vitimaIndexAtual] : null;
            const cadId = OcorrenciasApp.currentId;
            const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
            const interrogatorio = $('#inputInterrogatorioVitima').val();
            const notaCulpa = $('#inputNotaVitima').val();
            if (cadId && pessoaId) {
                $.ajax({
                    url: '/apfd/detalhes/salvar',
                    method: 'POST',
                    data: {
                        cadprincipal_id: cadId,
                        pessoa_id: pessoaId,
                        papel: 'VITIMA',
                        interrogatorio: interrogatorio,
                        nota_culpa: notaCulpa,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        bootstrap.Modal.getInstance($('#modalDadosVitima')).hide();
                        vitimaIndexAtual = null;
                        if (OcorrenciasApp.detalhes && pessoaId) {
                            OcorrenciasApp.detalhes.VITIMA[pessoaId] = {
                                pessoa_id: pessoaId,
                                interrogatorio: interrogatorio,
                                nota_culpa: notaCulpa
                            };
                        }
                    },
                    error: function () { window.mostrarErro('Erro ao salvar dados da vítima'); }
                });
            } else {
                bootstrap.Modal.getInstance($('#modalDadosVitima')).hide();
                vitimaIndexAtual = null;
            }
        }
    });

    $('.card-body').on('click', '[data-bs-target="#modalDadosTestemunha"]', function () {
        testemunhaIndexAtual = $(this).data('index');
        const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.testemunhas) ? OcorrenciasApp.vinculos.testemunhas[testemunhaIndexAtual] : null;
        $('#formDadosTestemunhaModal')[0].reset();
        const cadId = OcorrenciasApp.currentId;
        const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
        if (cadId && pessoaId) {
            const cache = OcorrenciasApp.detalhes && OcorrenciasApp.detalhes.TESTEMUNHA ? OcorrenciasApp.detalhes.TESTEMUNHA[pessoaId] : null;
            if (cache) {
                $('#inputInterrogatorioTestemunha').val(cache.interrogatorio || '');
                $('#inputNotaTestemunha').val(cache.nota_culpa || '');
            } else {
                $.ajax({
                    url: `/apfd/detalhes/buscar/${cadId}/${pessoaId}/TESTEMUNHA`,
                    method: 'GET',
                    success: function (resp) {
                        if (resp.success && resp.data) {
                            $('#inputInterrogatorioTestemunha').val(resp.data.interrogatorio || '');
                            $('#inputNotaTestemunha').val(resp.data.nota_culpa || '');
                        }
                    }
                });
            }
        }
    });

    $('#btnSalvarDadosTestemunhaModal').on('click', function () {
        if (testemunhaIndexAtual !== null) {
            const meta = (OcorrenciasApp.vinculos && OcorrenciasApp.vinculos.testemunhas) ? OcorrenciasApp.vinculos.testemunhas[testemunhaIndexAtual] : null;
            const cadId = OcorrenciasApp.currentId;
            const pessoaId = meta && meta.pessoa_id ? meta.pessoa_id : null;
            const interrogatorio = $('#inputInterrogatorioTestemunha').val();
            const notaCulpa = $('#inputNotaTestemunha').val();
            if (cadId && pessoaId) {
                $.ajax({
                    url: '/apfd/detalhes/salvar',
                    method: 'POST',
                    data: {
                        cadprincipal_id: cadId,
                        pessoa_id: pessoaId,
                        papel: 'TESTEMUNHA',
                        interrogatorio: interrogatorio,
                        nota_culpa: notaCulpa,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function () {
                        bootstrap.Modal.getInstance($('#modalDadosTestemunha')).hide();
                        testemunhaIndexAtual = null;
                        if (OcorrenciasApp.detalhes && pessoaId) {
                            OcorrenciasApp.detalhes.TESTEMUNHA[pessoaId] = {
                                pessoa_id: pessoaId,
                                interrogatorio: interrogatorio,
                                nota_culpa: notaCulpa
                            };
                        }
                    },
                    error: function () { window.mostrarErro('Erro ao salvar dados da testemunha'); }
                });
            } else {
                bootstrap.Modal.getInstance($('#modalDadosTestemunha')).hide();
                testemunhaIndexAtual = null;
            }
        }
    });



    // Função para limpar o formulário (CORRIGIDA: Reseta para data atual e limpa tudo)
    function limparFormularioApfd() {
        console.log('🧹 [script_apfd] Limpando formulário...');

        // 1. Resetar formulários nativos
        const form = $('#formInicio')[0];
        if (form) { form.reset(); }

        const fDocs = $('#formDocumentos')[0];
        if (fDocs) { fDocs.reset(); }

        // 2. Limpeza FORÇADA de Dados Complementares e Campos Persistentes
        $('#formDocumentos input, #formDocumentos select, #formDocumentos textarea').val('');
        $('#inputStatus').val('');
        $('#inputPrioridade').val('');

        // 3. Limpar campos principais do APFD
        $('#inputBOE').val('');
        $('#inputIP').val('');
        $('#inputDelegado').val('');
        $('#inputEscrivao').val('');
        $('#inputDelegacia').val('');
        $('#inputCidade').val('');
        $('#inputPolicial1').val('');
        $('#inputPolicial2').val('');
        $('#inputEndFato').val('');
        $('#inputIncidenciaPenal').val('');
        $('#inputApreensao').val('');

        // Limpar condutor
        $('#inputCondutor').val('');

        // ✅ CORREÇÃO: Limpar arrays de envolvidos (incluindo condutores e outros)
        if (OcorrenciasApp.envolvidos) {
            OcorrenciasApp.envolvidos.vitimas = [];
            OcorrenciasApp.envolvidos.autores = [];
            OcorrenciasApp.envolvidos.testemunhas = [];
            OcorrenciasApp.envolvidos.condutores = [];
            OcorrenciasApp.envolvidos.outros = [];
        }

        // ✅ CORREÇÃO: Limpar também os chips globais (window.envolvidosChips)
        if (window.envolvidosChips) {
            if (window.envolvidosChips.vitimas) window.envolvidosChips.vitimas.length = 0;
            if (window.envolvidosChips.autores) window.envolvidosChips.autores.length = 0;
            if (window.envolvidosChips.testemunhas) window.envolvidosChips.testemunhas.length = 0;
            if (window.envolvidosChips.condutores) window.envolvidosChips.condutores.length = 0;
            if (window.envolvidosChips.outros) window.envolvidosChips.outros.length = 0;
        }

        // ✅ CORREÇÃO: Limpar visualmente TODOS os containers
        ['chipsVitimas', 'chipsAutores', 'chipsTestemunhas', 'chipsCondutores', 'chipsOutros'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = '';
        });

        OcorrenciasApp.dadosImportados = {};

        if (typeof OcorrenciasApp.atualizarChips === 'function') {
            OcorrenciasApp.atualizarChips('vitimas');
            OcorrenciasApp.atualizarChips('autores');
            OcorrenciasApp.atualizarChips('testemunhas');
            OcorrenciasApp.atualizarChips('condutores');
            OcorrenciasApp.atualizarChips('outros');
        }

        // Botões de estado
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);

        // 4. DATA ATUAL (Fix para não manter data antiga)
        if (typeof OcorrenciasApp.preencherDataAtual === 'function') {
            OcorrenciasApp.preencherDataAtual();
        } else {
            // Fallback se a função não existir
            const hoje = new Date().toLocaleDateString('pt-BR');
            $('#inputData').val(hoje);
        }

        // ✅ NOVO: Ao limpar, sempre resetar para modo "dono" (novo procedimento)
        if (window.OcorrenciasApp) {
            window.OcorrenciasApp.isOwner = true;
            window.OcorrenciasApp.ownerName = null;
            if (typeof window.OcorrenciasApp.desbloquearCamposFormulario === 'function') {
                window.OcorrenciasApp.desbloquearCamposFormulario();
            }
        }
        $('#bannerPropriedade').remove();

        // ✅ REINICIAR ESTADO DO MODAL DE IMPORTAÇÃO (caso o usuário tenha cancelado ou extraído)
        $('#btnProcessarBoe').prop('disabled', false).html('Processar pelo Sistema');
        $('#boeProgressWrapper').hide();
        $('#boeProgressBar').css('width', '0%').attr('aria-valuenow', 0);
        $('#boeProgressPercent').text('0%');

        console.log('✨ [script_apfd] Limpeza concluída.');
    }

    // Botões Novo/Limpar apontam para a mesma ação
    $('#btnLimpar').off('click').on('click', limparFormularioApfd);
    $('#btnNovo').off('click').on('click', limparFormularioApfd);

    // Lógica para o botão SALVAR do modal de pessoas
    $('#btnSalvarPessoaModal').on('click', function () {
        // Verifica qual aba está ativa
        const isCadastroTab = $('#cadastrar-tab').hasClass('active');

        if (isCadastroTab) {
            // Lógica para CADASTRAR uma nova pessoa
            const formData = new FormData($('#formCadPessoaModal')[0]);

            $.ajax({
                url: '/pessoas/store',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (pessoa) {
                    const boe = $('#inputBOE').val().trim();
                    const papelMap = { vitimas: 'VITIMA', autores: 'AUTOR', testemunhas: 'TESTEMUNHA' };
                    if (boe) {
                        $.ajax({
                            url: '/boe/vinculos/adicionar',
                            method: 'POST',
                            data: { boe: boe, pessoa_id: pessoa.id, tipo: papelMap[tipoPessoaAtual] },
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (resp) {
                                if (!OcorrenciasApp.envolvidos[tipoPessoaAtual].some(p => p === pessoa.nome)) {
                                    OcorrenciasApp.envolvidos[tipoPessoaAtual].push(pessoa.nome);
                                    OcorrenciasApp.vinculos[tipoPessoaAtual] = OcorrenciasApp.vinculos[tipoPessoaAtual] || [];
                                    OcorrenciasApp.vinculos[tipoPessoaAtual].push({
                                        nome: pessoa.nome,
                                        pessoa_id: pessoa.id,
                                        vinculo_id: resp.vinculo_id || null,
                                        status_aprovacao: 'aprovado',
                                        criado_por_nome: null
                                    });
                                    if (window.envolvidosChips && window.envolvidosChips[tipoPessoaAtual] && !window.envolvidosChips[tipoPessoaAtual].some(c => c.nome === pessoa.nome)) {
                                        window.envolvidosChips[tipoPessoaAtual].push({ nome: pessoa.nome, id: pessoa.id, boevinculo_id: resp.vinculo_id || null });
                                    }
                                    OcorrenciasApp.atualizarChips(tipoPessoaAtual);
                                }
                                bootstrap.Modal.getInstance($('#modalPessoa')).hide();
                            },
                            error: function (xhr) {
                                window.mostrarErro('Erro ao vincular pessoa: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                            }
                        });
                    } else {
                        if (tipoPessoaAtual && !OcorrenciasApp.envolvidos[tipoPessoaAtual].some(p => p === pessoa.nome)) {
                            OcorrenciasApp.envolvidos[tipoPessoaAtual].push(pessoa.nome);
                            if (window.envolvidosChips && window.envolvidosChips[tipoPessoaAtual] && !window.envolvidosChips[tipoPessoaAtual].some(c => c.nome === pessoa.nome)) {
                                window.envolvidosChips[tipoPessoaAtual].push({ nome: pessoa.nome, id: pessoa.id });
                            }
                            OcorrenciasApp.atualizarChips(tipoPessoaAtual);
                        }
                        bootstrap.Modal.getInstance($('#modalPessoa')).hide();
                    }
                },
                error: function (response) {
                    // TODO: Melhorar o tratamento de erros (ex: exibir mensagens de validação)
                    if (window.mostrarAlerta) {
                        window.mostrarAlerta('Erro ao salvar a pessoa. Verifique os dados e tente novamente.');
                    } else {
                        Swal.fire("Atenção", 'Erro ao salvar a pessoa. Verifique os dados e tente novamente.', "warning");
                    }
                    console.error(response);
                }
            });

        } else {
            // Lógica para ADICIONAR pessoa selecionada da busca (se necessário)
            // Esta lógica já está no evento de clique do item da lista, então aqui podemos só fechar o modal
            // ou adicionar uma pessoa que foi digitada mas não selecionada (se essa for a regra de negócio)
            const nomeDigitado = $('#inputBuscaPessoaModal').val();
            const pessoaSelecionadaId = $('#hiddenPessoaIdModal').val();

            if (!pessoaSelecionadaId && nomeDigitado) {
                if (tipoPessoaAtual && nomeDigitado && !OcorrenciasApp.envolvidos[tipoPessoaAtual].some(p => p === nomeDigitado)) {
                    OcorrenciasApp.envolvidos[tipoPessoaAtual].push(nomeDigitado);
                    if (window.envolvidosChips && window.envolvidosChips[tipoPessoaAtual] && !window.envolvidosChips[tipoPessoaAtual].some(c => c.nome === nomeDigitado)) {
                        window.envolvidosChips[tipoPessoaAtual].push({ nome: nomeDigitado, id: null });
                    }
                    OcorrenciasApp.atualizarChips(tipoPessoaAtual);
                }
            }
            bootstrap.Modal.getInstance($('#modalPessoa')).hide();
        }
    });

    // Abrir abas dinâmicas no lugar de popups
    function ensureTab(id, label, linkId) {
        if (!document.getElementById(linkId)) {
            $('#subAbasInicio').append(`
                <li class="nav-item">
                    <a class="nav-link" id="${linkId}" data-bs-toggle="tab" href="#${id}" role="tab">${label}</a>
                </li>`);
        }
        const el = document.getElementById(linkId);
        if (el) new bootstrap.Tab(el).show();
    }

    $('#btnAddVitima').off('click').on('click', () => {
        try { $('#btnNovaVitima1').trigger('click'); } catch (e) { }
        ensureTab('tab-vitima', 'Vítima', 'tabLinkVitima');
    });
    $('#btnAddAutor').off('click').on('click', () => {
        try { $('#btnNovoAutor1').trigger('click'); } catch (e) { }
        ensureTab('tab-autor', 'Autor', 'tabLinkAutor');
    });
    $('#btnAddTestemunha').off('click').on('click', () => {
        try { $('#btnNovaTestemunha1').trigger('click'); } catch (e) { }
        ensureTab('tab-testemunha', 'Testemunha', 'tabLinkTestemunha');
    });





    // Evento para o botão de importar BOE - Abre o modal
    $('#btnImportarBoe').on('click', function () {
        // Limpa a área de texto antes de abrir
        $('#textoBoe').val('');
        // Abre o modal
        var modal = new bootstrap.Modal(document.getElementById('modalImportarBoe'));
        modal.show();
    });

    // Evento para processar o Texto ou PDF do BOE pelo Gemini
    $('#btnProcessarBoe').on('click', function () {
        const $btn = $(this);
        const originalHtml = $btn.html();
        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Detectar aba ativa pelo botao que tem classe active
        const pdfAtivo = document.getElementById('tab-pdf') && document.getElementById('tab-pdf').classList.contains('active');

        // Helper: progresso suave
        let _boeProgressInterval = null;
        function iniciarProgresso(label) {
            let pct = 0;
            $('#boeProgressWrapper').show();
            $('#boeProgressLabel').text(label);
            $('#boeProgressBar').css('width', '0%').attr('aria-valuenow', 0);
            $('#boeProgressPercent').text('0%');
            _boeProgressInterval = setInterval(function () {
                // Avança devagar até 85%, simulando espera pela IA
                const acelerador = pct < 40 ? 3 : pct < 70 ? 1.5 : 0.4;
                pct = Math.min(pct + acelerador, 85);
                $('#boeProgressBar').css('width', pct + '%').attr('aria-valuenow', Math.round(pct));
                $('#boeProgressPercent').text(Math.round(pct) + '%');
            }, 500);
        }
        function finalizarProgresso(sucesso) {
            clearInterval(_boeProgressInterval);
            const cor = sucesso ? 'bg-success' : 'bg-danger';
            const label = sucesso ? '✅ Dados extraídos com sucesso!' : '❌ Erro na extração.';
            $('#boeProgressBar').removeClass('progress-bar-animated progress-bar-striped bg-primary bg-danger bg-success').addClass(cor).css('width', '100%').attr('aria-valuenow', 100);
            $('#boeProgressLabel').text(label);
            $('#boeProgressPercent').text('100%');
            setTimeout(function () { 
                $('#boeProgressWrapper').hide();
                // Se foi sucesso, já limpamos os campos para a próxima abertura
                if (sucesso) {
                    $('#textoBoe').val('');
                    $('#pdfBoe').val('');
                    // Reset da barra para 0% para a próxima vez
                    $('#boeProgressBar').css('width', '0%').attr('aria-valuenow', 0);
                    $('#boeProgressPercent').text('0%');
                }
            }, 2500);
        }

        if (pdfAtivo) {
            var fileInput = document.getElementById('pdfBoe');
            if (!fileInput || fileInput.files.length === 0) {
                window.mostrarErro('Selecione um arquivo PDF antes de processar.');
                return;
            }
            formData.append('pdfBOE', fileInput.files[0]);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Lendo documento...');
            iniciarProgresso('Lendo o documento...');
        } else {
            var texto = $('#textoBoe').val();
            if (!texto || texto.trim() === '') {
                window.mostrarErro('Cole o texto do BOE primeiro.');
                return;
            }
            formData.append('textoBOE', texto);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Analisando dados...');
            iniciarProgresso('Analisando informações do BOE...');
        }

        $.ajax({
            url: '/apfd/importar-boe-texto',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                finalizarProgresso(response.success);
                
                if (response.success) {
                    const dados = response.dados;
                    // Preenche os campos do formulário principal com os dados retornados
                    // if (dados.delegado) $('#inputDelegado').val(dados.delegado); // Removido automático a pedido do usuário

                    // Armazena os detalhes importados para uso no modal de edição
                    if (dados.envolvidos_detalhes) {
                        OcorrenciasApp.dadosImportados = dados.envolvidos_detalhes;
                    } else {
                        OcorrenciasApp.dadosImportados = {};
                    }
                    // if (dados.escrivao) $('#inputEscrivao').val(dados.escrivao); // Removido automático a pedido do usuário
                    if (dados.boe) $('#inputBOE').val(dados.boe);
                    if (dados.ip) $('#inputIP').val(dados.ip);
                    // if (dados.delegacia) $('#inputDelegacia').val(dados.delegacia); // Removido automático a pedido do usuário
                    // if (dados.cidade) $('#inputCidade').val(dados.cidade); // Garantir que cidade também não seja preenchida
                    if (dados.data_fato) $('#inputDataFato').val(dados.data_fato);
                    if (dados.hora_fato) $('#inputHoraFato').val(dados.hora_fato);
                    if (dados.end_fato) $('#inputEndFato').val(dados.end_fato);

                    // Novos campos mapeados
                    if (dados.natureza) $('#inputIncidenciaPenal').val(dados.natureza);
                    if (dados.objetos_apreendidos) {
                        $('#inputApreensao').val(dados.objetos_apreendidos);
                        atualizarContadorItens(dados.objetos_apreendidos);
                        setTimeout(autoResizeApreensao, 100);
                    }

                    if (dados.condutor && dados.condutor.length > 0) {
                        // ✅ Adicionar também ao array de envolvidos para gerar chips e permitir conciliação
                        dados.condutor.forEach(nome => {
                            if (!OcorrenciasApp.envolvidos.condutores.some(p => p === nome)) {
                                OcorrenciasApp.envolvidos.condutores.push(nome);
                            }
                        });
                        OcorrenciasApp.atualizarChips('condutores');
                    }

                    if (dados.vitimas && dados.vitimas.length > 0) {
                        dados.vitimas.forEach(nome => {
                            if (!OcorrenciasApp.envolvidos.vitimas.some(p => p === nome)) {
                                OcorrenciasApp.envolvidos.vitimas.push(nome);
                            }
                        });
                        OcorrenciasApp.atualizarChips('vitimas');
                    }
                    if (dados.autores && dados.autores.length > 0) {
                        dados.autores.forEach(nome => {
                            if (!OcorrenciasApp.envolvidos.autores.some(p => p === nome)) {
                                OcorrenciasApp.envolvidos.autores.push(nome);
                            }
                        });
                        OcorrenciasApp.atualizarChips('autores');
                    }
                    if (dados.testemunhas && dados.testemunhas.length > 0) {
                        dados.testemunhas.forEach(nome => {
                            if (!OcorrenciasApp.envolvidos.testemunhas.some(p => p === nome)) {
                                OcorrenciasApp.envolvidos.testemunhas.push(nome);
                            }
                        });
                        OcorrenciasApp.atualizarChips('testemunhas');
                    }
                    if (dados.outros && dados.outros.length > 0) {
                        if (!OcorrenciasApp.envolvidos.outros) OcorrenciasApp.envolvidos.outros = [];
                        dados.outros.forEach(nome => {
                            if (!OcorrenciasApp.envolvidos.outros.some(p => p === nome)) {
                                OcorrenciasApp.envolvidos.outros.push(nome);
                            }
                        });
                        OcorrenciasApp.atualizarChips('outros');
                    }

                    // ✅ NOVO: Armazenar globalmente para que as abas possam recuperar se forem abertas depois
                    window.pendentesIA_Celulares = dados.celulares || [];
                    window.pendentesIA_Veiculos = dados.veiculos || [];
                    window.pendenteIA_Geral = {
                        boe: $('#inputBOE').val(), // Pega o BOE atual do formulário
                        data: $('#inputData').val(),
                        ip: $('#inputIP').val()
                    };
                    
                    // ✅ NOVO: Verificar se o BOE já existia na base
                    if (response.registroExistenteId) {
                        window.mostrarErro('Já existe um registro no sistema com esse BOE. Os dados extraídos foram transferidos para o formulário para você atualizar o registro original.');
                        if (typeof OcorrenciasApp !== 'undefined') {
                            OcorrenciasApp.currentId = response.registroExistenteId;
                            // A próxima atualização de botões forçará o estado para Editar.
                        }
                    }

                    console.log('🤖 SISTEMA EXTRAÇÃO CONCLUÍDA:', {
                        celulares: window.pendentesIA_Celulares.length,
                        veiculos: window.pendentesIA_Veiculos.length
                    });

                    // ✅ NOVO: Conciliar nomes importados com o banco de dados imediatamente
                    // Isso fará com que nomes já existentes fiquem AZUIS e novos fiquem VERMELHOS
                    if (typeof OcorrenciasApp.conciliarEnvolvidosBD === 'function') {
                        OcorrenciasApp.conciliarEnvolvidosBD(['vitimas', 'autores', 'testemunhas', 'condutores', 'outros']);
                    }

                    // ✅ NOVO: Para chips VERMELHOS, armazenar detalhes por nome dentro de vinculos
                    ['vitimas', 'autores', 'testemunhas', 'condutores', 'outros'].forEach(function (tipo) {
                        const arr = OcorrenciasApp.envolvidos[tipo] || [];
                        OcorrenciasApp.vinculos[tipo] = OcorrenciasApp.vinculos[tipo] || [];
                        arr.forEach(function (nome, idx) {
                            const vinc = OcorrenciasApp.vinculos[tipo][idx];
                            const temPessoa = !!(vinc && vinc.pessoa_id);
                            if (!temPessoa) {
                                // Tenta obter detalhes de várias fontes
                                let det = null;

                                // 1. Tenta do objeto dadosImportados (chave exata)
                                if (OcorrenciasApp.dadosImportados && OcorrenciasApp.dadosImportados[nome]) {
                                    det = OcorrenciasApp.dadosImportados[nome];
                                }

                                // 2. Tenta busca normalizada em dadosImportados
                                if (!det && typeof OcorrenciasApp.obterDadosImportadosPorNome === 'function') {
                                    det = OcorrenciasApp.obterDadosImportadosPorNome(nome);
                                }

                                // 3. Tenta extrair do texto bruto (fallback mais forte agora)
                                if (!det && typeof OcorrenciasApp.extrairDetalhesDoTexto === 'function') {
                                    det = OcorrenciasApp.extrairDetalhesDoTexto(nome);
                                }

                                if (det) {
                                    console.log(`[Importação] Detalhes encontrados para ${nome}:`, det);
                                    OcorrenciasApp.vinculos[tipo][idx] = Object.assign({}, vinc, { detalhes: det });
                                } else {
                                    OcorrenciasApp.vinculos[tipo][idx] = Object.assign({}, vinc, { detalhes: null });
                                }
                            }
                        });
                    });

                    if (!response.registroExistenteId) {
                        window.mostrarSucesso('Dados do BOE importados com sucesso!');
                    }

                    // Fecha o modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalImportarBoe'));
                    if (modal) modal.hide();
                } else {
                    window.mostrarErro('Falha ao processar o texto. Nenhum dado foi extraído.');
                }
            },
            error: function (xhr) {
                finalizarProgresso(false);
                $btn.prop('disabled', false).html(originalHtml);
                let msgErro = 'Ocorreu um erro ao se comunicar com o servidor. Tente novamente.';
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
});
