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
    // Instância global para evitar conflitos de reinjeção
    window.graficoCelularInstance = window.graficoCelularInstance || null;
    let todosResultadosPesquisa = []; // Para paginação
    let paginaAtualCelular = 1;
    const itensPorPaginaCelular = 10;

    // === VARIÁVEIS DE IA ===
    let celularesPendentesIA = [];
    let indiceIAPendente = 0;


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
            // Se não há termo, mostra estado vazio e esconde tabela
            $('#conteinerTabelaPesquisaCelular').hide();
            $('#emptyStatePesquisaCelular').show();
            $('#botoesExportarPesquisaCelular').hide();
            return;
        }

        // Prepara visual para exibir resultados
        $('#emptyStatePesquisaCelular').hide();
        $('#conteinerTabelaPesquisaCelular').show();

        // Mostrar loading
        const $tbody = $('#tabelaResultadosCelular tbody');
        $tbody.html('<tr><td colspan="7" class="text-center">Pesquisando...</td></tr>');

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
                    $tbody.html('<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="7" class="text-center">Erro ao pesquisar</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        todosResultadosPesquisa = dados;
        paginaAtualCelular = 1;
        renderizarTabelaPaginadaCelular();

        // Mostrar botões de exportação se houver dados
        if (dados.length > 0) {
            $('#botoesExportarPesquisaCelular').css('display', 'flex');
        } else {
            $('#botoesExportarPesquisaCelular').css('display', 'none');
        }
    }

    function renderizarTabelaPaginadaCelular() {
        const $tbody = $('#tabelaResultadosCelular tbody');
        $tbody.empty();
        const dados = todosResultadosPesquisa;
        const total = dados.length;
        const totalPaginas = Math.ceil(total / itensPorPaginaCelular);
        const inicio = (paginaAtualCelular - 1) * itensPorPaginaCelular;
        const fim = Math.min(inicio + itensPorPaginaCelular, total);
        const paginaDados = dados.slice(inicio, fim);

        if (paginaDados.length === 0) {
            $tbody.html('<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>');
            $('#infoPaginacaoCelular').css('display', 'none');
            return;
        }

        paginaDados.forEach(function (item) {
            const dataFormatada = formatarDataParaInput(item.data) || '-';
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
                    <td class="align-middle" title="${item.processo || '-'}">${item.processo || '-'}</td>
                    <td class="align-middle text-truncate" title="${displayBoe}">${displayBoe}</td>
                    <td class="align-middle text-truncate" title="${item.imei1 || '-'}">${item.imei1 || '-'}</td>
                    <td class="align-middle">${statusBadge}</td>
                    <td class="align-middle text-truncate" title="${item.responsavel || '-'}"><span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>${responsavelNome}</span></td>
                    <td class="align-middle text-center">
                        <button type="button" class="btn btn-sm btn-primary btn-selecionar-celular shadow-sm" data-id="${item.id}" title="Selecionar registro" style="border-radius: 6px; padding: 4px 10px; transition: all 0.2s ease;">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });

        // Paginação
        $('#textoTotalCelular').text(`Exibindo ${inicio + 1}–${fim} de ${total} registros`);
        const $pag = $('#paginacaoCelular').empty();
        if (totalPaginas > 1) {
            for (let p = 1; p <= totalPaginas; p++) {
                const $btn = $(`<button class="btn btn-sm ${p === paginaAtualCelular ? 'btn-primary' : 'btn-outline-secondary'}">${p}</button>`);
                $btn.on('click', function () { paginaAtualCelular = p; renderizarTabelaPaginadaCelular(); });
                $pag.append($btn);
            }
        }
        $('#infoPaginacaoCelular').css('display', 'flex');
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
        // Preencher formulário e armazenar os dados no data do botão salvar
        $('#celular_id').val(dados.id);
        $('#btnSalvarCelular').data('registro', dados);
        
        $('#inputDataCelular').val(formatarDataParaInput(dados.data));
        // Dispara evento para o Flatpickr atualizar visualmente se formatarDataParaInput funcionar
        if(document.querySelector('#inputDataCelular')._flatpickr) {
            document.querySelector('#inputDataCelular')._flatpickr.setDate(formatarDataParaInput(dados.data));
        }

        $('#inputIpCelular').val(dados.ip || '');
        $('#inputBoeCelular').val(dados.boe || '');
        $('#inputPessoaCelular').val(dados.pessoa || '');
        $('#inputTelefoneCelular').val(dados.telefone || '');
        $('#inputImei1Celular').val(dados.imei1 || '');
        $('#inputImei2Celular').val(dados.imei2 || '');
        $('#inputProcessoCelular').val(dados.processo || '');
        $('#inputStatusCelular').val(dados.status || '');

        // === CONTROLE DE ACESSO (PERMISSÕES) ===
        const isOwner = (dados.user_id == window.currentUserId);
        const isAdmin = window.isAdminUser;

        if (isOwner || isAdmin) {
            // Pode editar/excluir
            $('#btnEditarCelular').prop('disabled', false).removeClass('btn-secondary').addClass('btn-warning');
            $('#btnExcluirCelular').prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
            $('#btnSalvarCelular').prop('disabled', true); // Se selecionou, habilita Editar, não Salvar (novo)
        } else {
            // Apenas visualização
            $('#btnEditarCelular').prop('disabled', true).removeClass('btn-warning').addClass('btn-secondary').attr('title', 'Você não tem permissão para editar este registro');
            $('#btnExcluirCelular').prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary').attr('title', 'Você não tem permissão para excluir este registro');
            // $('#btnSalvarCelular').prop('disabled', true); // Mantem desabilitado
        }

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
                        // carregarUltimosCelulares(); // Removido para não recarregar tabela automaticamente
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
                        // carregarUltimosCelulares(); // Removido para não recarregar tabela automaticamente
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
                        // carregarUltimosCelulares(); // Removido para não recarregar tabela automaticamente
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

        $('#formCelular')[0].reset();
        $('#celular_id').val('');
        $('#btnSalvarCelular').removeData('registro');
        $('#btnSalvarCelular').prop('disabled', false);
        $('#btnEditarCelular').prop('disabled', true).removeClass('btn-secondary').addClass('btn-warning').removeAttr('title');
        $('#btnExcluirCelular').prop('disabled', true).removeClass('btn-secondary').addClass('btn-danger').removeAttr('title');

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

        // ── Gráfico de rosca ──
        const ctxCelular = document.getElementById('graficoCelular');
        if (ctxCelular) {
            console.log('📊 Inicializando gráfico de celulares...');
            
            // Destruir instância anterior se existir (usando a global)
            if (window.graficoCelularInstance) {
                window.graficoCelularInstance.destroy();
                window.graficoCelularInstance = null;
            }

            if (typeof Chart !== 'undefined') {
                try {
                    window.graficoCelularInstance = new Chart(ctxCelular, {
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
                    console.log('✅ Gráfico de celulares renderizado com sucesso');
                } catch (err) {
                    console.error('❌ Erro ao criar gráfico de celulares:', err);
                }
            } else {
                console.error('❌ Biblioteca Chart.js não encontrada!');
                $(ctxCelular).parent().html('<div class="text-danger small">Erro: Chart.js não carregado</div>');
            }
        }

        // ── Resumo narrativo ──
        if (contadorTotal > 0) {
            const texto = `No período: ${contadorApreendido} apreendido(s), ${contadorDevolvido} devolvido(s), ${contadorAnalise} em perícia, ${contadorArquivado} arquivado(s). Total: ${contadorTotal} celular(es).`;
            $('#textoResumoNarrativoCelular').text(texto);
            $('#resumoNarrativoCelular').show();
        } else {
            $('#resumoNarrativoCelular').hide();
        }

        // Preencher tabela de controle
        const $corpoTabela = $('#corpoTabelaControleCelular').empty();

        // Adicionar linhas para cada status que tenha celulares
        const statusOrdem = ['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ARQUIVADO', 'OUTROS'];

        statusOrdem.forEach(function (status) {
            const celularesStatus = celularesPorStatus[status] || [];
            if (celularesStatus.length > 0) {
                const contador = celularesStatus.length;
                const $linha = $(`
                    <tr>
                        <td><strong>${status}</strong></td>
                        <td class="text-center"><span class="badge bg-${getStatusColor(status)}">${contador}</span></td>
                        <td>Celulares com status ${status.toLowerCase()}</td>
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

        // Se não houver celulares
        if ($corpoTabela.children().length === 0) {
            $corpoTabela.append('<tr><td colspan="4" class="text-center text-muted py-3">Nenhum celular encontrado no período</td></tr>');
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
        const celularesDoStatus = status ? celulares.filter(celular => {
            const s = celular.status || 'OUTROS';
            if (status === 'OUTROS') {
                return !['APREENDIDO', 'DEVOLVIDO', 'EM PERÍCIA', 'ANALISE', 'ARQUIVADO'].includes(s.toUpperCase());
            }
            return s.toUpperCase() === status.toUpperCase();
        }) : celulares;

        // Preencher modal
        const tituloStatus = status ? status : 'TODOS';
        $('#modalDetalhesStatusCelularTitulo').text(`Celulares — Status: ${tituloStatus} (${celularesDoStatus.length})`);
        const $conteudo = $('#conteudoDetalhesStatusCelular').empty();

        if (celularesDoStatus.length === 0) {
            $conteudo.append('<p class="text-center text-muted">Nenhum celular encontrado para este status.</p>');
        } else {
            const tabela = `
            <div class="table-responsive">
                <table id="tabelaDetalhesModalCelular" class="table table-striped table-hover table-sm" style="font-size: 0.85rem; white-space: nowrap; width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Data</th>
                            <th>BOE</th>
                            <th>Processo</th>
                            <th>Pessoa</th>
                            <th>Especificação</th>
                            <th>IMEI 1</th>
                            <th>IMEI 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${celularesDoStatus.map((c, i) => {
                            const nomesBadges = (c.pessoa || '-').split(' - ').map(nome => 
                                `<span class="badge bg-light border text-dark fw-normal me-1 mb-1" style="font-size: 0.8rem; padding: 4px 8px;">${nome.trim()}</span>`
                            ).join('');
                            return `
                            <tr>
                                <td>${i+1}</td>
                                <td>${formatarDataParaInput(c.data) || '-'}</td>
                                <td><strong>${c.boe || '-'}</strong></td>
                                <td>${c.processo || '-'}</td>
                                <td style="white-space: normal; min-width: 150px;">${nomesBadges}</td>
                                <td style="white-space: normal; min-width: 200px;">${c.telefone || '-'}</td>
                                <td><code>${c.imei1 || '-'}</code></td>
                                <td><code>${c.imei2 || '-'}</code></td>
                            </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>`;
            $conteudo.html(tabela);

            var el = document.getElementById('modalDetalhesStatusCelular');
            var m = el ? bootstrap.Modal.getOrCreateInstance(el) : null;

            try {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tabelaDetalhesModalCelular')) {
                    $('#tabelaDetalhesModalCelular').DataTable().destroy();
                }
                if ($.fn.DataTable) {
                    $('#tabelaDetalhesModalCelular').DataTable({
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

    // Botão filtrar — agora aceita filtro de status também
    $('#btnFiltrarCelulares').click(function () {
        const dataInicio = $('#filtroDataInicioCelular').val();
        const dataFim = $('#filtroDataFimCelular').val();
        const status = $('#filtroStatusCelular').val();
        carregarControleCelulares(dataInicio, dataFim, status);
    });

    // === BOTÕES DE EXPORTAÇÃO — CONTROLE ===
    $('#btnExportarExcelCelular').click(function () {
        const dataInicio = $('#filtroDataInicioCelular').val();
        const dataFim = $('#filtroDataFimCelular').val();
        const status = $('#filtroStatusCelular').val();
        let url = rotasCelular.exportarExcel;
        const params = new URLSearchParams();
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);
        if (status) params.append('status', status);
        if ([...params].length) url += '?' + params.toString();
        window.location.href = url;
    });

    $('#btnExportarPdfCelular').click(function () {
        const dataInicio = $('#filtroDataInicioCelular').val();
        const dataFim = $('#filtroDataFimCelular').val();
        const status = $('#filtroStatusCelular').val();
        let url = rotasCelular.exportarPdf;
        const params = new URLSearchParams();
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);
        if (status) params.append('status', status);
        if ([...params].length) url += '?' + params.toString();
        window.location.href = url;
    });

    // === BOTÕES DE EXPORTAÇÃO — PESQUISA ===
    $('#btnExportarExcelPesquisaCelular, #btnExportarPdfPesquisaCelular').click(function () {
        const isPdf = $(this).attr('id').includes('Pdf');
        const url = isPdf ? rotasCelular.exportarPdf : rotasCelular.exportarExcel;
        window.location.href = url; // exporta tudo (sem filtro de data específico da pesquisa)
    });

    // Ver detalhes de um status e Clique no Card de Resumo
    $(document).on('click', '.btn-ver-detalhes-status, .card-status-clicavel', function () {
        const status = $(this).data('status');
        mostrarDetalhesStatus(status === 'TODOS' ? '' : status);
    });

    // === FUNÇÃO carregarControleCelulares atualizada para suportar filtro de status ===
    function carregarControleCelularesComStatus(dataInicio, dataFim, status) {
        const $corpoTabela = $('#corpoTabelaControleCelular').html('<tr><td colspan="4" class="text-center">Carregando...</td></tr>');
        const dados = {};
        if (dataInicio) dados.data_inicio = dataInicio;
        if (dataFim) dados.data_fim = dataFim;
        $.ajax({
            url: rotasCelular.controleStatus,
            method: 'GET',
            data: dados,
            success: function (response) {
                if (response.success && response.data) {
                    let filtrados = response.data;
                    if (status) filtrados = filtrados.filter(c => (c.status || 'OUTROS') === status);
                    processarDadosControle(filtrados);
                } else {
                    $corpoTabela.html('<tr><td colspan="4" class="text-center">Nenhum celular encontrado</td></tr>');
                }
            },
            error: function (xhr) {
                $corpoTabela.html('<tr><td colspan="4" class="text-center">Erro ao carregar</td></tr>');
            }
        });
    }
    // Redefine carregarControleCelulares para aceitar status
    carregarControleCelulares = carregarControleCelularesComStatus;

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

        // Deixar filtros de data vazios inicialmente
        $('#filtroDataInicioCelular').val('');
        $('#filtroDataFimCelular').val('');

        // Aplicar máscara no campo Processo/SEI
        if ($.fn.mask) {
            $('#inputProcessoCelular').mask('0000000000.000000/0000-00');
        }

        // ✅ CARREGAR ÚLTIMOS CELULARES AUTOMATICAMENTE AO INICIAR
        // carregarUltimosCelulares(); // Removido para otimizar carregamento inicial

        // Configurar comunicação entre abas
        configurarComunicacaoEntreAbas();

        // ✅ NOVO: Ouvir celulares extraídos pela IA
        $(document).on('celularesExtraidosIA', function (event, lista) {
            console.log('🤖 Módulo Celular recebeu objetos da IA:', lista);
            celularesPendentesIA = lista;
            indiceIAPendente = 0;
            if (celularesPendentesIA.length > 0) {
                preencherComObjetoIA(celularesPendentesIA[0]);
                mostrarAvisoIA();
            }
        });

        // ✅ NOVO: Checar se já existem dados globais ou no localStorage
        const boeAtual = $('#inputBoeCelular').val();
        if (window.pendentesIA_Celulares && window.pendentesIA_Celulares.length > 0) {
            console.log('🤖 Módulo Celular detectou dados globais pendentes:', window.pendentesIA_Celulares);
            celularesPendentesIA = window.pendentesIA_Celulares;
            indiceIAPendente = 0;
            preencherComObjetoIA(celularesPendentesIA[0]);
            mostrarAvisoIA();
        } else if (boeAtual) {
            // Tenta recuperar do localStorage pelo BOE
            const dadosSalvos = localStorage.getItem('pendentesSistema_Celulares_' + boeAtual);
            if (dadosSalvos) {
                const lista = JSON.parse(dadosSalvos);
                if (lista.length > 0) {
                    console.log('🤖 Módulo Celular recuperou dados do localStorage para o BOE:', boeAtual);
                    celularesPendentesIA = lista;
                    const dadosGerais = localStorage.getItem('pendentesSistema_Geral_' + boeAtual);
                    if (dadosGerais) window.pendenteIA_Geral = JSON.parse(dadosGerais);
                    indiceIAPendente = 0;
                    preencherComObjetoIA(celularesPendentesIA[0]);
                    mostrarAvisoIA();
                }
            }
        }

        function preencherComObjetoIA(obj) {
            console.log('📝 Preenchendo formulário com dados da IA:', obj);

            // Tenta pegar o BOE e Data do contexto geral se não estiver no objeto
            const boeIA = obj.boe || (window.pendenteIA_Geral ? window.pendenteIA_Geral.boe : '');
            const dataIA = obj.data || (window.pendenteIA_Geral ? window.pendenteIA_Geral.data : '');
            const ipIA = obj.ip || (window.pendenteIA_Geral ? window.pendenteIA_Geral.ip : '');

            // Não resetar tudo, apenas o específico
            if (boeIA) $('#inputBoeCelular').val(boeIA);
            if (dataIA) $('#inputDataCelular').val(dataIA);
            if (ipIA) $('#inputIpCelular').val(ipIA);

            if (obj.marca_modelo) $('#inputTelefoneCelular').val(obj.marca_modelo);
            if (obj.imei1) $('#inputImei1Celular').val(obj.imei1);
            if (obj.imei2) $('#inputImei2Celular').val(obj.imei2);
            if (obj.cor) {
                // Se o campo for um select com cores, tenta selecionar
                $('#inputCorCelular').val(obj.cor.toUpperCase());
                // Se for input texto normal
                if ($('#inputCorCelular').val() === "") {
                    // Caso precise criar a opção ou se for texto
                }
            }
            if (obj.proprietario) $('#inputPessoaCelular').val(obj.proprietario);
            // O campo Processo/SEI deve ficar em branco conforme pedido do usuário
            $('#inputProcessoCelular').val('');
            // if (obj.observacao) $('#inputObservacaoCelular').val(obj.observacao);

            $('#inputStatusCelular').val('APREENDIDO');
            $('#inputTelefoneCelular, #inputImei1Celular, #inputImei2Celular, #inputPessoaCelular').addClass('border-success shadow-sm');
            setTimeout(() => {
                $('#inputTelefoneCelular, #inputImei1Celular, #inputImei2Celular, #inputPessoaCelular').removeClass('border-success shadow-sm');
            }, 3000);
        }

        function mostrarAvisoIA() {
            // Cria um badge flutuante ou aviso no topo do formulário
            if ($('#avisoIA_Celular').length === 0) {
                const html = `
                    <div id="avisoIA_Celular" class="alert alert-info d-flex justify-content-between align-items-center mb-3 shadow-sm" style="border-left: 5px solid #0dcaf0;">
                        <div>
                            <i class="bi bi-robot me-2"></i>
                            <span id="textoAvisoIA_Celular">O sistema encontrou ${celularesPendentesIA.length} aparelhos. Exibindo ${indiceIAPendente + 1} de ${celularesPendentesIA.length}.</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnAnteriorIA_Celular" disabled><i class="bi bi-chevron-left"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnProximoIA_Celular"><i class="bi bi-chevron-right"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger me-2" id="btnDispensarIA_Celular" title="Remover estas sugestões para este BOE"><i class="bi bi-trash"></i> Dispensar</button>
                            <button type="button" class="btn btn-close" onclick="$('#avisoIA_Celular').fadeOut()"></button>
                        </div>
                    </div>
                `;
                $('#formCelular').prepend(html);
                
                $('#btnProximoIA_Celular').click(function() {
                    if (indiceIAPendente < celularesPendentesIA.length - 1) {
                        indiceIAPendente++;
                        preencherComObjetoIA(celularesPendentesIA[indiceIAPendente]);
                        atualizarControlesIA();
                    }
                });
                
                $('#btnAnteriorIA_Celular').click(function() {
                    if (indiceIAPendente > 0) {
                        indiceIAPendente--;
                        preencherComObjetoIA(celularesPendentesIA[indiceIAPendente]);
                        atualizarControlesIA();
                    }
                });

                $('#btnDispensarIA_Celular').click(function() {
                    const boe = $('#inputBoeCelular').val();
                    if (boe && confirm('Deseja remover permanentemente as sugestões extras deste BOE?')) {
                        localStorage.removeItem('pendentesSistema_Celulares_' + boe);
                        $('#avisoIA_Celular').fadeOut();
                        celularesPendentesIA = [];
                        console.log('🤖 Sugestões dispensadas para o BOE:', boe);
                    }
                });
            } else {
                $('#avisoIA_Celular').show();
                indiceIAPendente = 0;
                atualizarControlesIA();
            }
        }

        function atualizarControlesIA() {
            $('#textoAvisoIA_Celular').html(`O sistema encontrou ${celularesPendentesIA.length} aparelhos. Exibindo <b>${indiceIAPendente + 1} de ${celularesPendentesIA.length}</b>.`);
            $('#btnAnteriorIA_Celular').prop('disabled', indiceIAPendente === 0);
            $('#btnProximoIA_Celular').prop('disabled', indiceIAPendente === celularesPendentesIA.length - 1);
        }

        // ✅ NOVO: Reabrir assistente ao digitar/mudar o BOE
        $('#inputBoeCelular').on('blur change', function() {
            const boe = $(this).val();
            if (boe) {
                const dadosSalvosC = localStorage.getItem('pendentesSistema_Celulares_' + boe);
                if (dadosSalvosC) {
                    const listaC = JSON.parse(dadosSalvosC);
                    if (listaC.length > 0) {
                        console.log('🤖 Módulo Celular reabrindo assistente para o BOE:', boe);
                        celularesPendentesIA = listaC;
                        const dadosGeraisC = localStorage.getItem('pendentesSistema_Geral_' + boe);
                        if (dadosGeraisC) window.pendenteIA_Geral = JSON.parse(dadosGeraisC);
                        indiceIAPendente = 0;
                        preencherComObjetoIA(celularesPendentesIA[0]);
                        if ($('#avisoIA_Celular').length > 0) {
                            $('#avisoIA_Celular').fadeIn();
                            atualizarControlesIA();
                        } else {
                            mostrarAvisoIA();
                        }
                    }
                }
            }
        });

        console.log('✅ CelularApp inicializada com sucesso!');
        console.log('✅ Sistema de preenchimento automático ativado!');
    }

    // Inicializar quando documento estiver pronto
    inicializarCelular();
});
