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
                    mostrarModalSucesso(response.message);
                    limparFormularioVeiculo();

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function() {
                        // carregarUltimosVeiculos(); // Removido para não recarregar tabela automaticamente
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
                        // carregarUltimosVeiculos(); // Removido para não recarregar tabela automaticamente
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
                        // carregarUltimosVeiculos(); // Removido para não recarregar tabela automaticamente
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

        $('#formVeiculo')[0].reset();
        $('#veiculo_id').val('');
        $('#btnSalvarVeiculo').removeData('registro');
        $('#btnSalvarVeiculo').prop('disabled', false);
        $('#btnEditarVeiculo').prop('disabled', true).removeClass('btn-secondary').addClass('btn-warning').removeAttr('title');
        $('#btnExcluirVeiculo').prop('disabled', true).removeClass('btn-secondary').addClass('btn-danger').removeAttr('title');

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

        // ✅ NOVO: Checar se já existem dados globais ou no localStorage
        const boeVeiculoAtual = $('#inputBoeVeiculo').val();
        if (window.pendentesIA_Veiculos && window.pendentesIA_Veiculos.length > 0) {
            console.log('🤖 Módulo Veículo detectou dados globais pendentes:', window.pendentesIA_Veiculos);
            veiculosPendentesIA = window.pendentesIA_Veiculos;
            indiceIAPendenteVeiculo = 0;
            preencherComObjetoIAVeiculo(veiculosPendentesIA[0]);
            mostrarAvisoIAVeiculo();
        } else if (boeVeiculoAtual) {
            // Tenta recuperar do localStorage pelo BOE
            const dadosSalvosV = localStorage.getItem('pendentesSistema_Veiculos_' + boeVeiculoAtual);
            if (dadosSalvosV) {
                const listaV = JSON.parse(dadosSalvosV);
                if (listaV.length > 0) {
                    console.log('🤖 Módulo Veículo recuperou dados do localStorage para o BOE:', boeVeiculoAtual);
                    veiculosPendentesIA = listaV;
                    const dadosGeraisV = localStorage.getItem('pendentesSistema_Geral_' + boeVeiculoAtual);
                    if (dadosGeraisV) window.pendenteIA_Geral = JSON.parse(dadosGeraisV);
                    indiceIAPendenteVeiculo = 0;
                    preencherComObjetoIAVeiculo(veiculosPendentesIA[0]);
                    mostrarAvisoIAVeiculo();
                }
            }
        }

        function preencherComObjetoIAVeiculo(obj) {
            console.log('📝 Preenchendo formulário de veículo com dados da IA:', obj);
            
            // Tenta pegar o BOE e Data do contexto geral se não estiver no objeto
            const boeIA = obj.boe || (window.pendenteIA_Geral ? window.pendenteIA_Geral.boe : '');
            const dataIA = obj.data || (window.pendenteIA_Geral ? window.pendenteIA_Geral.data : '');
            const ipIA = obj.ip || (window.pendenteIA_Geral ? window.pendenteIA_Geral.ip : '');

            // Não resetar tudo, apenas o específico
            if (boeIA) $('#inputBoeVeiculo').val(boeIA);
            if (dataIA) $('#inputDataVeiculo').val(dataIA);
            if (ipIA) $('#inputIpVeiculo').val(ipIA);

            // Preenche campos específicos da IA
            if (obj.marca_modelo) $('#inputVeiculoVeiculo').val(obj.marca_modelo);
            if (obj.placa) $('#inputPlacaVeiculo').val(obj.placa);
            if (obj.chassi) $('#inputChassiVeiculo').val(obj.chassi);
            if (obj.cor) {
                const specAtual = $('#inputVeiculoVeiculo').val();
                if (!specAtual.includes(obj.cor)) {
                    $('#inputVeiculoVeiculo').val(specAtual + ' - COR: ' + obj.cor.toUpperCase());
                }
            }
            if (obj.proprietario) $('#inputPessoaVeiculo').val(obj.proprietario);
            // O campo SEI deve ficar em branco conforme pedido do usuário
            $('#inputSeiVeiculo').val('');            
            // Define status padrão
            $('#inputStatusVeiculo').val('APREENDIDO');
            
            // Piscar os campos preenchidos
            $('#inputVeiculoVeiculo, #inputPlacaVeiculo, #inputChassiVeiculo, #inputPessoaVeiculo').addClass('is-valid');
            setTimeout(() => {
                $('#inputVeiculoVeiculo, #inputPlacaVeiculo, #inputChassiVeiculo, #inputPessoaVeiculo').removeClass('is-valid');
            }, 3000);
        }

        function mostrarAvisoIAVeiculo() {
            if ($('#avisoIA_Veiculo').length === 0) {
                const html = `
                    <div id="avisoIA_Veiculo" class="alert alert-info d-flex justify-content-between align-items-center mb-3 shadow-sm" style="border-left: 5px solid #0dcaf0;">
                        <div>
                            <i class="bi bi-robot me-2"></i>
                            <span id="textoAvisoIA_Veiculo">O sistema encontrou ${veiculosPendentesIA.length} veículos. Exibindo ${indiceIAPendenteVeiculo + 1} de ${veiculosPendentesIA.length}.</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnAnteriorIA_Veiculo" disabled><i class="bi bi-chevron-left"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnProximoIA_Veiculo"><i class="bi bi-chevron-right"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger me-2" id="btnDispensarIA_Veiculo" title="Remover estas sugestões para este BOE"><i class="bi bi-trash"></i> Dispensar</button>
                            <button type="button" class="btn btn-close" onclick="$('#avisoIA_Veiculo').fadeOut()"></button>
                        </div>
                    </div>
                `;
                $('#formVeiculo').prepend(html);
                
                $('#btnProximoIA_Veiculo').click(function() {
                    if (indiceIAPendenteVeiculo < veiculosPendentesIA.length - 1) {
                        indiceIAPendenteVeiculo++;
                        preencherComObjetoIAVeiculo(veiculosPendentesIA[indiceIAPendenteVeiculo]);
                        atualizarControlesIAVeiculo();
                    }
                });
                
                $('#btnAnteriorIA_Veiculo').click(function() {
                    if (indiceIAPendenteVeiculo > 0) {
                        indiceIAPendenteVeiculo--;
                        preencherComObjetoIAVeiculo(veiculosPendentesIA[indiceIAPendenteVeiculo]);
                        atualizarControlesIAVeiculo();
                    }
                });

                $('#btnDispensarIA_Veiculo').click(function() {
                    const boe = $('#inputBoeVeiculo').val();
                    if (boe && confirm('Deseja remover permanentemente as sugestões extras deste BOE?')) {
                        localStorage.removeItem('pendentesSistema_Veiculos_' + boe);
                        $('#avisoIA_Veiculo').fadeOut();
                        veiculosPendentesIA = [];
                        console.log('🤖 Sugestões de veículo dispensadas para o BOE:', boe);
                    }
                });
            } else {
                $('#avisoIA_Veiculo').show();
                indiceIAPendenteVeiculo = 0;
                atualizarControlesIAVeiculo();
            }
        }

        function atualizarControlesIAVeiculo() {
            $('#textoAvisoIA_Veiculo').html(`O sistema encontrou ${veiculosPendentesIA.length} veículos. Exibindo <b>${indiceIAPendenteVeiculo + 1} de ${veiculosPendentesIA.length}</b>.`);
            $('#btnAnteriorIA_Veiculo').prop('disabled', indiceIAPendenteVeiculo === 0);
            $('#btnProximoIA_Veiculo').prop('disabled', indiceIAPendenteVeiculo === veiculosPendentesIA.length - 1);
        }

        // ✅ NOVO: Reabrir assistente ao digitar/mudar o BOE
        $('#inputBoeVeiculo').on('blur change', function() {
            const boe = $(this).val();
            if (boe) {
                const dadosSalvosV = localStorage.getItem('pendentesSistema_Veiculos_' + boe);
                if (dadosSalvosV) {
                    const listaV = JSON.parse(dadosSalvosV);
                    if (listaV.length > 0) {
                        console.log('🤖 Módulo Veículo reabrindo assistente para o BOE:', boe);
                        veiculosPendentesIA = listaV;
                        const dadosGeraisV = localStorage.getItem('pendentesSistema_Geral_' + boe);
                        if (dadosGeraisV) window.pendenteIA_Geral = JSON.parse(dadosGeraisV);
                        indiceIAPendenteVeiculo = 0;
                        preencherComObjetoIAVeiculo(veiculosPendentesIA[0]);
                        if ($('#avisoIA_Veiculo').length > 0) {
                            $('#avisoIA_Veiculo').fadeIn();
                            atualizarControlesIAVeiculo();
                        } else {
                            mostrarAvisoIAVeiculo();
                        }
                    }
                }
            }
        });

        console.log('✅ VeiculoApp inicializada com sucesso!');
        console.log('✅ Sistema de preenchimento automático ativado!');
    }

    // Inicializar o módulo
    inicializarVeiculo();
});
