// script_condutor_apfd.js - VERSÃO OTIMIZADA COM BUSCA AUTOMÁTICA
$(document).ready(function () {
    // === ✅✅✅ OBJETO DE ROTAS DE IMPRESSÃO (CORRIGIDO) ===
    const rotasImpressaoCondutor = window.RotasImpressao?.condutor || {
        // ✅ DOCUMENTOS INDIVIDUAIS
        "TERMO DE DECLARACAO": "/declaracao/--DADOS--",
        "TERMO DE DEPOIMENTO": "/depoimento/--DADOS--",
        "TERMO DE INTERROGATORIO": "/interrogatorio/--DADOS--",
        "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
        "AUTO DE APRESENTACAO E APREENSAO": "/auto-apreensao/--DADOS--",
        "TERMO DE RESTITUICAO": "/documentos/termo-restituicao/--DADOS--",
        "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO": "/documentos/termo-renuncia-representacao/--DADOS--",
        "TERMO DE REPRESENTACAO": "/documentos/termo-representacao/--DADOS--",
        "TERMO DE COMPROMISSO": "/documentos/termo-compromisso-juizo/--DADOS--",
        "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
        "PERICIA EM LOCAL DE CRIME": "/pericia-local-de-crime/--DADOS--",

        // ✅ DOCUMENTOS MÚLTIPLOS
        "CERTIDAO DE ASSINATURA APFD": "/certidao-assinaturas-apfd/--DADOS--",
        "AAFAI CONDUTOR": "/aafai-condutor/--DADOS--",
        "APFD CONDUTOR": "/apfd-condutor/--DADOS--"
    };

    console.log('✅ ROTAS DE IMPRESSÃO CARREGADAS:', Object.keys(rotasImpressaoCondutor).length, 'documentos');

    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    $('#inputDataNascimento').mask('00/00/0000');
    $('#inputCPF').mask('000.000.000-00', { reverse: true });
    $('#inputTelefone').mask('(00) 00000-0000');

    // === VARIÁVEL GLOBAL ===
    let currentCondutorId = null;

    // ✅ CONFIGURAÇÃO CENTRALIZADA DOS CAMPOS (SOLUÇÃO 2)
    const mapeamentoCampos = {
        nome: 'Nome',
        alcunha: 'Alcunha',
        nascimento: 'DataNascimento',
        idade: 'Idade',
        estcivil: 'EstadoCivil',
        naturalidade: 'Naturalidade',
        rg: 'RG',
        cpf: 'CPF',
        profissao: 'Profissao',
        instrucao: 'Instrucao',
        telefone: 'Telefone',
        mae: 'Mae',
        pai: 'Pai',
        endereco: 'Endereco'
    };

    // ✅ FUNÇÃO MELHORADA PARA BUSCAR DADOS AUTOMATICAMENTE
    function buscarDadosAutomaticamente(prefixo) {
        const dados = {};

        Object.entries(mapeamentoCampos).forEach(([chave, campo]) => {
            const idCampo = `input${campo}${prefixo}`;
            const elemento = document.getElementById(idCampo);
            dados[chave] = elemento ? elemento.value || '' : '';
        });

        return dados;
    }

    // ✅ FUNÇÃO PARA BUSCAR DADOS DO CONDUTOR (CASO ESPECIAL)
    function buscarDadosCondutor() {
        return {
            nome: $('#inputNomeCondutor').val() || 'NÃO INFORMADO',
            alcunha: $('#inputAlcunha').val() || '',
            nascimento: $('#inputDataNascimento').val() || '',
            idade: $('#inputIdade').val() || '',
            estcivil: $('#inputEstadoCivil').val() || '',
            naturalidade: $('#inputNaturalidade').val() || '',
            rg: $('#inputRG').val() || '',
            cpf: $('#inputCPF').val() || '',
            profissao: $('#inputProfissao').val() || '',
            instrucao: $('#inputInstrucao').val() || '',
            telefone: $('#inputTelefone').val() || '',
            mae: $('#inputMae').val() || '',
            pai: $('#inputPai').val() || '',
            endereco: $('#inputEndereco').val() || ''
        };
    }

    // ✅ FUNÇÃO CORRIGIDA: SALVAR VÍNCULO BOE-CONDUTOR
    function salvarVinculoBoeCondutor() {
        const boe = $('#inputBOE').val().trim();
        const condutorId = currentCondutorId || $('#condutor_id').val();

        console.log('💾 SALVANDO VÍNCULO BOE-CONDUTOR:', { boe, condutorId });

        if (!boe || boe === 'N/A' || !condutorId) {
            console.log('⚠️ Dados insuficientes para salvar vínculo:', { boe, condutorId });
            return;
        }

        $.ajax({
            url: '/boe/vinculos/salvar',
            method: 'POST',
            data: {
                boe: boe,
                condutor_id: condutorId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO SALVO COM SUCESSO:', response);
                } else {
                    console.error('❌ ERRO AO SALVAR VÍNCULO:', response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AJAX AO SALVAR VÍNCULO:', xhr.responseJSON);
            }
        });
    }

    // ✅ FUNÇÃO CORRIGIDA: EXCLUIR VÍNCULO ESPECÍFICO DO CONDUTOR
    function excluirVinculoBoeCondutor() {
        const boe = $('#inputBOE').val().trim();

        if (!boe || boe === 'N/A') {
            console.log('⚠️ BOE não disponível para excluir vínculo condutor');
            return;
        }

        console.log('🗑️ EXCLUINDO VÍNCULO CONDUTOR DO BOE:', boe);

        $.ajax({
            url: '/boe/vinculos/excluir-condutor/' + encodeURIComponent(boe),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO CONDUTOR EXCLUÍDO COM SUCESSO');
                } else {
                    console.log('ℹ️ Vínculo condutor não encontrado ou já excluído');
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AO EXCLUIR VÍNCULO CONDUTOR:', xhr.responseJSON);
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
    $('#inputDataNascimento').on('change', function () {
        $('#inputIdade').val(calcularIdade($(this).val()));
    });

    // === GRID DE PESQUISA DE CONDUTOR ===
    function carregarGridCondutor(filtro, termo) {
        if (!termo) {
            $('#tabelaResultadosCondutor tbody').html('<tr><td colspan="5" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }

        $.ajax({
            url: rotas.condutor.pesquisar,
            method: "GET",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                const tbody = $('#tabelaResultadosCondutor tbody').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const row = `
                            <tr>
                                <td>${item.Nome || ''}</td>
                                <td>${item.Alcunha || ''}</td>
                                <td>${item.RG || ''}</td>
                                <td>${item.CPF || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-condutor" data-id="${item.IdCad}">Selecionar</button></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">Nenhum condutor encontrado.</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#tabelaResultadosCondutor tbody').html('<tr><td colspan="5" class="text-center">' + erro + '</td></tr>');
                console.error('Erro na pesquisa:', xhr);
            }
        });
    }

    $('#btnPesquisarCondutor').click(function () {
        carregarGridCondutor($('#filtroCondutor').val(), $('#termoPesquisaCondutor').val().trim());
    });

    // Máscara de CPF no campo de pesquisa de condutor
    $('#termoPesquisaCondutor').on('input', function() {
        if ($('#filtroCondutor').val() !== 'CPF') return;
        let val = $(this).val().replace(/\D/g, '').substring(0, 11);
        if (val.length > 0) {
            val = val.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(val);
        }
    });
    $('#filtroCondutor').on('change', function() {
        const c = $('#termoPesquisaCondutor');
        c.val('');
        if ($(this).val() === 'CPF') { c.attr('maxlength', 14).attr('placeholder', '000.000.000-00'); }
        else { c.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...'); }
    });

    // === SELEÇÃO DE CONDUTOR DA GRID ===
    $(document).on('click', '.btn-selecionar-condutor', function () {
        const id = $(this).data('id');
        currentCondutorId = id;

        $.ajax({
            url: rotas.condutor.buscar + '/' + id,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const c = response.data;
                    $('#condutor_id').val(c.IdCad);
                    $('#inputNomeCondutor').val(c.Nome || '');
                    $('#inputAlcunha').val(c.Alcunha || '');

                    if (c.Nascimento) {
                        const partes = c.Nascimento.split('-');
                        if (partes.length === 3) {
                            $('#inputDataNascimento').val(`${partes[2]}/${partes[1]}/${partes[0]}`);
                        } else {
                            $('#inputDataNascimento').val(c.Nascimento);
                        }
                    } else {
                        $('#inputDataNascimento').val('');
                    }

                    $('#inputIdade').val(calcularIdade($('#inputDataNascimento').val()));
                    $('#inputEstadoCivil').val(c.EstCivil || c.estado_civil || '');
                    $('#inputNaturalidade').val(c.Naturalidade || c.naturalidade || '');
                    $('#inputProfissao').val(c.Profissao || c.profissao || '');
                    $('#inputInstrucao').val(c.Instrucao || c.instrucao || c.escolaridade || '');
                    $('#inputRG').val(c.RG || c.rg || '');
                    $('#inputCPF').val(c.CPF || c.cpf || '').trigger('input');
                    $('#inputTelefone').val(c.Telefone || c.telefone || '(00) 00000-0000').trigger('input');
                    $('#inputMae').val(c.Mae || c.mae || '');
                    $('#inputPai').val(c.Pai || c.pai || '');
                    $('#inputEndereco').val(c.Endereco || c.endereco || '');

                    $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

                    // ✅ SALVAR VÍNCULO AO SELECIONAR CONDUTOR DA GRID
                    setTimeout(salvarVinculoBoeCondutor, 300);
                } else {
                    mostrarErro(response.message || 'Erro ao buscar condutor');
                }
            },
            error: function (xhr) {
                mostrarErro('Erro ao carregar condutor: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // ✅ FUNÇÃO PARA PREENCHER CONDUTOR VINCULADO (EXTERNA)
    window.preencherCondutorVinculado = function (dados) {
        console.log('🔄 PREENCHENDO CONDUTOR DO VÍNCULO:', dados);

        currentCondutorId = dados.IdCad;

        $('#condutor_id').val(dados.IdCad || '');
        $('#inputNomeCondutor').val(dados.Nome || '');
        $('#inputAlcunha').val(dados.Alcunha || '');

        if (dados.Nascimento) {
            const dataNasc = new Date(dados.Nascimento);
            if (!isNaN(dataNasc.getTime())) {
                $('#inputDataNascimento').val(dataNasc.toLocaleDateString('pt-BR'));
                $('#inputIdade').val(calcularIdade($('#inputDataNascimento').val()));
            }
        }

        $('#inputEstadoCivil').val(dados.EstCivil || dados.estado_civil || '');
        $('#inputNaturalidade').val(dados.Naturalidade || dados.naturalidade || '');
        $('#inputProfissao').val(dados.Profissao || dados.profissao || '');
        $('#inputInstrucao').val(dados.Instrucao || dados.instrucao || dados.escolaridade || '');
        $('#inputRG').val(dados.RG || dados.rg || '');
        $('#inputCPF').val(dados.CPF || dados.cpf || '').trigger('input');
        $('#inputTelefone').val(dados.Telefone || dados.telefone || '(00) 00000-0000').trigger('input');
        $('#inputMae').val(dados.Mae || dados.mae || '');
        $('#inputPai').val(dados.Pai || dados.pai || '');
        $('#inputEndereco').val(dados.Endereco || dados.endereco || '');

        $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

        console.log('✅ CONDUTOR VINCULADO PREENCHIDO - ID:', currentCondutorId);
    };

    // === LIMPAR / NOVO CONDUTOR ===
    $('#btnNovoCondutor, #btnLimparCondutor').click(function () {
        currentCondutorId = null;
        $('#formCondutor')[0].reset();
        $('#inputIdade').val('');
        $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', true);

        // ✅ REMOVIDO: Excluir vínculo ao criar novo condutor (conflitava com sistema de chips)
        // excluirVinculoBoeCondutor();
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioCondutor(url, metodo) {
        const cpf = $('#inputCPF').val();
        if (!validarCPF(cpf)) {
            mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
            return;
        }

        const formData = $('#formCondutor').serializeArray();
        const dataObj = {};
        formData.forEach(function (item) {
            dataObj[item.name] = item.value;
        });

        if (metodo === 'PUT') {
            dataObj._method = 'PUT';
        }

        $('#btnSalvarCondutor, #btnEditarCondutor').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: dataObj,
            success: function (response) {
                if (response.success) {
                    currentCondutorId = response.id || currentCondutorId;
                    mostrarSucesso(metodo === 'PUT' ? 'Condutor atualizado com sucesso!' : 'Condutor salvo com sucesso!');

                    // ✅ SALVAR VÍNCULO APÓS SALVAR/EDITAR CONDUTOR
                    setTimeout(salvarVinculoBoeCondutor, 500);

                    // Atualizar grid
                    const nome = ($('#inputNomeCondutor').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunha').val() || 'N/A').trim();
                    const rg = ($('#inputRG').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPF').val() || 'N/A').trim();
                    const id = currentCondutorId;
                    const $tbody = $('#tabelaResultadosCondutor tbody');

                    if (metodo === 'POST') {
                        if ($tbody.find('td[colspan]').length || $tbody.text().includes('Nenhum')) {
                            $tbody.empty();
                        }

                        const $novaLinha = $(`
                            <tr class="table-success">
                                <td>${nome}</td>
                                <td>${alcunha}</td>
                                <td>${rg}</td>
                                <td>${cpfFormatado}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-condutor" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        const $btn = $(`button.btn-selecionar-condutor[data-id="${id}"]`);
                        if ($btn.length) {
                            const $tr = $btn.closest('tr');
                            $tr.find('td').eq(0).text(nome);
                            $tr.find('td').eq(1).text(alcunha);
                            $tr.find('td').eq(2).text(rg);
                            $tr.find('td').eq(3).text(cpfFormatado);
                            $tr.addClass('table-success');
                            setTimeout(() => $tr.removeClass('table-success'), 2000);
                        } else {
                            if ($tbody.find('td[colspan]').length || $tbody.text().includes('Nenhum')) {
                                $tbody.empty();
                            }
                            const $novaLinha = $(`
                                <tr class="table-success">
                                    <td>${nome}</td>
                                    <td>${alcunha}</td>
                                    <td>${rg}</td>
                                    <td>${cpfFormatado}</td>
                                    <td><button type="button" class="btn btn-sm btn-success btn-selecionar-condutor" data-id="${id}">Selecionar</button></td>
                                </tr>
                            `);
                            $tbody.prepend($novaLinha);
                            setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);
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
                $('#btnSalvarCondutor, #btnEditarCondutor').prop('disabled', false);
            }
        });
    }

    $('#btnSalvarCondutor').click(function () {
        enviarFormularioCondutor(rotas.condutor.salvar, 'POST');
    });

    $('#btnEditarCondutor').click(function () {
        const nome = $('#inputNomeCondutor').val().trim();
        const condutorId = $('#condutor_id').val() || currentCondutorId;

        if (!nome) {
            mostrarErro('Preencha o nome do condutor antes de editar!');
            return;
        }

        if (!condutorId) {
            mostrarErro('Selecione um condutor para editar!');
            return;
        }

        console.log('✏️ EDITANDO CONDUTOR ID:', condutorId);
        currentCondutorId = condutorId;
        enviarFormularioCondutor(rotas.condutor.atualizar + '/' + condutorId, 'PUT');
    });

    // === CRUD: EXCLUIR ===
    // === CRUD: EXCLUIR ===
    $('#btnExcluirCondutor').click(function () {
        const condutorId = $('#condutor_id').val() || currentCondutorId;

        if (!condutorId) {
            mostrarErro('Selecione um condutor para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este condutor?', function () {
            const condutorId = $('#condutor_id').val() || currentCondutorId;
            if (!condutorId) return;

            const termoPesquisa = $('#termoPesquisaCondutor').val().trim();
            const filtro = $('#filtroCondutor').val();
            const boe = $('#inputBOE').val().trim();

            if (boe && boe !== 'N/A') {
                $.ajax({
                    url: '/boe/vinculos/excluir-condutor/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (responseVinculo) {
                        console.log(responseVinculo.success ? '✅ VÍNCULO EXCLUÍDO' : 'ℹ️ Vínculo não encontrado');
                        excluirRegistroCondutor();
                    },
                    error: function (xhr) {
                        console.error('❌ ERRO AO EXCLUIR VÍNCULO:', xhr.responseJSON);
                        excluirRegistroCondutor();
                    }
                });
            } else {
                excluirRegistroCondutor();
            }

            function excluirRegistroCondutor() {
                $.ajax({
                    url: rotas.condutor.excluir + '/' + condutorId,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            mostrarSucesso('Condutor excluído com sucesso!');

                            $('#formCondutor')[0].reset();
                            $('#inputIdade').val('');
                            $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', true);
                            currentCondutorId = null;

                            $(`button.btn-selecionar-condutor[data-id="${condutorId}"]`).closest('tr').remove();

                            if ($('#tabelaResultadosCondutor tbody tr').length === 0) {
                                $('#tabelaResultadosCondutor tbody').html('<tr><td colspan="5" class="text-center">Nenhum condutor encontrado.</td></tr>');
                            }

                            if (termoPesquisa && $('#tabelaResultadosCondutor tbody tr td').text().includes('Nenhum')) {
                                carregarGridCondutor(filtro, termoPesquisa);
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
    // Removido para usar as funções globais window.mostrarSucesso e window.mostrarErro definidas em script.js


    // === IMPRESSÃO DE DOCUMENTOS CONDUTOR === (VERSÃO COMPLETA OTIMIZADA)
    $('#btnImprimirDocumentoCondutor').click(function () {
        const nome = $('#inputNomeCondutor').val();
        const documentoSelecionado = $('#termoDocumentoCondutor').val().trim().toUpperCase();

        if (!nome) {
            mostrarErro('Por favor, preencha o nome do condutor.');
            return;
        }
        if (!documentoSelecionado) {
            mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        if (typeof rotasImpressaoCondutor === 'undefined') {
            mostrarErro('Rotas de impressão não configuradas.');
            return;
        }

        if (!rotasImpressaoCondutor[documentoSelecionado]) {
            mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        }

        try {
            // ✅ CAPTURA CENTRALIZADA E ROBUSTA (Chips + Formulários)
            let dados = DocumentoService.capturarDadosGlobais();

            // ✅ SOBREPOSIÇÃO COM DADOS ATUAIS DA ABA (Garante que o que foi digitado agora seja usado)
            const dadosAtuaisCondutor = {
                nome: $('#inputNomeCondutor').val(),
                alcunha: ($('#inputAlcunha').val() || '').toUpperCase(),
                nascimento: $('#inputDataNascimento').val(),
                idade: $('#inputIdade').val(),
                rg: $('#inputRG').val(),
                cpf: $('#inputCPF').val(),
                mae: ($('#inputMae').val() || '').toUpperCase(),
                pai: ($('#inputPai').val() || '').toUpperCase(),
                endereco: ($('#inputEndereco').val() || '').toUpperCase(),
                profissao: ($('#inputProfissao').val() || '').toUpperCase(),
                naturalidade: ($('#inputNaturalidade').val() || '').toUpperCase(),
                estcivil: ($('#inputEstadoCivil').val() || '').toUpperCase(),
                instrucao: ($('#inputInstrucao').val() || '').toUpperCase(),
                telefone: $('#inputTelefone').val()
            };

            // Mescla no objeto principal e também no objeto condutor para compatibilidade
            Object.assign(dados, dadosAtuaisCondutor);
            dados.condutor = dadosAtuaisCondutor;

            const rota = rotasImpressaoCondutor[documentoSelecionado];
            console.log('🚀 Enviando para DocumentoService:', { documentoSelecionado, dados });

            // Usa o DocumentoService para gerar (trata POST e Cache automaticamente)
            DocumentoService.gerar(rota, dados);

        } catch (error) {
            console.error('❌ Erro ao preparar documento:', error);
            mostrarErro('Erro ao preparar os dados para o documento.');
        }
    });

    // === AUTOCOMPLETE PARA DOCUMENTOS ===
    (function () {
        const documentos = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "LAUDO TRAUMATOLOGICO",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL",
            "CERTIDAO DE ASSINATURA APFD",
            "AAFAI CONDUTOR",
            "APFD CONDUTOR"
        ];

        let selectedIndex = -1;
        let sugestoesAtuais = [];
        const $inputDocumento = $('#termoDocumentoCondutor');
        const $sugestoesContainer = $('#sugestoesDocumentosCondutor');

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
            const termo = $(this).val();
            const sugestoes = filtrarSugestoes(termo);
            mostrarSugestoes(sugestoes);
        });

        $inputDocumento.on('keydown', function (e) {
            if (!$sugestoesContainer.is(':visible')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, sugestoesAtuais.length - 1);
                atualizarSelecao();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                atualizarSelecao();
            } else if (e.key === 'Enter' && selectedIndex >= 0) {
                e.preventDefault();
                $inputDocumento.val(sugestoesAtuais[selectedIndex]);
                $sugestoesContainer.hide();
            } else if (e.key === 'Escape') {
                $sugestoesContainer.hide();
            }
        });

        function atualizarSelecao() {
            $sugestoesContainer.find('a').removeClass('active');
            if (selectedIndex >= 0) {
                $sugestoesContainer.find(`a[data-index="${selectedIndex}"]`).addClass('active');
            }
        }

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#termoDocumentoCondutor, #sugestoesDocumentosCondutor').length) {
                $sugestoesContainer.hide();
            }
        });
    })();

    console.log('✅ SCRIPT CONDUTOR CARREGADO - VERSÃO COM BUSCA AUTOMÁTICA');
});
