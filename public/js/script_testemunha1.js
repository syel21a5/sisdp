// script_testemunha1.js - VERSÃO COMPLETA E CORRIGIDA COM VÍNCULOS
$(document).ready(function () {
    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    $('#inputDataNascimentoTestemunha1').mask('00/00/0000');
    $('#inputCPFTestemunha1').mask('000.000.000-00', { reverse: true });
    $('#inputTelefoneTestemunha1').mask('(00) 00000-0000');

    // === VARIÁVEL GLOBAL ===
    let currentTestemunha1Id = null;

    // ✅ FUNÇÃO CORRIGIDA: SALVAR VÍNCULO BOE-TESTEMUNHA1
    function salvarVinculoBoeTestemunha1() {
        const boe = $('#inputBOE').val().trim();
        const testemunha1Id = currentTestemunha1Id || $('#testemunha1_id').val();

        console.log('💾 SALVANDO VÍNCULO BOE-TESTEMUNHA1:', { boe, testemunha1Id });

        if (!boe || boe === 'N/A' || !testemunha1Id) {
            console.log('⚠️ Dados insuficientes para salvar vínculo:', { boe, testemunha1Id });
            return;
        }

        $.ajax({
            url: '/boe/vinculos/salvar',
            method: 'POST',
            data: {
                boe: boe,
                testemunha1_id: testemunha1Id,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO TESTEMUNHA1 SALVO COM SUCESSO:', response);
                } else {
                    console.error('❌ ERRO AO SALVAR VÍNCULO TESTEMUNHA1:', response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AJAX AO SALVAR VÍNCULO TESTEMUNHA1:', xhr.responseJSON);
            }
        });
    }

    // ✅ FUNÇÃO CORRIGIDA: EXCLUIR VÍNCULO ESPECÍFICO DA TESTEMUNHA1
    function excluirVinculoBoeTestemunha1() {
        const boe = $('#inputBOE').val().trim();

        if (!boe || boe === 'N/A') {
            console.log('⚠️ BOE não disponível para excluir vínculo testemunha1');
            return;
        }

        console.log('🗑️ EXCLUINDO VÍNCULO TESTEMUNHA1 DO BOE:', boe);

        $.ajax({
            url: '/boe/vinculos/excluir-testemunha1/' + encodeURIComponent(boe),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO TESTEMUNHA1 EXCLUÍDO COM SUCESSO');
                } else {
                    console.log('ℹ️ Vínculo testemunha1 não encontrado ou já excluído');
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AO EXCLUIR VÍNCULO TESTEMUNHA1:', xhr.responseJSON);
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
    $('#inputDataNascimentoTestemunha1').on('change', function () {
        $('#inputIdadeTestemunha1').val(calcularIdade($(this).val()));
    });

    // === GRID DE PESQUISA DE TESTEMUNHA1 ===
    function carregarGridTestemunha1(filtro, termo) {
        if (!termo) {
            $('#tabelaResultadosTestemunha1 tbody').html('<tr><td colspan="5" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }

        $.ajax({
            url: rotas.testemunha1.pesquisar,
            method: "POST",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                const tbody = $('#tabelaResultadosTestemunha1 tbody').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const row = `
                            <tr>
                                <td>${item.Nome || ''}</td>
                                <td>${item.Alcunha || ''}</td>
                                <td>${item.RG || ''}</td>
                                <td>${item.CPF || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-testemunha1" data-id="${item.IdCad}">Selecionar</button></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">Nenhuma testemunha encontrada.</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#tabelaResultadosTestemunha1 tbody').html('<tr><td colspan="5" class="text-center">' + erro + '</td></tr>');
                console.error('Erro na pesquisa:', xhr);
            }
        });
    }

    $('#btnPesquisarTestemunha1').click(function () {
        carregarGridTestemunha1($('#filtroTestemunha1').val(), $('#termoPesquisaTestemunha1').val().trim());
    });

    // Máscara de CPF no campo de pesquisa de testemunha
    $('#termoPesquisaTestemunha1').on('input', function() {
        if ($('#filtroTestemunha1').val() !== 'CPF') return;
        let val = $(this).val().replace(/\D/g, '').substring(0, 11);
        if (val.length > 0) {
            val = val.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(val);
        }
    });
    $('#filtroTestemunha1').on('change', function() {
        const c = $('#termoPesquisaTestemunha1');
        c.val('');
        if ($(this).val() === 'CPF') { c.attr('maxlength', 14).attr('placeholder', '000.000.000-00'); }
        else { c.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...'); }
    });

    // === SELEÇÃO DE TESTEMUNHA1 DA GRID ===
    $(document).on('click', '.btn-selecionar-testemunha1', function () {
        const id = $(this).data('id');
        currentTestemunha1Id = id;

        $.ajax({
            url: rotas.testemunha1.buscar + '/' + id,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const c = response.data;
                    $('#testemunha1_id').val(c.IdCad);
                    $('#inputNomeTestemunha1').val(c.Nome || '');
                    $('#inputAlcunhaTestemunha1').val(c.Alcunha || '');

                    if (c.Nascimento) {
                        const partes = c.Nascimento.split('-');
                        if (partes.length === 3) {
                            $('#inputDataNascimentoTestemunha1').val(`${partes[2]}/${partes[1]}/${partes[0]}`);
                        } else {
                            $('#inputDataNascimentoTestemunha1').val(c.Nascimento);
                        }
                    } else {
                        $('#inputDataNascimentoTestemunha1').val('');
                    }

                    $('#inputIdadeTestemunha1').val(calcularIdade($('#inputDataNascimentoTestemunha1').val()));
                    $('#inputEstadoCivilTestemunha1').val(c.EstCivil || c.estado_civil || '');
                    $('#inputNaturalidadeTestemunha1').val(c.Naturalidade || c.naturalidade || '');
                    $('#inputProfissaoTestemunha1').val(c.Profissao || c.profissao || '');
                    $('#inputInstrucaoTestemunha1').val(c.Instrucao || c.instrucao || c.escolaridade || '');
                    $('#inputRGTestemunha1').val(c.RG || c.rg || '');
                    $('#inputCPFTestemunha1').val(c.CPF || c.cpf || '').trigger('input');
                    $('#inputTelefoneTestemunha1').val(c.Telefone || c.telefone || '(00) 00000-0000').trigger('input');
                    $('#inputMaeTestemunha1').val(c.Mae || c.mae || '');
                    $('#inputPaiTestemunha1').val(c.Pai || c.pai || '');
                    $('#inputEnderecoTestemunha1').val(c.Endereco || c.endereco || '');

                    $('#btnEditarTestemunha1, #btnExcluirTestemunha1').prop('disabled', false);

                    // ✅ SALVAR VÍNCULO AO SELECIONAR TESTEMUNHA1 DA GRID
                    setTimeout(salvarVinculoBoeTestemunha1, 300);
                } else {
                    mostrarErro(response.message || 'Erro ao buscar testemunha');
                }
            },
            error: function (xhr) {
                mostrarErro('Erro ao carregar testemunha: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // ✅ FUNÇÃO PARA PREENCHER TESTEMUNHA1 VINCULADA (EXTERNA)
    window.preencherTestemunha1Vinculada = function (dados) {
        console.log('🔄 PREENCHENDO TESTEMUNHA1 DO VÍNCULO:', dados);

        currentTestemunha1Id = dados.IdCad;

        $('#testemunha1_id').val(dados.IdCad || '');
        $('#inputNomeTestemunha1').val(dados.Nome || '');
        $('#inputAlcunhaTestemunha1').val(dados.Alcunha || '');

        if (dados.Nascimento) {
            const dataNasc = new Date(dados.Nascimento);
            if (!isNaN(dataNasc.getTime())) {
                $('#inputDataNascimentoTestemunha1').val(dataNasc.toLocaleDateString('pt-BR'));
                $('#inputIdadeTestemunha1').val(calcularIdade($('#inputDataNascimentoTestemunha1').val()));
            }
        }

        $('#inputEstadoCivilTestemunha1').val(dados.EstCivil || dados.estado_civil || '');
        $('#inputNaturalidadeTestemunha1').val(dados.Naturalidade || dados.naturalidade || '');
        $('#inputProfissaoTestemunha1').val(dados.Profissao || dados.profissao || '');
        $('#inputInstrucaoTestemunha1').val(dados.Instrucao || dados.instrucao || dados.escolaridade || '');
        $('#inputRGTestemunha1').val(dados.RG || dados.rg || '');
        $('#inputCPFTestemunha1').val(dados.CPF || dados.cpf || '').trigger('input');
        $('#inputTelefoneTestemunha1').val(dados.Telefone || dados.telefone || '(00) 00000-0000').trigger('input');
        $('#inputMaeTestemunha1').val(dados.Mae || dados.mae || '');
        $('#inputPaiTestemunha1').val(dados.Pai || dados.pai || '');
        $('#inputEnderecoTestemunha1').val(dados.Endereco || dados.endereco || '');

        $('#btnEditarTestemunha1, #btnExcluirTestemunha1').prop('disabled', false);

        console.log('✅ TESTEMUNHA1 VINCULADA PREENCHIDA - ID:', currentTestemunha1Id);
    };

    // === LIMPAR / NOVA TESTEMUNHA1 ===
    $('#btnNovaTestemunha1, #btnLimparTestemunha1').click(function () {
        currentTestemunha1Id = null;
        $('#formTestemunha1')[0].reset();
        $('#inputIdadeTestemunha1').val('');
        $('#btnEditarTestemunha1, #btnExcluirTestemunha1').prop('disabled', true);

        // ✅ REMOVIDO: Excluir vínculo ao criar nova testemunha (conflitava com sistema de chips)
        // excluirVinculoBoeTestemunha1();
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioTestemunha1(url, metodo) {
        const cpf = $('#inputCPFTestemunha1').val();
        if (!validarCPF(cpf)) {
            mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
            return;
        }

        const formData = $('#formTestemunha1').serializeArray();
        const dataObj = {};
        formData.forEach(function (item) {
            dataObj[item.name] = item.value;
        });

        if (metodo === 'PUT') {
            dataObj._method = 'PUT';
        }

        $('#btnSalvarTestemunha1, #btnEditarTestemunha1').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: dataObj,
            success: function (response) {
                if (response.success) {
                    currentTestemunha1Id = response.id || currentTestemunha1Id;
                    mostrarSucesso(metodo === 'PUT' ? 'Testemunha atualizada com sucesso!' : 'Testemunha salva com sucesso!');

                    // ✅ SALVAR VÍNCULO APÓS SALVAR/EDITAR TESTEMUNHA1
                    setTimeout(salvarVinculoBoeTestemunha1, 500);

                    // Atualizar grid
                    const nome = ($('#inputNomeTestemunha1').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunhaTestemunha1').val() || 'N/A').trim();
                    const rg = ($('#inputRGTestemunha1').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPFTestemunha1').val() || 'N/A').trim();
                    const id = currentTestemunha1Id;
                    const $tbody = $('#tabelaResultadosTestemunha1 tbody');

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
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-testemunha1" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        // Atualizar linha existente
                        const $btn = $(`button.btn-selecionar-testemunha1[data-id="${id}"]`);
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
                                    <td><button type="button" class="btn btn-sm btn-success btn-selecionar-testemunha1" data-id="${id}">Selecionar</button></td>
                                </tr>
                            `);
                            $tbody.prepend($novaLinha);
                            setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarTestemunha1, #btnExcluirTestemunha1').prop('disabled', false);
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
                $('#btnSalvarTestemunha1, #btnEditarTestemunha1').prop('disabled', false);
            }
        });
    }

    $('#btnSalvarTestemunha1').click(function () {
        enviarFormularioTestemunha1(rotas.testemunha1.salvar, 'POST');
    });

    // ✅ BOTÃO EDITAR CORRIGIDO
    $('#btnEditarTestemunha1').click(function () {
        const nome = $('#inputNomeTestemunha1').val().trim();
        const testemunha1Id = $('#testemunha1_id').val() || currentTestemunha1Id;

        if (!nome) {
            mostrarErro('Preencha o nome da testemunha antes de editar!');
            return;
        }

        if (!testemunha1Id) {
            mostrarErro('Selecione uma testemunha para editar!');
            return;
        }

        console.log('✏️ EDITANDO TESTEMUNHA1 ID:', testemunha1Id);
        currentTestemunha1Id = testemunha1Id;
        enviarFormularioTestemunha1(rotas.testemunha1.atualizar + '/' + testemunha1Id, 'PUT');
    });

    // === CRUD: EXCLUIR ===
    $('#btnExcluirTestemunha1').click(function () {
        const testemunha1Id = $('#testemunha1_id').val() || currentTestemunha1Id;

        if (!testemunha1Id) {
            mostrarErro('Selecione uma testemunha para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir esta testemunha?', function () {
            const testemunha1Id = $('#testemunha1_id').val() || currentTestemunha1Id;
            if (!testemunha1Id) return;

            const termoPesquisa = $('#termoPesquisaTestemunha1').val().trim();
            const filtro = $('#filtroTestemunha1').val();
            const boe = $('#inputBOE').val().trim();

            // ✅ PRIMEIRO EXCLUI O VÍNCULO
            if (boe && boe !== 'N/A') {
                console.log('🗑️ EXCLUINDO VÍNCULO TESTEMUNHA1 DO BOE:', boe);

                $.ajax({
                    url: '/boe/vinculos/excluir-testemunha1/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (responseVinculo) {
                        if (responseVinculo.success) {
                            console.log('✅ VÍNCULO TESTEMUNHA1 EXCLUÍDO COM SUCESSO');
                        } else {
                            console.log('ℹ️ Vínculo testemunha1 não encontrado ou já excluído');
                        }

                        // DEPOIS EXCLUI O REGISTRO
                        excluirRegistroTestemunha1();
                    },
                    error: function (xhr) {
                        console.error('❌ ERRO AO EXCLUIR VÍNCULO TESTEMUNHA1:', xhr.responseJSON);
                        excluirRegistroTestemunha1();
                    }
                });
            } else {
                excluirRegistroTestemunha1();
            }

            function excluirRegistroTestemunha1() {
                $.ajax({
                    url: rotas.testemunha1.excluir + '/' + testemunha1Id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            mostrarSucesso('Testemunha excluída com sucesso!');

                            // LIMPA FORMULÁRIO
                            $('#formTestemunha1')[0].reset();
                            $('#inputIdadeTestemunha1').val('');
                            $('#btnEditarTestemunha1, #btnExcluirTestemunha1').prop('disabled', true);
                            currentTestemunha1Id = null;

                            // REMOVE DA GRID
                            $(`button.btn-selecionar-testemunha1[data-id="${testemunha1Id}"]`).closest('tr').remove();

                            if ($('#tabelaResultadosTestemunha1 tbody tr').length === 0) {
                                $('#tabelaResultadosTestemunha1 tbody').html('<tr><td colspan="5" class="text-center">Nenhuma testemunha encontrada.</td></tr>');
                            }

                            if (termoPesquisa && $('#tabelaResultadosTestemunha1 tbody tr td').text().includes('Nenhum')) {
                                carregarGridTestemunha1(filtro, termoPesquisa);
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

    // === IMPRESSÃO DE DOCUMENTOS TESTEMUNHA1 ===
    $('#btnImprimirDocumentoTestemunha1').click(function () {
        const nome = $('#inputNomeTestemunha1').val();
        const documentoSelecionado = $('#termoDocumentoTestemunha1').val().trim().toUpperCase();

        console.log('=== DEBUG IMPRESSÃO TESTEMUNHA1-APFD ===');
        console.log('📄 Documento selecionado:', documentoSelecionado);
        console.log('👤 Nome da testemunha:', nome);

        // Validações básicas
        if (!nome) {
            mostrarErro('Por favor, preencha o nome da testemunha.');
            return;
        }
        if (!documentoSelecionado) {
            mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        let dados;

        // ✅✅✅ MESMA LÓGICA INTELIGENTE DOS VITIMAS
        const documentosIndividuais = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AAFAI - TESTEMUNHA 1",
            "APFD - TESTEMUNHA 1",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL"
        ];

        const documentosMultiplos = [
            "CERTIDAO DE ASSINATURA APFD"
        ];

        if (documentosIndividuais.includes(documentoSelecionado)) {
            // Para documentos individuais
            dados = {
                nome: nome,
                alcunha: $('#inputAlcunhaTestemunha1').val(),
                nascimento: $('#inputDataNascimentoTestemunha1').val(),
                idade: $('#inputIdadeTestemunha1').val(),
                estcivil: $('#inputEstadoCivilTestemunha1').val(),
                naturalidade: $('#inputNaturalidadeTestemunha1').val(),
                rg: $('#inputRGTestemunha1').val(),
                cpf: $('#inputCPFTestemunha1').val(),
                profissao: $('#inputProfissaoTestemunha1').val(),
                instrucao: $('#inputInstrucaoTestemunha1').val(),
                telefone: $('#inputTelefoneTestemunha1').val(),
                mae: $('#inputMaeTestemunha1').val(),
                pai: $('#inputPaiTestemunha1').val(),
                endereco: $('#inputEnderecoTestemunha1').val(),
                // Dados do wf_geral
                data: $('#inputData').val(),
                data_comp: $('#inputDataComp').val(),
                data_ext: $('#inputDataExt').val(),
                cidade: $('#inputCidade').val(),
                delegado: $('#inputDelegado').val(),
                escrivao: $('#inputEscrivao').val(),
                delegacia: $('#inputDelegacia').val(),
                boe: $('#inputBOE').val(),
                apreensao: $('#inputApreensao').val(),
                ip: $('#inputIP').val()
            };
        } else if (documentosMultiplos.includes(documentoSelecionado)) {
            // ✅✅✅ CORREÇÃO: Para AAFAI TESTEMUNHA 1, usar estrutura com testemunha1
            dados = {
                // Dados principais
                data: $('#inputData').val(),
                data_comp: $('#inputDataComp').val(),
                data_ext: $('#inputDataExt').val(),
                cidade: $('#inputCidade').val(),
                delegado: $('#inputDelegado').val(),
                escrivao: $('#inputEscrivao').val(),
                delegacia: $('#inputDelegacia').val(),
                boe: $('#inputBOE').val(),
                ip: $('#inputIP').val(),
                apreensao: $('#inputApreensao').val(),
                // ✅ ESTRUTURA CORRETA: Dados dentro de testemunha1
                testemunha1: {
                    nome: nome,
                    alcunha: $('#inputAlcunhaTestemunha1').val(),
                    nascimento: $('#inputDataNascimentoTestemunha1').val(),
                    idade: $('#inputIdadeTestemunha1').val(),
                    estcivil: $('#inputEstadoCivilTestemunha1').val(),
                    naturalidade: $('#inputNaturalidadeTestemunha1').val(),
                    rg: $('#inputRGTestemunha1').val(),
                    cpf: $('#inputCPFTestemunha1').val(),
                    profissao: $('#inputProfissaoTestemunha1').val(),
                    instrucao: $('#inputInstrucaoTestemunha1').val(),
                    telefone: $('#inputTelefoneTestemunha1').val(),
                    mae: $('#inputMaeTestemunha1').val(),
                    pai: $('#inputPaiTestemunha1').val(),
                    endereco: $('#inputEnderecoTestemunha1').val()
                }
            };
        } else {
            // Fallback: usa dados individuais por padrão
            dados = {
                nome: nome,
                alcunha: $('#inputAlcunhaTestemunha1').val(),
                nascimento: $('#inputDataNascimentoTestemunha1').val(),
                idade: $('#inputIdadeTestemunha1').val(),
                estcivil: $('#inputEstadoCivilTestemunha1').val(),
                naturalidade: $('#inputNaturalidadeTestemunha1').val(),
                rg: $('#inputRGTestemunha1').val(),
                cpf: $('#inputCPFTestemunha1').val(),
                profissao: $('#inputProfissaoTestemunha1').val(),
                instrucao: $('#inputInstrucaoTestemunha1').val(),
                telefone: $('#inputTelefoneTestemunha1').val(),
                mae: $('#inputMaeTestemunha1').val(),
                pai: $('#inputPaiTestemunha1').val(),
                endereco: $('#inputEnderecoTestemunha1').val(),
                data: $('#inputData').val(),
                data_comp: $('#inputDataComp').val(),
                data_ext: $('#inputDataExt').val(),
                cidade: $('#inputCidade').val(),
                delegado: $('#inputDelegado').val(),
                escrivao: $('#inputEscrivao').val(),
                delegacia: $('#inputDelegacia').val(),
                boe: $('#inputBOE').val(),
                apreensao: $('#inputApreensao').val(),
                ip: $('#inputIP').val()
            };
        }

        console.log('📊 Dados coletados para testemunha 1 APFD:', dados);

        // ✅ CORREÇÃO: Verifica se as rotas de impressão da TESTEMUNHA 1 APFD estão definidas
        if (typeof rotasImpressaoTestemunha1 === 'undefined') {
            console.error('❌ ERRO CRÍTICO: rotasImpressaoTestemunha1 não configurada');
            mostrarErro('Rotas de impressão não configuradas. Recarregue a página.');
            return;
        }

        // ✅ CORREÇÃO: Verifica se o documento existe nas rotas da TESTEMUNHA 1 APFD
        if (!rotasImpressaoTestemunha1[documentoSelecionado]) {
            console.error('❌ ERRO: Documento não encontrado nas rotas');
            console.log('📋 Documentos disponíveis:', Object.keys(rotasImpressaoTestemunha1));
            mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        }

        console.log('✅ Rota encontrada:', rotasImpressaoTestemunha1[documentoSelecionado]);

        // ✅ USANDO O NOVO SERVIÇO CENTRALIZADO (EVITA URLs LONGAS)
        DocumentoService.gerar(rotasImpressaoTestemunha1[documentoSelecionado], dados);
    });


    // === AUTOCOMPLETE PARA DOCUMENTOS ===
    (function () {
        const documentos = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AAFAI - TESTEMUNHA 1",
            "APFD - TESTEMUNHA 1",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL"
        ];

        let selectedIndex = -1;
        let sugestoesAtuais = [];

        const $inputDocumento = $('#termoDocumentoTestemunha1');
        const $sugestoesContainer = $('#sugestoesDocumentosTestemunha1');

        function filtrarSugestoes(termo) {
            if (!termo) return [];
            termo = termo.toUpperCase();
            return documentos.filter(doc => doc.toUpperCase().includes(termo));
        }

        function mostrarSugestoes(sugestoes) {
            $sugestoesContainer.empty();
            selectedIndex = -1;
            sugestoesAtuais = sugestoes;

            if (!sugestoes.length) {
                $sugestoesContainer.hide();
                return;
            }

            sugestoes.forEach((sugestao, index) => {
                const $item = $('<a>', {
                    class: 'list-group-item list-group-item-action',
                    href: '#',
                    text: sugestao,
                    'data-index': index
                });

                $item.on('mouseenter', function () {
                    $sugestoesContainer.find('a').removeClass('active');
                    $(this).addClass('active');
                    selectedIndex = index;
                });

                $item.on('click', function (e) {
                    e.preventDefault();
                    $inputDocumento.val(sugestao);
                    $sugestoesContainer.hide();
                    $inputDocumento.focus();
                });

                $sugestoesContainer.append($item);
            });

            $sugestoesContainer.show();
        }

        $inputDocumento.on('input', function () {
            mostrarSugestoes(filtrarSugestoes($(this).val().trim()));
        });

        $inputDocumento.on('keydown', function (e) {
            const $items = $sugestoesContainer.find('a');
            if (!$items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + $items.length) % $items.length;
                $items.removeClass('active').eq(selectedIndex).addClass('active');
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                $inputDocumento.val(sugestoesAtuais[selectedIndex]);
                $sugestoesContainer.hide();
            } else if (e.key === 'Escape') {
                $sugestoesContainer.hide();
            }
        });

        $(document).on('click', function (e) {
            if (!$inputDocumento.is(e.target) &&
                !$sugestoesContainer.is(e.target) &&
                !$sugestoesContainer.has(e.target).length) {
                $sugestoesContainer.hide();
            }
        });

        $inputDocumento.on('blur', function () {
            setTimeout(() => $sugestoesContainer.hide(), 150);
        });
    })();
});
