// script_vitima1.js - VERSÃO COMPLETA E CORRIGIDA COM VÍNCULOS
$(document).ready(function () {
    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    $('#inputDataNascimentoVitima1').mask('00/00/0000');
    $('#inputCPFVitima1').mask('000.000.000-00', { reverse: true });
    $('#inputTelefoneVitima1').mask('(00) 00000-0000');

    // === VARIÁVEL GLOBAL ===
    let currentVitima1Id = null;

    // ✅ FUNÇÃO CORRIGIDA: SALVAR VÍNCULO BOE-VITIMA1
    function salvarVinculoBoeVitima1() {
        const boe = $('#inputBOE').val().trim();
        const vitima1Id = currentVitima1Id || $('#vitima1_id').val();

        console.log('💾 SALVANDO VÍNCULO BOE-VITIMA1:', { boe, vitima1Id });

        if (!boe || boe === 'N/A' || !vitima1Id) {
            console.log('⚠️ Dados insuficientes para salvar vínculo:', { boe, vitima1Id });
            return;
        }

        $.ajax({
            url: '/boe/vinculos/salvar',
            method: 'POST',
            data: {
                boe: boe,
                vitima1_id: vitima1Id,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO VITIMA1 SALVO COM SUCESSO:', response);
                } else {
                    console.error('❌ ERRO AO SALVAR VÍNCULO VITIMA1:', response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AJAX AO SALVAR VÍNCULO VITIMA1:', xhr.responseJSON);
            }
        });
    }

    // ✅ FUNÇÃO CORRIGIDA: EXCLUIR VÍNCULO ESPECÍFICO DA VITIMA1
    function excluirVinculoBoeVitima1() {
        const boe = $('#inputBOE').val().trim();

        if (!boe || boe === 'N/A') {
            console.log('⚠️ BOE não disponível para excluir vínculo vitima1');
            return;
        }

        console.log('🗑️ EXCLUINDO VÍNCULO VITIMA1 DO BOE:', boe);

        $.ajax({
            url: '/boe/vinculos/excluir-vitima1/' + encodeURIComponent(boe),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO VITIMA1 EXCLUÍDO COM SUCESSO');
                } else {
                    console.log('ℹ️ Vínculo vitima1 não encontrado ou já excluído');
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AO EXCLUIR VÍNCULO VITIMA1:', xhr.responseJSON);
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
    $('#inputDataNascimentoVitima1').on('change', function () {
        $('#inputIdadeVitima1').val(calcularIdade($(this).val()));
    });

    // === GRID DE PESQUISA DE VITIMA1 ===
    function carregarGridVitima1(filtro, termo) {
        if (!termo) {
            $('#tabelaResultadosVitima1 tbody').html('<tr><td colspan="5" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }

        $.ajax({
            url: rotas.vitima1.pesquisar,
            method: "POST",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                const tbody = $('#tabelaResultadosVitima1 tbody').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const row = `
                            <tr>
                                <td>${item.Nome || ''}</td>
                                <td>${item.Alcunha || ''}</td>
                                <td>${item.RG || ''}</td>
                                <td>${item.CPF || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-vitima1" data-id="${item.IdCad}">Selecionar</button></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">Nenhuma vítima encontrada.</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#tabelaResultadosVitima1 tbody').html('<tr><td colspan="5" class="text-center">' + erro + '</td></tr>');
                console.error('Erro na pesquisa:', xhr);
            }
        });
    }

    $('#btnPesquisarVitima1').click(function () {
        carregarGridVitima1($('#filtroVitima1').val(), $('#termoPesquisaVitima1').val().trim());
    });

    // Máscara de CPF no campo de pesquisa de vítima
    $('#termoPesquisaVitima1').on('input', function() {
        if ($('#filtroVitima1').val() !== 'CPF') return;
        let val = $(this).val().replace(/\D/g, '').substring(0, 11);
        if (val.length > 0) {
            val = val.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(val);
        }
    });
    $('#filtroVitima1').on('change', function() {
        const c = $('#termoPesquisaVitima1');
        c.val('');
        if ($(this).val() === 'CPF') { c.attr('maxlength', 14).attr('placeholder', '000.000.000-00'); }
        else { c.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...'); }
    });

    // === SELEÇÃO DE VITIMA1 DA GRID ===
    $(document).on('click', '.btn-selecionar-vitima1', function () {
        const id = $(this).data('id');
        currentVitima1Id = id;

        $.ajax({
            url: rotas.vitima1.buscar + '/' + id,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const c = response.data;
                    $('#vitima1_id').val(c.IdCad);
                    $('#inputNomeVitima1').val(c.Nome || '');
                    $('#inputAlcunhaVitima1').val(c.Alcunha || '');

                    if (c.Nascimento) {
                        const partes = c.Nascimento.split('-');
                        if (partes.length === 3) {
                            $('#inputDataNascimentoVitima1').val(`${partes[2]}/${partes[1]}/${partes[0]}`);
                        } else {
                            $('#inputDataNascimentoVitima1').val(c.Nascimento);
                        }
                    } else {
                        $('#inputDataNascimentoVitima1').val('');
                    }

                    $('#inputIdadeVitima1').val(calcularIdade($('#inputDataNascimentoVitima1').val()));
                    $('#inputEstadoCivilVitima1').val(c.EstCivil || '');
                    $('#inputNaturalidadeVitima1').val(c.Naturalidade || '');
                    $('#inputProfissaoVitima1').val(c.Profissao || '');
                    $('#inputInstrucaoVitima1').val(c.Instrucao || '');
                    $('#inputRGVitima1').val(c.RG || '');
                    $('#inputCPFVitima1').val(c.CPF || '').trigger('input');
                    $('#inputTelefoneVitima1').val(c.Telefone || '(00) 00000-0000').trigger('input');
                    $('#inputMaeVitima1').val(c.Mae || '');
                    $('#inputPaiVitima1').val(c.Pai || '');
                    $('#inputEnderecoVitima1').val(c.Endereco || '');

                    $('#btnEditarVitima1, #btnExcluirVitima1').prop('disabled', false);

                    // ✅ SALVAR VÍNCULO AO SELECIONAR VITIMA1 DA GRID
                    setTimeout(salvarVinculoBoeVitima1, 300);
                } else {
                    mostrarErro(response.message || 'Erro ao buscar vítima');
                }
            },
            error: function (xhr) {
                mostrarErro('Erro ao carregar vítima: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // ✅ FUNÇÃO PARA PREENCHER VITIMA1 VINCULADA (EXTERNA)
    window.preencherVitima1Vinculada = function (dados) {
        console.log('🔄 PREENCHENDO VITIMA1 DO VÍNCULO:', dados);

        currentVitima1Id = dados.IdCad;

        $('#vitima1_id').val(dados.IdCad || '');
        $('#inputNomeVitima1').val(dados.Nome || '');
        $('#inputAlcunhaVitima1').val(dados.Alcunha || '');

        if (dados.Nascimento) {
            const dataNasc = new Date(dados.Nascimento);
            if (!isNaN(dataNasc.getTime())) {
                $('#inputDataNascimentoVitima1').val(dataNasc.toLocaleDateString('pt-BR'));
                $('#inputIdadeVitima1').val(calcularIdade($('#inputDataNascimentoVitima1').val()));
            }
        }

        $('#inputEstadoCivilVitima1').val(dados.EstCivil || '');
        $('#inputNaturalidadeVitima1').val(dados.Naturalidade || '');
        $('#inputProfissaoVitima1').val(dados.Profissao || '');
        $('#inputInstrucaoVitima1').val(dados.Instrucao || '');
        $('#inputRGVitima1').val(dados.RG || '');
        $('#inputCPFVitima1').val(dados.CPF || '').trigger('input');
        $('#inputTelefoneVitima1').val(dados.Telefone || '(00) 00000-0000').trigger('input');
        $('#inputMaeVitima1').val(dados.Mae || '');
        $('#inputPaiVitima1').val(dados.Pai || '');
        $('#inputEnderecoVitima1').val(dados.Endereco || '');

        $('#btnEditarVitima1, #btnExcluirVitima1').prop('disabled', false);

        console.log('✅ VITIMA1 VINCULADA PREENCHIDA - ID:', currentVitima1Id);
    };

    // === LIMPAR / NOVA VITIMA1 ===
    $('#btnNovaVitima1, #btnLimparVitima1').click(function () {
        currentVitima1Id = null;
        $('#formVitima1')[0].reset();
        $('#inputIdadeVitima1').val('');
        $('#btnEditarVitima1, #btnExcluirVitima1').prop('disabled', true);

        // ✅ REMOVIDO: Excluir vínculo ao criar nova vítima (conflitava com sistema de chips)
        // excluirVinculoBoeVitima1();
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioVitima1(url, metodo) {
        const cpf = $('#inputCPFVitima1').val();
        if (!validarCPF(cpf)) {
            mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
            return;
        }

        const formData = $('#formVitima1').serializeArray();
        const dataObj = {};
        formData.forEach(function (item) {
            dataObj[item.name] = item.value;
        });

        if (metodo === 'PUT') {
            dataObj._method = 'PUT';
        }

        $('#btnSalvarVitima1, #btnEditarVitima1').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: dataObj,
            success: function (response) {
                if (response.success) {
                    currentVitima1Id = response.id || currentVitima1Id;
                    mostrarSucesso(metodo === 'PUT' ? 'Vítima atualizada com sucesso!' : 'Vítima salva com sucesso!');

                    // ✅ SALVAR VÍNCULO APÓS SALVAR/EDITAR VITIMA1
                    setTimeout(salvarVinculoBoeVitima1, 500);

                    // Atualizar grid
                    const nome = ($('#inputNomeVitima1').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunhaVitima1').val() || 'N/A').trim();
                    const rg = ($('#inputRGVitima1').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPFVitima1').val() || 'N/A').trim();
                    const id = currentVitima1Id;
                    const $tbody = $('#tabelaResultadosVitima1 tbody');

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
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-vitima1" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        // Atualizar linha existente
                        const $btn = $(`button.btn-selecionar-vitima1[data-id="${id}"]`);
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
                                    <td><button type="button" class="btn btn-sm btn-success btn-selecionar-vitima1" data-id="${id}">Selecionar</button></td>
                                </tr>
                            `);
                            $tbody.prepend($novaLinha);
                            setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarVitima1, #btnExcluirVitima1').prop('disabled', false);
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
                $('#btnSalvarVitima1, #btnEditarVitima1').prop('disabled', false);
            }
        });
    }

    $('#btnSalvarVitima1').click(function () {
        enviarFormularioVitima1(rotas.vitima1.salvar, 'POST');
    });

    // ✅ BOTÃO EDITAR CORRIGIDO
    $('#btnEditarVitima1').click(function () {
        const nome = $('#inputNomeVitima1').val().trim();
        const vitima1Id = $('#vitima1_id').val() || currentVitima1Id;

        if (!nome) {
            mostrarErro('Preencha o nome da vítima antes de editar!');
            return;
        }

        if (!vitima1Id) {
            mostrarErro('Selecione uma vítima para editar!');
            return;
        }

        console.log('✏️ EDITANDO VITIMA1 ID:', vitima1Id);
        currentVitima1Id = vitima1Id;
        enviarFormularioVitima1(rotas.vitima1.atualizar + '/' + vitima1Id, 'PUT');
    });


    // === CRUD: EXCLUIR ===
    // === CRUD: EXCLUIR ===
    $('#btnExcluirVitima1').click(function () {
        const vitima1Id = $('#vitima1_id').val() || currentVitima1Id;

        if (!vitima1Id) {
            mostrarErro('Selecione uma vítima para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir esta vítima?', function () {
            const vitima1Id = $('#vitima1_id').val() || currentVitima1Id;
            if (!vitima1Id) return;

            const termoPesquisa = $('#termoPesquisaVitima1').val().trim();
            const filtro = $('#filtroVitima1').val();
            const boe = $('#inputBOE').val().trim();

            // ✅ PRIMEIRO EXCLUI O VÍNCULO
            if (boe && boe !== 'N/A') {
                console.log('🗑️ EXCLUINDO VÍNCULO VITIMA1 DO BOE:', boe);

                $.ajax({
                    url: '/boe/vinculos/excluir-vitima1/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (responseVinculo) {
                        if (responseVinculo.success) {
                            console.log('✅ VÍNCULO VITIMA1 EXCLUÍDO COM SUCESSO');
                        } else {
                            console.log('ℹ️ Vínculo vitima1 não encontrado ou já excluído');
                        }

                        // DEPOIS EXCLUI O REGISTRO
                        excluirRegistroVitima1();
                    },
                    error: function (xhr) {
                        console.error('❌ ERRO AO EXCLUIR VÍNCULO VITIMA1:', xhr.responseJSON);
                        excluirRegistroVitima1();
                    }
                });
            } else {
                excluirRegistroVitima1();
            }

            function excluirRegistroVitima1() {
                $.ajax({
                    url: rotas.vitima1.excluir + '/' + vitima1Id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            mostrarSucesso('Vítima excluída com sucesso!');

                            // LIMPA FORMULÁRIO
                            $('#formVitima1')[0].reset();
                            $('#inputIdadeVitima1').val('');
                            $('#btnEditarVitima1, #btnExcluirVitima1').prop('disabled', true);
                            currentVitima1Id = null;

                            // REMOVE DA GRID
                            $(`button.btn-selecionar-vitima1[data-id="${vitima1Id}"]`).closest('tr').remove();

                            if ($('#tabelaResultadosVitima1 tbody tr').length === 0) {
                                $('#tabelaResultadosVitima1 tbody').html('<tr><td colspan="5" class="text-center">Nenhuma vítima encontrada.</td></tr>');
                            }

                            if (termoPesquisa && $('#tabelaResultadosVitima1 tbody tr td').text().includes('Nenhum')) {
                                carregarGridVitima1(filtro, termoPesquisa);
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


    // === IMPRESSÃO DE DOCUMENTOS VÍTIMA 1 ===
    $('#btnImprimirDocumentoVitima1').click(function () {
        const nome = $('#inputNomeVitima1').val();
        const documentoSelecionado = $('#termoDocumentoVitima1').val().trim().toUpperCase();

        console.log('=== DEBUG IMPRESSÃO VITIMA1-APFD ===');
        console.log('📄 Documento selecionado:', documentoSelecionado);
        console.log('👤 Nome da vítima:', nome);

        // Validações básicas
        if (!nome) {
            mostrarErro('Por favor, preencha o nome da vítima.');
            return;
        }
        if (!documentoSelecionado) {
            mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        // ✅ CORREÇÃO: Verifica se as rotas de impressão da VÍTIMA 1 estão definidas
        if (typeof rotasImpressaoVitima1 === 'undefined') {
            console.error('❌ ERRO CRÍTICO: rotasImpressaoVitima1 não configurada');
            mostrarErro('Rotas de impressão não configuradas. Recarregue a página.');
            return;
        }

        // ✅✅✅ CORREÇÃO: DECIDE QUAL FUNÇÃO USAR BASEADO NO DOCUMENTO
        let dados;

        // Documentos que precisam de dados INDIVIDUAIS (apenas da vítima1)
        const documentosIndividuais = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AAFAI - VITIMA 1",
            "APFD - VITIMA 1",
            "CERTIDAO DE ASSINATURA INDIVIDUAL",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO IML",
            "PERICIA EM LOCAL DE CRIME"
        ];

        // Documentos que precisam de dados MÚLTIPLOS (todas as pessoas)
        const documentosMultiplos = [
            "CERTIDAO DE ASSINATURA APFD"
        ];

        if (documentosIndividuais.includes(documentoSelecionado)) {
            // Para documentos individuais, usa dados apenas da vítima1
            dados = {
                nome: nome,
                alcunha: $('#inputAlcunhaVitima1').val(),
                nascimento: $('#inputDataNascimentoVitima1').val(),
                idade: $('#inputIdadeVitima1').val(),
                estcivil: $('#inputEstadoCivilVitima1').val(),
                naturalidade: $('#inputNaturalidadeVitima1').val(),
                rg: $('#inputRGVitima1').val(),
                cpf: $('#inputCPFVitima1').val(),
                profissao: $('#inputProfissaoVitima1').val(),
                instrucao: $('#inputInstrucaoVitima1').val(),
                telefone: $('#inputTelefoneVitima1').val(),
                mae: $('#inputMaeVitima1').val(),
                pai: $('#inputPaiVitima1').val(),
                endereco: $('#inputEnderecoVitima1').val(),
                // Dados do wf_geral (formulário principal)
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
            console.log('📄 Usando dados INDIVIDUAIS para:', documentoSelecionado);
        } else if (documentosMultiplos.includes(documentoSelecionado)) {
            // Para documentos múltiplos, usa função que coleta dados de TODAS as pessoas
            dados = window.prepararDadosMultiplosAPFD?.() || prepararDadosMultiplosFallback();
            console.log('📄 Usando dados MÚLTIPLOS para:', documentoSelecionado);
        } else {
            // Fallback: usa dados individuais por padrão
            dados = {
                nome: nome,
                alcunha: $('#inputAlcunhaVitima1').val(),
                nascimento: $('#inputDataNascimentoVitima1').val(),
                idade: $('#inputIdadeVitima1').val(),
                estcivil: $('#inputEstadoCivilVitima1').val(),
                naturalidade: $('#inputNaturalidadeVitima1').val(),
                rg: $('#inputRGVitima1').val(),
                cpf: $('#inputCPFVitima1').val(),
                profissao: $('#inputProfissaoVitima1').val(),
                instrucao: $('#inputInstrucaoVitima1').val(),
                telefone: $('#inputTelefoneVitima1').val(),
                mae: $('#inputMaeVitima1').val(),
                pai: $('#inputPaiVitima1').val(),
                endereco: $('#inputEnderecoVitima1').val(),
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
            console.log('📄 Usando dados INDIVIDUAIS (fallback) para:', documentoSelecionado);
        }

        // Função fallback caso a função principal não exista
        function prepararDadosMultiplosFallback() {
            console.warn('⚠️ Função prepararDadosMultiplosAPFD não encontrada, usando fallback');
            return {
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
                // Vítima 1 (dados atuais)
                vitima1: {
                    nome: $('#inputNomeVitima1').val(),
                    alcunha: $('#inputAlcunhaVitima1').val(),
                    nascimento: $('#inputDataNascimentoVitima1').val(),
                    idade: $('#inputIdadeVitima1').val(),
                    estcivil: $('#inputEstadoCivilVitima1').val(),
                    naturalidade: $('#inputNaturalidadeVitima1').val(),
                    rg: $('#inputRGVitima1').val(),
                    cpf: $('#inputCPFVitima1').val(),
                    profissao: $('#inputProfissaoVitima1').val(),
                    instrucao: $('#inputInstrucaoVitima1').val(),
                    telefone: $('#inputTelefoneVitima1').val(),
                    mae: $('#inputMaeVitima1').val(),
                    pai: $('#inputPaiVitima1').val(),
                    endereco: $('#inputEnderecoVitima1').val()
                }
            };
        }

        console.log('📊 Dados coletados:', dados);

        // ✅ CORREÇÃO: Verifica se o documento existe nas rotas da VÍTIMA 1 APFD
        if (!rotasImpressaoVitima1[documentoSelecionado]) {
            console.error('❌ ERRO: Documento não encontrado nas rotas');
            console.log('📋 Documentos disponíveis:', Object.keys(rotasImpressaoVitima1));
            mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        }

        console.log('✅ Rota encontrada:', rotasImpressaoVitima1[documentoSelecionado]);

        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
        const url = rotasImpressaoVitima1[documentoSelecionado].replace('--DADOS--', dadosCodificados);

        console.log('🌐 URL final:', url);
        console.log('=== FIM DEBUG ===');

        window.open(url, "_blank");
    });


    // === AUTOCOMPLETE PARA DOCUMENTOS ===
    (function () {
        const documentos = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AAFAI - VITIMA 1", // ✅ MANTIDO
            "APFD - VITIMA 1",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL",
            "PERICIA EM LOCAL DE CRIME"
        ];
        let selectedIndex = -1;
        let sugestoesAtuais = [];

        const $inputDocumento = $('#termoDocumentoVitima1');
        const $sugestoesContainer = $('#sugestoesDocumentosVitima1');

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

// ✅ FUNÇÃO PARA ATUALIZAR DADOS NO AAFAI CONDUTOR
window.atualizarDadosAafaiCondutor = function () {
    const nome = $('#inputNomeVitima1').val().trim();
    const alcunha = $('#inputAlcunhaVitima1').val().trim();

    if (nome && window.parent && window.parent.dadosParaImpressao) {
        // Atualiza os dados no AAFAI Condutor
        window.parent.dadosParaImpressao.vitima1 = {
            nome: nome,
            alcunha: alcunha
        };
        console.log('✅ Dados da Vítima 1 atualizados no AAFAI Condutor:', { nome, alcunha });
    }
};

// Chame esta função sempre que os dados da vítima forem alterados
$('#inputNomeVitima1, #inputAlcunhaVitima1').on('change', function () {
    setTimeout(window.atualizarDadosAafaiCondutor, 100);
});

// Também chame quando selecionar uma vítima da grid
$(document).on('click', '.btn-selecionar-vitima1', function () {
    setTimeout(window.atualizarDadosAafaiCondutor, 300);
});
