// script_outros.js - VERSÃO ADAPTADA PARA OUTROS
$(document).ready(function () {
    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    $('#inputDataNascimentoOutro').mask('00/00/0000');
    $('#inputCPFOutro').mask('000.000.000-00', { reverse: true });
    $('#inputTelefoneOutro').mask('(00) 00000-0000');

    // === VARIÁVEL GLOBAL ===
    let currentOutroId = null;

    // ✅ FUNÇÃO CORRIGIDA: SALVAR VÍNCULO BOE-OUTRO
    function salvarVinculoBoeOutro() {
        const boe = $('#inputBOE').val().trim();
        const outroId = currentOutroId || $('#outro_id').val();

        console.log('💾 SALVANDO VÍNCULO BOE-OUTRO:', { boe, outroId });

        if (!boe || boe === 'N/A' || !outroId) {
            console.log('⚠️ Dados insuficientes para salvar vínculo:', { boe, outroId });
            return;
        }

        $.ajax({
            url: '/boe/vinculos/salvar',
            method: 'POST',
            data: {
                boe: boe,
                outro_id: outroId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO OUTRO SALVO COM SUCESSO:', response);
                } else {
                    console.error('❌ ERRO AO SALVAR VÍNCULO OUTRO:', response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AJAX AO SALVAR VÍNCULO OUTRO:', xhr.responseJSON);
            }
        });
    }

    // ✅ FUNÇÃO CORRIGIDA: EXCLUIR VÍNCULO ESPECÍFICO DO OUTRO
    function excluirVinculoBoeOutro() {
        const boe = $('#inputBOE').val().trim();

        if (!boe || boe === 'N/A') {
            console.log('⚠️ BOE não disponível para excluir vínculo outro');
            return;
        }

        console.log('🗑️ EXCLUINDO VÍNCULO OUTRO DO BOE:', boe);

        $.ajax({
            url: '/boe/vinculos/excluir-outro/' + encodeURIComponent(boe),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO OUTRO EXCLUÍDO COM SUCESSO');
                } else {
                    console.log('ℹ️ Vínculo outro não encontrado ou já excluído');
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AO EXCLUIR VÍNCULO OUTRO:', xhr.responseJSON);
            }
        });
    }

    // === FUNÇÕES AUXILIARES ===
    function calcularIdade(dataNascimento) {
        if (!dataNascimento) return '';
        const partes = dataNascimento.split('/');
        if (partes.length !== 3) return '';
        const nascimento = new Date(`${partes[2]}-${partes[1]}-${partes[0]}`);
        if (isNaN(nascimento)) return '';
        const hoje = new Date();
        let idade = hoje.getFullYear() - nascimento.getFullYear();
        const m = hoje.getMonth() - nascimento.getMonth();
        if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) idade--;
        return idade;
    }

    function validarCPF(cpf) {
        if (!cpf) return false;
        const limpo = cpf.replace(/[^\d]+/g, '');
        if (limpo === '00000000000') return true;
        if (limpo === '' || limpo.length !== 11 || /^(\d)\1+$/.test(limpo)) return false;
        let soma = 0;
        for (let i = 0; i < 9; i++) soma += parseInt(limpo.charAt(i)) * (10 - i);
        let resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(limpo.charAt(9))) return false;
        soma = 0;
        for (let i = 0; i < 10; i++) soma += parseInt(limpo.charAt(i)) * (11 - i);
        resto = 11 - (soma % 11);
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(limpo.charAt(10))) return false;
        return true;
    }

    // === EVENTOS ===
    $('#inputDataNascimentoOutro').on('change', function () {
        $('#inputIdadeOutro').val(calcularIdade($(this).val()));
    });

    // === GRID DE PESQUISA DE OUTRO ===
    function carregarGridOutro(filtro, termo) {
        if (!termo) {
            $('#tabelaResultadosOutro tbody').html('<tr><td colspan="5" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }

        $.ajax({
            url: rotas.outro.pesquisar, // Precisa definir essa rota em rotas_impressao.js ou similar?
            method: "POST",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                const tbody = $('#tabelaResultadosOutro tbody').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const nasc = item.Nascimento ? item.Nascimento.split('-').reverse().join('/') : '';
                        const row = `
                            <tr>
                                <td>${item.Nome || ''}</td>
                                <td>${item.Mae || ''}</td>
                                <td>${nasc}</td>
                                <td>${item.CPF || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-outro" data-id="${item.IdCad}">Selecionar</button></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">Nenhum "outro" encontrado.</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#tabelaResultadosOutro tbody').html('<tr><td colspan="5" class="text-center">' + erro + '</td></tr>');
                console.error('Erro na pesquisa:', xhr);
            }
        });
    }

    $('#btnPesquisarOutro').click(function () {
        carregarGridOutro($('#filtroOutro').val(), $('#termoPesquisaOutro').val().trim());
    });

    // Máscara de CPF no campo de pesquisa de outro envolvido
    $('#termoPesquisaOutro').on('input', function() {
        if ($('#filtroOutro').val() !== 'CPF') return;
        let val = $(this).val().replace(/\D/g, '').substring(0, 11);
        if (val.length > 0) {
            val = val.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(val);
        }
    });
    $('#filtroOutro').on('change', function() {
        const c = $('#termoPesquisaOutro');
        c.val('');
        if ($(this).val() === 'CPF') { c.attr('maxlength', 14).attr('placeholder', '000.000.000-00'); }
        else { c.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...'); }
    });

    // === BUSCAR POR ID (GLOBAL) ===
    window.buscarOutroPorId = function (id) {
        currentOutroId = id;
        $.ajax({
            url: rotas.outro.buscar + '/' + id,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const c = response.data;
                    window.preencherOutroVinculado(c);
                    // Salvar vínculo automaticamente se for edição via chip? 
                    // Talvez sim, mas o preencherOutroVinculado já habilita botões.
                    // Se for chamado via clique no chip, queremos apenas carregar.
                } else {
                    mostrarErro(response.message || 'Erro ao buscar dados');
                }
            },
            error: function (xhr) {
                mostrarErro('Erro ao carregar dados: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    };

    // === SELEÇÃO DE OUTRO DA GRID ===
    $(document).on('click', '.btn-selecionar-outro', function () {
        const id = $(this).data('id');
        window.buscarOutroPorId(id);
        // Ao selecionar da grid, assume-se que queremos vincular
        setTimeout(salvarVinculoBoeOutro, 500);
    });

    // ✅ FUNÇÃO PARA PREENCHER OUTRO VINCULADO (EXTERNA)
    window.preencherOutroVinculado = function (dados) {
        console.log('🔄 PREENCHENDO OUTRO DO VÍNCULO:', dados);

        // Helper: busca valor em PascalCase ou lowercase
        const g = (pascal, lower) => dados[pascal] || dados[lower] || '';

        currentOutroId = dados.IdCad || dados.id || null;

        $('#outro_id').val(currentOutroId || '');
        $('#inputNomeOutro').val(g('Nome', 'nome'));
        $('#inputAlcunhaOutro').val(g('Alcunha', 'alcunha'));

        // Nascimento: pode vir como "YYYY-MM-DD" (BD) ou "DD/MM/YYYY" (extração)
        let dataNascStr = g('Nascimento', 'nascimento');
        if (dataNascStr) {
            if (/^\d{4}-\d{2}-\d{2}$/.test(dataNascStr)) {
                // Formato BD (YYYY-MM-DD) → converte para DD/MM/YYYY
                const p = dataNascStr.split('-');
                dataNascStr = `${p[2]}/${p[1]}/${p[0]}`;
            } else if (dataNascStr.includes('/')) {
                // Formato extração (D/M/YYYY) → padding
                const p = dataNascStr.split('/');
                if (p.length === 3) {
                    let d = p[0].trim(), m = p[1].trim();
                    if (d.length === 1) d = '0' + d;
                    if (m.length === 1) m = '0' + m;
                    dataNascStr = `${d}/${m}/${p[2].trim()}`;
                }
            }
            $('#inputDataNascimentoOutro').val(dataNascStr);
            $('#inputIdadeOutro').val(calcularIdade(dataNascStr));
        }

        $('#inputEstadoCivilOutro').val(g('EstCivil', 'estado_civil'));
        $('#inputNaturalidadeOutro').val(g('Naturalidade', 'naturalidade'));
        $('#inputProfissaoOutro').val(g('Profissao', 'profissao'));
        $('#inputInstrucaoOutro').val(g('Instrucao', 'instrucao') || g('Escolaridade', 'escolaridade'));
        $('#inputRGOutro').val(g('RG', 'rg'));
        $('#inputCPFOutro').val(g('CPF', 'cpf')).trigger('input');
        let tel = g('Telefone', 'telefone');
        if (!tel || tel.trim() === '') tel = '(00) 00000-0000';
        $('#inputTelefoneOutro').val(tel).trigger('input');
        $('#inputMaeOutro').val(g('Mae', 'mae'));
        $('#inputPaiOutro').val(g('Pai', 'pai'));
        $('#inputEnderecoOutro').val(g('Endereco', 'endereco'));

        if (currentOutroId) {
            $('#btnEditarOutro, #btnExcluirOutro').prop('disabled', false);
        }

        console.log('✅ OUTRO VINCULADO PREENCHIDO - ID:', currentOutroId);
    };

    // === LIMPAR / NOVA OUTRO ===
    $('#btnNovoOutro, #btnLimparOutro').click(function () {
        currentOutroId = null;
        $('#formOutro')[0].reset();
        $('#inputIdadeOutro').val('');
        $('#btnEditarOutro, #btnExcluirOutro').prop('disabled', true);

        // ✅ REMOVIDO: Excluir vínculo ao criar novo outro (conflitava com sistema de chips)
        // excluirVinculoBoeOutro();
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioOutro(url, metodo) {
        const cpf = $('#inputCPFOutro').val();
        if (!validarCPF(cpf)) {
            mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
            return;
        }

        const formData = $('#formOutro').serializeArray();
        const dataObj = {};
        formData.forEach(function (item) {
            dataObj[item.name] = item.value;
        });

        if (metodo === 'PUT') {
            dataObj._method = 'PUT';
        }

        $('#btnSalvarOutro, #btnEditarOutro').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: dataObj,
            success: function (response) {
                if (response.success) {
                    currentOutroId = response.id || currentOutroId;
                    mostrarSucesso(metodo === 'PUT' ? 'Registro atualizado com sucesso!' : 'Registro salvo com sucesso!');

                    // ✅ SALVAR VÍNCULO APÓS SALVAR/EDITAR
                    setTimeout(salvarVinculoBoeOutro, 500);

                    // Atualizar grid
                    const nome = ($('#inputNomeOutro').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunhaOutro').val() || 'N/A').trim();
                    const rg = ($('#inputRGOutro').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPFOutro').val() || 'N/A').trim();
                    const id = currentOutroId;
                    const $tbody = $('#tabelaResultadosOutro tbody');

                    if (metodo === 'POST') {
                        // Nova linha
                        if ($tbody.find('td[colspan]').length || $tbody.text().includes('Nenhum')) {
                            $tbody.empty();
                        }

                        const $novaLinha = $(`
                            <tr class="table-success">
                                <td>${nome}</td>
                                <td>${alcunha}</td>
                                <td>${rg}</td>
                                <td>${cpfFormatado}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-outro" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        // Atualizar linha existente
                        const $btn = $(`button.btn-selecionar-outro[data-id="${id}"]`);
                        if ($btn.length) {
                            const $tr = $btn.closest('tr');
                            $tr.find('td').eq(0).text(nome);
                            $tr.find('td').eq(1).text(alcunha);
                            $tr.find('td').eq(2).text(rg);
                            $tr.find('td').eq(3).text(cpfFormatado);
                            $tr.addClass('table-success');
                            setTimeout(() => $tr.removeClass('table-success'), 2000);
                        } else {
                            // Se não encontrou na grid, adiciona nova linha
                            if ($tbody.find('td[colspan]').length || $tbody.text().includes('Nenhum')) {
                                $tbody.empty();
                            }
                            const $novaLinha = $(`
                                <tr class="table-success">
                                    <td>${nome}</td>
                                    <td>${alcunha}</td>
                                    <td>${rg}</td>
                                    <td>${cpfFormatado}</td>
                                    <td><button type="button" class="btn btn-sm btn-success btn-selecionar-outro" data-id="${id}">Selecionar</button></td>
                                </tr>
                            `);
                            $tbody.prepend($novaLinha);
                            setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarOutro, #btnExcluirOutro').prop('disabled', false);
                } else {
                    mostrarErro(response.message || 'Erro ao processar');
                }
            },
            error: function (xhr) {
                const messages = [];
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        for (const field in errors) {
                            errors[field].forEach(function (msg) {
                                if (msg.toLowerCase().includes('cpf') && msg.toLowerCase().includes('cadastrado')) {
                                    messages.push('Já existe um registro com esse CPF.');
                                } else {
                                    messages.push('<strong>' + field + ':</strong> ' + msg);
                                }
                            });
                        }
                    } else if (xhr.responseJSON?.message) {
                        const msg = xhr.responseJSON.message;
                        messages.push(msg.toLowerCase().includes('cpf') && msg.toLowerCase().includes('cadastrado') ? 'Já existe um registro com esse CPF.' : msg);
                    }
                } else {
                    messages.push(xhr.responseJSON?.message || 'Erro ao processar');
                }
                mostrarErro(messages.join('<br>'));
            },
            complete: function () {
                $('#btnSalvarOutro, #btnEditarOutro').prop('disabled', false);
            }
        });
    }

    $('#btnSalvarOutro').click(function () {
        enviarFormularioOutro(rotas.outro.salvar, 'POST');
    });

    // ✅ BOTÃO EDITAR CORRIGIDO
    $('#btnEditarOutro').click(function () {
        const nome = $('#inputNomeOutro').val().trim();
        const outroId = $('#outro_id').val() || currentOutroId;

        if (!nome) {
            mostrarErro('Preencha o nome antes de editar!');
            return;
        }

        if (!outroId) {
            mostrarErro('Selecione um registro para editar!');
            return;
        }

        console.log('✏️ EDITANDO OUTRO ID:', outroId);
        currentOutroId = outroId;
        enviarFormularioOutro(rotas.outro.atualizar + '/' + outroId, 'PUT');
    });

    // === CRUD: EXCLUIR ===
    $('#btnExcluirOutro').click(function () {
        const outroId = $('#outro_id').val() || currentOutroId;

        if (!outroId) {
            mostrarErro('Selecione um registro para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este registro?', function () {
            const outroId = $('#outro_id').val() || currentOutroId;
            if (!outroId) return;

            const termoPesquisa = $('#termoPesquisaOutro').val().trim();
            const filtro = $('#filtroOutro').val();
            const boe = $('#inputBOE').val().trim();

            // ✅ PRIMEIRO EXCLUI O VÍNCULO
            if (boe && boe !== 'N/A') {
                console.log('🗑️ EXCLUINDO VÍNCULO OUTRO DO BOE:', boe);

                $.ajax({
                    url: '/boe/vinculos/excluir-outro/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (responseVinculo) {
                        if (responseVinculo.success) {
                            console.log('✅ VÍNCULO OUTRO EXCLUÍDO COM SUCESSO');
                        } else {
                            console.log('ℹ️ Vínculo outro não encontrado ou já excluído');
                        }

                        // DEPOIS EXCLUI O REGISTRO
                        excluirRegistroOutro();
                    },
                    error: function (xhr) {
                        console.error('❌ ERRO AO EXCLUIR VÍNCULO OUTRO:', xhr.responseJSON);
                        excluirRegistroOutro();
                    }
                });
            } else {
                excluirRegistroOutro();
            }

            function excluirRegistroOutro() {
                $.ajax({
                    url: rotas.outro.excluir + '/' + outroId,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            mostrarSucesso('Registro excluído com sucesso!');

                            // LIMPA FORMULÁRIO
                            $('#formOutro')[0].reset();
                            $('#inputIdadeOutro').val('');
                            $('#btnEditarOutro, #btnExcluirOutro').prop('disabled', true);
                            currentOutroId = null;

                            // REMOVE DA GRID
                            $(`button.btn-selecionar-outro[data-id="${outroId}"]`).closest('tr').remove();

                            if ($('#tabelaResultadosOutro tbody tr').length === 0) {
                                $('#tabelaResultadosOutro tbody').html('<tr><td colspan="5" class="text-center">Nenhum "outro" encontrado.</td></tr>');
                            }

                            if (termoPesquisa && $('#tabelaResultadosOutro tbody tr td').text().includes('Nenhum')) {
                                carregarGridOutro(filtro, termoPesquisa);
                            }
                        } else {
                            mostrarErro(response.message);
                        }
                    },
                    error: function (xhr) {
                        mostrarErro('Erro ao excluir: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                    }
                });
            }
        });
    });

    // === FUNÇÕES DE MODAL ===
    // Usam window.mostrarSucesso e window.mostrarErro definidos em script.js




    // === BOTÃO FECHAR ===
    $('#btnFecharOutro').click(function () {
        if (window.ocTabs && typeof window.ocTabs.closeTab === 'function') {
            window.ocTabs.closeTab('tab-outro', 'tabLinkOutro');
        }
    });

    // === AUTOCOMPLETE PARA DOCUMENTOS ===
    (function () {
        const documentosOutro = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL"
        ];

        let selectedIndex = -1;
        let sugestoesAtuais = [];
        const $input = $('#termoDocumentoOutro');
        const $sugestoes = $('#sugestoesDocumentosOutro');

        function mostrarSugestoes(sugestoes) {
            $sugestoes.empty();
            selectedIndex = -1;
            sugestoesAtuais = sugestoes;

            if (sugestoes.length === 0) {
                $sugestoes.hide();
                return;
            }

            sugestoes.forEach((doc, index) => {
                const $item = $(`<a href="#" class="list-group-item list-group-item-action item-sugestao-outro" data-index="${index}">${doc}</a>`);

                $item.on('mouseenter', function () {
                    $sugestoes.find('a').removeClass('active');
                    $(this).addClass('active');
                    selectedIndex = index;
                });

                $sugestoes.append($item);
            });

            $sugestoes.show();
        }

        $input.on('input', function () {
            const valor = $(this).val().toUpperCase();
            if (valor.length > 0) {
                const filtrados = documentosOutro.filter(doc => doc.includes(valor));
                mostrarSugestoes(filtrados);
            } else {
                $sugestoes.hide();
            }
        });

        $input.on('keydown', function (e) {
            const $items = $sugestoes.find('a');
            if (!$items.length || $sugestoes.is(':hidden')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + $items.length) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0) {
                    e.preventDefault();
                    $input.val($items.eq(selectedIndex).text());
                    $sugestoes.hide();
                    selectedIndex = -1;
                }
            } else if (e.key === 'Escape') {
                $sugestoes.hide();
            }
        });

        // Selecionar sugestão via clique
        $(document).on('click', '.item-sugestao-outro', function (e) {
            e.preventDefault();
            $input.val($(this).text());
            $sugestoes.hide();
            $input.focus();
        });

        // Ocultar ao clicar fora
        $(document).click(function (e) {
            if (!$(e.target).closest('#termoDocumentoOutro, #sugestoesDocumentosOutro').length) {
                $sugestoes.hide();
            }
        });

        // Botão Imprimir
        $('#btnImprimirDocumentoOutro').click(function () {
            const nome = $('#inputNomeOutro').val();
            const documentoSelecionado = $('#termoDocumentoOutro').val().trim().toUpperCase();

            if (!nome) {
                mostrarErro('Por favor, preencha o nome.');
                return;
            }
            if (!documentoSelecionado) {
                mostrarErro('Por favor, selecione ou digite o nome do documento.');
                return;
            }

            try {
                // ✅ CAPTURA CENTRALIZADA E ROBUSTA (Chips + Formulários)
                let dados = DocumentoService.capturarDadosGlobais();

                // ✅ SOBREPOSIÇÃO COM DADOS ATUAIS DA ABA (Garante que o que foi digitado agora seja usado)
                const dadosAtuaisOutro = {
                    nome: $('#inputNomeOutro').val(),
                    alcunha: ($('#inputAlcunhaOutro').val() || '').toUpperCase(),
                    nascimento: $('#inputDataNascimentoOutro').val(),
                    idade: $('#inputIdadeOutro').val(),
                    rg: $('#inputRGOutro').val(),
                    cpf: $('#inputCPFOutro').val(),
                    mae: ($('#inputMaeOutro').val() || '').toUpperCase(),
                    pai: ($('#inputPaiOutro').val() || '').toUpperCase(),
                    endereco: ($('#inputEnderecoOutro').val() || '').toUpperCase(),
                    profissao: ($('#inputProfissaoOutro').val() || '').toUpperCase(),
                    naturalidade: ($('#inputNaturalidadeOutro').val() || '').toUpperCase(),
                    estcivil: ($('#inputEstadoCivilOutro').val() || '').toUpperCase(),
                    instrucao: ($('#inputInstrucaoOutro').val() || '').toUpperCase(),
                    telefone: $('#inputTelefoneOutro').val()
                };

                // Mescla no objeto principal e também no objeto testemunha1 (hack para compatibilidade)
                Object.assign(dados, dadosAtuaisOutro);
                dados.testemunha1 = dadosAtuaisOutro;

                // Verifica rotas (Usa rotas da testemunha 1 como padrão para "Outros")
                if (typeof rotasImpressaoTestemunha1 !== 'undefined' && rotasImpressaoTestemunha1[documentoSelecionado]) {
                    const rota = rotasImpressaoTestemunha1[documentoSelecionado];
                    console.log('🚀 Enviando para DocumentoService (via Outros):', { documentoSelecionado, dados });
                    DocumentoService.gerar(rota, dados);
                } else {
                    mostrarErro(`Documento "${documentoSelecionado}" não configurado.`);
                }

            } catch (error) {
                console.error('❌ Erro ao preparar documento:', error);
                mostrarErro('Erro ao preparar os dados para o documento.');
            }
        });
    })();
});
