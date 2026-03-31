$(document).ready(function () {
    console.log('✅ Script Administrativo Avançado carregado');

    // Configuração do token CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Inicializar Flatpickr
    flatpickr(".flatpickr-date", {
        dateFormat: "d/m/Y",
        allowInput: true,
        locale: "pt"
    });

    // Variáveis globais
    let currentAdministrativoId = null;
    let dadosRelatorioAtual = [];
    let filtrosAtuais = {};
    let envolvidos = { vitimas: [], autores: [], testemunhas: [], capturados: [], outros: [] };

    // ========== EVENTOS PRINCIPAIS ==========

    // Eventos dos botões do formulário principal
    $('#btnNovoAdministrativo').on('click', limparFormulario);
    $('#btnSalvarAdministrativo').on('click', salvarAdministrativo);
    $('#btnEditarAdministrativo').on('click', editarAdministrativo);
    $('#btnExcluirAdministrativo').on('click', function () {
        if (currentAdministrativoId) {
            window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este registro administrativo?', excluirAdministrativo);
        } else {
            window.mostrarErro('Nenhum registro selecionado para exclusão.');
        }
    });
    $('#btnConfirmarExclusaoAdministrativo').on('click', excluirAdministrativo);
    $('#btnLimparAdministrativo').on('click', limparFormulario);
    $('#btnImportarBoeTexto').on('click', function () {
        $('#modalImportarDadosBoe').modal('show');
    });
    $('#btnProcessarImportacaoBoe').on('click', importarBoeTexto);
    $('#btnPesquisarAdministrativo').on('click', pesquisarAdministrativo);
    $('#btnAddEnvolvido').on('click', function () {
        const nome = $('#inputNomeEnvolvido').val();
        const tipo = $('#selectTipoEnvolvido').val();
        adicionarNome(tipo, nome);
    });
    $('#inputNomeEnvolvido').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnAddEnvolvido').click();
        }
    });

    // Pesquisar ao pressionar Enter
    $('#termoPesquisaAdministrativo').on('keypress', function (e) {
        if (e.which === 13) {
            pesquisarAdministrativo();
        }
    });

    // ========== SISTEMA DE RELATÓRIOS AVANÇADO ==========

    // Eventos do Dashboard Geral
    $('#btnGerarRelatorio').on('click', gerarRelatorioGeral);
    $('#btnExportarGeral').on('click', function () {
        $('#modalExportacao').modal('show');
    });
    $('#btnExportarTabela').on('click', function () {
        window.exportarRelatorio('excel', 'geral');
    });
    $('#filtroPeriodo').on('change', atualizarFiltroPeriodo);
    $('#btnConfirmarExportacao').on('click', confirmarExportacao);

    // Eventos das abas específicas
    $('a[data-bs-toggle="tab"][href="#dashboard-administrativo"]').on('shown.bs.tab', function () {
        inicializarDashboardGeral();
    });

    $('a[data-bs-toggle="tab"][href="#relatorio-crimes"]').on('shown.bs.tab', function () {
        carregarRelatorioCrimes();
    });

    $('a[data-bs-toggle="tab"][href="#relatorio-pessoas"]').on('shown.bs.tab', function () {
        carregarRelatorioPessoas();
    });

    $('a[data-bs-toggle="tab"][href="#relatorio-apreensoes"]').on('shown.bs.tab', function () {
        carregarRelatorioApreensoes();
    });

    // ========== FUNÇÕES DO FORMULÁRIO PRINCIPAL ==========

    function limparFormulario() {
        $('#formAdministrativo')[0].reset();
        $('#administrativo_id').val('');
        currentAdministrativoId = null;

        $('#btnEditarAdministrativo').prop('disabled', true);
        $('#btnExcluirAdministrativo').prop('disabled', true);
        $('#btnSalvarAdministrativo').prop('disabled', false);

        $('#tabelaResultadosAdministrativo tbody tr').removeClass('table-active');

        // Preencher data atual
        $('#inputDataCadastro').val(new Date().toLocaleDateString('pt-BR'));

        envolvidos = { vitimas: [], autores: [], testemunhas: [], capturados: [], outros: [] };
        renderTabelaEnvolvidos();
        atualizarCampoEnvolvidos();
        console.log('✅ Formulário limpo');
    }

    function getFormData() {
        const vitimaStr = envolvidos.vitimas.join(', ');
        const autorStr = envolvidos.autores.join(', ');
        $('#inputVitima').val(vitimaStr || $('#inputVitima').val());
        $('#inputAutor').val(autorStr || $('#inputAutor').val());
        atualizarCampoEnvolvidos();
        return {
            data_cadastro: $('#inputDataCadastro').val(),
            boe: $('#inputBoe').val(),
            ip: $('#inputIp').val(),
            vitima: $('#inputVitima').val(),
            autor: $('#inputAutor').val(),
            crime: $('#inputCrime').val(),
            tipificacao: $('#inputTipificacao').val(),
            apreensao: $('#inputApreensao').val(),
            cartorio: $('#inputCartorio').val(),
            envolvidos: $('#inputEnvolvidos').val(),
        };
    }

    function validarFormulario(dados) {
        if (!dados.data_cadastro) {
            mostrarModalErro('O campo Data de Cadastro é obrigatório.');
            return false;
        }
        if (!dados.boe || String(dados.boe).trim() === '') {
            mostrarModalErro('O campo BOE é obrigatório.');
            return false;
        }
        return true;
    }

    function salvarAdministrativo() {
        const dados = getFormData();

        if (!validarFormulario(dados)) {
            return;
        }

        const $btn = $('#btnSalvarAdministrativo');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        const formData = new FormData(document.getElementById('formAdministrativo'));

        $.ajax({
            url: rotasAdministrativo.salvar,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);

                    if (response.id) {
                        $('#administrativo_id').val(response.id);
                        currentAdministrativoId = response.id;

                        $('#btnEditarAdministrativo').prop('disabled', false);
                        $('#btnExcluirAdministrativo').prop('disabled', false);
                        $('#btnSalvarAdministrativo').prop('disabled', true);


                    }

                    carregarUltimosRegistros();
                } else {
                    mostrarModalErro(response.message || 'Ocorreu um erro ao salvar.');
                }
            },
            error: function (xhr) {
                console.error('Erro ao salvar:', xhr);
                let errorMessage = 'Erro ao salvar o registro.';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errors) {
                        errorMessage = Object.values(errors).join('\n');
                    }
                } else if (xhr.status === 500) {
                    errorMessage = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro interno do servidor. Tente novamente.';
                }

                mostrarModalErro(errorMessage);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> SALVAR');
            }
        });
    }

    function editarAdministrativo() {
        if (!currentAdministrativoId) {
            mostrarModalErro('Nenhum registro selecionado para edição.');
            return;
        }

        const dados = getFormData();

        if (!validarFormulario(dados)) {
            return;
        }

        const $btn = $('#btnEditarAdministrativo');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Editando...');

        const formData = new FormData(document.getElementById('formAdministrativo'));
        formData.append('_method', 'PUT');

        var u = rotasAdministrativo.atualizar;
        u = (u && u.indexOf(':id') !== -1) ? u.replace(':id', currentAdministrativoId) : (u + '/' + currentAdministrativoId);
        console.log('✏️ Atualizar Administrativo URL:', u);
        $.ajax({
            url: u,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    mostrarModalSucesso(response.message);
                    carregarUltimosRegistros();
                } else {
                    mostrarModalErro(response.message || 'Ocorreu um erro ao atualizar.');
                }
            },
            error: function (xhr) {
                console.error('Erro ao editar:', xhr);
                let errorMessage = 'Erro ao atualizar o registro.';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        errorMessage = Object.values(errors).join('\n');
                    }
                }

                mostrarModalErro(errorMessage);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-pencil-square me-1"></i> EDITAR');
            }
        });
    }

    function excluirAdministrativo() {
        if (!currentAdministrativoId) {
            mostrarModalErro('Nenhum registro selecionado para exclusão.');
            return;
        }

        var u = rotasAdministrativo.excluir;
        u = (u && u.indexOf(':id') !== -1) ? u.replace(':id', currentAdministrativoId) : (u + '/' + currentAdministrativoId);
        console.log('🗑️ Excluir Administrativo URL:', u);
        $.ajax({
            url: u,
            method: 'POST',
            data: {
                _method: 'DELETE'
            },
            success: function (response) {
                if (response.success) {
                    window.mostrarSucesso(response.message);
                    limparFormulario();
                    carregarUltimosRegistros();
                } else {
                    window.mostrarErro(response.message);
                }
            },
            error: function (xhr) {
                console.error('Erro ao excluir:', xhr);
                window.mostrarErro('Erro ao excluir o registro.');
            }
        });
    }

    function pesquisarAdministrativo() {
        const filtro = $('#filtroAdministrativo').val();
        const termo = $('#termoPesquisaAdministrativo').val().trim();

        if (!termo) {
            carregarUltimosRegistros();
            return;
        }

        const $tbody = $('#tabelaResultadosAdministrativo tbody');
        $tbody.html('<tr><td colspan="5" class="text-center">Pesquisando...</td></tr>');

        $.ajax({
            url: rotasAdministrativo.pesquisar,
            method: 'GET',
            data: { filtro, termo },
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                } else {
                    $tbody.html('<tr><td colspan="5" class="text-center">Nenhum registro encontrado.</td></tr>');
                }
            },
            error: function (xhr) {
                console.error('Erro na pesquisa:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao carregar os registros.</td></tr>');
                mostrarModalErro('Erro ao realizar pesquisa.');
            }
        });
    }

    function carregarUltimosRegistros() {
        console.log('🔄 Carregando últimos registros administrativos...');

        const $tbody = $('#tabelaResultadosAdministrativo tbody');
        $tbody.html('<tr><td colspan="5" class="text-center">Carregando...</td></tr>');

        $.ajax({
            url: rotasAdministrativo.ultimos,
            method: 'GET',
            success: function (response) {
                if (response.success && response.data && response.data.length > 0) {
                    exibirResultadosPesquisa(response.data);
                    console.log('✅ ' + response.data.length + ' registros carregados');
                } else {
                    $tbody.html('<tr><td colspan="5" class="text-center">Nenhum registro cadastrado</td></tr>');
                }
            },
            error: function (xhr) {
                console.error('❌ Erro ao carregar últimos:', xhr);
                $tbody.html('<tr><td colspan="6" class="text-center">Erro ao carregar dados</td></tr>');
            }
        });
    }

    function exibirResultadosPesquisa(dados) {
        const $tbody = $('#tabelaResultadosAdministrativo tbody');
        $tbody.empty();

        const termo = ($('#termoPesquisaAdministrativo').val() || '').trim().toLowerCase();

        dados.forEach(function (item) {
            const dataFormatada = formatarDataParaInput(item.data_cadastro) || '-';
            const vitRaw = (item.vitimas_list && item.vitimas_list.length) ? item.vitimas_list : (item.vitima || '');
            const autRaw = (item.autores_list && item.autores_list.length) ? item.autores_list : (item.autor || '');
            const vitArr = (vitRaw || '').split(',').map(s => s.trim()).filter(s => s);
            const autArr = (autRaw || '').split(',').map(s => s.trim()).filter(s => s);
            let vitStr = '';
            let autStr = '';
            if (vitArr.length) {
                if (termo) {
                    const m = vitArr.find(n => n.toLowerCase().includes(termo));
                    vitStr = m || vitArr[0];
                } else {
                    vitStr = vitArr[0];
                }
            }
            if (autArr.length) {
                if (termo) {
                    const m = autArr.find(n => n.toLowerCase().includes(termo));
                    autStr = m || autArr[0];
                } else {
                    autStr = autArr[0];
                }
            }

            const $linha = $(
                `
                <tr data-id="${item.id}">
                    <td>${item.boe || ''}</td>
                    <td>${item.ip || ''}</td>
                    <td>${vitStr}</td>
                    <td>${autStr}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-selecionar-administrativo" data-id="${item.id}">
                            <i class="bi bi-check-lg me-1"></i> Selecionar
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });
    }

    function buscarAdministrativo(id) {
        var u = rotasAdministrativo.buscar;
        u = (u && u.indexOf(':id') !== -1) ? u.replace(':id', id) : (u + '/' + id);
        console.log('🔎 Buscar Administrativo URL:', u);
        $.ajax({
            url: u,
            method: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    preencherFormularioAdministrativo(response.data);

                    $('#btnEditarAdministrativo').prop('disabled', false);
                    $('#btnExcluirAdministrativo').prop('disabled', false);
                    $('#btnSalvarAdministrativo').prop('disabled', true);

                    mostrarModalSucesso('Registro carregado com sucesso!');
                } else {
                    mostrarModalErro('Erro ao carregar o registro.');
                }
            },
            error: function (xhr) {
                console.error('Erro ao buscar:', xhr);
                mostrarModalErro('Erro ao buscar o registro.');
            }
        });
    }

    function preencherFormularioAdministrativo(dados) {
        $('#administrativo_id').val(dados.id);
        $('#inputDataCadastro').val(dados.data_cadastro);
        $('#inputBoe').val(dados.boe || '');
        $('#inputIp').val(dados.ip || '');
        $('#inputVitima').val(dados.vitima || '');
        $('#inputAutor').val(dados.autor || '');
        $('#inputCrime').val(dados.crime || '');
        $('#inputTipificacao').val(dados.tipificacao || '');
        $('#inputApreensao').val(dados.apreensao || '');
        $('#inputCartorio').val(dados.cartorio || '');

        currentAdministrativoId = dados.id;
        if (dados.envolvidos) {
            envolvidos = {
                vitimas: Array.isArray(dados.envolvidos.vitimas) ? dados.envolvidos.vitimas : [],
                autores: Array.isArray(dados.envolvidos.autores) ? dados.envolvidos.autores : [],
                testemunhas: Array.isArray(dados.envolvidos.testemunhas) ? dados.envolvidos.testemunhas : [],
                capturados: Array.isArray(dados.envolvidos.capturados) ? dados.envolvidos.capturados : [],
                outros: Array.isArray(dados.envolvidos.outros) ? dados.envolvidos.outros : []
            };
        } else {
            envolvidos = {
                vitimas: (dados.vitima || '').split(',').map(s => s.trim()).filter(s => s),
                autores: (dados.autor || '').split(',').map(s => s.trim()).filter(s => s),
                testemunhas: [],
                capturados: [],
                outros: []
            };
        }
        renderTabelaEnvolvidos();
        atualizarCampoEnvolvidos();
    }

    function adicionarNome(tipo, nome) {
        const n = (nome || '').trim();
        if (!n) {
            mostrarModalErro('Digite o nome do envolvido.');
            return;
        }

        let exists = false;
        Object.keys(envolvidos).forEach(key => {
            if (envolvidos[key].includes(n)) exists = true;
        });

        if (exists) {
            mostrarModalErro('Este nome já foi adicionado.');
            return;
        }

        if (!envolvidos[tipo]) envolvidos[tipo] = [];
        envolvidos[tipo].push(n);

        renderTabelaEnvolvidos();
        atualizarCampoEnvolvidos();

        $('#inputNomeEnvolvido').val('').focus();
    }

    function removerNome(tipo, nome) {
        envolvidos[tipo] = envolvidos[tipo].filter(n => n !== nome);
        renderTabelaEnvolvidos();
        atualizarCampoEnvolvidos();
    }

    function renderTabelaEnvolvidos() {
        const $tbody = $('#tabelaEnvolvidos tbody');
        $tbody.empty();

        const tiposLabel = {
            'vitimas': '<span class="badge bg-warning text-dark">Vítima</span>',
            'autores': '<span class="badge bg-danger">Autor</span>',
            'testemunhas': '<span class="badge bg-info text-dark">Testemunha</span>',
            'capturados': '<span class="badge bg-dark">Capturado</span>',
            'adolescentes': '<span class="badge bg-secondary">Adolescente</span>',
            'outros': '<span class="badge bg-light text-dark border">Outro</span>'
        };

        let hasItems = false;

        Object.keys(envolvidos).forEach(tipo => {
            if (envolvidos[tipo] && envolvidos[tipo].length > 0) {
                envolvidos[tipo].forEach((nome) => {
                    hasItems = true;
                    const $tr = $(`
                        <tr>
                            <td class="align-middle fw-medium">${nome}</td>
                            <td class="align-middle">${tiposLabel[tipo] || tipo}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-warning btn-editar-envolvido me-1" data-tipo="${tipo}" data-nome="${nome}" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remover-envolvido" data-tipo="${tipo}" data-nome="${nome}" title="Remover">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                    $tbody.append($tr);
                });
            }
        });

        if (!hasItems) {
            $tbody.html('<tr><td colspan="3" class="text-center text-muted py-3">Nenhum envolvido adicionado</td></tr>');
        }

        $('.btn-remover-envolvido').on('click', function () {
            const tipo = $(this).data('tipo');
            const nome = $(this).data('nome');
            removerNome(tipo, nome);
        });

        $('.btn-editar-envolvido').on('click', function () {
            const tipo = $(this).data('tipo');
            const nome = $(this).data('nome');

            // Preencher campos
            $('#inputNomeEnvolvido').val(nome);
            $('#selectTipoEnvolvido').val(tipo);

            // Remover da lista para permitir edição
            removerNome(tipo, nome);

            // Focar no campo de nome
            $('#inputNomeEnvolvido').focus();
        });
    }

    function atualizarCampoEnvolvidos() {
        $('#inputEnvolvidos').val(JSON.stringify(envolvidos));
    }

    // ========== SISTEMA DE RELATÓRIOS AVANÇADO ==========

    function inicializarDashboardGeral() {
        console.log('📊 Inicializando Dashboard Geral...');

        // Configurar datas padrão - usar formato Y-m-d
        const hoje = new Date();
        const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);

        $('#filtroDataInicio').val(primeiroDiaMes.toISOString().split('T')[0]);
        $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);

        // Carregar relatório inicial com pequeno delay
        setTimeout(() => gerarRelatorioGeral(), 1000);
    }

    function gerarRelatorioGeral() {
        console.log('📈 Gerando relatório geral...');

        const periodo = $('#filtroPeriodo').val();
        const dataInicio = $('#filtroDataInicio').val();
        const dataFim = $('#filtroDataFim').val();
        const tipoRelatorio = $('#filtroTipo').val();
        const crime = $('#filtroCrime').val();
        const cartorio = $('#filtroCartorio').val();

        // Mostrar loading
        $('#graficoCrimes').html('<p class="text-center text-muted my-5"><i class="bi bi-arrow-repeat spinner me-2"></i>Carregando crimes...</p>');
        $('#graficoMensal').html('<p class="text-center text-muted my-5"><i class="bi bi-arrow-repeat spinner me-2"></i>Carregando evolução...</p>');
        $('#corpoTabelaRelatorio').html('<tr><td colspan="8" class="text-center">Gerando relatório...</td></tr>');

        // Preparar filtros - apenas enviar se tiver valor
        filtrosAtuais = {
            periodo: periodo,
            data_inicio: dataInicio || null,
            data_fim: dataFim || null,
            tipo: tipoRelatorio,
            crime: crime || null,
            cartorio: cartorio || null
        };

        // Remover parâmetros nulos ou vazios
        Object.keys(filtrosAtuais).forEach(key => {
            if (filtrosAtuais[key] === null || filtrosAtuais[key] === '') {
                delete filtrosAtuais[key];
            }
        });

        $.ajax({
            url: rotasAdministrativo.relatorio,
            method: 'GET',
            data: filtrosAtuais,
            success: function (response) {
                if (response.success) {
                    dadosRelatorioAtual = response.data;
                    atualizarMetricasPrincipais(response.metricas);
                    atualizarMetricasSecundarias(response.metricas);
                    atualizarGraficosAvancados(response.graficos);
                    atualizarTabelaRelatorio(response.data);
                    atualizarEstatisticasAvancadas(response.estatisticas);

                    console.log('✅ Relatório gerado com sucesso:', response.data.length, 'registros');
                } else {
                    mostrarModalErro('Erro ao gerar relatório: ' + response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ Erro ao gerar relatório:', xhr);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    console.error('Erros de validação:', errors);
                    mostrarModalErro('Erro de validação: ' + JSON.stringify(errors));
                } else {
                    mostrarModalErro('Erro ao carregar dados do relatório.');
                }
            }
        });
    }

    function atualizarMetricasPrincipais(metricas) {
        $('#metric-total').text(metricas.total || 0);
        $('#metric-hoje').text(metricas.hoje || 0);
        $('#metric-semana').text(metricas.esta_semana || 0);
        $('#metric-mes').text(metricas.este_mes || 0);
        $('#metric-ano').text(metricas.este_ano || 0);
    }

    function atualizarMetricasSecundarias(metricas) {
        $('#metric-vitimas').text(metricas.vitimas_unicas || 0);
        $('#metric-autores').text(metricas.autores_unicos || 0);
        $('#metric-apreensoes').text(metricas.com_apreensao || 0);

        const taxaApreensao = metricas.total > 0 ?
            Math.round((metricas.com_apreensao / metricas.total) * 100) : 0;
        $('#metric-taxa-apreensao').text(taxaApreensao + '% dos casos');
    }

    function atualizarGraficosAvancados(graficos) {
        // Gráfico de Crimes (Top 10)
        if (graficos.crimes && graficos.crimes.length > 0) {
            let htmlCrimes = '<div class="row g-2">';
            graficos.crimes.forEach(item => {
                const percentual = item.percentual || 0;
                htmlCrimes += `
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-truncate" title="${item.crime}">${item.crime}</span>
                            <span class="badge bg-primary stat-badge">${item.total} (${percentual}%)</span>
                        </div>
                        <div class="progress progress-thin mb-3">
                            <div class="progress-bar" role="progressbar" style="width: ${percentual}%"></div>
                        </div>
                    </div>
                `;
            });
            htmlCrimes += '</div>';
            $('#graficoCrimes').html(htmlCrimes);
        } else {
            $('#graficoCrimes').html('<p class="text-center text-muted my-5">Nenhum dado de crime disponível</p>');
        }

        // Gráfico Mensal
        if (graficos.mensal && graficos.mensal.length > 0) {
            let htmlMensal = '<div class="row g-3 text-center">';
            const maxVal = Math.max(...graficos.mensal.map(item => item.total));

            graficos.mensal.forEach(item => {
                const altura = maxVal > 0 ? Math.max((item.total / maxVal) * 120, 20) : 20;
                const cor = item.total > 0 ? 'bg-primary' : 'bg-secondary';
                htmlMensal += `
                    <div class="col-2">
                        <div class="mb-2 position-relative" style="height: 140px;">
                            <div class="position-absolute bottom-0 start-50 translate-middle-x ${cor} rounded-top"
                                 style="width: 25px; height: ${altura}px;"></div>
                            <div class="position-absolute top-0 start-50 translate-middle-x small fw-bold">${item.total}</div>
                        </div>
                        <small class="text-muted d-block">${item.mes}</small>
                    </div>
                `;
            });
            htmlMensal += '</div>';
            $('#graficoMensal').html(htmlMensal);
        }
    }

    function atualizarTabelaRelatorio(dados) {
        const $tbody = $('#corpoTabelaRelatorio');
        $tbody.empty();

        if (dados.length === 0) {
            $tbody.html('<tr><td colspan="8" class="text-center">Nenhum registro encontrado para os filtros selecionados</td></tr>');
            $('#contadorRegistros').text('0 registros');
            return;
        }

        const termo = ($('#termoPesquisaAdministrativo').val() || '').trim().toLowerCase();

        dados.forEach(item => {
            const vitRaw = item.vitima || '';
            const autRaw = item.autor || '';
            const vitArr = vitRaw.split(',').map(s => s.trim()).filter(s => s);
            const autArr = autRaw.split(',').map(s => s.trim()).filter(s => s);
            let vitStr = '-';
            let autStr = '-';
            if (vitArr.length) {
                if (termo) {
                    const m = vitArr.find(n => n.toLowerCase().includes(termo));
                    vitStr = m || vitArr[0];
                } else {
                    vitStr = vitArr[0];
                }
            }
            if (autArr.length) {
                if (termo) {
                    const m = autArr.find(n => n.toLowerCase().includes(termo));
                    autStr = m || autArr[0];
                } else {
                    autStr = autArr[0];
                }
            }

            const $linha = $(`
                <tr>
                    <td>${item.boe || '-'}</td>
                    <td class="text-truncate" style="max-width: 220px;" title="${item.vitima || ''}">${vitStr}</td>
                    <td class="text-truncate" style="max-width: 220px;" title="${item.autor || ''}">${autStr}</td>
                    <td class="text-truncate" style="max-width: 180px;" title="${item.crime || ''}">
                        <span class="badge crime-tag bg-danger">${item.crime || 'N/I'}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-detalhes-relatorio" data-id="${item.id}">
                            <i class="bi bi-eye me-1"></i> Detalhes
                        </button>
                    </td>
                </tr>
            `);
            $tbody.append($linha);
        });

        $('#contadorRegistros').text(dados.length + ' registros');
    }

    function atualizarEstatisticasAvancadas(estatisticas) {
        console.log('📊 Estatísticas avançadas:', estatisticas);
        // Aqui você pode adicionar mais visualizações para estatísticas avançadas
    }

    // ========== RELATÓRIOS ESPECÍFICOS ==========

    function carregarRelatorioCrimes() {
        console.log('🔫 Carregando relatório de crimes...');

        const $conteudo = $('#conteudo-relatorio-crimes');
        $conteudo.html(`
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-funnel me-1"></i> Filtros do Relatório de Crimes</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="filtroCrimesPeriodo">
                                <option value="hoje">Hoje</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes" selected>Este Mês</option>
                                <option value="ano">Este Ano</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Agrupar Por</label>
                            <select class="form-select" id="filtroCrimesAgrupar">
                                <option value="crime">Tipo de Crime</option>
                                <option value="tipificacao">Tipificação</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="btnGerarRelatorioCrimes">
                                <i class="bi bi-gear me-1"></i> Gerar Relatório
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="resultado-relatorio-crimes">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>Use os filtros acima para gerar o relatório de crimes</p>
                </div>
            </div>
        `);

        $('#btnGerarRelatorioCrimes').on('click', gerarRelatorioCrimes);

        // Gerar relatório inicial
        setTimeout(() => gerarRelatorioCrimes(), 500);
    }

    function gerarRelatorioCrimes() {
        const periodo = $('#filtroCrimesPeriodo').val();
        const agruparPor = $('#filtroCrimesAgrupar').val();

        $('#resultado-relatorio-crimes').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Gerando análise de crimes...</p>
            </div>
        `);

        $.ajax({
            url: rotasAdministrativo.relatorioCrimes,
            method: 'GET',
            data: {
                periodo: periodo,
                agrupar_por: agruparPor
            },
            success: function (response) {
                if (response.success) {
                    exibirRelatorioCrimes(response.dados, response.estatisticas);
                } else {
                    $('#resultado-relatorio-crimes').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro ao gerar relatório: ${response.message}
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                console.error('Erro no relatório de crimes:', xhr);
                $('#resultado-relatorio-crimes').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erro ao carregar dados dos crimes.
                    </div>
                `);
            }
        });
    }

    function exibirRelatorioCrimes(dados, estatisticas) {
        let html = `
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center py-3">
                            <h4 class="metric-number">${estatisticas.total_ocorrencias}</h4>
                            <p class="mb-0">Total de Ocorrências</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center py-3">
                            <h4 class="metric-number">${estatisticas.tipos_diferentes}</h4>
                            <p class="mb-0">Tipos Diferentes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center py-3">
                            <h4 class="metric-number">${estatisticas.mais_comum ? estatisticas.mais_comum.total : 0}</h4>
                            <p class="mb-0 small">${estatisticas.mais_comum ? estatisticas.mais_comum.agrupamento || 'N/A' : 'N/A'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Distribuição de Crimes</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportarRelatorio('excel','crimes')">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Crime/Tipificação</th>
                                    <th>Total Ocorrências</th>
                                    <th>Vítimas Únicas</th>
                                    <th>Autores Únicos</th>
                                    <th>Primeira Ocorrência</th>
                                    <th>Última Ocorrência</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        dados.forEach(item => {
            const primeira = formatarDataParaInput(item.primeira_ocorrencia) || '-';
            const ultima = formatarDataParaInput(item.ultima_ocorrencia) || '-';
            const label = item.agrupamento || item.crime || item.tipificacao || 'N/I';
            html += `
                <tr>
                    <td><strong>${label}</strong></td>
                    <td><span class="badge bg-primary">${item.total}</span></td>
                    <td>${item.vitimas_unicas || 0}</td>
                    <td>${item.autores_unicos || 0}</td>
                    <td>${primeira}</td>
                    <td>${ultima}</td>
                </tr>
            `;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        $('#resultado-relatorio-crimes').html(html);
    }

    function carregarRelatorioPessoas() {
        console.log('👥 Carregando relatório de pessoas...');

        const $conteudo = $('#conteudo-relatorio-pessoas');
        $conteudo.html(`
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-funnel me-1"></i> Filtros do Relatório de Pessoas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="filtroPessoasTipo">
                                <option value="vitima">Apenas Vítimas</option>
                                <option value="autor">Apenas Autores</option>
                                <option value="ambos" selected>Vítimas e Autores</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="filtroPessoasPeriodo">
                                <option value="hoje">Hoje</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes" selected>Este Mês</option>
                                <option value="ano">Este Ano</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Limite</label>
                            <select class="form-select" id="filtroPessoasLimite">
                                <option value="10">Top 10</option>
                                <option value="25" selected>Top 25</option>
                                <option value="50">Top 50</option>
                                <option value="100">Top 100</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="btnGerarRelatorioPessoas">
                                <i class="bi bi-gear me-1"></i> Gerar Relatório
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="resultado-relatorio-pessoas">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>Use os filtros acima para gerar o relatório de pessoas</p>
                </div>
            </div>
        `);

        $('#btnGerarRelatorioPessoas').on('click', gerarRelatorioPessoas);

        // Gerar relatório inicial
        setTimeout(() => gerarRelatorioPessoas(), 500);
    }

    function gerarRelatorioPessoas() {
        const tipo = $('#filtroPessoasTipo').val();
        const periodo = $('#filtroPessoasPeriodo').val();
        const limite = $('#filtroPessoasLimite').val();

        $('#resultado-relatorio-pessoas').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Analisando dados de pessoas...</p>
            </div>
        `);

        $.ajax({
            url: rotasAdministrativo.relatorioPessoas,
            method: 'GET',
            data: {
                tipo: tipo,
                periodo: periodo,
                limite: limite
            },
            success: function (response) {
                if (response.success) {
                    exibirRelatorioPessoas(response.dados, response.estatisticas);
                } else {
                    $('#resultado-relatorio-pessoas').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro ao gerar relatório: ${response.message}
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                console.error('Erro no relatório de pessoas:', xhr);
                $('#resultado-relatorio-pessoas').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Erro ao carregar dados de pessoas.
                    </div>
                `);
            }
        });
    }

    function exibirRelatorioPessoas(dados, estatisticas) {
        let html = `
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center py-3">
                            <h4 class="metric-number">${estatisticas.total_pessoas}</h4>
                            <p class="mb-0">Total de Pessoas Envolvidas</p>
                            <small>Período: ${estatisticas.periodo} | Tipo: ${estatisticas.tipo === 'ambos' ? 'Vítimas e Autores' : estatisticas.tipo}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Pessoas Mais Envolvidas</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportarRelatorio('excel','pessoas')">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Total Ocorrências</th>
                                    <th>Crimes Diferentes</th>
                                    <th>Primeira Ocorrência</th>
                                    <th>Última Ocorrência</th>
                                    <th>Crimes Envolvidos</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        dados.forEach(item => {
            const primeira = formatarDataParaInput(item.primeira_ocorrencia) || '-';
            const ultima = formatarDataParaInput(item.ultima_ocorrencia) || '-';
            const crimes = item.crimes_array && item.crimes_array.length > 0 ?
                item.crimes_array.slice(0, 3).map(crime => `<span class="badge crime-tag bg-secondary me-1">${crime}</span>`).join('') :
                '<span class="text-muted">N/A</span>';

            html += `
                <tr>
                    <td><strong>${item.nome}</strong></td>
                    <td><span class="badge ${item.tipo === 'Vítima' ? 'bg-warning' : 'bg-danger'}">${item.tipo}</span></td>
                    <td><span class="badge bg-primary">${item.total_ocorrencias}</span></td>
                    <td>${item.crimes_diferentes}</td>
                    <td>${primeira}</td>
                    <td>${ultima}</td>
                    <td>${crimes}</td>
                </tr>
            `;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        $('#resultado-relatorio-pessoas').html(html);
    }

    function carregarRelatorioApreensoes() {
        console.log('🎒 Carregando relatório de apreensões...');

        const $conteudo = $('#conteudo-relatorio-apreensoes');
        $conteudo.html(`
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-funnel me-1"></i> Filtros do Relatório de Apreensões</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Período</label>
                            <select class="form-select" id="filtroApreensoesPeriodo">
                                <option value="hoje">Hoje</option>
                                <option value="semana">Esta Semana</option>
                                <option value="mes" selected>Este Mês</option>
                                <option value="ano">Este Ano</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Apreensão</label>
                            <input type="text" class="form-control" id="filtroApreensoesTipo" placeholder="Ex: celular, arma, droga...">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="btnGerarRelatorioApreensoes">
                                <i class="bi bi-gear me-1"></i> Gerar Relatório
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="resultado-relatorio-apreensoes">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>Use os filtros acima para gerar o relatório de apreensões</p>
                </div>
            </div>
        `);

        $('#btnGerarRelatorioApreensoes').on('click', gerarRelatorioApreensoes);

        // Gerar relatório inicial
        setTimeout(() => gerarRelatorioApreensoes(), 500);
    }

    function gerarRelatorioApreensoes() {
        const periodo = $('#filtroApreensoesPeriodo').val();
        const tipo = $('#filtroApreensoesTipo').val();

        // Preparar dados - enviar tipo_apreensao apenas se tiver valor
        const dados = {
            periodo: periodo
        };

        if (tipo && tipo.trim() !== '') {
            dados.tipo_apreensao = tipo;
        }

        $('#resultado-relatorio-apreensoes').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p>Analisando dados de apreensões...</p>
            </div>
        `);

        $.ajax({
            url: rotasAdministrativo.relatorioApreensoes,
            method: 'GET',
            data: dados,
            success: function (response) {
                if (response.success) {
                    exibirRelatorioApreensoes(response.dados, response.analise);
                } else {
                    $('#resultado-relatorio-apreensoes').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro ao gerar relatório: ${response.message}
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                console.error('Erro no relatório de apreensões:', xhr);
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    console.error('Erros de validação:', errors);
                    $('#resultado-relatorio-apreensoes').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro de validação: ${JSON.stringify(errors)}
                        </div>
                    `);
                } else {
                    $('#resultado-relatorio-apreensoes').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Erro ao carregar dados de apreensões.
                        </div>
                    `);
                }
            }
        });
    }

    function exibirRelatorioApreensoes(dados, analise) {
        let html = `
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center py-3">
                            <h4 class="metric-number">${analise.total_apreensoes}</h4>
                            <p class="mb-0">Total de Apreensões</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-bar-chart me-1"></i> Distribuição por Tipo de Item</h6>
                        </div>
                        <div class="card-body">
        `;

        if (Object.keys(analise.itens_apreendidos).length > 0) {
            Object.entries(analise.itens_apreendidos).forEach(([item, quantidade]) => {
                html += `
                    <div class="row align-items-center mb-2">
                        <div class="col-4">
                            <span class="small">${item}</span>
                        </div>
                        <div class="col-6">
                            <div class="progress progress-thin">
                                <div class="progress-bar bg-warning" role="progressbar"
                                     style="width: ${(quantidade / analise.total_apreensoes) * 100}%">
                                </div>
                            </div>
                        </div>
                        <div class="col-2 text-end">
                            <span class="badge bg-warning text-dark">${quantidade}</span>
                        </div>
                    </div>
                `;
            });
        } else {
            html += `<p class="text-center text-muted my-3">Não foi possível categorizar os itens apreendidos</p>`;
        }

        html += `
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Detalhes das Apreensões</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportarRelatorio('excel','apreensoes')">
                        <i class="bi bi-download me-1"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
        `;

        if (dados.length > 0) {
            html += `
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>BOE</th>
                                    <th>Crime</th>
                                    <th>Vítima</th>
                                    <th>Autor</th>
                                    <th>Itens Apreendidos</th>
                                </tr>
                            </thead>
                            <tbody>
            `;

            dados.forEach(item => {
                const dataFormatada = formatarDataParaInput(item.data_cadastro) || '-';
                html += `
                    <tr>
                        <td>${item.boe || '-'}</td>
                        <td><span class="badge crime-tag bg-danger">${item.crime || 'N/I'}</span></td>
                        <td>${item.vitima || '-'}</td>
                        <td>${item.autor || '-'}</td>
                        <td>${item.apreensao || '-'}</td>
                    </tr>
                `;
            });

            html += `
                            </tbody>
                        </table>
                    </div>
            `;
        } else {
            html += `<p class="text-center text-muted my-4">Nenhuma apreensão encontrada para os filtros selecionados</p>`;
        }

        html += `
                            </div>
                </div>
            </div>
        `;

        $('#resultado-relatorio-apreensoes').html(html);
    }

    // ========== SISTEMA DE EXPORTAÇÃO ==========

    function abrirModalExportacao(tipo) {
        $('#tipoRelatorioExport').val(tipo);
        $('#modalExportacao').modal('show');
    }

    // Delegated handlers for dynamic export buttons in tabs


    function confirmarExportacao() {
        const formatoSelecionado = $('#formatoExportacao').val();
        const tipoRelatorio = $('#tipoRelatorioExport').val();
        const formatoFinal = (tipoRelatorio === 'geral') ? formatoSelecionado : 'excel';
        window.exportarRelatorio(formatoFinal, tipoRelatorio);
        $('#modalExportacao').modal('hide');
    }

    window.exportarRelatorio = function (formato, tipoRelatorio) {
        console.log(`📤 Exportando ${tipoRelatorio} como ${formato}...`);

        // Preparar dados baseados no tipo de relatório
        let dadosExportacao = {
            formato: formato,
            tipo_relatorio: tipoRelatorio,
            filtros: {}
        };

        // Adicionar filtros específicos baseados no tipo
        switch (tipoRelatorio) {
            case 'geral':
                dadosExportacao.filtros = filtrosAtuais;
                break;
            case 'crimes':
                dadosExportacao.filtros = {
                    periodo: $('#filtroCrimesPeriodo').val(),
                    agrupar_por: $('#filtroCrimesAgrupar').val()
                };
                break;
            case 'pessoas':
                dadosExportacao.filtros = {
                    tipo: $('#filtroPessoasTipo').val(),
                    periodo: $('#filtroPessoasPeriodo').val(),
                    limite: $('#filtroPessoasLimite').val()
                };
                break;
            case 'apreensoes':
                dadosExportacao.filtros = {
                    periodo: $('#filtroApreensoesPeriodo').val(),
                    tipo_apreensao: $('#filtroApreensoesTipo').val()
                };
                break;
        }

        // Mostrar loading
        mostrarModalSucesso(`Iniciando exportação para ${formato.toUpperCase()}...`);

        // Para Excel, fazer download direto
        if (formato === 'excel') {
            // Criar um formulário temporário para submissão
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rotasAdministrativo.exportar;
            form.style.display = 'none';

            // Adicionar token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfToken);

            // Adicionar campos do formulário
            const formatoInput = document.createElement('input');
            formatoInput.type = 'hidden';
            formatoInput.name = 'formato';
            formatoInput.value = formato;
            form.appendChild(formatoInput);

            const tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo_relatorio';
            tipoInput.value = tipoRelatorio;
            form.appendChild(tipoInput);

            const filtrosInput = document.createElement('input');
            filtrosInput.type = 'hidden';
            filtrosInput.name = 'filtros';
            filtrosInput.value = JSON.stringify(dadosExportacao.filtros);
            form.appendChild(filtrosInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

        } else {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rotasAdministrativo.exportar;
            if (formato === 'pdf') {
                form.target = '_blank';
            }
            form.style.display = 'none';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfToken);

            const formatoInput = document.createElement('input');
            formatoInput.type = 'hidden';
            formatoInput.name = 'formato';
            formatoInput.value = formato;
            form.appendChild(formatoInput);

            const tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo_relatorio';
            tipoInput.value = tipoRelatorio;
            form.appendChild(tipoInput);

            const filtrosInput = document.createElement('input');
            filtrosInput.type = 'hidden';
            filtrosInput.name = 'filtros';
            filtrosInput.value = JSON.stringify(dadosExportacao.filtros);
            form.appendChild(filtrosInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    }

    // ========== FUNÇÕES AUXILIARES ==========

    function atualizarFiltroPeriodo() {
        const periodo = $(this).val();
        const hoje = new Date();

        switch (periodo) {
            case 'hoje':
                $('#filtroDataInicio').val(hoje.toISOString().split('T')[0]);
                $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
                break;
            case 'semana':
                const umaSemana = new Date(hoje);
                umaSemana.setDate(umaSemana.getDate() - 7);
                $('#filtroDataInicio').val(umaSemana.toISOString().split('T')[0]);
                $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
                break;
            case 'mes':
                const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                $('#filtroDataInicio').val(primeiroDia.toISOString().split('T')[0]);
                $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
                break;
            case 'ano':
                const primeiroDiaAno = new Date(hoje.getFullYear(), 0, 1);
                $('#filtroDataInicio').val(primeiroDiaAno.toISOString().split('T')[0]);
                $('#filtroDataFim').val(hoje.toISOString().split('T')[0]);
                break;
            case 'todos':
                $('#filtroDataInicio').val('');
                $('#filtroDataFim').val('');
                break;
        }
    }

    function mostrarDetalhesRelatorio(id) {
        const registro = dadosRelatorioAtual.find(item => item.id == id);

        if (!registro) {
            mostrarModalErro('Registro não encontrado.');
            return;
        }

        const dataFormatada = formatarDataParaInput(registro.data_cadastro) || '-';
        const dataCriacao = registro.created_at ? new Date(registro.created_at).toLocaleString('pt-BR') : '-';
        const dataAtualizacao = registro.updated_at ? new Date(registro.updated_at).toLocaleString('pt-BR') : '-';

        const conteudo = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Informações Básicas</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Data Cadastro:</strong></td>
                            <td>${dataFormatada}</td>
                        </tr>
                        <tr>
                            <td><strong>BOE:</strong></td>
                            <td>${registro.boe || 'Não informado'}</td>
                        </tr>
                        <tr>
                            <td><strong>IP:</strong></td>
                            <td>${registro.ip || 'Não informado'}</td>
                        </tr>
                        <tr>
                            <td><strong>Cartório:</strong></td>
                            <td>${registro.cartorio || 'Não informado'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Envolvidos</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Vítima:</strong></td>
                            <td>${registro.vitima || ''}</td>
                        </tr>
                        <tr>
                            <td><strong>Autor:</strong></td>
                            <td>${registro.autor || ''}</td>
                        </tr>
                        <tr>
                            <td><strong>Testemunha:</strong></td>
                            <td>${registro.testemunha || ''}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Informações do Crime</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Crime:</strong></td>
                            <td><span class="badge bg-danger">${registro.crime || 'Não informado'}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Tipificação:</strong></td>
                            <td>${registro.tipificacao || 'Não informado'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Apreensão</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td colspan="2">
                                ${registro.apreensao ?
                `<div class="alert alert-warning p-2 mb-0">${registro.apreensao}</div>` :
                '<span class="text-muted">Nenhuma apreensão registrada</span>'
            }
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6 class="border-bottom pb-2">Metadados</h6>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Data de Criação:</strong></td>
                            <td>${dataCriacao}</td>
                        </tr>
                        <tr>
                            <td><strong>Última Atualização:</strong></td>
                            <td>${dataAtualizacao}</td>
                        </tr>

                    </table>
                </div>
            </div>

        `;

        $('#modalDetalhesRelatorioTitulo').text(`Detalhes - ${registro.boe || 'Registro ' + registro.id}`);
        $('#conteudoDetalhesRelatorio').html(conteudo);
        $('#modalDetalhesRelatorio').modal('show');
    }

    // Função global para carregar registro para edição
    window.carregarRegistroParaEdicao = function (id) {
        $('#modalDetalhesRelatorio').modal('hide');

        // Mudar para a aba de dados
        $('a[href="#dados-administrativo"]').tab('show');

        // Buscar e preencher o registro
        setTimeout(() => {
            buscarAdministrativo(id);
        }, 500);
    };

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

    function importarBoeTexto() {
        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        const pdfAtivo = document.getElementById('tab-pdf-admin') && document.getElementById('tab-pdf-admin').classList.contains('active');

        // Helper: progresso suave Admin
        let _adminProgressInterval = null;
        function iniciarProgressoAdmin(label) {
            let pct = 0;
            $('#boeProgressWrapperAdmin').show();
            $('#boeProgressLabelAdmin').text(label);
            $('#boeProgressBarAdmin').css('width', '0%').attr('aria-valuenow', 0);
            $('#boeProgressPercentAdmin').text('0%');
            _adminProgressInterval = setInterval(function () {
                const acelerador = pct < 40 ? 3 : pct < 70 ? 1.5 : 0.4;
                pct = Math.min(pct + acelerador, 85);
                $('#boeProgressBarAdmin').css('width', pct + '%').attr('aria-valuenow', Math.round(pct));
                $('#boeProgressPercentAdmin').text(Math.round(pct) + '%');
            }, 500);
        }
        function finalizarProgressoAdmin(sucesso) {
            clearInterval(_adminProgressInterval);
            const cor = sucesso ? 'bg-success' : 'bg-danger';
            const label = sucesso ? '✅ Dados extraídos com sucesso!' : '❌ Erro na extração.';
            $('#boeProgressBarAdmin').removeClass('progress-bar-animated progress-bar-bar-striped bg-info bg-danger bg-success').addClass(cor).css('width', '100%').attr('aria-valuenow', 100);
            $('#boeProgressLabelAdmin').text(label);
            $('#boeProgressPercentAdmin').text('100%');
            setTimeout(function () { $('#boeProgressWrapperAdmin').hide(); }, 2500);
        }

        if (pdfAtivo) {
            var fileInput = document.getElementById('pdfBoeImportacao');
            if (!fileInput || fileInput.files.length === 0) {
                mostrarModalErro('Selecione um arquivo PDF antes de processar.');
                return;
            }
            formData.append('pdfBOE', fileInput.files[0]);
            $('#btnProcessarImportacaoBoe').prop('disabled', true).html('<i class="bi bi-cpu me-1"></i> Lendo PDF com o Sistema...');
            iniciarProgressoAdmin('🤖 O sistema está lendo o documento...');
        } else {
            var texto = $('#textoBoeImportacao').val();
            if (!texto || texto.trim() === '') {
                mostrarModalErro('Por favor, cole o texto do BOE.');
                return;
            }
            formData.append('textoBOE', texto);
            $('#btnProcessarImportacaoBoe').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Processando...');
            iniciarProgressoAdmin('🤖 O sistema está analisando o texto do BOE...');
        }

        $.ajax({
            url: rotasAdministrativo.importarBoeTexto,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                finalizarProgressoAdmin(response.success && !!response.dados);
                $('#textoBoeImportacao').val('');

                if (response.success && response.dados) {
                    const dados = response.dados;

                    // Preencher formulário
                    if (dados.boe) $('#inputBoe').val(dados.boe);
                    // if (dados.data_cadastro) $('#inputDataCadastro').val(dados.data_cadastro); // REMOVIDO: Data sempre atual
                    if (dados.crime) $('#inputCrime').val(dados.crime);
                    if (dados.tipificacao) $('#inputTipificacao').val(dados.tipificacao);
                    if (dados.apreensao) $('#inputApreensao').val(dados.apreensao);
                    if (dados.cartorio) $('#inputCartorio').val(dados.cartorio);

                    // Envolvidos
                    if (dados.envolvidos) {
                        if (dados.envolvidos.vitimas && dados.envolvidos.vitimas.length > 0) {
                            dados.envolvidos.vitimas.forEach(nome => adicionarNome('vitimas', nome));
                        }
                        if (dados.envolvidos.autores && dados.envolvidos.autores.length > 0) {
                            dados.envolvidos.autores.forEach(nome => adicionarNome('autores', nome));
                        }
                        if (dados.envolvidos.testemunhas && dados.envolvidos.testemunhas.length > 0) {
                            dados.envolvidos.testemunhas.forEach(nome => adicionarNome('testemunhas', nome));
                        }
                        if (dados.envolvidos.capturados && dados.envolvidos.capturados.length > 0) {
                            dados.envolvidos.capturados.forEach(nome => adicionarNome('capturados', nome));
                        }
                        if (dados.envolvidos.outros && dados.envolvidos.outros.length > 0) {
                            dados.envolvidos.outros.forEach(nome => adicionarNome('outros', nome));
                        }
                    }
                    // Remover localStorage antigo

                    // Fechar modal de importação e mostrar sucesso
                    setTimeout(function () {
                        var elModal = document.getElementById('modalImportarDadosBoe');
                        if (elModal) {
                            var bsModal = bootstrap.Modal.getOrCreateInstance(elModal);
                            bsModal.hide();
                        }
                    }, 800);
                    mostrarModalSucesso('Dados do BOE importados com sucesso! Formulário preenchido.');
                } else {
                    mostrarModalErro('Erro na importação: Resposta inválida.');
                }
            },
            error: function (xhr) {
                finalizarProgressoAdmin(false);
                $('#btnProcessarImportacaoBoe').prop('disabled', false).html('<i class="bi bi-file-earmark-check-fill me-1"></i> Reprocessar');
                let msgErro = 'Erro ao processar os dados. Tente novamente.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msgErro = xhr.responseJSON.message;
                }
                mostrarModalErro(msgErro);
            },
            complete: function () {
                $('#btnProcessarImportacaoBoe').prop('disabled', false).html('<i class="bi bi-file-earmark-check-fill me-1"></i> Extrair Dados do BOE');
            }
        });
    }

    function mostrarToast(mensagem, tipo = 'success') {
        const icones = {
            'success': 'bi-check-circle-fill',
            'danger': 'bi-x-circle-fill',
            'warning': 'bi-exclamation-triangle-fill',
            'info': 'bi-info-circle-fill'
        };

        // Configuração de cores de fundo e texto para cada tipo
        const estilos = {
            'success': { bg: 'bg-success', text: 'text-white', btnClose: 'btn-close-white' },
            'danger': { bg: 'bg-danger', text: 'text-white', btnClose: 'btn-close-white' },
            'warning': { bg: 'bg-warning', text: 'text-dark', btnClose: '' }, // Warning geralmente fica melhor com texto escuro
            'info': { bg: 'bg-info', text: 'text-white', btnClose: 'btn-close-white' }
        };

        const estilo = estilos[tipo] || { bg: 'bg-primary', text: 'text-white', btnClose: 'btn-close-white' };

        const toastHtml = `
            <div class="toast align-items-center border-0 mb-2 shadow-lg ${estilo.bg} ${estilo.text}" role="alert" aria-live="assertive" aria-atomic="true" style="opacity: 1;">
                <div class="d-flex">
                    <div class="toast-body fw-bold">
                        <i class="bi ${icones[tipo] || 'bi-info-circle'} fs-5 me-2"></i>
                        ${mensagem}
                    </div>
                    <button type="button" class="btn-close ${estilo.btnClose} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const $toast = $(toastHtml);
        $('.toast-container').append($toast);

        const toast = new bootstrap.Toast($toast[0], { delay: 500 });
        toast.show();

        $toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    function mostrarModalSucesso(mensagem) {
        window.mostrarSucesso(mensagem);
    }

    function mostrarModalErro(mensagem) {
        window.mostrarErro(mensagem);
    }

    // ========== EVENTOS ADICIONAIS ==========

    $(document).on('click', '.btn-selecionar-administrativo', function (e) {
        e.stopPropagation();
        var id = $(this).data('id');
        if (!id) id = $(this).attr('data-id');
        if (!id) id = $(this).closest('tr').data('id');
        if (!id) id = $(this).closest('tr').attr('data-id');
        id = (id !== undefined && id !== null) ? String(id).trim() : '';
        console.log('📥 Selecionar Administrativo ID:', id);
        if (!id) { mostrarModalErro('ID do registro não encontrado'); return; }
        buscarAdministrativo(id);

        $('#tabelaResultadosAdministrativo tbody tr').removeClass('table-active');
        $(this).closest('tr').addClass('table-active');
    });

    $('#tabelaResultadosAdministrativo tbody').off('click', 'tr').on('click', 'tr', function () {
        $('#tabelaResultadosAdministrativo tbody tr').removeClass('table-active');
        $(this).addClass('table-active');
    });

    $(document).on('click', '.btn-detalhes-relatorio', function () {
        const id = $(this).data('id');
        mostrarDetalhesRelatorio(id);
    });

    // ========== INICIALIZAÇÃO ==========

    function inicializarAdministrativo() {
        console.log('🚀 Inicializando Sistema Administrativo Avançado...');

        // Preencher data atual no formulário
        $('#inputDataCadastro').val(new Date().toLocaleDateString('pt-BR'));

        // Configurar estados iniciais dos botões
        $('#btnEditarAdministrativo').prop('disabled', true);
        $('#btnExcluirAdministrativo').prop('disabled', true);

        // Carregar últimos registros
        carregarUltimosRegistros();

        // Configurar data/hora atual (REMOVIDO: Já gerenciado pelo menu_lateral.js)
        // atualizarDataHora(); 
        // setInterval(atualizarDataHora, 1000);

        console.log('✅ Sistema Administrativo Avançado inicializado com sucesso!');
    }

    // Função atualizarDataHora removida pois conflitava com menu_lateral.js

    // Inicializar o sistema
    inicializarAdministrativo();
});
