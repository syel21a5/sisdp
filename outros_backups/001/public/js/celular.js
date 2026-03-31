// celular.js - VERSÃO COMPLETA CORRIGIDA
$(document).ready(function () {
    // === VERIFICA SE O FORMULÁRIO EXISTE NA PÁGINA ===
    if (!$('#formCelular').length) return;

    console.log('✅ Script Celular carregado na aba correta');

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
    // === MÁSCARAS PARA OS CAMPOS ===
    // Máscara de telefone removida conforme solicitação


    // === VARIÁVEL GLOBAL ===
    let currentCelularId = null;

    // === FUNÇÕES PARA RECEBER DADOS AUTOMATICAMENTE DE OUTROS FORMULÁRIOS ===

    // Receber dados do formulário GERAL (BOE, IP, DATA)
    function receberDadosGeral(dados) {
        console.log('📥 Dados recebidos do GERAL:', dados);

        if (dados.boe && !$('#inputBoeCelular').val()) {
            $('#inputBoeCelular').val(dados.boe);
            console.log('✅ Preencheu BOE:', dados.boe);
        }
        if (dados.ip && !$('#inputIpCelular').val()) {
            $('#inputIpCelular').val(dados.ip);
            console.log('✅ Preencheu IP:', dados.ip);
        }
        // ✅ NOVO: Preencher data do celular com a data do cadastro administrativo
        if (dados.data_cadastro) {
            $('#inputDataCelular').val(dados.data_cadastro);
            console.log('✅ Preencheu DATA do celular:', dados.data_cadastro);
        }
    }

    // Receber dados do formulário CONDUTOR (Pessoa, Telefone)
    function receberDadosCondutor(dados) {
        console.log('📥 Dados recebidos do CONDUTOR:', dados);

        if (dados.nome && !$('#inputPessoaCelular').val()) {
            $('#inputPessoaCelular').val(dados.nome);
            console.log('✅ Preencheu Pessoa:', dados.nome);
        }
        /*
        if (dados.telefone && !$('#inputTelefoneCelular').val()) {
            $('#inputTelefoneCelular').val(dados.telefone);
            console.log('✅ Preencheu Telefone:', dados.telefone);
        }
        */
    }

    // === SISTEMA DE COMUNICAÇÃO ENTRE ABAS ===
    function configurarComunicacaoEntreAbas() {
        // Ouvir eventos de mudança nos outros formulários
        $(document).on('dadosGeralAlterados', function (event, dados) {
            console.log('🎯 Evento dadosGeralAlterados recebido no CELULAR');
            if ($('#aba-celular').is(':visible')) {
                receberDadosGeral(dados);
            } else {
                console.log('ℹ️  Aba celular não está visível, dados serão aplicados quando abrir');
            }
        });

        $(document).on('dadosCondutorAlterados', function (event, dados) {
            console.log('🎯 Evento dadosCondutorAlterados recebido no CELULAR');
            if ($('#aba-celular').is(':visible')) {
                receberDadosCondutor(dados);
            } else {
                console.log('ℹ️  Aba celular não está visível, dados serão aplicados quando abrir');
            }
        });

        // Também detectar quando a aba de celular é aberta e buscar dados atuais
        $('a[data-bs-toggle="tab"][href="#aba-celular"]').on('shown.bs.tab', function () {
            console.log('🔍 Aba CELULAR aberta - buscando dados atuais...');

            // Buscar dados atuais do formulário geral
            const dadosGeral = {
                boe: $('#inputBOE').val(),
                ip: $('#inputIP').val(),
                data_cadastro: $('#inputDataCadastro').val() // ✅ NOVO: Buscar data do cadastro
            };

            // Buscar dados atuais do formulário condutor
            const dadosCondutor = {
                nome: $('#inputNomeCondutor').val(),
                nome: $('#inputNomeCondutor').val()
                // telefone: $('#inputTelefone').val() // Removido para não preencher especificação
            };

            console.log('📋 Dados atuais do GERAL:', dadosGeral);
            console.log('📋 Dados atuais do CONDUTOR:', dadosCondutor);

            // Aplicar os dados se os campos do celular estiverem vazios
            receberDadosGeral(dadosGeral);
            receberDadosCondutor(dadosCondutor);
        });
    }

    // === FUNÇÕES AUXILIARES ===
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

    function converterDataParaMySQL(dataString) {
        if (!dataString) return '';
        const partes = dataString.split('/');
        if (partes.length !== 3) return '';
        return `${partes[2]}-${partes[1]}-${partes[0]}`;
    }

    // === FUNÇÕES DE MODAL ===
    function mostrarModalSucesso(mensagem) {
        $('#sucessoMensagemCelular').text(mensagem);
        var el = document.getElementById('modalSucessoCelular');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    function mostrarModalErro(mensagem) {
        $('#erroMensagemCelular').text(mensagem);
        var el = document.getElementById('modalErroCelular');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    // === FUNÇÕES DE PESQUISA ===
    function pesquisarCelulares() {
        const filtro = $('#filtroCelular').val();
        const termo = $('#termoPesquisaCelular').val().trim();

        if (!termo) {
            // Se não há termo, carregar últimos registros
            carregarUltimosCelulares();
            return;
        }

        // Mostrar loading
        const $tbody = $('#tabelaResultadosCelular tbody');
        $tbody.html('<tr><td colspan="6" class="text-center">Pesquisando...</td></tr>');

        $.ajax({
            url: rotasCelular.pesquisar,
            method: "GET",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                } else {
                    $tbody.html('<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao pesquisar</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa');
            }
        });
    }

    function carregarUltimosCelulares() {
        console.log('🔄 Carregando últimos celulares...');

        const $tbody = $('#tabelaResultadosCelular tbody');
        $tbody.html('<tr><td colspan="6" class="text-center">Carregando...</td></tr>');

        $.ajax({
            url: rotasCelular.ultimos,
            method: "GET",
            success: function (response) {
                console.log('📦 Resposta da API:', response);

                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                    console.log('✅ ' + response.data.length + ' celulares carregados');
                } else {
                    $tbody.html('<tr><td colspan="6" class="text-center">Nenhum celular cadastrado</td></tr>');
                    console.log('ℹ️  Nenhum celular encontrado');
                }
            },
            error: function (xhr) {
                console.error('❌ Erro ao carregar últimos:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao carregar dados</td></tr>');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        const $tbody = $('#tabelaResultadosCelular tbody');
        $tbody.empty();

        dados.forEach(function (item) {
            const dataFormatada = formatarDataParaInput(item.data) || '-';
            const statusBadge = item.status ? `<span class="badge bg-${getStatusColor(item.status)}">${item.status}</span>` : '-';

            const $linha = $(`
                <tr>
                    <td>${dataFormatada}</td>
                    <td>${item.pessoa || '-'}</td>
                    <td>${item.boe || '-'}</td>
                    <td>${item.imei1 || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-selecionar-celular" data-id="${item.id}">
                            <i class="bi bi-check-lg me-1"></i> Selecionar
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });
    }

    function getStatusColor(status) {
        switch (status) {
            case 'APREENDIDO': return 'danger';
            case 'DEVOLVIDO': return 'success';
            case 'ANALISE': return 'warning';
            case 'EM PERÍCIA': return 'warning';
            case 'ARQUIVADO': return 'secondary';
            default: return 'info';
        }
    }

    // === FUNÇÕES PARA SELECIONAR E CARREGAR DADOS ===
    function carregarDadosCelular(id) {
        $.ajax({
            url: rotasCelular.buscar + '/' + id,
            method: "GET",
            success: function (response) {
                if (response.success && response.data) {
                    preencherFormularioCelular(response.data);

                    // Habilitar botões de edição/exclusão
                    $('#btnEditarCelular, #btnExcluirCelular').prop('disabled', false);

                    // Mostrar mensagem de sucesso
                    mostrarModalSucesso('Celular carregado com sucesso!');
                } else {
                    mostrarModalErro('Erro ao carregar celular');
                }
            },
            error: function (xhr) {
                console.error('Erro ao buscar celular:', xhr);
                mostrarModalErro('Erro ao carregar celular');
            }
        });
    }

    function preencherFormularioCelular(dados) {
        // Preencher campos do formulário
        $('#celular_id').val(dados.id);
        $('#inputDataCelular').val(formatarDataParaInput(dados.data));
        $('#inputIpCelular').val(dados.ip || '');
        $('#inputBoeCelular').val(dados.boe || '');
        $('#inputPessoaCelular').val(dados.pessoa || '');
        $('#inputTelefoneCelular').val(dados.telefone || '');
        $('#inputImei1Celular').val(dados.imei1 || '');
        $('#inputImei2Celular').val(dados.imei2 || '');
        $('#inputProcessoCelular').val(dados.processo || '');
        $('#inputStatusCelular').val(dados.status || '');

        // Atualizar current ID
        currentCelularId = dados.id;
    }

    // === FUNÇÕES CRUD ===
    function salvarCelular() {
        const $btn = $('#btnSalvarCelular');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        var dataVal = ($('#inputDataCelular').val() || '').trim();
        var boeVal = ($('#inputBoeCelular').val() || '').trim();
        if (!dataVal) {
            mostrarModalErro('Preencha o campo Data');
            $btn.prop('disabled', false).html('Salvar');
            return;
        }
        if (!boeVal) {
            mostrarModalErro('Preencha o campo BOE');
            $btn.prop('disabled', false).html('Salvar');
            return;
        }
        const formData = new FormData(document.getElementById('formCelular'));

        $.ajax({
            url: rotasCelular.salvar,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    limparFormularioCelular();

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function () {
                        carregarUltimosCelulares();
                        console.log('✅ Lista atualizada após salvar novo celular');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function (xhr) {
                console.error('Erro ao salvar:', xhr);
                var msg = 'Erro ao salvar celular';
                if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        try { msg = Object.values(xhr.responseJSON.errors).flat().join('\n'); } catch (e) { }
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                mostrarModalErro(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('Salvar');
            }
        });
    }

    function editarCelular() {
        if (!currentCelularId) {
            mostrarModalErro('Nenhum celular selecionado para edição');
            return;
        }

        const $btn = $('#btnEditarCelular');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Editando...');

        const formData = new FormData(document.getElementById('formCelular'));
        formData.append('_method', 'PUT');

        $.ajax({
            url: rotasCelular.atualizar + '/' + currentCelularId,
            method: "POST",
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);

                    // ✅ ATUALIZAR LISTA APÓS EDITAR
                    setTimeout(function () {
                        carregarUltimosCelulares();
                        console.log('✅ Lista atualizada após editar celular');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function (xhr) {
                var msg = 'Erro ao editar celular';
                if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        try { msg = Object.values(xhr.responseJSON.errors).flat().join('\n'); } catch (e) { }
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                console.error('Erro ao editar:', xhr.responseText || xhr);
                mostrarModalErro(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('Editar');
            }
        });
    }

    function excluirCelular() {
        if (!currentCelularId) {
            mostrarModalErro('Nenhum celular selecionado para exclusão');
            return;
        }

        var el = document.getElementById('modalConfirmacaoCelular');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    function confirmarExclusaoCelular() {
        $.ajax({
            url: rotasCelular.excluir + '/' + currentCelularId,
            method: "POST",
            data: {
                _method: 'DELETE'
            },
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    limparFormularioCelular();

                    // ✅ ATUALIZAR LISTA APÓS EXCLUIR
                    setTimeout(function () {
                        carregarUltimosCelulares();
                        console.log('✅ Lista atualizada após excluir celular');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
                (function () { var el = document.getElementById('modalConfirmacaoCelular'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao excluir celular';
                console.error('Erro ao excluir:', xhr.responseText || xhr);
                mostrarModalErro(msg);
                (function () { var el = document.getElementById('modalConfirmacaoCelular'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            }
        });
    }

    function limparFormularioCelular() {
        document.getElementById('formCelular').reset();
        $('#celular_id').val('');
        currentCelularId = null;

        // ✅ PREENCHER DATA ATUAL quando limpar o formulário
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataCelular').val(dataFormatada);

        // Desabilitar botões de edição/exclusão
        $('#btnEditarCelular, #btnExcluirCelular').prop('disabled', true);

        console.log('✅ Formulário celular limpo e data atual preenchida');
    }

    function novoCelular() {
        limparFormularioCelular();
        mostrarModalSucesso('Formulário limpo para novo cadastro');
    }

    // === FUNÇÕES PARA O CONTROLE DE CELULARES ===
    function carregarControleCelulares(dataInicio = null, dataFim = null) {
        // Mostrar loading
        const $corpoTabela = $('#corpoTabelaControleCelular').html('<tr><td colspan="4" class="text-center">Carregando...</td></tr>');

        // Preparar dados para a requisição
        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasCelular.controleStatus,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDadosControle(response.data);
                } else {
                    $corpoTabela.html('<tr><td colspan="4" class="text-center">Nenhum celular encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro ao carregar controle';
                $corpoTabela.html('<tr><td colspan="4" class="text-center">' + erro + '</td></tr>');
                console.error('Erro no controle:', xhr);
            }
        });
    }

    function processarDadosControle(celulares) {
        // Contadores por status
        let contadorApreendido = 0;
        let contadorDevolvido = 0;
        let contadorAnalise = 0;
        let contadorArquivado = 0;
        let contadorOutros = 0;
        let contadorTotal = celulares.length;

        // Agrupar por status
        const celularesPorStatus = {
            'APREENDIDO': [],
            'DEVOLVIDO': [],
            'EM PERÍCIA': [],
            'ARQUIVADO': [],
            'OUTROS': []
        };

        // Processar cada celular
        celulares.forEach(function (celular) {
            const status = celular.status || 'OUTROS';

            const celularData = {
                id: celular.id,
                data: formatarDataParaInput(celular.data),
                pessoa: celular.pessoa,
                boe: celular.boe,
                imei1: celular.imei1,
                processo: celular.processo,
                status: status
            };

            switch (status) {
                case 'APREENDIDO':
                    contadorApreendido++;
                    celularesPorStatus.APREENDIDO.push(celularData);
                    break;
                case 'DEVOLVIDO':
                    contadorDevolvido++;
                    celularesPorStatus.DEVOLVIDO.push(celularData);
                    break;
                case 'ANALISE':
                case 'EM PERÍCIA':
                    contadorAnalise++;
                    celularesPorStatus['EM PERÍCIA'].push(celularData);
                    break;
                case 'ARQUIVADO':
                    contadorArquivado++;
                    celularesPorStatus.ARQUIVADO.push(celularData);
                    break;
                default:
                    contadorOutros++;
                    celularesPorStatus.OUTROS.push(celularData);
            }
        });

        // Atualizar contadores
        $('#contador-apreendido').text(contadorApreendido);
        $('#contador-devolvido').text(contadorDevolvido);
        $('#contador-analise').text(contadorAnalise);
        $('#contador-total').text(contadorTotal);

        // Preencher tabela de controle
        const $corpoTabela = $('#corpoTabelaControleCelular').empty();

        // Adicionar linhas para cada status que tenha celulares
        const statusOrdem = ['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ARQUIVADO', 'OUTROS'];

        statusOrdem.forEach(function (status) {
            const celularesStatus = celularesPorStatus[status];
            if (celularesStatus.length > 0) {
                const contador = celularesStatus.length;
                const $linha = $(`
                    <tr>
                        <td><strong>${status}</strong></td>
                        <td><span class="badge bg-${getStatusColor(status)}">${contador}</span></td>
                        <td>Celulares com status ${status.toLowerCase()}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-status" data-status="${status}">
                                <i class="bi bi-eye me-1"></i> Ver Detalhes
                            </button>
                        </td>
                    </tr>
                `);
                $corpoTabela.append($linha);
            }
        });

        // Se não houver celulares
        if ($corpoTabela.children().length === 0) {
            $corpoTabela.append('<tr><td colspan="4" class="text-center">Nenhum celular encontrado</td></tr>');
        }
    }

    function mostrarDetalhesStatus(status) {
        // Buscar dados atualizados do banco
        const dataInicio = $('#filtroDataInicioCelular').val();
        const dataFim = $('#filtroDataFimCelular').val();

        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasCelular.controleStatus,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDetalhesStatus(status, response.data);
                } else {
                    $('#conteudoDetalhesStatus').html('<p class="text-center">Nenhum celular encontrado para este status.</p>');
                }
            },
            error: function (xhr) {
                $('#conteudoDetalhesStatus').html('<p class="text-center">Erro ao carregar detalhes.</p>');
                console.error('Erro nos detalhes:', xhr);
            }
        });
    }

    function processarDetalhesStatus(status, celulares) {
        // Filtrar celulares pelo status
        const celularesDoStatus = celulares.filter(celular =>
            (celular.status || 'OUTROS') === status
        );

        // Preencher modal
        $('#modalDetalhesStatusTitulo').text(`Celulares com Status: ${status}`);
        const $conteudo = $('#conteudoDetalhesStatus').empty();

        if (celularesDoStatus.length === 0) {
            $conteudo.append('<p class="text-center">Nenhum celular encontrado para este status.</p>');
        } else {
            // Ordenar por data
            celularesDoStatus.sort((a, b) => {
                return new Date(converterDataParaMySQL(formatarDataParaInput(b.data))) -
                    new Date(converterDataParaMySQL(formatarDataParaInput(a.data)));
            });

            celularesDoStatus.forEach(celular => {
                const $card = $(`
                    <div class="card mb-2 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-truncate" title="${celular.data || 'N/A'}">
                                    <small class="text-muted">Data:</small><br>
                                    <strong class="small">${celular.data || 'N/A'}</strong>
                                </div>
                                <div class="col-md-3 text-truncate" title="${celular.pessoa || 'N/A'}">
                                    <small class="text-muted">Pessoa:</small><br>
                                    <strong class="small">${celular.pessoa || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${celular.boe || 'N/A'}">
                                    <small class="text-muted">BOE:</small><br>
                                    <strong class="small">${celular.boe || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${celular.imei1 || 'N/A'}">
                                    <small class="text-muted">IMEI 1:</small><br>
                                    <strong class="small">${celular.imei1 || 'N/A'}</strong>
                                </div>
                                <div class="col-md-3 text-truncate" title="${celular.processo || 'N/A'}">
                                    <small class="text-muted">Processo:</small><br>
                                    <strong class="small">${celular.processo || 'N/A'}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $conteudo.append($card);
            });
        }

        // Aumentar tamanho do modal para extra large
        $('#modalDetalhesStatus .modal-dialog').addClass('modal-xl');
        $('#modalDetalhesStatus').modal('show');
    }

    // === EVENTOS ===

    // Eventos dos botões principais
    $('#btnNovoCelular').click(novoCelular);
    $('#btnSalvarCelular').off('click').on('click', salvarCelular);
    $('#btnEditarCelular').click(editarCelular);
    $('#btnExcluirCelular').click(excluirCelular);
    $('#btnLimparCelular').click(limparFormularioCelular);
    $('#btnFecharCelular').click(function () {
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'close-subtab', id: 'aba-celulares' }, '*');
            }
        } catch (e) { }
    });

    // Evento do botão pesquisar
    $('#btnPesquisarCelular').click(pesquisarCelulares);

    // Evento para pesquisa ao pressionar Enter no campo de pesquisa
    $('#termoPesquisaCelular').on('keypress', function (e) {
        if (e.which === 13) {
            pesquisarCelulares();
        }
    });

    // Evento para selecionar celular da tabela
    $(document).on('click', '.btn-selecionar-celular', function () {
        const id = $(this).data('id');
        carregarDadosCelular(id);
    });

    // Evento de confirmação de exclusão
    $('#btnConfirmarExclusaoCelular').click(confirmarExclusaoCelular);

    // === EVENTOS PARA A NOVA ABA DE CONTROLE ===

    // Quando a aba de controle for mostrada
    $('a[href="#controle-celular"]').on('shown.bs.tab', function () {
        carregarControleCelulares();
    });

    // Botão filtrar
    $('#btnFiltrarCelulares').click(function () {
        const dataInicio = $('#filtroDataInicioCelular').val();
        const dataFim = $('#filtroDataFimCelular').val();

        carregarControleCelulares(dataInicio, dataFim);
    });

    // Ver detalhes de um status
    $(document).on('click', '.btn-ver-detalhes-status', function () {
        const status = $(this).data('status');
        mostrarDetalhesStatus(status);
    });

    // === INICIALIZAÇÃO AUTOMÁTICA ===
    function inicializarCelular() {
        // ✅ CORREÇÃO: Preencher data atual no formato correto (DD/MM/AAAA)
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataCelular').val(dataFormatada);

        // Inicializar Flatpickr no campo data (igual ao administrativo)
        var localeCfg = (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default';
        flatpickr("#inputDataCelular", {
            dateFormat: "d/m/Y",
            allowInput: true,
            locale: localeCfg
        });

        // Desabilitar botões inicialmente
        $('#btnEditarCelular, #btnExcluirCelular').prop('disabled', true);

        // Preencher datas padrão nos filtros
        const hoje = new Date();
        const umMesAtras = new Date(hoje);
        umMesAtras.setMonth(umMesAtras.getMonth() - 1);

        $('#filtroDataInicioCelular').val(umMesAtras.toISOString().split('T')[0]);
        $('#filtroDataFimCelular').val(hoje.toISOString().split('T')[0]);

        // ✅ CARREGAR ÚLTIMOS CELULARES AUTOMATICAMENTE AO INICIAR
        carregarUltimosCelulares();

        // Configurar comunicação entre abas
        configurarComunicacaoEntreAbas();

        console.log('✅ CelularApp inicializada com sucesso!');
        console.log('✅ Sistema de preenchimento automático ativado!');
    }

    // Inicializar quando documento estiver pronto
    inicializarCelular();
});
