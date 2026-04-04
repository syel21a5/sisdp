// veiculo.js - VERSÃO COMPLETA CORRIGIDA
$(document).ready(function () {
    // === VERIFICA SE O FORMULÁRIO EXISTE NA PÁGINA ===
    if (!$('#formVeiculo').length) return;

    console.log('✅ Script Veículo carregado na aba correta');

    var cfgEl = document.getElementById('veiculoPageConfig');
    if (cfgEl) {
        var userIdRaw = cfgEl.getAttribute('data-current-user-id');
        window.currentUserId = userIdRaw ? parseInt(userIdRaw, 10) : null;
        window.isAdminUser = cfgEl.getAttribute('data-is-admin-user') === '1';
        window.rotasVeiculo = {
            importarBoeTexto: cfgEl.getAttribute('data-route-importar-boe-texto'),
            pesquisar: cfgEl.getAttribute('data-route-pesquisar'),
            salvar: cfgEl.getAttribute('data-route-salvar'),
            buscar: cfgEl.getAttribute('data-route-buscar'),
            atualizar: cfgEl.getAttribute('data-route-atualizar'),
            excluir: cfgEl.getAttribute('data-route-excluir'),
            controleStatus: cfgEl.getAttribute('data-route-controle-status'),
            ultimos: cfgEl.getAttribute('data-route-ultimos'),
            exportarExcel: cfgEl.getAttribute('data-route-exportar-excel'),
            exportarPdf: cfgEl.getAttribute('data-route-exportar-pdf'),
            seiVerificar: cfgEl.getAttribute('data-route-sei-verificar')
        };
    }

    var rotasVeiculo = window.rotasVeiculo || {};

    $('#btnAbrirSei').on('click', function () {
        var sei = ($('#inputSeiVeiculo').val() || '').trim();
        var baseUrl = (typeof rotasVeiculo !== 'undefined' && rotasVeiculo.seiVerificar) ? rotasVeiculo.seiVerificar : '/sei/verificar';
        var url = baseUrl;
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'tipo=veiculo';
        if (sei) {
            url += '&sei=' + encodeURIComponent(sei);
        }
        window.open(url, '_blank');
    });

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
    // Instância global para evitar conflitos de reinjeção
    window.graficoVeiculoInstance = window.graficoVeiculoInstance || null;
    let todosResultadosPesquisaVeiculo = [];
    let paginaAtualVeiculo = 1;
    const itensPorPaginaVeiculo = 10;

    // === VARIÁVEIS DE IA ===
    let veiculosPendentesIA = [];
    let indiceIAPendenteVeiculo = 0;


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

    function mostrarModalSucesso(mensagem) {
        window.mostrarSucesso(mensagem);
    }

    function mostrarModalErro(mensagem) {
        window.mostrarErro(mensagem);
    }




    // === FUNÇÕES DE PESQUISA ===
    function pesquisarVeiculos() {
        const filtro = $('#filtroVeiculo').val();
        const termo = $('#termoPesquisaVeiculo').val().trim();

        if (!termo) {
            // Se não há termo, mostra estado vazio e esconde tabela
            $('#conteinerTabelaPesquisaVeiculo').hide();
            $('#emptyStatePesquisaVeiculo').show();
            $('#botoesExportarPesquisaVeiculo').hide();
            return;
        }

        // Prepara visual para exibir resultados
        $('#emptyStatePesquisaVeiculo').hide();
        $('#conteinerTabelaPesquisaVeiculo').show();

        // Mostrar loading
        const $tbody = $('#tabelaResultadosVeiculo tbody');
        $tbody.html('<tr><td colspan="7" class="text-center">Pesquisando...</td></tr>');

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
                    $tbody.html('<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="7" class="text-center">Erro ao pesquisar</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        todosResultadosPesquisaVeiculo = dados;
        paginaAtualVeiculo = 1;
        renderizarTabelaPaginadaVeiculo();
        if (dados.length > 0) {
            $('#botoesExportarPesquisaVeiculo').css('display', 'flex');
        } else {
            $('#botoesExportarPesquisaVeiculo').css('display', 'none');
        }
    }

    function renderizarTabelaPaginadaVeiculo() {
        const $tbody = $('#tabelaResultadosVeiculo tbody');
        $tbody.empty();
        const dados = todosResultadosPesquisaVeiculo;
        const total = dados.length;
        const totalPaginas = Math.ceil(total / itensPorPaginaVeiculo);
        const inicio = (paginaAtualVeiculo - 1) * itensPorPaginaVeiculo;
        const fim = Math.min(inicio + itensPorPaginaVeiculo, total);
        const paginaDados = dados.slice(inicio, fim);

        if (paginaDados.length === 0) {
            $tbody.html('<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>');
            $('#infoPaginacaoVeiculo').css('display', 'none');
            return;
        }

        paginaDados.forEach(function(item) {
            let displayBoe = (item.boe || '-').toUpperCase();
            if (displayBoe !== '-' && displayBoe !== 'NÃO INFORMADO' && displayBoe !== 'NAO INFORMADO') {
                let tempBoe = displayBoe.replace(/O/g, '0');
                if (/^[\d\-\/]+[A-Z]?[\d\-\/]+$/i.test(tempBoe)) {
                    displayBoe = tempBoe;
                }
            }

            const responsavelNome = item.responsavel ? item.responsavel.split(' ')[0] : '-'; // Pega só o primeiro nome
            const statusBadge = item.status ? `<span class="badge bg-${getStatusColor(item.status)}">${item.status}</span>` : '-';
            const $linha = $(`
                <tr>
                    <td class="align-middle text-truncate" style="max-width: 350px;" title="${item.pessoa || '-'}">${item.pessoa || '-'}</td>
                    <td class="align-middle" title="${item.sei || '-'}">${item.sei || '-'}</td>
                    <td class="align-middle text-truncate" title="${displayBoe}">${displayBoe}</td>
                    <td class="align-middle text-truncate" title="${item.placa || '-'}">${item.placa || '-'}</td>
                    <td class="align-middle">${statusBadge}</td>
                    <td class="align-middle text-truncate" title="${item.responsavel || '-'}"><span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>${responsavelNome}</span></td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-sm btn-primary btn-selecionar-veiculo shadow-sm" data-id="${item.id}" title="Selecionar registro" style="border-radius: 6px; padding: 4px 10px; transition: all 0.2s ease;">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });

        $('#textoTotalVeiculo').text(`Exibindo ${inicio + 1}–${fim} de ${total} registros`);
        const $pag = $('#paginacaoVeiculo').empty();
        if (totalPaginas > 1) {
            for (let p = 1; p <= totalPaginas; p++) {
                const $btn = $(`<button class="btn btn-sm ${p === paginaAtualVeiculo ? 'btn-primary' : 'btn-outline-secondary'}">${p}</button>`);
                $btn.on('click', function () { paginaAtualVeiculo = p; renderizarTabelaPaginadaVeiculo(); });
                $pag.append($btn);
            }
        }
        $('#infoPaginacaoVeiculo').css('display', 'flex');
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
                    
                    // A visibilidade dos botões é tratada dentro de preencherFormularioVeiculo
                    
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
        // Preencher formulário e armazenar os dados no data do botão salvar
        $('#veiculo_id').val(dados.id);
        $('#btnSalvarVeiculo').data('registro', dados);

        $('#inputDataVeiculo').val(formatarDataParaInput(dados.data));
        // Dispara evento para o Flatpickr atualizar visualmente se formatarDataParaInput funcionar
        if(document.querySelector('#inputDataVeiculo')._flatpickr) {
            document.querySelector('#inputDataVeiculo')._flatpickr.setDate(formatarDataParaInput(dados.data));
        }

        $('#inputIpVeiculo').val(dados.ip || '');
        $('#inputBoeVeiculo').val(dados.boe || '');
        $('#inputPessoaVeiculo').val(dados.pessoa || '');
        $('#inputVeiculoVeiculo').val(dados.veiculo || '');
        $('#inputPlacaVeiculo').val(dados.placa || '');
        $('#inputChassiVeiculo').val(dados.chassi || '');
        $('#inputSeiVeiculo').val(dados.sei || '');
        $('#inputStatusVeiculo').val(dados.status || '');

        // === CONTROLE DE ACESSO (PERMISSÕES) ===
        const isOwner = (dados.user_id == window.currentUserId);
        const isAdmin = window.isAdminUser;

        if (isOwner || isAdmin) {
            // Pode editar/excluir
            $('#btnEditarVeiculo').prop('disabled', false).removeClass('btn-secondary').addClass('btn-warning');
            $('#btnExcluirVeiculo').prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
            $('#btnSalvarVeiculo').prop('disabled', true); // Se selecionou, habilita Editar, não Salvar (novo)
        } else {
            // Apenas visualização
            $('#btnEditarVeiculo').prop('disabled', true).removeClass('btn-warning').addClass('btn-secondary').attr('title', 'Você não tem permissão para editar este registro');
            $('#btnExcluirVeiculo').prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary').attr('title', 'Você não tem permissão para excluir este registro');
            // $('#btnSalvarVeiculo').prop('disabled', true); // Mantem desabilitado
        }

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
                    mostrarModalSucesso('Veículo salvo com sucesso!');
                    
                    // ✅ NOVO: Remover o item salvo da lista de pendentes da IA
                    if (veiculosPendentesIA.length > 0) {
                        const boe = $('#inputBoeVeiculo').val();
                        if (boe) {
                            const boeKey = boe.replace(/[^a-zA-Z0-9]/g, '');
                            veiculosPendentesIA.splice(indiceIAPendenteVeiculo, 1);
                            
                            if (veiculosPendentesIA.length > 0) {
                                localStorage.setItem('pendentes_veiculos_' + boeKey, JSON.stringify(veiculosPendentesIA));
                                if (indiceIAPendenteVeiculo >= veiculosPendentesIA.length) indiceIAPendenteVeiculo = veiculosPendentesIA.length - 1;
                                preencherComObjetoIAVeiculo(veiculosPendentesIA[indiceIAPendenteVeiculo]);
                                atualizarControlesIAVeiculo();
                            } else {
                                localStorage.removeItem('pendentes_veiculos_' + boeKey);
                                $('#avisoIA_Veiculo').fadeOut();
                            }
                            if (typeof window.atualizarBadgesPendentes === 'function') window.atualizarBadgesPendentes();
                        }
                    }

                    let boeLocal = $('#inputBoeVeiculo').val();
                    limparFormularioVeiculo();

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function() {
                        if (boeLocal) {
                            $('#filtroVeiculo').val('boe');
                            $('#termoPesquisaVeiculo').val(boeLocal);
                            $('#btnPesquisarVeiculo').click();
                        }
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

                    let boeLocal = $('#inputBoeVeiculo').val();
                    limparFormularioVeiculo();

                    // ✅ ATUALIZAR LISTA APÓS EDITAR
                    setTimeout(function() {
                        if (boeLocal) {
                            $('#filtroVeiculo').val('boe');
                            $('#termoPesquisaVeiculo').val(boeLocal);
                            $('#btnPesquisarVeiculo').click();
                        }
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
                    let boeLocal = $('#inputBoeVeiculo').val();
                    limparFormularioVeiculo();

                    // ✅ ATUALIZAR LISTA APÓS EXCLUIR
                    setTimeout(function() {
                        if (boeLocal) {
                            $('#filtroVeiculo').val('boe');
                            $('#termoPesquisaVeiculo').val(boeLocal);
                        }
                        $('#btnPesquisarVeiculo').click();
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

        // Atualiza a visualização e instancia do inputDate
        $('#inputDataVeiculo').val(dataFormatada);
        if(document.querySelector('#inputDataVeiculo')._flatpickr) {
            document.querySelector('#inputDataVeiculo')._flatpickr.setDate(dataFormatada);
        }

        $('#veiculo_id').val('');
        $('#btnSalvarVeiculo').removeData('registro');
        $('#btnSalvarVeiculo').prop('disabled', false);
        $('#btnEditarVeiculo').prop('disabled', true).removeClass('btn-secondary').addClass('btn-warning').removeAttr('title');
        $('#btnExcluirVeiculo').prop('disabled', true).removeClass('btn-secondary').addClass('btn-danger').removeAttr('title');

        // ✅ REINICIAR ESTADO DO MODAL DE IMPORTAÇÃO (caso o usuário tenha cancelado ou extraído)
        $('#btnProcessarBoeVeiculo').prop('disabled', false).html('<i class="bi bi-cpu me-1"></i> Processar pelo Sistema');
        $('#veiculoProgressWrapper').hide();
        $('#veiculoProgressBar').css('width', '0%');
        $('#veiculoProgressPercent').text('0%');

        console.log('✅ Formulário veículo limpo');
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

        // ── Gráfico de rosca ──
        const ctxVeiculo = document.getElementById('graficoVeiculo');
        if (ctxVeiculo) {
            console.log('📊 Inicializando gráfico de veículos...');
            
            // Destruir instância anterior se existir
            if (window.graficoVeiculoInstance) {
                window.graficoVeiculoInstance.destroy();
                window.graficoVeiculoInstance = null;
            }

            if (typeof Chart !== 'undefined') {
                try {
                    window.graficoVeiculoInstance = new Chart(ctxVeiculo, {
                        type: 'doughnut',
                        data: {
                            labels: ['Apreendidos', 'Devolvidos', 'Em Perícia', 'Arquivados', 'Outros'],
                            datasets: [{
                                data: [contadorApreendido, contadorDevolvido, contadorAnalise, contadorArquivado, contadorOutros],
                                backgroundColor: ['#ef4444', '#22c55e', '#eab308', '#3b82f6', '#6b7280'],
                                borderWidth: 2, borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true } },
                                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
                            },
                            cutout: '65%'
                        }
                    });
                    console.log('✅ Gráfico de veículos renderizado com sucesso');
                } catch (err) {
                    console.error('❌ Erro ao criar gráfico de veículos:', err);
                }
            } else {
                console.error('❌ Biblioteca Chart.js não encontrada!');
                $(ctxVeiculo).parent().html('<div class="text-danger small">Erro: Chart.js não carregado</div>');
            }
        }

        // ── Resumo narrativo ──
        if (contadorTotal > 0) {
            const texto = `No período: ${contadorApreendido} apreendido(s), ${contadorDevolvido} devolvido(s), ${contadorAnalise} em perícia, ${contadorArquivado} arquivado(s). Total: ${contadorTotal} veículo(s).`;
            $('#textoResumoNarrativoVeiculo').text(texto);
            $('#resumoNarrativoVeiculo').show();
        } else {
            $('#resumoNarrativoVeiculo').hide();
        }

        // Preencher tabela de controle
        const $corpoTabela = $('#corpoTabelaControleVeiculo').empty();

        // Adicionar linhas para cada status que tenha veículos
        const statusOrdem = ['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ARQUIVADO', 'OUTROS'];

        statusOrdem.forEach(function(status) {
            const veiculosStatus = veiculosPorStatus[status] || [];
            if (veiculosStatus.length > 0) {
                const contador = veiculosStatus.length;
                const $linha = $(`
                    <tr>
                        <td><strong>${status}</strong></td>
                        <td class="text-center"><span class="badge bg-${getStatusColor(status)}">${contador}</span></td>
                        <td>Veículos com status ${status.toLowerCase()}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-ver-detalhes-status" data-status="${status}">
                                <i class="bi bi-eye me-1"></i> Ver Detalhes
                            </button>
                        </td>
                    </tr>
                `);
                $corpoTabela.append($linha);
            }
        });

        // Se não houver veículos
        if ($corpoTabela.children().length === 0) {
            $corpoTabela.append('<tr><td colspan="4" class="text-center text-muted py-3">Nenhum veículo encontrado no período</td></tr>');
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
        const veiculosDoStatus = status ? veiculos.filter(v => {
            const s = v.status || 'OUTROS';
            if (status === 'OUTROS') {
                return !['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ANALISE', 'ARQUIVADO'].includes(s.toUpperCase());
            }
            return s.toUpperCase() === status.toUpperCase();
        }) : veiculos;

        const tituloStatus = status ? status : 'TODOS';
        $('#modalDetalhesStatusVeiculoTitulo').text(`Veículos — Status: ${tituloStatus} (${veiculosDoStatus.length})`);
        const $conteudo = $('#conteudoDetalhesStatusVeiculo').empty();

        if (veiculosDoStatus.length === 0) {
            $conteudo.append('<p class="text-center text-muted">Nenhum veículo encontrado para este status.</p>');
        } else {
            const tabela = `
            <div class="table-responsive">
                <table id="tabelaDetalhesModalVeiculo" class="table table-striped table-hover table-sm" style="font-size: 0.85rem; white-space: nowrap; width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>BOE</th>
                            <th>SEI</th>
                            <th>Pessoa</th>
                            <th>Veículo</th>
                            <th>Placa</th>
                            <th>Chassi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${veiculosDoStatus.map((v, i) => {
                            const nomesBadges = (v.pessoa || '-').split(' - ').map(nome => `<span class="badge bg-light border text-dark fw-normal me-1 mb-1" style="font-size: 0.8rem; padding: 4px 8px;">${nome.trim()}</span>`).join('');
                            return `
                            <tr>
                                <td>${i+1}</td>
                                <td>${formatarDataParaInput(v.data) || '-'}</td>
                                <td><strong>${v.boe || '-'}</strong></td>
                                <td>${v.sei || '-'}</td>
                                <td style="white-space: normal; min-width: 150px;">${nomesBadges}</td>
                                <td style="white-space: normal; min-width: 200px;">${v.veiculo || '-'}</td>
                                <td>${v.placa || '-'}</td>
                                <td><code>${v.chassi || '-'}</code></td>
                            </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>`;
            $conteudo.html(tabela);

            var el = document.getElementById('modalDetalhesStatusVeiculo');
            var m = el ? bootstrap.Modal.getOrCreateInstance(el) : null;

            try {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tabelaDetalhesModalVeiculo')) {
                    $('#tabelaDetalhesModalVeiculo').DataTable().destroy();
                }
                if ($.fn.DataTable) {
                    $('#tabelaDetalhesModalVeiculo').DataTable({
                        language: {
                            emptyTable: "Nenhum registro encontrado na tabela",
                            info: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                            infoEmpty: "Mostrando 0 até 0 de 0 registros",
                            infoFiltered: "(Filtrados de _MAX_ registros)",
                            lengthMenu: "Mostrar _MENU_ registros",
                            loadingRecords: "Carregando...",
                            processing: "Processando...",
                            search: "Pesquisar:",
                            zeroRecords: "Nenhum registro encontrado",
                            paginate: {
                                first: "Primeiro",
                                last: "Último",
                                next: "Próximo",
                                previous: "Anterior"
                            }
                        },
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                        responsive: true
                    });
                }
            } catch (e) {
                console.error("Erro ao inicializar DataTables:", e);
            }

            if (m) m.show();
        }
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

    // Botão filtrar — agora aceita status
    $('#btnFiltrarVeiculos').click(function() {
        const dataInicio = $('#filtroDataInicioVeiculo').val();
        const dataFim = $('#filtroDataFimVeiculo').val();
        const status = $('#filtroStatusVeiculo').val();
        carregarControleVeiculos(dataInicio, dataFim, status);
    });

    // === BOTÕES DE EXPORTAÇÃO — CONTROLE ===
    $('#btnExportarExcelVeiculo').click(function () {
        const dataInicio = $('#filtroDataInicioVeiculo').val();
        const dataFim = $('#filtroDataFimVeiculo').val();
        const status = $('#filtroStatusVeiculo').val();
        let url = rotasVeiculo.exportarExcel;
        const params = new URLSearchParams();
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);
        if (status) params.append('status', status);
        if ([...params].length) url += '?' + params.toString();
        window.location.href = url;
    });

    $('#btnExportarPdfVeiculo').click(function () {
        const dataInicio = $('#filtroDataInicioVeiculo').val();
        const dataFim = $('#filtroDataFimVeiculo').val();
        const status = $('#filtroStatusVeiculo').val();
        let url = rotasVeiculo.exportarPdf;
        const params = new URLSearchParams();
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);
        if (status) params.append('status', status);
        if ([...params].length) url += '?' + params.toString();
        window.location.href = url;
    });

    // === BOTÕES DE EXPORTAÇÃO — PESQUISA ===
    $('#btnExportarExcelPesquisaVeiculo, #btnExportarPdfPesquisaVeiculo').click(function () {
        const isPdf = $(this).attr('id').includes('Pdf');
        const url = isPdf ? rotasVeiculo.exportarPdf : rotasVeiculo.exportarExcel;
        window.location.href = url;
    });

    // Ver detalhes de um status e Clique no Card de Resumo
    $(document).on('click', '.btn-ver-detalhes-status, .card-status-clicavel', function() {
        const status = $(this).data('status');
        mostrarDetalhesStatus(status === 'TODOS' ? '' : status);
    });

    // === carregarControleVeiculos atualizado para suportar status ===
    function carregarControleVeiculosComStatus(dataInicio, dataFim, status) {
        const $corpoTabela = $('#corpoTabelaControleVeiculo').html('<tr><td colspan="4" class="text-center">Carregando...</td></tr>');
        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;
        $.ajax({
            url: rotasVeiculo.controleStatus,
            method: 'GET',
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    let filtrados = response.data;
                    if (status) filtrados = filtrados.filter(v => (v.status || 'OUTROS') === status);
                    processarDadosControle(filtrados);
                } else {
                    $corpoTabela.html('<tr><td colspan="4" class="text-center">Nenhum veículo encontrado</td></tr>');
                }
            },
            error: function () {
                $corpoTabela.html('<tr><td colspan="4" class="text-center">Erro ao carregar</td></tr>');
            }
        });
    }
    carregarControleVeiculos = carregarControleVeiculosComStatus;

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

        // Deixar filtros de data vazios inicialmente        // Redefinir datas de pesquisa ao iniciar
        $('#filtroDataInicioVeiculo').val('');
        $('#filtroDataFimVeiculo').val('');

        // Aplicar máscara no campo Processo/SEI
        if ($.fn.mask) {
            $('#inputSeiVeiculo').mask('0000000000.000000/0000-00');
        }

        // ✅ CARREGAR ÚLTIMOS VEÍCULOS AUTOMATICAMENTE AO INICIAR
        // carregarUltimosVeiculos(); // Removido para otimizar carregamento inicial

        // Configurar comunicação entre abas
        configurarComunicacaoEntreAbas();

        // ✅ NOVO: Ouvir veículos extraídos pela IA
        $(document).on('veiculosExtraidosIA', function (event, lista) {
            console.log('🤖 Módulo Veículo recebeu objetos da IA:', lista);
            veiculosPendentesIA = lista;
            indiceIAPendenteVeiculo = 0;
            if (veiculosPendentesIA.length > 0) {
                preencherComObjetoIAVeiculo(veiculosPendentesIA[0]);
                mostrarAvisoIAVeiculo();
            }
        });

        // ✅ NOVO: Lógica de Importação Híbrida de Veículos
        $(document).off('click', '#btnProcessarBoeVeiculo').on('click', '#btnProcessarBoeVeiculo', function () {
            console.log('🔘 Botão Processar Ciclo de IA clicado!');
            const formData = new FormData();
            
            // Verificar aba ativa (Texto ou PDF)
            const isTextoAtivo = $('#tab-texto-veiculo').hasClass('active');
            
            if (isTextoAtivo) {
                const texto = $('#textoBoeVeiculo').val();
                if (!texto.trim()) {
                    window.mostrarErro('Cole o texto do BOE antes de processar.');
                    return;
                }
                formData.append('textoBOE', texto);
            } else {
                const inputPdf = document.getElementById('pdfBoeVeiculo');
                if (inputPdf.files.length === 0) {
                    window.mostrarErro('Selecione um arquivo PDF antes de processar.');
                    return;
                }
                formData.append('pdfBOE', inputPdf.files[0]);
            }

            // Iniciar UI de carregamento
            $('#btnProcessarBoeVeiculo').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mt-1 mb-1 me-2" role="status" aria-hidden="true"></span> Extraindo...');
            $('#veiculoProgressWrapper').show();
            let percent = 0;
            const progressInterval = setInterval(() => {
                percent += 5;
                if (percent > 90) percent = 90; // Trava no 90% até o retorno
                $('#veiculoProgressBar').css('width', percent + '%');
                $('#veiculoProgressPercent').text(percent + '%');
            }, 500);

            // Adicionar token CSRF
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: rotasVeiculo.importarBoeTexto || '/veiculo/importar-boe-texto',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    clearInterval(progressInterval);
                    $('#veiculoProgressBar').css('width', '100%');
                    $('#veiculoProgressPercent').text('100%');

                    if (response.success && response.dados) {
                        const dados = response.dados;
                        window.pendenteIA_Geral = { boe: dados.boe, ip: dados.ip, data: dados.data };
                        
                        if (dados.veiculos && dados.veiculos.length > 0) {
                            // Preencher o primeiro se for APENAS UM veículo detectado
                            if (dados.veiculos.length === 1) {
                                if (dados.boe) {
                                    $.ajax({
                                        url: rotasVeiculo.pesquisar,
                                        method: "GET",
                                        data: { filtro: "boe", termo: dados.boe },
                                        success: function(pesqResp) {
                                            $('#modalImportarVeiculo').modal('hide');
                                            
                                            let placaLocal = dados.veiculos[0].placa ? dados.veiculos[0].placa.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                            let chassiLocal = dados.veiculos[0].chassi ? dados.veiculos[0].chassi.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                            let specLocal = ((dados.veiculos[0].marca_modelo || '') + ' ' + (dados.veiculos[0].cor || '')).replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                            
                                            let jaExisteExato = false;
                                            let jaExisteSimilar = false;
                                            
                                            let itensDB = (pesqResp.success && pesqResp.data) ? pesqResp.data : [];
                                            
                                            for (let dbItem of itensDB) {
                                                let dbPlaca = dbItem.placa ? dbItem.placa.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                                let dbChassi = dbItem.chassi ? dbItem.chassi.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                                
                                                let matchPlaca = placaLocal && placaLocal !== 'SPLACA' && dbPlaca === placaLocal;
                                                let matchChassi = chassiLocal && chassiLocal !== 'SCHASSI' && dbChassi === chassiLocal;
                                                
                                                if (matchPlaca || matchChassi) {
                                                    jaExisteExato = true;
                                                    break;
                                                }
                                                
                                                let placaAusente = (!placaLocal || placaLocal === 'SPLACA') && (!dbPlaca || dbPlaca === 'SPLACA');
                                                let chassiAusente = (!chassiLocal || chassiLocal === 'SCHASSI') && (!dbChassi || dbChassi === 'SCHASSI');
                                                
                                                if (placaAusente && chassiAusente) {
                                                    let dbSpec = (dbItem.veiculo || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                                    if (specLocal && dbSpec && (dbSpec.includes(specLocal) || specLocal.includes(dbSpec))) {
                                                        jaExisteSimilar = true;
                                                    }
                                                }
                                            }
                                            
                                            if (jaExisteExato) {
                                                window.mostrarErro(`Atenção: Preenchimento automático bloqueado! Este veículo (Placa/Chassi) já foi salvo neste Boletim.`);
                                            } else {
                                                preencherComObjetoIAVeiculo(dados.veiculos[0]);
                                                if (jaExisteSimilar) {
                                                    window.mostrarAviso ? window.mostrarAviso(`⚠️ Atenção: Um veículo "similar" sem placa/chassi já consta neste BOE. Verifique se não é repetição.`) : window.mostrarErro(`⚠️ Atenção: Um veículo "similar" sem placa/chassi já consta neste BOE. Verifique se não é repetição.`);
                                                } else {
                                                    window.mostrarSucesso(`Extração concluída! 1 veículo detectado e preenchido no formulário.`);
                                                }
                                            }
                                        },
                                        error: function() {
                                            $('#modalImportarVeiculo').modal('hide');
                                            preencherComObjetoIAVeiculo(dados.veiculos[0]);
                                            window.mostrarSucesso(`Extração concluída! 1 veículo detectado.`);
                                        }
                                    });
                                } else {
                                    $('#modalImportarVeiculo').modal('hide');
                                    preencherComObjetoIAVeiculo(dados.veiculos[0]);
                                    window.mostrarSucesso(`Extração concluída! 1 veículo detectado e preenchido.`);
                                }
                            } else {
                                // MÚLTIPLOS VEÍCULOS - BATCH IMPORT MODAL
                                $('#modalImportarVeiculo').modal('hide');
                                
                                // Nova funcionalidade: Pesquisar se já há veículos com este BOE cadastrados
                                if (dados.boe) {
                                    $.ajax({
                                        url: rotasVeiculo.pesquisar,
                                        method: "GET",
                                        data: { filtro: "boe", termo: dados.boe },
                                        success: function(pesqResp) {
                                            let itensDB = (pesqResp.success && pesqResp.data) ? pesqResp.data : [];
                                            abrirModalRevisaoMultiplosVeiculos(dados.veiculos, itensDB);
                                        },
                                        error: function() {
                                            abrirModalRevisaoMultiplosVeiculos(dados.veiculos, []);
                                        }
                                    });
                                } else {
                                    abrirModalRevisaoMultiplosVeiculos(dados.veiculos, []);
                                }
                            }
                        } else {
                            window.mostrarSucesso('Extração concluída. NENHUM veículo foi detectado no documento.');
                            $('#modalImportarVeiculo').modal('hide');
                        }
                    } else {
                        window.mostrarErro(response.message || 'Erro na resposta do servidor.');
                    }
                },
                error: function (xhr) {
                    clearInterval(progressInterval);
                    let msg = 'Erro ao processar a extração com o sistema.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    window.mostrarErro(msg);
                },
                complete: function () {
                    setTimeout(() => {
                        $('#btnProcessarBoeVeiculo').prop('disabled', false).html('<i class="bi bi-cpu me-1"></i> Processar pelo Sistema');
                        $('#veiculoProgressWrapper').hide();
                        $('#veiculoProgressBar').css('width', '0%');
                        $('#veiculoProgressPercent').text('0%');
                    }, 1000);
                }
            });
        });

        // =========================================================================
        // NOVO: MODAL DE BATCH IMPORT (LOTES) 
        // Em vez de "salvar 1, clicar próximo", mostra todos de uma vez
        // =========================================================================
        function abrirModalRevisaoMultiplosVeiculos(veiculosList, itensDB = []) {
            // Limpa modais defasados
            $('#modalRevisaoMultiplosVeiculos').remove();
            
            let gridHtml = veiculosList.map((v, i) => {
                let descOriginal = (v.marca_modelo || 'Veículo Não Especificado') + (v.cor ? ' - COR: ' + v.cor.toUpperCase() : '');
                let desc = descOriginal;
                let proprietario = v.proprietario || 'N/A';
                let placa = v.placa || 'S/Placa';
                let chassi = v.chassi || '<span class="text-muted fst-italic">Não Extraído</span>';
                let status = v.status || 'APREENDIDO';
                
                // Precisamos escapar aspas pro onclick JS
                let jsonStr = JSON.stringify(v).replace(/"/g, '&quot;');
                
                let placaLimpa = placa.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                let chassiLimpo = chassi.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                let specLimpa = descOriginal.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                
                let isDuplicadoExato = false;
                let isDuplicadoSimilar = false;
                
                for (let dbItem of itensDB) {
                    let dbPlaca = dbItem.placa ? dbItem.placa.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                    let dbChassi = dbItem.chassi ? dbItem.chassi.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                    
                    let matchPlaca = placaLimpa && placaLimpa !== 'SPLACA' && dbPlaca === placaLimpa;
                    let matchChassi = chassiLimpo && chassiLimpo !== 'NOEXTRAIDO' && chassiLimpo !== 'SCHASSI' && dbChassi === chassiLimpo;
                    
                    if (matchPlaca || matchChassi) {
                        isDuplicadoExato = true;
                        break;
                    }
                    
                    let placaAusente = (!placaLimpa || placaLimpa === 'SPLACA') && (!dbPlaca || dbPlaca === 'SPLACA');
                    let chassiAusente = (!chassiLimpo || chassiLimpo === 'NOEXTRAIDO' || chassiLimpo === 'SCHASSI') && (!dbChassi || dbChassi === 'SCHASSI' || dbChassi === 'NOEXTRAIDO');
                    
                    if (placaAusente && chassiAusente) {
                        let dbSpec = (dbItem.veiculo || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                        if (specLimpa && dbSpec && (dbSpec.includes(specLimpa) || specLimpa.includes(dbSpec))) {
                            isDuplicadoSimilar = true;
                        }
                    }
                }
                
                let btnHtml = '';
                let rowClass = '';

                if (isDuplicadoExato) {
                    rowClass = 'table-secondary opacity-75';
                    btnHtml = `<span class="badge bg-secondary border border-secondary shadow-sm py-2 px-3 fw-bold w-100" title="Veículo já atrelado a este boletim no seu sistema."><i class="bi bi-shield-check me-1"></i> Já Existe (Omitido)</span>`;
                } else if (isDuplicadoSimilar) {
                    rowClass = 'table-warning';
                    btnHtml = `
                        <span class="d-block mb-1 text-danger fw-bold" style="font-size:0.75rem;"><i class="bi bi-exclamation-triangle-fill"></i> Similar no BD</span>
                        <button type="button" class="btn btn-sm btn-warning text-dark border-dark fw-bold shadow-sm w-100 btn-lote-salvar" onclick="window.salvarVeiculoEmLote(this, ${i}, '${jsonStr}')">
                            <i class="bi bi-cloud-upload me-1"></i> Importar Mesmo Assim
                        </button>
                        <span class="lote-status text-success fw-bold d-none fs-6"><i class="bi bi-check-circle-fill"></i> Salvo!</span>
                    `;
                } else {
                    btnHtml = `
                        <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm w-100 btn-lote-salvar" onclick="window.salvarVeiculoEmLote(this, ${i}, '${jsonStr}')">
                            <i class="bi bi-cloud-upload me-1"></i> Importar
                        </button>
                        <span class="lote-status text-success fw-bold d-none fs-6"><i class="bi bi-check-circle-fill"></i> Salvo!</span>
                    `;
                }
                
                return `
                    <tr id="linhaVtr_${i}" class="${rowClass}">
                        <td class="align-middle fw-bold text-center">${placa}</td>
                        <td class="align-middle text-wrap" style="max-width:250px;">${desc}</td>
                        <td class="align-middle"><code>${chassi}</code></td>
                        <td class="align-middle text-center"><span class="badge bg-warning text-dark">${status}</span></td>
                        <td class="align-middle">${proprietario}</td>
                        <td class="align-middle text-center" style="min-width: 170px;">
                            ${btnHtml}
                        </td>
                    </tr>
                `;
            }).join("");

            const modalHtml = `
                <div class="modal fade" id="modalRevisaoMultiplosVeiculos" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <!-- Trocado para modal-xl para acomodar novas colunas com folga -->
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                            <div class="modal-header bg-primary text-white border-0 py-3 d-flex justify-content-between align-items-center" style="cursor: move;" title="Clique e arraste para mover a janela">
                                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-car-front-fill me-2"></i> ${veiculosList.length} Veículos Encontrados no BOE</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 bg-light">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                    <div>O sistema de inspeção encontrou múltiplos veículos no boletim. Para evitar retrabalho de digitação manual, você pode **revisar** e pular de forma seletiva, salvar de uma só vez apertando o botão [Importar] em apenas cada linha que desejar incluir no registro da apreensão.</div>
                                </div>
                                <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th class="text-center">Placa</th>
                                                <th>Descrição</th>
                                                <th>Chassi</th>
                                                <th class="text-center">Status</th>
                                                <th>Envolvido/Proprietário</th>
                                                <th class="text-center">Confirmar Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${gridHtml}
                                        </tbody>
                                        <tfoot class="border-top-0">
                                            <tr><td colspan="6" class="text-center bg-light py-2"><small class="text-muted">Ações são enviadas para o banco de dados imedia e individualmente.</small></td></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-white justify-content-center">
                                <button type="button" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold" data-bs-dismiss="modal" onclick="limparFormularioVeiculo()">Finalizar e Fechar Tela de Lote</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            const modalEl = document.getElementById('modalRevisaoMultiplosVeiculos');
            const modal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false, focus: true });
            modal.show();

            // Ativar jQuery UI Draggable no modal dialog usando o cabeçalho como alça
            if (typeof $.ui !== 'undefined' && $.isFunction($.fn.draggable)) {
                try {
                    $(modalEl).find('.modal-dialog').draggable({
                        handle: ".modal-header",
                        cursor: "move"
                    });
                } catch(e) {
                    console.log("Draggable ignorado. JQueryUI não detectado corretamente no form.");
                }
            }
        }

        // Função Disparada por CADA Linha da Tabela do Novo Modal de Lotes
        window.salvarVeiculoEmLote = function(btnElement, indexLinha, objJsonStr) {
            const obj = JSON.parse(objJsonStr);
            console.log('📦 Solicitado salvar LOTE:', obj);

            // Resgata o BOE global
            const boeIA = obj.boe || (window.pendenteIA_Geral ? window.pendenteIA_Geral.boe : '');
            const dataIA = obj.data || (window.pendenteIA_Geral ? window.pendenteIA_Geral.data : '');
            const ipIA = obj.ip || (window.pendenteIA_Geral ? window.pendenteIA_Geral.ip : '');

            let dataFormatada = dataIA;
            if (dataFormatada && dataFormatada.includes(' ')) dataFormatada = dataFormatada.split(' ')[0];

            const formAutoData = new FormData();
            formAutoData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            // Usar data default se a IA errar ou se o Python não trouxe Data
            formAutoData.append('data', dataFormatada || $('#inputDataVeiculo').val() || ''); 
            formAutoData.append('ip', ipIA || '');
            formAutoData.append('boe', boeIA || '');
            formAutoData.append('pessoa', obj.proprietario || '');
            
            const specAtual = obj.marca_modelo || '';
            const descCor = obj.cor ? ' - COR: ' + obj.cor.toUpperCase() : '';
            formAutoData.append('veiculo', specAtual + descCor);
            
            formAutoData.append('placa', obj.placa || '');
            formAutoData.append('chassi', obj.chassi || '');
            formAutoData.append('sei', '');
            formAutoData.append('status', 'APREENDIDO');

            const $btn = $(btnElement);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Acessando BD...');

            $.ajax({
                url: rotasVeiculo.salvar,
                method: "POST",
                data: formAutoData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Linha Sucesso Visual
                        $btn.addClass('d-none');
                        $btn.siblings('.lote-status').removeClass('d-none');
                        $('#linhaVtr_' + indexLinha).addClass('table-success');
                        
                        setTimeout(function() {
                            if (boeIA) {
                                $('#filtroVeiculo').val('boe');
                                $('#termoPesquisaVeiculo').val(boeIA);
                                $('#btnPesquisarVeiculo').click();
                            }
                        }, 500);
                    } else {
                        window.mostrarErro(response.message || 'Falha ao salvar linha do Lote.');
                        $btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Falhou. Tentar Novamente');
                    }
                },
                error: function(xhr) {
                    let msg = 'Erro ao processar linha do Lote no Backend.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    // Custom fallback para regra única de data/validação
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    window.mostrarErro("LOTE FALHOU: " + msg);
                    $btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> Tentar Novamente');
                }
            });
        };

        // Mantém apenas a função antiga para preencher form simples (1 veículo)
        function preencherComObjetoIAVeiculo(obj) {
            console.log('📝 Preenchendo formulário de veículo ÚNICO:', obj);
            
            const boeIA = obj.boe || (window.pendenteIA_Geral ? window.pendenteIA_Geral.boe : '');
            const dataIA = obj.data || (window.pendenteIA_Geral ? window.pendenteIA_Geral.data : '');
            const ipIA = obj.ip || (window.pendenteIA_Geral ? window.pendenteIA_Geral.ip : '');

            if (boeIA) $('#inputBoeVeiculo').val(boeIA);
            
            if (dataIA) {
                let dataFormatada = dataIA;
                if (dataFormatada.includes(' ')) dataFormatada = dataFormatada.split(' ')[0];
                if (!dataFormatada.includes(':')) {
                    $('#inputDataVeiculo').val(dataFormatada);
                }
            }

            if (ipIA) $('#inputIpVeiculo').val(ipIA);

            if (obj.marca_modelo) $('#inputVeiculoVeiculo').val(obj.marca_modelo);
            if (obj.placa) $('#inputPlacaVeiculo').val(obj.placa);
            if (obj.chassi) $('#inputChassiVeiculo').val(obj.chassi);
            if (obj.cor) {
                const specAtual = $('#inputVeiculoVeiculo').val();
                if (!specAtual.includes(obj.cor.toUpperCase())) {
                    $('#inputVeiculoVeiculo').val(specAtual + ' - COR: ' + obj.cor.toUpperCase());
                }
            }
            if (obj.proprietario) $('#inputPessoaVeiculo').val(obj.proprietario);
            $('#inputSeiVeiculo').val('');            
            $('#inputStatusVeiculo').val('APREENDIDO');
            
            $('#inputVeiculoVeiculo, #inputPlacaVeiculo, #inputChassiVeiculo, #inputPessoaVeiculo').addClass('is-valid');
            setTimeout(() => {
                $('#inputVeiculoVeiculo, #inputPlacaVeiculo, #inputChassiVeiculo, #inputPessoaVeiculo').removeClass('is-valid');
            }, 3000);
        }

        console.log('✅ VeiculoApp inicializada com sucesso!');
    }

    // Inicializar o módulo
    inicializarVeiculo();
});
