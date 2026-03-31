// veiculo.js - VERSÃO COMPLETA CORRIGIDA
$(document).ready(function () {
    // === VERIFICA SE O FORMULÁRIO EXISTE NA PÁGINA ===
    if (!$('#formVeiculo').length) return;

    console.log('✅ Script Veículo carregado na aba correta');

    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: (function(){
            var token = $('meta[name="csrf-token"]').attr('content');
            if (!token) {
                var m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
                if (m) { try { token = decodeURIComponent(m[1]); } catch(e) {} }
            }
            return { 'X-CSRF-TOKEN': token };
        })()
    });

    // === VARIÁVEL GLOBAL ===
    let currentVeiculoId = null;

    // === FUNÇÕES PARA RECEBER DADOS AUTOMATICAMENTE DE OUTROS FORMULÁRIOS ===

    // Receber dados do formulário GERAL (BOE, IP, DATA)
    function receberDadosGeral(dados) {
        console.log('📥 Dados recebidos do GERAL:', dados);

        if (dados.boe && !$('#inputBoeVeiculo').val()) {
            $('#inputBoeVeiculo').val(dados.boe);
            console.log('✅ Preencheu BOE:', dados.boe);
        }
        if (dados.ip && !$('#inputIpVeiculo').val()) {
            $('#inputIpVeiculo').val(dados.ip);
            console.log('✅ Preencheu IP:', dados.ip);
        }
        // ✅ NOVO: Preencher data do veículo com a data do cadastro administrativo
        if (dados.data_cadastro) {
            $('#inputDataVeiculo').val(dados.data_cadastro);
            console.log('✅ Preencheu DATA do veículo:', dados.data_cadastro);
        }
    }

    // Receber dados do formulário CONDUTOR (Pessoa)
    function receberDadosCondutor(dados) {
        console.log('📥 Dados recebidos do CONDUTOR:', dados);

        if (dados.nome && !$('#inputPessoaVeiculo').val()) {
            $('#inputPessoaVeiculo').val(dados.nome);
            console.log('✅ Preencheu Pessoa:', dados.nome);
        }
    }

    // === SISTEMA DE COMUNICAÇÃO ENTRE ABAS ===
    function configurarComunicacaoEntreAbas() {
        // Ouvir eventos de mudança nos outros formulários
        $(document).on('dadosGeralAlterados', function(event, dados) {
            console.log('🎯 Evento dadosGeralAlterados recebido no VEÍCULO');
            if ($('#aba-veiculo').is(':visible')) {
                receberDadosGeral(dados);
            } else {
                console.log('ℹ️  Aba veículo não está visível, dados serão aplicados quando abrir');
            }
        });

        $(document).on('dadosCondutorAlterados', function(event, dados) {
            console.log('🎯 Evento dadosCondutorAlterados recebido no VEÍCULO');
            if ($('#aba-veiculo').is(':visible')) {
                receberDadosCondutor(dados);
            } else {
                console.log('ℹ️  Aba veículo não está visível, dados serão aplicados quando abrir');
            }
        });

        // Também detectar quando a aba de veículo é aberta e buscar dados atuais
        $('a[data-bs-toggle="tab"][href="#aba-veiculo"]').on('shown.bs.tab', function() {
            console.log('🔍 Aba VEÍCULO aberta - buscando dados atuais...');

            // Buscar dados atuais do formulário geral
            const dadosGeral = {
                boe: $('#inputBOE').val(),
                ip: $('#inputIP').val(),
                data_cadastro: $('#inputDataCadastro').val() // ✅ NOVO: Buscar data do cadastro
            };

            // Buscar dados atuais do formulário condutor
            const dadosCondutor = {
                nome: $('#inputNomeCondutor').val()
            };

            console.log('📋 Dados atuais do GERAL:', dadosGeral);
            console.log('📋 Dados atuais do CONDUTOR:', dadosCondutor);

            // Aplicar os dados se os campos do veículo estiverem vazios
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
        $('#sucessoMensagemVeiculo').text(mensagem);
        var el = document.getElementById('modalSucessoVeiculo');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    function mostrarModalErro(mensagem) {
        $('#erroMensagemVeiculo').text(mensagem);
        var el = document.getElementById('modalErroVeiculo');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    // === FUNÇÕES DE PESQUISA ===
    function pesquisarVeiculos() {
        const filtro = $('#filtroVeiculo').val();
        const termo = $('#termoPesquisaVeiculo').val().trim();

        if (!termo) {
            // Se não há termo, carregar últimos registros
            carregarUltimosVeiculos();
            return;
        }

        // Mostrar loading
        const $tbody = $('#tabelaResultadosVeiculo tbody');
        $tbody.html('<tr><td colspan="6" class="text-center">Pesquisando...</td></tr>');

        $.ajax({
            url: rotasVeiculo.pesquisar,
            method: "GET",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                } else {
                    $tbody.html('<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao pesquisar</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa');
            }
        });
    }

    function carregarUltimosVeiculos() {
        console.log('🔄 Carregando últimos veículos...');

        const $tbody = $('#tabelaResultadosVeiculo tbody');
        $tbody.html('<tr><td colspan="6" class="text-center">Carregando...</td></tr>');

        $.ajax({
            url: rotasVeiculo.ultimos,
            method: "GET",
            success: function(response) {
                console.log('📦 Resposta da API:', response);

                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                    console.log('✅ ' + response.data.length + ' veículos carregados');
                } else {
                    $tbody.html('<tr><td colspan="6" class="text-center">Nenhum veículo cadastrado</td></tr>');
                    console.log('ℹ️  Nenhum veículo encontrado');
                }
            },
            error: function(xhr) {
                console.error('❌ Erro ao carregar últimos:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao carregar dados</td></tr>');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        const $tbody = $('#tabelaResultadosVeiculo tbody');
        $tbody.empty();

        dados.forEach(function(item) {
            const dataFormatada = formatarDataParaInput(item.data) || '-';
            const statusBadge = item.status ? `<span class="badge bg-${getStatusColor(item.status)}">${item.status}</span>` : '-';

            const $linha = $(`
                <tr>
                    <td>${dataFormatada}</td>
                    <td>${item.pessoa || '-'}</td>
                    <td>${item.boe || '-'}</td>
                    <td>${item.placa || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-selecionar-veiculo" data-id="${item.id}">
                            <i class="bi bi-check-lg me-1"></i> Selecionar
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });
    }

    function getStatusColor(status) {
        switch(status) {
            case 'APREENDIDO': return 'danger';
            case 'DEVOLVIDO': return 'success';
            case 'ANALISE': return 'warning';
            case 'EM PERÍCIA': return 'warning';
            case 'ARQUIVADO': return 'secondary';
            default: return 'info';
        }
    }

    // === FUNÇÕES PARA SELECIONAR E CARREGAR DADOS ===
    function carregarDadosVeiculo(id) {
        $.ajax({
            url: rotasVeiculo.buscar + '/' + id,
            method: "GET",
            success: function(response) {
                if (response.success && response.data) {
                    preencherFormularioVeiculo(response.data);

                    // Habilitar botões de edição/exclusão
                    $('#btnEditarVeiculo, #btnExcluirVeiculo').prop('disabled', false);

                    // Mostrar mensagem de sucesso
                    mostrarModalSucesso('Veículo carregado com sucesso!');
                } else {
                    mostrarModalErro('Erro ao carregar veículo');
                }
            },
            error: function(xhr) {
                console.error('Erro ao buscar veículo:', xhr);
                mostrarModalErro('Erro ao carregar veículo');
            }
        });
    }

    function preencherFormularioVeiculo(dados) {
        // Preencher campos do formulário
        $('#veiculo_id').val(dados.id);
        $('#inputDataVeiculo').val(formatarDataParaInput(dados.data));
        $('#inputIpVeiculo').val(dados.ip || '');
        $('#inputBoeVeiculo').val(dados.boe || '');
        $('#inputPessoaVeiculo').val(dados.pessoa || '');
        $('#inputVeiculoVeiculo').val(dados.veiculo || '');
        $('#inputPlacaVeiculo').val(dados.placa || '');
        $('#inputChassiVeiculo').val(dados.chassi || '');
        $('#inputSeiVeiculo').val(dados.sei || '');
        $('#inputStatusVeiculo').val(dados.status || '');

        // Atualizar current ID
        currentVeiculoId = dados.id;
    }

    // === FUNÇÕES CRUD ===
    function salvarVeiculo() {
        const $btn = $('#btnSalvarVeiculo');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        var dataVal = ($('#inputDataVeiculo').val() || '').trim();
        var boeVal = ($('#inputBoeVeiculo').val() || '').trim();
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
        const formData = new FormData(document.getElementById('formVeiculo'));

        $.ajax({
            url: rotasVeiculo.salvar,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    limparFormularioVeiculo();

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function() {
                        carregarUltimosVeiculos();
                        console.log('✅ Lista atualizada após salvar novo veículo');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function(xhr) {
                console.error('Erro ao salvar:', xhr);
                var msg = 'Erro ao salvar veículo';
                if (xhr.status === 422 && xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        try { msg = Object.values(xhr.responseJSON.errors).flat().join('\n'); } catch(e) {}
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                mostrarModalErro(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('Salvar');
            }
        });
    }

    function editarVeiculo() {
        if (!currentVeiculoId) {
            mostrarModalErro('Nenhum veículo selecionado para edição');
            return;
        }

        const $btn = $('#btnEditarVeiculo');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Editando...');

        const formData = new FormData(document.getElementById('formVeiculo'));

        $.ajax({
            url: rotasVeiculo.atualizar + '/' + currentVeiculoId,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);

                    // ✅ ATUALIZAR LISTA APÓS EDITAR
                    setTimeout(function() {
                        carregarUltimosVeiculos();
                        console.log('✅ Lista atualizada após editar veículo');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao editar veículo';
                console.error('Erro ao editar:', xhr.responseText || xhr);
                mostrarModalErro(msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('Editar');
            }
        });
    }

    function excluirVeiculo() {
        if (!currentVeiculoId) {
            mostrarModalErro('Nenhum veículo selecionado para exclusão');
            return;
        }

        var el = document.getElementById('modalConfirmacaoVeiculo');
        if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
    }

    function confirmarExclusaoVeiculo() {
        $.ajax({
            url: rotasVeiculo.excluir + '/' + currentVeiculoId,
            method: "POST",
            data: {
                _method: 'DELETE'
            },
            success: function(response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    limparFormularioVeiculo();

                    // ✅ ATUALIZAR LISTA APÓS EXCLUIR
                    setTimeout(function() {
                        carregarUltimosVeiculos();
                        console.log('✅ Lista atualizada após excluir veículo');
                    }, 500);

                } else {
                    mostrarModalErro(response.message);
                }
                (function(){ var el = document.getElementById('modalConfirmacaoVeiculo'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao excluir veículo';
                console.error('Erro ao excluir:', xhr.responseText || xhr);
                mostrarModalErro(msg);
                (function(){ var el = document.getElementById('modalConfirmacaoVeiculo'); if (el) { var m = bootstrap.Modal.getOrCreateInstance(el); m.hide(); } })();
            }
        });
    }

    function limparFormularioVeiculo() {
        document.getElementById('formVeiculo').reset();
        $('#veiculo_id').val('');
        currentVeiculoId = null;

        // ✅ PREENCHER DATA ATUAL quando limpar o formulário
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataVeiculo').val(dataFormatada);

        // Desabilitar botões de edição/exclusão
        $('#btnEditarVeiculo, #btnExcluirVeiculo').prop('disabled', true);

        console.log('✅ Formulário veículo limpo e data atual preenchida');
    }

    function novoVeiculo() {
        limparFormularioVeiculo();
        mostrarModalSucesso('Formulário limpo para novo cadastro');
    }

    // === FUNÇÕES PARA O CONTROLE DE VEÍCULOS ===
    function carregarControleVeiculos(dataInicio = null, dataFim = null) {
        // Mostrar loading
        const $corpoTabela = $('#corpoTabelaControleVeiculo').html('<tr><td colspan="4" class="text-center">Carregando...</td></tr>');

        // Preparar dados para a requisição
        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasVeiculo.controleStatus,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDadosControle(response.data);
                } else {
                    $corpoTabela.html('<tr><td colspan="4" class="text-center">Nenhum veículo encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro ao carregar controle';
                $corpoTabela.html('<tr><td colspan="4" class="text-center">' + erro + '</td></tr>');
                console.error('Erro no controle:', xhr);
            }
        });
    }

    function processarDadosControle(veiculos) {
        // Contadores por status
        let contadorApreendido = 0;
        let contadorDevolvido = 0;
        let contadorAnalise = 0;
        let contadorArquivado = 0;
        let contadorOutros = 0;
        let contadorTotal = veiculos.length;

        // Agrupar por status
        const veiculosPorStatus = {
            'APREENDIDO': [],
            'DEVOLVIDO': [],
            'EM PERÍCIA': [],
            'ARQUIVADO': [],
            'OUTROS': []
        };

        // Processar cada veículo
        veiculos.forEach(function(veiculo) {
            const status = veiculo.status || 'OUTROS';

            const veiculoData = {
                id: veiculo.id,
                data: formatarDataParaInput(veiculo.data),
                pessoa: veiculo.pessoa,
                boe: veiculo.boe,
                placa: veiculo.placa,
                veiculo: veiculo.veiculo,
                chassi: veiculo.chassi,
                sei: veiculo.sei,
                status: status
            };

            switch(status) {
                case 'APREENDIDO':
                    contadorApreendido++;
                    veiculosPorStatus.APREENDIDO.push(veiculoData);
                    break;
                case 'DEVOLVIDO':
                    contadorDevolvido++;
                    veiculosPorStatus.DEVOLVIDO.push(veiculoData);
                    break;
                case 'ANALISE':
                case 'EM PERÍCIA':
                    contadorAnalise++;
                    veiculosPorStatus['EM PERÍCIA'].push(veiculoData);
                    break;
                case 'ARQUIVADO':
                    contadorArquivado++;
                    veiculosPorStatus.ARQUIVADO.push(veiculoData);
                    break;
                default:
                    contadorOutros++;
                    veiculosPorStatus.OUTROS.push(veiculoData);
            }
        });

        // Atualizar contadores
        $('#contador-apreendido').text(contadorApreendido);
        $('#contador-devolvido').text(contadorDevolvido);
        $('#contador-analise').text(contadorAnalise);
        $('#contador-total').text(contadorTotal);

        // Preencher tabela de controle
        const $corpoTabela = $('#corpoTabelaControleVeiculo').empty();

        // Adicionar linhas para cada status que tenha veículos
        const statusOrdem = ['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ARQUIVADO', 'OUTROS'];

        statusOrdem.forEach(function(status) {
            const veiculosStatus = veiculosPorStatus[status];
            if (veiculosStatus.length > 0) {
                const contador = veiculosStatus.length;
                const $linha = $(`
                    <tr>
                        <td><strong>${status}</strong></td>
                        <td><span class="badge bg-${getStatusColor(status)}">${contador}</span></td>
                        <td>Veículos com status ${status.toLowerCase()}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-status" data-status="${status}">
                                <i class="bi bi-eye me-1"></i> Ver Detalhes
                            </td>
                        </td>
                    </tr>
                `);
                $corpoTabela.append($linha);
            }
        });

        // Se não houver veículos
        if ($corpoTabela.children().length === 0) {
            $corpoTabela.append('<tr><td colspan="4" class="text-center">Nenhum veículo encontrado</td></tr>');
        }
    }

    function mostrarDetalhesStatus(status) {
        // Buscar dados atualizados do banco
        const dataInicio = $('#filtroDataInicioVeiculo').val();
        const dataFim = $('#filtroDataFimVeiculo').val();

        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;

        $.ajax({
            url: rotasVeiculo.controleStatus,
            method: "GET",
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    processarDetalhesStatus(status, response.data);
                } else {
                    $('#conteudoDetalhesStatus').html('<p class="text-center">Nenhum veículo encontrado para este status.</p>');
                }
            },
            error: function (xhr) {
                $('#conteudoDetalhesStatus').html('<p class="text-center">Erro ao carregar detalhes.</p>');
                console.error('Erro nos detalhes:', xhr);
            }
        });
    }

    function processarDetalhesStatus(status, veiculos) {
        // Filtrar veículos pelo status
        const veiculosDoStatus = veiculos.filter(veiculo =>
            (veiculo.status || 'OUTROS') === status
        );

        // Preencher modal
        $('#modalDetalhesStatusTitulo').text(`Veículos com Status: ${status}`);
        const $conteudo = $('#conteudoDetalhesStatus').empty();

        if (veiculosDoStatus.length === 0) {
            $conteudo.append('<p class="text-center">Nenhum veículo encontrado para este status.</p>');
        } else {
            // Ordenar por data
            veiculosDoStatus.sort((a, b) => {
                return new Date(converterDataParaMySQL(formatarDataParaInput(b.data))) -
                       new Date(converterDataParaMySQL(formatarDataParaInput(a.data)));
            });

            veiculosDoStatus.forEach(veiculo => {
                const $card = $(`
                    <div class="card mb-2 border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-truncate" title="${veiculo.data || 'N/A'}">
                                    <small class="text-muted">Data:</small><br>
                                    <strong class="small">${veiculo.data || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${veiculo.pessoa || 'N/A'}">
                                    <small class="text-muted">Pessoa:</small><br>
                                    <strong class="small">${veiculo.pessoa || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${veiculo.boe || 'N/A'}">
                                    <small class="text-muted">BOE:</small><br>
                                    <strong class="small">${veiculo.boe || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${veiculo.placa || 'N/A'}">
                                    <small class="text-muted">Placa:</small><br>
                                    <strong class="small">${veiculo.placa || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${veiculo.veiculo || 'N/A'}">
                                    <small class="text-muted">Veículo:</small><br>
                                    <strong class="small">${veiculo.veiculo || 'N/A'}</strong>
                                </div>
                                <div class="col-md-2 text-truncate" title="${veiculo.sei || 'N/A'}">
                                    <small class="text-muted">SEI:</small><br>
                                    <strong class="small">${veiculo.sei || 'N/A'}</strong>
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
    $('#btnNovoVeiculo').click(novoVeiculo);
    $('#btnSalvarVeiculo').off('click').on('click', salvarVeiculo);
    $('#btnEditarVeiculo').click(editarVeiculo);
    $('#btnExcluirVeiculo').click(excluirVeiculo);
    $('#btnLimparVeiculo').click(limparFormularioVeiculo);
    $('#btnFecharVeiculo').click(function(){
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'close-subtab', id: 'aba-veiculos' }, '*');
            }
        } catch(e) {}
    });

    // Evento do botão pesquisar
    $('#btnPesquisarVeiculo').click(pesquisarVeiculos);

    // Evento para pesquisa ao pressionar Enter no campo de pesquisa
    $('#termoPesquisaVeiculo').on('keypress', function(e) {
        if (e.which === 13) {
            pesquisarVeiculos();
        }
    });

    // Evento para selecionar veículo da tabela
    $(document).on('click', '.btn-selecionar-veiculo', function() {
        const id = $(this).data('id');
        carregarDadosVeiculo(id);
    });

    // Evento de confirmação de exclusão
    $('#btnConfirmarExclusaoVeiculo').click(confirmarExclusaoVeiculo);

    // === EVENTOS PARA A NOVA ABA DE CONTROLE ===

    // Quando a aba de controle for mostrada
    $('a[href="#controle-veiculo"]').on('shown.bs.tab', function() {
        carregarControleVeiculos();
    });

    // Botão filtrar
    $('#btnFiltrarVeiculos').click(function() {
        const dataInicio = $('#filtroDataInicioVeiculo').val();
        const dataFim = $('#filtroDataFimVeiculo').val();

        carregarControleVeiculos(dataInicio, dataFim);
    });

    // Ver detalhes de um status
    $(document).on('click', '.btn-ver-detalhes-status', function() {
        const status = $(this).data('status');
        mostrarDetalhesStatus(status);
    });

    // === INICIALIZAÇÃO AUTOMÁTICA ===
    function inicializarVeiculo() {
        // ✅ CORREÇÃO: Preencher data atual no formato correto (DD/MM/AAAA)
        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
        const ano = dataAtual.getFullYear();
        const dataFormatada = `${dia}/${mes}/${ano}`;

        $('#inputDataVeiculo').val(dataFormatada);

        // Inicializar Flatpickr no campo data (igual ao administrativo)
        var localeCfg = (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default';
        flatpickr("#inputDataVeiculo", {
            dateFormat: "d/m/Y",
            allowInput: true,
            locale: localeCfg
        });

        // Desabilitar botões inicialmente
        $('#btnEditarVeiculo, #btnExcluirVeiculo').prop('disabled', true);

        // Preencher datas padrão nos filtros
        const hoje = new Date();
        const umMesAtras = new Date(hoje);
        umMesAtras.setMonth(umMesAtras.getMonth() - 1);

        $('#filtroDataInicioVeiculo').val(umMesAtras.toISOString().split('T')[0]);
        $('#filtroDataFimVeiculo').val(hoje.toISOString().split('T')[0]);

        // ✅ CARREGAR ÚLTIMOS VEÍCULOS AUTOMATICAMENTE AO INICIAR
        carregarUltimosVeiculos();

        // Configurar comunicação entre abas
        configurarComunicacaoEntreAbas();

        console.log('✅ VeiculoApp inicializada com sucesso!');
        console.log('✅ Sistema de preenchimento automático ativado!');
    }

    // Inicializar quando documento estiver pronto
    inicializarVeiculo();
});
