// script_condutor.js - VERSÃO COMPLETA COM PREENCHIMENTO AUTOMÁTICO PARA INTIMAÇÃO
document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) { return; }
    var $ = window.jQuery;
    // === VERIFICA SE ESTÁ NA ABA CORRETA ===
    if (!$('#aba-condutor').length) return;
    if (window.__condutorInitialized) { return; }
    window.__condutorInitialized = true;

    console.log('✅ Script Condutor carregado na aba correta');

    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    if ($.fn.mask) {
        $('#inputDataNascimento').mask('00/00/0000');
        $('#inputCPF').mask('000.000.000-00', { reverse: true });
        $('#inputTelefone').mask('(00) 00000-0000');
    }

    // === VARIÁVEL GLOBAL ===
    let currentCondutorId = null;

    // === FUNÇÃO PARA OBSERVAR MUDANÇAS E TRANSMITIR DADOS ===
    function observarMudancasFormularioCondutor() {
        // Campos do formulário condutor que devem ser transmitidos
        const camposObservados = [
            '#inputNomeCondutor',
            '#inputEndereco',
            '#inputTelefone'
        ];

        camposObservados.forEach(function (seletor) {
            $(seletor).on('change input', function () {
                const dadosCondutor = {
                    nome: $('#inputNomeCondutor').val(),
                    endereco: $('#inputEndereco').val(),
                    telefone: $('#inputTelefone').val()
                };

                // Disparar evento para a intimação
                $(document).trigger('dadosCondutorAlterados', [dadosCondutor]);
                console.log('📤 Dados do CONDUTOR transmitidos para INTIMAÇÃO:', dadosCondutor);
            });
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

    // === FUNÇÕES DE MODAL - USANDO GLOBAIS DO SCRIPT.JS ===
    // As funções locais mostrarSucessoCondutor e mostrarErroCondutor foram removidas
    // para usar as versões globais window.mostrarSucesso e window.mostrarErro
    // que possuem o design profissional atualizado e gerenciamento correto do DOM.

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
                    $('#inputEstadoCivil').val(c.EstCivil || '');
                    $('#inputNaturalidade').val(c.Naturalidade || '');
                    $('#inputProfissao').val(c.Profissao || '');
                    $('#inputInstrucao').val(c.Instrucao || '');
                    $('#inputRG').val(c.RG || '');
                    $('#inputCPF').val(c.CPF || '');
                    $('#inputTelefone').val(c.Telefone || '');
                    $('#inputMae').val(c.Mae || '');
                    $('#inputPai').val(c.Pai || '');
                    $('#inputEndereco').val(c.Endereco || '');

                    $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

                    // === TRANSMITIR DADOS PARA INTIMAÇÃO AO SELECIONAR CONDUTOR ===
                    const dadosCondutor = {
                        nome: c.Nome || '',
                        endereco: c.Endereco || '',
                        telefone: c.Telefone || ''
                    };
                    $(document).trigger('dadosCondutorAlterados', [dadosCondutor]);
                    console.log('📤 Dados do CONDUTOR (selecionado) transmitidos para INTIMAÇÃO:', dadosCondutor);

                } else {
                    window.mostrarErro(response.message || 'Erro ao buscar condutor');
                }
            },
            error: function (xhr) {
                window.mostrarErro('Erro ao carregar condutor: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // === LIMPAR / NOVO CONDUTOR ===
    $('#btnNovoCondutor, #btnLimparCondutor').click(function () {
        currentCondutorId = null;
        $('#formCondutor')[0].reset();
        $('#inputIdade').val('');
        $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', true);
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioCondutor(url, metodo) {
        const cpf = $('#inputCPF').val();
        if (!validarCPF(cpf)) {
            window.mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
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
                    window.mostrarSucesso(metodo === 'PUT' ? 'Condutor atualizado com sucesso!' : 'Condutor salvo com sucesso!');

                    // Atualizar grid
                    const nome = ($('#inputNomeCondutor').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunha').val() || 'N/A').trim();
                    const rg = ($('#inputRG').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPF').val() || 'N/A').trim();
                    const id = currentCondutorId;
                    const $tbody = $('#tabelaResultadosCondutor tbody');

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
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-condutor" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        // Atualizar linha existente
                        const $btn = $(`button.btn-selecionar-condutor[data-id="${id}"]`);
                        if ($btn.length) {
                            const $tr = $btn.closest('tr');
                            $tr.find('td').eq(0).text(nome);
                            $tr.find('td').eq(1).text(alcunha);
                            $tr.find('td').eq(2).text(rg);
                            $tr.find('td').eq(3).text(cpfFormatado);
                            $tr.addClass('table-success');
                            setTimeout(() => $tr.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarCondutor, #btnExcluirCondutor').prop('disabled', false);

                    // TRANSMITIR DADOS PARA INTIMAÇÃO APÓS SALVAR/EDITAR
                    const dadosCondutor = {
                        nome: nome,
                        endereco: $('#inputEndereco').val() || '',
                        telefone: $('#inputTelefone').val() || ''
                    };
                    $(document).trigger('dadosCondutorAlterados', [dadosCondutor]);

                } else {
                    window.mostrarErro(response.message || 'Erro ao processar');
                }
            },
            error: function (xhr) {
                const messages = [];
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        for (const field in errors) {
                            errors[field].forEach(function (msg) {
                                messages.push('<strong>' + field + ':</strong> ' + msg);
                            });
                        }
                    } else if (xhr.responseJSON?.message) {
                        messages.push(xhr.responseJSON.message);
                    }
                } else {
                    messages.push(xhr.responseJSON?.message || 'Erro ao processar');
                }
                window.mostrarErro(messages.join('<br>'));
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
        if (currentCondutorId) {
            enviarFormularioCondutor(rotas.condutor.atualizar + '/' + currentCondutorId, 'PUT');
        }
    });

    // === CRUD: EXCLUIR ===
    $('#btnExcluirCondutor').click(function () {
        if (!currentCondutorId) {
            window.mostrarErro('Selecione um condutor para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este condutor?', function () {
            if (!currentCondutorId) return;
            const termoPesquisa = $('#termoPesquisaCondutor').val().trim();
            const filtro = $('#filtroCondutor').val();
            const condutorId = currentCondutorId;

            $.ajax({
                url: rotas.condutor.excluir + '/' + currentCondutorId,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.success) {
                        // $('#modalConfirmacaoGenerico').modal('hide'); // Já tratado pelo callback genérico
                        window.mostrarSucesso('Condutor excluído com sucesso!');

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
                        window.mostrarErro(response.message);
                    }
                },
                error: function (xhr) {
                    window.mostrarErro('Erro ao excluir: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                }
            });
        });
    });

    // === IMPRESSÃO DE DOCUMENTOS ===
    $('#btnImprimirDocumentoCondutor').click(function () {
        // Captura os valores dos campos do condutor
        const nome = $('#inputNomeCondutor').val();
        const documentoSelecionado = $('#termoDocumentoCondutor').val().trim().toUpperCase();

        // DEBUG: Verifique se os campos estão sendo capturados
        console.log('Data completa (inputDataComp):', $('#inputDataComp').val());
        console.log('Data simples (inputData):', $('#inputData').val());
        console.log('Data por extenso (inputDataExt):', $('#inputDataExt').val());

        // Validações básicas
        if (!nome) {
            window.mostrarErro('Por favor, preencha o nome do envolvido.');
            return;
        }
        if (!documentoSelecionado) {
            window.mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        // Verifica se as rotas de impressão estão definidas
        if (typeof rotasImpressao === 'undefined') {
            window.mostrarErro('Rotas de impressão não configuradas. Recarregue a página.');
            return;
        }

        // Captura os valores dos campos do wf_geral (formulário principal)
        const data = $('#inputData').val();
        const dataComp = $('#inputDataComp').val(); // ✅ NOVO: Captura data completa
        const dataExtenso = $('#inputDataExt').val();
        const cidade = $('#inputCidade').val();
        const delegado = $('#inputDelegado').val();
        const escrivao = $('#inputEscrivao').val();
        const delegacia = $('#inputDelegacia').val();
        const boe = $('#inputBOE').val();
        const apreensao = $('#inputApreensao').val();

        // Cria um objeto com todos os dados necessários
        const dados = {
            nome: nome,
            alcunha: $('#inputAlcunha').val(),
            nascimento: $('#inputDataNascimento').val(),
            idade: $('#inputIdade').val(),
            estcivil: $('#inputEstadoCivil').val(),
            naturalidade: $('#inputNaturalidade').val(),
            rg: $('#inputRG').val(),
            cpf: $('#inputCPF').val(),
            profissao: $('#inputProfissao').val(),
            instrucao: $('#inputInstrucao').val(),
            telefone: $('#inputTelefone').val(),
            mae: $('#inputMae').val(),
            pai: $('#inputPai').val(),
            endereco: $('#inputEndereco').val(),
            // Dados do wf_geral (formulário principal)
            data: data,
            data_comp: dataComp, // ✅ ADICIONADO: Data completa
            data_ext: dataExtenso,  // ✅ CORREÇÃO: mudar para data_ext
            cidade: cidade,
            delegado: delegado,
            escrivao: escrivao,
            delegacia: delegacia,
            boe: boe,
            apreensao: apreensao
        };

        if (!rotasImpressao[documentoSelecionado]) {
            window.mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        }

        const dadosCodificados = btoa(unescape(encodeURIComponent(JSON.stringify(dados))));
        const url = rotasImpressao[documentoSelecionado].replace('--DADOS--', dadosCodificados);
        window.open(url, "_blank");
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
            "LAUDO TRAUMATOLOGICO IML",
            "OFICIOS MANDADO DE PRISAO"
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

    // === INICIALIZAR OBSERVAÇÃO DE MUDANÇAS ===
    observarMudancasFormularioCondutor();
    console.log('✅ Sistema de transmissão de dados CONDUTOR → INTIMAÇÃO ativado!');
});
