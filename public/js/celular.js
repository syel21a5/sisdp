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
    // === VERIFICA ROTA SEI ===
    var rotasCelular = window.rotasCelular || {};
    $('#btnAbrirSei').on('click', function () {
        var sei = ($('#inputProcessoCelular').val() || '').trim();
        var baseUrl = (typeof rotasCelular !== 'undefined' && rotasCelular.seiVerificar) ? rotasCelular.seiVerificar : '/sei/verificar';
        var url = baseUrl;
        url += (url.indexOf('?') === -1 ? '?' : '&') + 'tipo=celular';
        if (sei) {
            url += '&sei=' + encodeURIComponent(sei);
        }
        window.open(url, '_blank');
    });


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

    function mostrarModalSucesso(mensagem) {
        window.mostrarSucesso(mensagem);
    }

    function mostrarModalErro(mensagem) {
        window.mostrarErro(mensagem);
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
                    // ✅ NOVO: Remover o item salvo da lista de pendentes da IA
                    if (celularesPendentesIA.length > 0) {
                        const boe = $('#inputBoeCelular').val();
                        if (boe) {
                            const boeKey = boe.replace(/[^a-zA-Z0-9]/g, '');
                            celularesPendentesIA.splice(indiceIAPendente, 1);
                            
                            if (celularesPendentesIA.length > 0) {
                                localStorage.setItem('pendentes_celulares_' + boeKey, JSON.stringify(celularesPendentesIA));
                                if (indiceIAPendente >= celularesPendentesIA.length) indiceIAPendente = celularesPendentesIA.length - 1;
                                preencherComObjetoIA(celularesPendentesIA[indiceIAPendente]);
                                atualizarControlesIA();
                            } else {
                                localStorage.removeItem('pendentes_celulares_' + boeKey);
                                $('#avisoIA_Celular').fadeOut();
                            }
                            if (typeof window.atualizarBadgesPendentes === 'function') window.atualizarBadgesPendentes();
                        }
                    }
                    let boeLocal = $('#inputBoeCelular').val();
                    limparFormularioCelular();

                    // ✅ GARANTIR QUE A LISTA É ATUALIZADA APÓS SALVAR
                    setTimeout(function () {
                        if (boeLocal) {
                            $('#filtroCelular').val('boe');
                            $('#termoPesquisaCelular').val(boeLocal);
                            $('#btnPesquisarCelular').click();
                        }
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
                    let boeLocal = $('#inputBoeCelular').val();
                    limparFormularioCelular();

                    // ✅ ATUALIZAR LISTA APÓS EDITAR
                    setTimeout(function () {
                        if (boeLocal) {
                            $('#filtroCelular').val('boe');
                            $('#termoPesquisaCelular').val(boeLocal);
                            $('#btnPesquisarCelular').click();
                        }
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
                    let boeLocal = $('#inputBoeCelular').val();
                    limparFormularioCelular();

                    // ✅ ATUALIZAR LISTA APÓS EXCLUIR
                    setTimeout(function () {
                        if (boeLocal) {
                            $('#filtroCelular').val('boe');
                            $('#termoPesquisaCelular').val(boeLocal);
                        }
                        $('#btnPesquisarCelular').click();
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

        // ✅ REINICIAR ESTADO DO MODAL DE IMPORTAÇÃO (caso o usuário tenha cancelado ou extraído)
        $('#btnProcessarBoeCelular').prop('disabled', false).html('<i class="bi bi-cpu me-1"></i> Processar pelo Sistema');
        $('#celularProgressWrapper').hide();
        $('#celularProgressBar').css('width', '0%');
        $('#celularProgressPercent').text('0%');

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

        // Inicializar Flatpickr no campo data e definir data atual via API do flatpickr
        var localeCfg = (window.flatpickr && flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default';
        var fpCelular = flatpickr("#inputDataCelular", {
            dateFormat: "d/m/Y",
            allowInput: true,
            locale: localeCfg,
            onReady: function(selectedDates, dateStr, instance) {
                if (!instance.selectedDates.length) {
                    instance.setDate(new Date(), true);
                }
            }
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

        // ✅ NOVO: Lógica de Importação Híbrida de Celulares
        $('#btnProcessarBoeCelular').click(function () {
            const formData = new FormData();
            
            // Verificar aba ativa (Texto ou PDF)
            const isTextoAtivo = $('#tab-texto-celular').hasClass('active');
            
            if (isTextoAtivo) {
                const texto = $('#textoBoeCelular').val();
                if (!texto.trim()) {
                    window.mostrarErro('Cole o texto do BOE antes de processar.');
                    return;
                }
                formData.append('textoBOE', texto);
            } else {
                const inputPdf = document.getElementById('pdfBoeCelular');
                if (inputPdf.files.length === 0) {
                    window.mostrarErro('Selecione um arquivo PDF antes de processar.');
                    return;
                }
                formData.append('pdfBOE', inputPdf.files[0]);
            }

            // Iniciar UI de carregamento
            $('#btnProcessarBoeCelular').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mt-1 mb-1 me-2" role="status" aria-hidden="true"></span> Extraindo...');
            $('#celularProgressWrapper').show();
            let percent = 0;
            const progressInterval = setInterval(() => {
                percent += 5;
                if (percent > 90) percent = 90; // Trava no 90% até o retorno
                $('#celularProgressBar').css('width', percent + '%');
                $('#celularProgressPercent').text(percent + '%');
            }, 500);

            // Adicionar token CSRF
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: rotasCelular.importarBoeTexto || '/celular/importar-boe-texto',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    clearInterval(progressInterval);
                    $('#celularProgressBar').css('width', '100%');
                    $('#celularProgressPercent').text('100%');

                    if (response.success && response.dados) {
                        const dados = response.dados;
                        window.pendenteIA_Geral = { boe: dados.boe, ip: dados.ip, data: dados.data };
                        
                        if (dados.celulares && dados.celulares.length > 0) {
                            celularesPendentesIA = dados.celulares;
                            indiceIAPendente = 0;
                            
                            // MUDANÇA ENTRA AQUI! 
                            if (dados.celulares.length === 1) {
                                if (dados.boe) {
                                    $.ajax({
                                        url: rotasCelular.pesquisar,
                                        method: "GET",
                                        data: { filtro: "boe", termo: dados.boe },
                                        success: function(pesqResp) {
                                            $('#modalImportarCelular').modal('hide');
                                            
                                            let imeiLocal = dados.celulares[0].imei1 ? dados.celulares[0].imei1.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                            let specLocal = ((dados.celulares[0].marca_modelo || '') + ' ' + (dados.celulares[0].cor || '')).replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                            
                                            let jaExisteExato = false;
                                            let jaExisteSimilar = false;
                                            
                                            let itensDB = (pesqResp.success && pesqResp.data) ? pesqResp.data : [];
                                            
                                            for (let dbItem of itensDB) {
                                                let imeiDB = dbItem.imei1 ? dbItem.imei1.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                                                
                                                if (imeiLocal && imeiLocal !== 'SIMEI' && imeiDB === imeiLocal) {
                                                    jaExisteExato = true;
                                                    break;
                                                }
                                                
                                                if ((!imeiLocal || imeiLocal === 'SIMEI') && (!imeiDB || imeiDB === 'SIMEI')) {
                                                    let specDB = (dbItem.telefone || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                                    if (specLocal && specDB && (specDB.includes(specLocal) || specLocal.includes(specDB))) {
                                                        jaExisteSimilar = true;
                                                    }
                                                }
                                            }
                                            
                                            if (jaExisteExato) {
                                                window.mostrarErro(`Atenção: Preenchimento automático bloqueado! Este celular (IMEI ${dados.celulares[0].imei1 || 'N/A'}) já foi salvo neste Boletim.`);
                                            } else {
                                                preencherComObjetoIA(celularesPendentesIA[0]);
                                                if (jaExisteSimilar) {
                                                    window.mostrarAviso ? window.mostrarAviso(`⚠️ Atenção: Um celular "similar" sem IMEI já consta neste BOE. Verifique se não é repetição.`) : window.mostrarErro(`⚠️ Atenção: Um celular "similar" sem IMEI já consta neste BOE. Verifique se não é duplicado.`);
                                                } else {
                                                    window.mostrarSucesso(`Extração concluída! 1 celular detectado e preenchido no formulário.`);
                                                }
                                            }
                                        },
                                        error: function() {
                                            $('#modalImportarCelular').modal('hide');
                                            preencherComObjetoIA(celularesPendentesIA[0]);
                                            window.mostrarSucesso(`Extração concluída! 1 celular detectado.`);
                                        }
                                    });
                                } else {
                                    $('#modalImportarCelular').modal('hide');
                                    preencherComObjetoIA(celularesPendentesIA[0]);
                                    window.mostrarSucesso(`Extração concluída! 1 celular detectado e preenchido.`);
                                }
                            } else {
                                // MÚLTIPLOS CELULARES - BATCH IMPORT MODAL
                                $('#modalImportarCelular').modal('hide');
                                
                                // Nova funcionalidade: Pesquisar se já há celulares com este BOE cadastrados
                                if (dados.boe) {
                                    $.ajax({
                                        url: rotasCelular.pesquisar,
                                        method: "GET",
                                        data: { filtro: "boe", termo: dados.boe },
                                        success: function(pesqResp) {
                                            let itensDB = (pesqResp.success && pesqResp.data) ? pesqResp.data : [];
                                            abrirModalRevisaoMultiplosCelulares(dados.celulares, itensDB);
                                        },
                                        error: function() {
                                            abrirModalRevisaoMultiplosCelulares(dados.celulares, []);
                                        }
                                    });
                                } else {
                                    abrirModalRevisaoMultiplosCelulares(dados.celulares, []);
                                }
                            }
                        } else {
                            window.mostrarSucesso('Extração concluída. NENHUM celular foi detectado no documento.');
                            $('#modalImportarCelular').modal('hide');
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
                        $('#btnProcessarBoeCelular').prop('disabled', false).html('<i class="bi bi-cpu me-1"></i> Processar pelo Sistema');
                        $('#celularProgressWrapper').hide();
                        $('#celularProgressBar').css('width', '0%');
                        $('#celularProgressPercent').text('0%');
                    }, 1000);
                }
            });
        });

        // =========================================================================
        // NOVO: MODAL DE BATCH IMPORT (LOTES) 
        // Em vez de "salvar 1, clicar próximo", mostra todos de uma vez
        // =========================================================================
        function abrirModalRevisaoMultiplosCelulares(celularesList, itensDB = []) {
            // Limpa modais defasados
            $('#modalRevisaoMultiplosCelulares').remove();
            
            let gridHtml = celularesList.map((c, i) => {
                let descOriginal = (c.marca_modelo || 'Celular Não Especificado') + (c.cor ? ' - COR: ' + c.cor.toUpperCase() : '');
                let desc = descOriginal;
                let proprietario = c.proprietario || 'N/A';
                let imei1 = c.imei1 || 'S/IMEI';
                let imei2 = c.imei2 || '<span class="text-muted fst-italic">N/A</span>';
                let status = c.status || 'APREENDIDO';
                
                // Precisamos escapar aspas pro onclick JS
                let jsonStr = JSON.stringify(c).replace(/"/g, '&quot;');
                
                let imeiLimpo = imei1.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                let specLimpo = descOriginal.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                
                let isDuplicadoExato = false;
                let isDuplicadoSimilar = false;
                
                for (let dbItem of itensDB) {
                    let dbImei = dbItem.imei1 ? dbItem.imei1.replace(/[^a-zA-Z0-9]/g, '').toUpperCase() : '';
                    if (imeiLimpo && imeiLimpo !== 'SIMEI' && dbImei === imeiLimpo) {
                        isDuplicadoExato = true;
                        break;
                    }
                    if ((!imeiLimpo || imeiLimpo === 'SIMEI') && (!dbImei || dbImei === 'SIMEI')) {
                        let dbSpec = (dbItem.telefone || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                        if (specLimpo && dbSpec && (dbSpec.includes(specLimpo) || specLimpo.includes(dbSpec))) {
                            isDuplicadoSimilar = true;
                        }
                    }
                }
                
                let btnHtml = '';
                let rowClass = '';

                if (isDuplicadoExato) {
                    rowClass = 'table-secondary opacity-75';
                    btnHtml = `<span class="badge bg-secondary border border-secondary shadow-sm py-2 px-3 fw-bold w-100" title="Celular já atrelado a este boletim no seu sistema."><i class="bi bi-shield-check me-1"></i> Já Existe (Omitido)</span>`;
                } else if (isDuplicadoSimilar) {
                    rowClass = 'table-warning';
                    btnHtml = `
                        <span class="d-block mb-1 text-danger fw-bold" style="font-size:0.72rem;"><i class="bi bi-exclamation-triangle-fill"></i> Similar no BD</span>
                        <button type="button" class="btn btn-sm btn-warning text-dark border-dark fw-bold shadow-sm w-100 btn-lote-salvar" onclick="window.salvarCelularEmLote(this, ${i}, '${jsonStr}')">
                            <i class="bi bi-cloud-upload me-1"></i> Importar Mesmo Assim
                        </button>
                        <span class="lote-status text-success fw-bold d-none fs-6"><i class="bi bi-check-circle-fill"></i> Salvo!</span>
                    `;
                } else {
                    btnHtml = `
                        <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm w-100 btn-lote-salvar" onclick="window.salvarCelularEmLote(this, ${i}, '${jsonStr}')">
                            <i class="bi bi-cloud-upload me-1"></i> Importar
                        </button>
                        <span class="lote-status text-success fw-bold d-none fs-6"><i class="bi bi-check-circle-fill"></i> Salvo!</span>
                    `;
                }
                
                return `
                    <tr id="linhaCelular_${i}" class="${rowClass}">
                        <td class="align-middle fw-bold text-center">${imei1}</td>
                        <td class="align-middle text-wrap" style="max-width:250px;">${desc}</td>
                        <td class="align-middle text-center"><code>${imei2}</code></td>
                        <td class="align-middle text-center"><span class="badge bg-warning text-dark">${status}</span></td>
                        <td class="align-middle">${proprietario}</td>
                        <td class="align-middle text-center" style="min-width: 170px;">
                            ${btnHtml}
                        </td>
                    </tr>
                `;
            }).join("");

            const modalHtml = `
                <div class="modal fade" id="modalRevisaoMultiplosCelulares" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4" style="overflow: hidden;">
                            <div class="modal-header bg-primary text-white border-0 py-3 d-flex justify-content-between align-items-center" style="cursor: move;" title="Clique e arraste para mover a janela">
                                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-phone-fill me-2"></i> ${celularesList.length} Celulares Encontrados no BOE</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 bg-light">
                                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                                    <div>O sistema de inspeção encontrou múltiplos celulares no boletim. Para evitar retrabalho de digitação manual, você pode **revisar** e pular de forma seletiva, salvar de uma só vez apertando o botão [Importar] em apenas cada linha que desejar incluir no registro da apreensão.</div>
                                </div>
                                <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th class="text-center">IMEI 1</th>
                                                <th>Descrição</th>
                                                <th class="text-center">IMEI 2</th>
                                                <th class="text-center">Status</th>
                                                <th>Envolvido/Proprietário</th>
                                                <th class="text-center">Confirmar Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${gridHtml}
                                        </tbody>
                                        <tfoot class="border-top-0">
                                            <tr><td colspan="6" class="text-center bg-light py-2"><small class="text-muted">Ações são enviadas para o banco de dados imediata e individualmente.</small></td></tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer border-0 bg-white justify-content-center">
                                <button type="button" class="btn btn-primary px-5 rounded-pill shadow-sm fw-bold" data-bs-dismiss="modal" onclick="limparFormularioCelular()">Finalizar e Fechar Tela de Lote</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);
            const modalEl = document.getElementById('modalRevisaoMultiplosCelulares');
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
        window.salvarCelularEmLote = function(btnElement, indexLinha, objJsonStr) {
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
            formAutoData.append('data', dataFormatada || $('#inputDataCelular').val() || ''); 
            formAutoData.append('ip', ipIA || '');
            formAutoData.append('boe', boeIA || '');
            formAutoData.append('pessoa', obj.proprietario || '');
            
            const specAtual = obj.marca_modelo || '';
            const descCor = obj.cor ? ' - COR: ' + obj.cor.toUpperCase() : '';
            formAutoData.append('telefone', specAtual + descCor);
            
            formAutoData.append('imei1', obj.imei1 || '');
            formAutoData.append('imei2', obj.imei2 || '');
            formAutoData.append('processo', '');
            formAutoData.append('status', 'APREENDIDO');

            const $btn = $(btnElement);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Acessando BD...');

            $.ajax({
                url: rotasCelular.salvar,
                method: "POST",
                data: formAutoData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Linha Sucesso Visual
                        $btn.addClass('d-none');
                        $btn.siblings('.lote-status').removeClass('d-none');
                        $('#linhaCelular_' + indexLinha).addClass('table-success');
                        
                        setTimeout(function() {
                            if (boeIA) {
                                $('#filtroCelular').val('boe');
                                $('#termoPesquisaCelular').val(boeIA);
                                $('#btnPesquisarCelular').click();
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

        function preencherComObjetoIA(obj) {
            console.log('📝 Preenchendo formulário com dados da IA:', obj);
            
            // Tenta pegar o BOE e Data do contexto geral se não estiver no objeto
            const boeIA = obj.boe || (window.pendenteIA_Geral ? window.pendenteIA_Geral.boe : '');
            const dataIA = obj.data || (window.pendenteIA_Geral ? window.pendenteIA_Geral.data : '');
            const ipIA = obj.ip || (window.pendenteIA_Geral ? window.pendenteIA_Geral.ip : '');

            if (boeIA) $('#inputBoeCelular').val(boeIA);
            
            if (dataIA) {
                let dataFormatada = dataIA;
                if (dataFormatada.includes(' ')) dataFormatada = dataFormatada.split(' ')[0];
                if (!dataFormatada.includes(':')) {
                    $('#inputDataCelular').val(dataFormatada);
                }
            }

            if (ipIA) $('#inputIpCelular').val(ipIA);

            if (obj.marca_modelo) $('#inputTelefoneCelular').val(obj.marca_modelo);
            if (obj.imei1) $('#inputImei1Celular').val(obj.imei1);
            if (obj.imei2) $('#inputImei2Celular').val(obj.imei2);
            if (obj.cor) {
                $('#inputCorCelular').val(obj.cor.toUpperCase());
            }
            if (obj.proprietario) $('#inputPessoaCelular').val(obj.proprietario);
            $('#inputProcessoCelular').val('');
            
            $('#inputStatusCelular').val('APREENDIDO');
        }

        console.log('✅ CelularApp inicializada com sucesso!');
    }

    // Inicializar quando documento estiver pronto
    inicializarCelular();
});
