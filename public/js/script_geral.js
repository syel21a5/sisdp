// script_geral.js - Código reorganizado para o formulário geral

// Global Modal Helpers removidos. Agora residentes em public/js/core.js


// Objeto principal para encapsular toda a funcionalidade
const OcorrenciasApp = {
    init: function() {
        this.currentId = null;
        this.setupMasks();
        this.setupAutocomplete();
        this.bindEvents();
        this.observarMudancasFormularioGeral(); // NOVO: Observar mudanças para transmitir dados
        this.resetForms();
        this.preencherDataAtual();
    },

    setupMasks: function() {
        try {
            if ($.fn.mask) {
                $('#inputData').mask('00/00/0000');
                $('#inputIP').mask('0000.0000.000000-00', {
                    placeholder: "____.____.______-__",
                    reverse: true
                });
            }
        } catch(e) {
            console.warn('Máscaras não carregadas:', e);
        }
    },

    setupAutocomplete: function() {
        const dadosAutocomplete = {
            delegacias: ["167ª Circunscrição", "Delegacia de Plantão", "Delegacia da Mulher"],
            cidades: ["Afogados da Ingazeira", "Recife", "Caruaru", "Petrolina", "Garanhuns"],
            delegados: ["Leandro Miranda Mai", "Israel Lima Braga Rubis", "Joedna Maria Soares Gomes", "Antonio Junior de Lima e Silva", "Vicktor de Araújo Melo"],
            escrivoes: ["Vandeleys da Silva Lima", "Marcos Antonio da Silva"]
        };
        $("#inputDelegado").autocomplete({
            source: dadosAutocomplete.delegados,
            minLength: 2,
            delay: 300
        });
        $("#inputEscrivao").autocomplete({
            source: dadosAutocomplete.escrivoes,
            minLength: 2,
            delay: 300
        });
        $("#inputDelegacia").autocomplete({
            source: dadosAutocomplete.delegacias,
            minLength: 2,
            delay: 300
        });
        $("#inputCidade").autocomplete({
            source: dadosAutocomplete.cidades,
            minLength: 2,
            delay: 300
        });
    },

    bindEvents: function() {
        $('#inputData').on('change', () => this.preencherDatasAutomaticamente($('#inputData').val()));
        $('#btnPesquisar').click(() => this.carregarGrid());
        $(document).on('click', '.btn-selecionar', (e) => this.selecionarRegistro(e));
        $('#btnNovo').click(() => this.novoRegistro());
        $('#btnSalvar').click(() => this.salvarRegistro());
        $('#btnEditar').click(() => this.editarRegistro());
        $('#btnExcluir').click(() => this.confirmarExclusao());
        // $('#btnConfirmarExclusao').click(() => this.excluirRegistro()); // Removido: agora tratado pelo modal genérico
        $('#btnLimpar').click(() => this.limparFormularios());
        
        this.setupExtratorBoe();
    },

    setupExtratorBoe: function() {
        if (typeof window.CoreExtractor !== 'undefined') {
            const self = this;
            new window.CoreExtractor({
                suffix: 'Geral',
                extractUrl: '/apfd/importar-boe-texto',
                extractUrlIA: '/prompt/extrair-ia',
                onSuccess: function(response, motor) {
                    if(response.success && response.dados) {
                        const dados = response.dados;
                        window.autoresExtraidos = dados.autores || [];
                        
                        if (dados.boe) $('#inputBOE').val(dados.boe);
                        if (dados.delegacia) $('#inputDelegacia').val(dados.delegacia);
                        if (dados.cidade) $('#inputCidade').val(dados.cidade);
                        
                        if (dados.objetos_apreendidos) {
                            const apreensaoFormatada = typeof window.formatarObjetosApreendidos === 'function' 
                                ? window.formatarObjetosApreendidos(dados.objetos_apreendidos) 
                                : (Array.isArray(dados.objetos_apreendidos) ? dados.objetos_apreendidos.join('\n') : dados.objetos_apreendidos);
                            $('#inputApreensao').val(apreensaoFormatada);
                        }
                        
                        let envolvidosMap = new Map();
                        const detalhes = dados.envolvidos_detalhes || {};
                        
                        const mapeamentoTipos = [
                            { chave: 'vitimas', tipo: 'vitima', cor: 'linear-gradient(135deg, #fd7e14, #ca6510)' },
                            { chave: 'autores', tipo: 'autor', cor: 'linear-gradient(135deg, #dc3545, #9c2532)' },
                            { chave: 'testemunhas', tipo: 'testemunha', cor: 'linear-gradient(135deg, #0d6efd, #084298)' },
                            { chave: 'condutor', tipo: 'condutor', cor: 'linear-gradient(135deg, #0d6efd, #084298)' },
                            { chave: 'condutores', tipo: 'condutor', cor: 'linear-gradient(135deg, #0d6efd, #084298)' },
                            { chave: 'outros', tipo: 'outros', cor: 'linear-gradient(135deg, #6c757d, #495057)' }
                        ];

                        mapeamentoTipos.forEach(map => {
                            const lista = dados[map.chave] || [];
                            lista.forEach(nome => {
                                if (nome && nome.trim().length > 2 && !nome.toUpperCase().includes('NÃO INFORMADO') && !nome.toUpperCase().includes('NAO INFORMADO')) {
                                    const nomeNorm = nome.trim().toUpperCase();
                                    if (!envolvidosMap.has(nomeNorm)) {
                                        let obj = detalhes[nome] || detalhes[nomeNorm] || Object.values(detalhes).find(d => (d.nome || d.Nome || '').toUpperCase() === nomeNorm) || { nome: nomeNorm };
                                        obj.tipoOriginal = map.tipo;
                                        obj.corGradient = map.cor;
                                        envolvidosMap.set(nomeNorm, obj);
                                    }
                                }
                            });
                        });
                        
                        // Garante que nomes em detalhes que não vieram nas listas também apareçam como 'outros'
                        Object.keys(detalhes).forEach(nome => {
                            const nomeNorm = nome.trim().toUpperCase();
                            if (nomeNorm.length > 2 && !envolvidosMap.has(nomeNorm) && !nomeNorm.includes('NÃO INFORMADO') && !nomeNorm.includes('NAO INFORMADO')) {
                                let obj = detalhes[nome];
                                obj.tipoOriginal = 'outros';
                                obj.corGradient = 'linear-gradient(135deg, #6c757d, #495057)'; // CINZA
                                envolvidosMap.set(nomeNorm, obj);
                            }
                        });

                        const envolvidosExtraidos = Array.from(envolvidosMap.values());

                        if (envolvidosExtraidos.length > 0) {
                            $('#emptyStateEnvolvidos').addClass('d-none');
                            $('#populatedStateEnvolvidos').removeClass('d-none');
                            const container = $('#chipsEnvolvidosGeral');
                            container.empty();
                            
                            envolvidosExtraidos.forEach((env, index) => {
                                const jsonStr = JSON.stringify(env).replace(/"/g, '&quot;');
                                const nomeExibicao = (env.nome || env.Nome || 'SEM NOME').toUpperCase();
                                const corOriginal = env.corGradient || 'linear-gradient(135deg, #6c757d, #495057)';
                                const tipoOriginal = env.tipoOriginal || 'outros';
                                const chipHtml = `
                                    <div class="d-inline-flex align-items-center gap-2 chip-envolvido-extraido border-0 shadow-sm" data-papel="${tipoOriginal}" data-json="${jsonStr}" data-cor-original="${corOriginal}" title="Clique para preencher o formulário" style="background: ${corOriginal}; color: white; border-radius: 6px; padding: 0.4rem 0.6rem; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; cursor: pointer; user-select: none;">
                                        <i class="bi bi-person-badge fs-5 pe-none"></i>
                                        <span class="pe-none">${nomeExibicao}</span>
                                        <span class="badge bg-light text-dark shadow-sm tipo-badge pe-none" style="font-size:0.65rem; border-radius: 4px;">${tipoOriginal.toUpperCase()}</span>
                                        
                                        <div class="dropdown ms-1">
                                            <button type="button" class="btn btn-sm btn-link text-white p-0 m-0 border-0 dropdown-toggle-no-caret btn-trocar-papel" data-bs-toggle="dropdown" aria-expanded="false" title="Trocar Papel">
                                                <i class="bi bi-arrow-left-right fs-6"></i>
                                            </button>
                                            <ul class="dropdown-menu shadow">
                                                <li><a class="dropdown-item mudar-papel-item" href="#" data-papel="vítima" data-cor="linear-gradient(135deg, #fd7e14, #ca6510)"><i class="bi bi-circle-fill text-warning me-2"></i> Vítima</a></li>
                                                <li><a class="dropdown-item mudar-papel-item" href="#" data-papel="autor" data-cor="linear-gradient(135deg, #dc3545, #9c2532)"><i class="bi bi-circle-fill text-danger me-2"></i> Autor</a></li>
                                                <li><a class="dropdown-item mudar-papel-item" href="#" data-papel="testemunha" data-cor="linear-gradient(135deg, #0d6efd, #084298)"><i class="bi bi-circle-fill text-primary me-2"></i> Testemunha</a></li>
                                                <li><a class="dropdown-item mudar-papel-item" href="#" data-papel="condutor" data-cor="linear-gradient(135deg, #0d6efd, #084298)"><i class="bi bi-circle-fill text-info me-2"></i> Condutor</a></li>
                                                <li><a class="dropdown-item mudar-papel-item" href="#" data-papel="outros" data-cor="linear-gradient(135deg, #6c757d, #495057)"><i class="bi bi-circle-fill text-secondary me-2"></i> Outros</a></li>
                                            </ul>
                                        </div>
                                        
                                        <button type="button" class="btn btn-sm btn-link text-white p-0 m-0 ms-1 border-0 btn-deletar-chip" title="Remover Envolvido">
                                            <i class="bi bi-x-circle fs-6"></i>
                                        </button>
                                    </div>
                                `;
                                container.append(chipHtml);
                            });
                            
                            // Adiciona efeito visual no hover
                            $('.chip-envolvido-extraido').hover(
                                function() { $(this).css('transform', 'translateY(-2px)').css('box-shadow', '0 4px 8px rgba(0,0,0,0.2)'); },
                                function() { $(this).css('transform', 'none').css('box-shadow', '0 .125rem .25rem rgba(0,0,0,.075)'); }
                            );
                            
                            // Previne que clicar no botão dropdown ou no menu acione a função do chip
                            $('.btn-trocar-papel').off('click').on('click', function(e) {
                                e.stopPropagation();
                            });
                            $('.dropdown-menu').off('click').on('click', function(e) {
                                e.stopPropagation();
                            });
                            
                            // Botão para deletar o chip
                            $('.btn-deletar-chip').off('click').on('click', function(e) {
                                e.stopPropagation();
                                $(this).closest('.chip-envolvido-extraido').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            });
                            
                            $('.mudar-papel-item').off('click').on('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const btnItem = $(e.currentTarget);
                                const novoPapel = btnItem.attr('data-papel');
                                const novaCor = btnItem.attr('data-cor');
                                const chip = btnItem.closest('.chip-envolvido-extraido');
                                
                                if (novaCor) {
                                    chip.attr('data-cor-original', novaCor);
                                    chip.css('background', novaCor);
                                }
                                
                                if (novoPapel) {
                                    chip.attr('data-papel', novoPapel); // <-- ATUALIZA O ATRIBUTO NO CHIP AQUI
                                    chip.find('.tipo-badge').text(novoPapel.toUpperCase());
                                }
                                
                                // Fecha o dropdown com segurança sem afetar o chip
                                try {
                                    const dropdownBtn = chip.find('.btn-trocar-papel')[0];
                                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownBtn);
                                    if (dropdownInstance) {
                                        dropdownInstance.hide();
                                    } else {
                                        chip.find('.dropdown-menu').removeClass('show');
                                    }
                                } catch (err) {
                                    chip.find('.dropdown-menu').removeClass('show');
                                }
                            });
                            
                            $('.chip-envolvido-extraido').off('click').on('click', function() {
                                const btn = $(this);
                                const dadosEnv = JSON.parse(btn.attr('data-json'));
                                
                                $('#inputNomeCondutor').val(dadosEnv.nome || dadosEnv.Nome || '');
                                $('#inputAlcunha').val(dadosEnv.alcunha || dadosEnv.Alcunha || '');
                                $('#inputDataNascimento').val(dadosEnv.data_nascimento || dadosEnv.nascimento || dadosEnv.Nascimento || '').trigger('change');
                                $('#inputEstadoCivil').val(dadosEnv.estado_civil || dadosEnv.EstCivil || '');
                                $('#inputNaturalidade').val(dadosEnv.naturalidade || dadosEnv.Naturalidade || '');
                                $('#inputInstrucao').val(dadosEnv.instrucao || dadosEnv.Instrucao || '');
                                $('#inputRG').val(dadosEnv.rg || dadosEnv.RG || '');
                                $('#inputCPF').val(dadosEnv.cpf || dadosEnv.CPF || '');
                                $('#inputTelefone').val(dadosEnv.telefone || dadosEnv.Telefone || '');
                                $('#inputProfissao').val(dadosEnv.profissao || dadosEnv.Profissao || '');
                                $('#inputMae').val(dadosEnv.mae || dadosEnv.Mae || '');
                                $('#inputPai').val(dadosEnv.pai || dadosEnv.Pai || '');
                                $('#inputEndereco').val(dadosEnv.endereco || dadosEnv.Endereco || '');
                                
                                $('#abasPrincipais a[href="#aba-condutor"]').tab('show');
                                
                                $('#formCondutor input').addClass('is-valid');
                                setTimeout(() => { $('#formCondutor input').removeClass('is-valid'); }, 2000);
                            });
                            
                        } else {
                            $('#emptyStateEnvolvidos').removeClass('d-none');
                            $('#populatedStateEnvolvidos').addClass('d-none');
                        }
                        
                        var modalEl = document.getElementById('modalImportarBoeGeral');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        
                        self.mostrarSucesso('Extração concluída. Clique nos envolvidos extraídos para preencher os dados.');
                    } else {
                        self.mostrarErro('Falha na extração dos dados.');
                    }
                }
            });
        }
    },

    // NOVA FUNÇÃO: Observar mudanças no formulário geral para transmitir dados
    observarMudancasFormularioGeral: function() {
        // Campos do formulário geral que devem ser transmitidos
        const camposObservados = [
            '#inputDelegado',
            '#inputEscrivao',
            '#inputDelegacia',
            '#inputCidade',
            '#inputBOE'
        ];

        camposObservados.forEach(function(seletor) {
            $(seletor).on('change input', function() {
                const dadosGeral = {
                    delegado: $('#inputDelegado').val(),
                    escrivao: $('#inputEscrivao').val(),
                    delegacia: $('#inputDelegacia').val(),
                    cidade: $('#inputCidade').val(),
                    boe: $('#inputBOE').val()
                };

                // Disparar evento para a intimação
                $(document).trigger('dadosGeralAlterados', [dadosGeral]);
                console.log('📤 Dados do GERAL transmitidos para INTIMAÇÃO:', dadosGeral);
            });
        });
    },

    carregarGrid: function() {
        const filtro = $('#ddlFiltro').val();
        const termo = $('#txtPesquisa').val().trim();
        if (!termo) {
            $('#gridResultados tbody').html('<tr><td colspan="4" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }
        $.ajax({
            url: rotas.geral.pesquisar,
            method: "GET",
            data: { filtro, termo },
            success: (response) => {
                const tbody = $('#gridResultados tbody');
                tbody.empty();
                if (response.data?.length > 0) {
                    response.data.forEach(item => {
                        tbody.append(`
                            <tr>
                                <td>${item.BOE || ''}</td>
                                <td>${item.IP || ''}</td>
                                <td>${item.data_formatada || ''}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success btn-selecionar" data-id="${item.id}">
                                        Selecionar
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
                }
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#gridResultados tbody').html(`<tr><td colspan="4" class="text-center">${errorMsg}</td></tr>`);
            }
        });
    },

    selecionarRegistro: function(e) {
        e.preventDefault();
        const id = $(e.currentTarget).data('id');
        this.currentId = id;
        $.ajax({
            url: `${rotas.geral.buscar}/${id}`,
            method: "GET",
            success: (response) => {
                if (response.success) {
                    // Dados Pessoais
                    $('#inputData').val(response.data.data_formatada || '');
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
                    $('#inputHoraFato').val(response.data.hora_fato || '');
                    $('#inputEndFato').val(response.data.end_fato || '');
                    $('#inputMeiosEmpregados').val(response.data.meios_empregados || '');
                    $('#inputMotivacao').val(response.data.motivacao || '');
                    $('#inputIncidenciaPenal').val(response.data.incidencia_penal || '');
                    $('#inputComarca').val(response.data.comarca || '');
                    $('#inputStatus').val(response.data.status || '');
                    // Apreensão
                    $('#inputApreensao').val(response.data.Apreensao || '');
                    $('#btnEditar').prop('disabled', false);
                    $('#btnExcluir').prop('disabled', false);

                    // TRANSMITIR DADOS PARA INTIMAÇÃO AO SELECIONAR REGISTRO
                    const dadosGeral = {
                        delegado: response.data.delegado || '',
                        escrivao: response.data.escrivao || '',
                        delegacia: response.data.delegacia || '',
                        cidade: response.data.cidade || '',
                        boe: response.data.BOE || ''
                    };
                    $(document).trigger('dadosGeralAlterados', [dadosGeral]);

                } else {
                    this.mostrarErro(response.message);
                }
            },
            error: (xhr) => {
                this.mostrarErro('Erro ao carregar registro: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    },

    novoRegistro: function() {
        this.currentId = null;
        this.resetForms();
        this.preencherDataAtual();
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);
    },

    salvarRegistro: function() {
        if (!this.validarCamposObrigatorios()) return;

        const $btn = $('#btnSalvar');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

        const formData = $('#formInicio').serializeArray();
        const formDocumentos = $('#documentos form').serializeArray();
        const formDadosComplementares = $('#dados-complementares form').serializeArray();
        const formApreensao = $('#apreensao form').serializeArray();
        const allData = formData.concat(formDocumentos, formDadosComplementares, formApreensao);

        $.ajax({
            url: rotas.geral.salvar,
            method: "POST",
            data: allData,
            success: (response) => {
                if (response.success) {
                    this.currentId = response.id || this.currentId;
                    this.mostrarSucesso('Registro salvo com sucesso!');

                    // === ADICIONA O NOVO REGISTRO NA GRID ===
                    const boe = $('#inputBOE').val().trim() || 'N/A';
                    const ip = $('#inputIP').val().trim() || 'N/A';
                    const data = $('#inputData').val().trim() || 'N/A';
                    const id = this.currentId;

                    const tbody = $('#gridResultados tbody');

                    // Se a tabela tem mensagem de "nenhum" ou está vazia, limpa para adicionar
                    if (tbody.find('td[colspan]').length || tbody.text().includes('Nenhum')) {
                        tbody.empty();
                    }

                    // Cria a nova linha (SEM a classe table-success inicialmente)
                    const novaLinha = `
                        <tr>
                            <td>${boe}</td>
                            <td>${ip}</td>
                            <td>${data}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success btn-selecionar" data-id="${id}">
                                    Selecionar
                                </button>
                            </td>
                        </tr>
                    `;

                    // Adiciona no topo (mais recente) e converte para objeto jQuery
                    const $novaLinha = $(novaLinha);
                    tbody.prepend($novaLinha);

                    // Aplica o destaque verde e configura para remover após 2 segundos
                    $novaLinha.addClass('table-success');
                    setTimeout(() => {
                        $novaLinha.removeClass('table-success');
                    }, 2000);

                    $('#btnEditar').prop('disabled', false);
                    $('#btnExcluir').prop('disabled', false);
                } else {
                    this.mostrarErro(response.message || 'Erro ao salvar');
                }
            },
            error: (xhr) => {
                const mensagem = xhr.responseJSON?.message || 'Erro ao salvar | Boletim de Ocorrência já cadastrado no sistema!';
                this.mostrarErro(mensagem);
            },
            complete: () => {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    },
    editarRegistro: function() {
        if (!this.currentId) {
            this.mostrarErro('Nenhum registro selecionado para editar.');
            return;
        }
        if (!this.validarCamposObrigatorios()) return;

        const $btn = $('#btnEditar');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Atualizando...');

        const formData = $('#formInicio').serializeArray();
        const formDocumentos = $('#documentos form').serializeArray();
        const formDadosComplementares = $('#dados-complementares form').serializeArray();
        const formApreensao = $('#apreensao form').serializeArray();
        const allData = formData.concat(formDocumentos, formDadosComplementares, formApreensao);

        $.ajax({
            url: `${rotas.geral.atualizar}/${this.currentId}`,
            method: "PUT",
            data: allData,
            success: (response) => {
                if (response.success) {
                    this.mostrarSucesso('Registro atualizado com sucesso!');

                    // Atualiza a linha na grid
                    const boe = $('#inputBOE').val().trim() || 'N/A';
                    const ip = $('#inputIP').val().trim() || 'N/A';
                    const data = $('#inputData').val().trim() || 'N/A';
                    const id = this.currentId;

                    const $btn = $(`button.btn-selecionar[data-id="${id}"]`);
                    if ($btn.length) {
                        const $tr = $btn.closest('tr');
                        $tr.find('td').eq(0).text(boe);
                        $tr.find('td').eq(1).text(ip);
                        $tr.find('td').eq(2).text(data);
                        $tr.addClass('table-success');
                        setTimeout(() => $tr.removeClass('table-success'), 2000);
                    } else {
                        // Se não encontrar a linha (grid estava vazia), adiciona nova
                        const tbody = $('#gridResultados tbody');
                        if (tbody.find('td[colspan]').length || tbody.text().includes('Nenhum')) {
                            tbody.empty();
                        }
                        const novaLinha = `
                            <tr class="table-success">
                                <td>${boe}</td>
                                <td>${ip}</td>
                                <td>${data}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success btn-selecionar" data-id="${id}">
                                        Selecionar
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.prepend(novaLinha);
                        setTimeout(() => $(novaLinha).removeClass('table-success'), 2000);
                    }
                } else {
                    this.mostrarErro(response.message);
                }
            },
            error: (xhr) => {
                this.mostrarErro('Erro ao editar: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            },
            complete: () => {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    },

    confirmarExclusao: function() {
        if (!this.currentId) {
            this.mostrarErro('Selecione um registro para excluir!');
            return;
        }
        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este registro?', () => {
            this.excluirRegistro();
        });
    },

    excluirRegistro: function() {
        const termoPesquisa = $('#txtPesquisa').val().trim();
        const registroId = this.currentId;

        $.ajax({
            url: `${rotas.geral.excluir}/${this.currentId}`,
            method: "DELETE",
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: (response) => {
                if (response.success) {
                    this.mostrarSucesso('Registro excluído com sucesso!');
                    this.resetForms();
                    this.currentId = null;
                    $('#btnEditar').prop('disabled', true);
                    $('#btnExcluir').prop('disabled', true);

                    // Remove a linha do registro excluído
                    $(`button.btn-selecionar[data-id="${registroId}"]`).closest('tr').remove();

                    // Se a tabela ficar vazia
                    if ($('#gridResultados tbody tr').length === 0) {
                        $('#gridResultados tbody').html('<tr><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
                    }

                    // Recarrega apenas se houver pesquisa ativa e a grid estiver vazia
                    if (termoPesquisa && $('#gridResultados tbody tr td').text().includes('Nenhum')) {
                        this.carregarGrid();
                    }
                } else {
                    this.mostrarErro(response.message);
                }
            },
            error: (xhr) => {
                this.mostrarErro('Erro ao excluir: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    },

    limparFormularios: function() {
        this.currentId = null;
        this.resetForms();
        this.preencherDataAtual();
        $('#btnEditar').prop('disabled', true);
        $('#btnExcluir').prop('disabled', true);
    },

    resetForms: function() {
        // Limpa formulário principal
        $('#formInicio')[0]?.reset();

        // Limpa formulário de documentos (se existir)
        document.getElementById('formDocumentos')?.reset();

        // Limpa textarea de apreensão diretamente (abordagem alternativa)
        $('#inputApreensao').val('');

        // Preenche data atual
        this.preencherDataAtual();

        // Debug: verifica se os elementos foram encontrados
        console.log('FormInicio:', document.getElementById('formInicio'));
        console.log('FormApreensao:', document.getElementById('formApreensao'));
        console.log('inputApreensao:', document.getElementById('inputApreensao'));
    },

    validarCamposObrigatorios: function() {
        const required = [
            { sel: '#inputData', label: 'Data' },
            { sel: '#inputDelegado', label: 'Delegado' },
            { sel: '#inputDelegacia', label: 'Delegacia' },
            { sel: '#inputBOE', label: 'BOE' }
        ];

        const missing = required.filter(r => !$(r.sel).val());
        if (missing.length) {
            const nomes = missing.map(m => m.label).join(', ');
            this.mostrarErro(`Preencha os campos obrigatórios: ${nomes}`);
            missing.forEach(r => this.realcarCampoInvalido(r.sel, `Informe ${r.label}`));
            return false;
        }
        return true;
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

    preencherDatasAutomaticamente: function(dataInput) {
        let dataObj;
        if (dataInput) {
            const partes = dataInput.split('/');
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

    preencherDataAtual: function() {
        $('#inputData').val(new Date().toLocaleDateString('pt-BR'));
        this.preencherDatasAutomaticamente($('#inputData').val());
    },

    mostrarSucesso: function(mensagem) {
        if (typeof window.mostrarSucesso === 'function') {
            window.mostrarSucesso(mensagem);
        } else {
            console.log(mensagem);
            alert(mensagem);
        }
    },

    mostrarErro: function(mensagem) {
        if (typeof window.mostrarErro === 'function') {
            window.mostrarErro(mensagem);
        } else {
            console.error(mensagem);
            alert(mensagem);
        }
    }
};

// Inicializa a aplicação quando o documento estiver pronto
$(document).ready(function() {
    OcorrenciasApp.init();
});
