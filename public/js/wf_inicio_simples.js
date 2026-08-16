/**
 * wf_inicio_simples.js — Tela inicial simplificada (dados descartáveis, nada salvo no banco)
 * Fluxo: importa BOE (motor nativo) → chips voláteis → clica no chip → formulário único → imprime peça.
 */
(function () {
    'use strict';

    // Estado local (somente memória — nada persiste)
    let envolvidosExtraidos = [];   // lista de envolvidos do BOE
    let envolvidoSelecionado = null; // envolvido atual no formulário

    // Papel original no BOE (só p/ agrupamento visual; o formulário é único/genérico)
    const PAPEL_LABEL = {
        condutores: 'Condutor', vitimas: 'Vítima', autores: 'Autor',
        testemunhas: 'Testemunha', outros: 'Outro'
    };

    // ---------- AUTCOMPLETE DE DOCUMENTOS (mesmas rotas da home) ----------
    const DOCS = (typeof rotasImpressaoInicio !== 'undefined')
        ? Object.keys(rotasImpressaoInicio).sort()
        : [];

    $('#termoDocumentoSimples').autocomplete({
        source: DOCS,
        minLength: 0,
        select: function (e, ui) { $(this).val(ui.item.value); }
    }).on('focus', function () { $(this).autocomplete('search', ''); });

    // ---------- IMPORTAR BOE (abre o modal) ----------
    $('#btnImportarBoeSimples').on('click', function () {
        $('#textoBoeSimples').val('');
        const tabTexto = document.getElementById('tab-textoSimples');
        if (tabTexto) new bootstrap.Tab(tabTexto).show();
        const modal = new bootstrap.Modal(document.getElementById('modalImportarBoeSimples'));
        modal.show();
    });

    // ---------- PROCESSAR: Motor Nativo (btnProcessarBoeSimples) ----------
    $('#btnProcessarBoeSimples').on('click', function () {
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Extraindo...');

        let formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Detecta aba ativa (texto ou pdf)
        const pdfAtivo = document.getElementById('tab-pdfSimples') &&
            document.getElementById('tab-pdfSimples').classList.contains('active');
        const txtAtivo = document.getElementById('tab-textoSimples') &&
            document.getElementById('tab-textoSimples').classList.contains('active');

        if (pdfAtivo) {
            const fileInput = document.getElementById('pdfBoeSimples');
            if (fileInput && fileInput.files.length > 0) {
                formData.append('pdfBOE', fileInput.files[0]);
            } else {
                window.mostrarErro ? window.mostrarErro('Selecione um arquivo PDF.') : alert('Selecione um arquivo PDF.');
                $btn.prop('disabled', false).html(originalHtml);
                return;
            }
        } else {
            const texto = $('#textoBoeSimples').val() || '';
            if (!texto.trim()) {
                window.mostrarErro ? window.mostrarErro('Cole o texto do BOE ou envie um PDF.') : alert('Cole o texto do BOE ou envie um PDF.');
                $btn.prop('disabled', false).html(originalHtml);
                return;
            }
            formData.append('texto', texto);
        }

        $.ajax({
            url: '/boe/extrair-nativo',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $btn.prop('disabled', false).html(originalHtml);
                if (response.success && response.dados) {
                    const dados = response.dados;

                    // Preenche dados gerais (descartáveis)
                    if (dados.boe) $('#inputBOESimples').val(dados.boe);
                    if (dados.boe_pm) $('#inputBOEPMSimples').val(dados.boe_pm);
                    if (dados.ip) $('#inputIPSimples').val(dados.ip);
                    if (dados.data_fato) $('#inputDataSimples').val(dados.data_fato);
                    if (dados.natureza || dados.incidencia_penal) $('#inputNaturezaSimples').val(dados.natureza || dados.incidencia_penal);

                    // Monta lista de envolvidos (volátil)
                    envolvidosExtraidos = [];
                    ['condutores', 'vitimas', 'autores', 'testemunhas', 'outros'].forEach(function (tipo) {
                        const arr = dados[tipo] || dados[tipo.replace(/s$/, '')] || [];
                        (Array.isArray(arr) ? arr : []).forEach(function (nome) {
                            if (typeof nome === 'string' && nome.trim().length > 2 &&
                                !nome.toUpperCase().includes('NÃO INFORMADO') &&
                                !nome.toUpperCase().includes('NAO INFORMADO')) {
                                envolvidosExtraidos.push({ nome: nome.trim(), papel: tipo });
                            }
                        });
                    });

                    // Detalhes por nome (envolvidos_detalhes) — preenche o formulário único depois
                    if (dados.envolvidos_detalhes) {
                        window.__detalhesEnvolvidos = dados.envolvidos_detalhes;
                    }

                    renderizarChips();

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalImportarBoeSimples'));
                    if (modal) modal.hide();

                    window.mostrarSucesso ? window.mostrarSucesso('✅ Dados extraídos pelo Motor Nativo. Nada foi salvo.') : alert('✅ Dados extraídos pelo Motor Nativo. Nada foi salvo.');
                } else {
                    window.mostrarErro ? window.mostrarErro(response.message || 'Falha na extração.') : alert(response.message || 'Falha na extração.');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Erro ao processar.';
                window.mostrarErro ? window.mostrarErro(msg) : alert(msg);
            }
        });
    });

    // ---------- CHIPS VOLÁTEIS ----------
    function renderizarChips() {
        const $container = $('#chipsEnvolvidosSimples');
        $container.empty();

        if (envolvidosExtraidos.length === 0) {
            $container.html('<span class="text-muted small">Nenhum envolvido identificado no BOE.</span>');
            return;
        }

        envolvidosExtraidos.forEach(function (env, idx) {
            const papel = PAPEL_LABEL[env.papel] || 'Envolvido';
            const chip = $('<button>', {
                type: 'button',
                class: 'btn btn-sm rounded-pill border d-inline-flex align-items-center gap-1',
                css: { backgroundColor: '#eef4ff', borderColor: '#b6d0f7', color: '#0d3b8f' },
                'data-index': idx
            }).html(`<i class="bi bi-person-badge"></i> ${env.nome} <span class="badge bg-info-subtle text-info-emphasis">${papel}</span>`);
            chip.on('click', function () { selecionarEnvolvido(idx); });
            $container.append(chip);
        });
    }

    // ---------- SELECIONAR ENVOLVIDO → formulário único ----------
    function selecionarEnvolvido(idx) {
        const env = envolvidosExtraidos[idx];
        if (!env) return;
        envolvidoSelecionado = env;

        // Busca detalhes por nome (se disponíveis)
        const detalhes = (window.__detalhesEnvolvidos || {})[env.nome] || {};

        $('#inputNomeEnvolvidoSimples').val(env.nome.toUpperCase());
        $('#inputAlcunhaSimples').val(detalhes.alcunha || '');
        $('#inputNascimentoSimples').val(detalhes.nascimento || '');
        $('#inputIdadeSimples').val(detalhes.idade || '');
        $('#inputRGSimples').val(detalhes.rg || '');
        $('#inputCPFSimples').val(detalhes.cpf || '');
        $('#inputProfissaoSimples').val(detalhes.profissao || '');
        $('#inputEstCivilSimples').val(detalhes.estcivil || detalhes.estado_civil || '');
        $('#inputNaturalidadeSimples').val(detalhes.naturalidade || '');
        $('#inputTelefoneSimples').val(detalhes.telefone || '');
        $('#inputMaeSimples').val(detalhes.mae || '');
        $('#inputPaiSimples').val(detalhes.pai || '');
        $('#inputEnderecoSimples').val(detalhes.endereco || '');
        $('#inputTipoPenalSimples').val(detalhes.tipopenal || '');

        $('#cardFormEnvolvido').show();
        $('#cardFormEnvolvido')[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    $('#btnLimparEnvolvidoSimples').on('click', function () {
        envolvidoSelecionado = null;
        $('#cardFormEnvolvido input, #cardFormEnvolvido textarea').val('');
        $('#cardFormEnvolvido').hide();
    });

    // ---------- IMPRIMIR (monta dados descartáveis e envia) ----------
    $('#btnImprimirSimples').on('click', function () {
        const documento = ($('#termoDocumentoSimples').val() || '').trim().toUpperCase();
        if (!documento) {
            window.mostrarErro ? window.mostrarErro('Selecione o documento a gerar.') : alert('Selecione o documento a gerar.');
            return;
        }
        if (typeof rotasImpressaoInicio === 'undefined' || !rotasImpressaoInicio[documento]) {
            window.mostrarErro ? window.mostrarErro(`Documento "${documento}" não configurado.`) : alert(`Documento "${documento}" não configurado.`);
            return;
        }

        // Monta o objeto de dados (mesmo formato do capturarDadosGlobais da home)
        const dados = {
            data: $('#inputDataSimples').val() || '',
            data_comp: '',
            data_ext: $('#inputDataExtSimples').val() || '',
            cidade: ($('#inputCidadeSimples').val() || '').toUpperCase(),
            delegado: ($('#inputDelegadoSimples').val() || '').toUpperCase(),
            escrivao: ($('#inputEscrivaoSimples').val() || '').toUpperCase(),
            delegacia: ($('#inputDelegaciaSimples').val() || '').toUpperCase(),
            boe: $('#inputBOESimples').val() || '',
            boe_pm: $('#inputBOEPMSimples').val() || '',
            ip: $('#inputIPSimples').val() || '',
            natureza: ($('#inputNaturezaSimples').val() || '').toUpperCase(),
            incidencia_penal: ($('#inputNaturezaSimples').val() || '').toUpperCase(),
            policial_1: '', policial_2: '', local_fato: '', hora_fato: '', apreensao: ''
        };

        // Envolvido único (formulário) — vira autor1/condutor/etc genérico
        if (envolvidoSelecionado || $('#inputNomeEnvolvidoSimples').val()) {
            const nome = ($('#inputNomeEnvolvidoSimples').val() || envolvidoSelecionado?.nome || '').toUpperCase();
            const pessoa = {
                nome: nome,
                alcunha: $('#inputAlcunhaSimples').val() || '',
                nascimento: $('#inputNascimentoSimples').val() || '',
                idade: $('#inputIdadeSimples').val() || '',
                rg: $('#inputRGSimples').val() || '',
                cpf: $('#inputCPFSimples').val() || '',
                profissao: $('#inputProfissaoSimples').val() || '',
                estcivil: $('#inputEstCivilSimples').val() || '',
                naturalidade: $('#inputNaturalidadeSimples').val() || '',
                telefone: $('#inputTelefoneSimples').val() || '',
                mae: $('#inputMaeSimples').val() || '',
                pai: $('#inputPaiSimples').val() || '',
                endereco: $('#inputEnderecoSimples').val() || '',
                tipopenal: $('#inputTipoPenalSimples').val() || ''
            };
            dados.autor1 = pessoa;
            dados.condutor = pessoa;
            dados.condutores = [pessoa];
            dados.autores = [pessoa];
            dados.vitimas = [];
            dados.testemunhas = [];
            dados.outros = [];
        } else {
            dados.autores = []; dados.vitimas = []; dados.testemunhas = []; dados.outros = []; dados.condutores = [];
        }

        const rota = rotasImpressaoInicio[documento];

        // POST (evita URI too long) — mesmo padrão da home
        if (!rota.includes('--DADOS--')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = rota;
            form.target = '_blank';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token';
            csrf.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrf);
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'dados';
            input.value = JSON.stringify(dados);
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            return;
        }

        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
        window.open(rota.replace('--DADOS--', dadosCodificados), '_blank');
    });

})();
