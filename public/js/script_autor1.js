// script_autor1.js - VERSÃO CORRIGIDA PARA VALORES ACIMA DE 999
$(document).ready(function () {
    // === Configuração global de CSRF ===
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // === MÁSCARAS PARA OS CAMPOS ===
    $('#inputDataNascimentoAutor1').mask('00/00/0000');
    $('#inputCPFAutor1').mask('000.000.000-00', { reverse: true });
    $('#inputTelefoneAutor1').mask('(00) 00000-0000');
    $('#inputFiancaAutor1').mask('#.##0,00', {
        reverse: true,
        translation: {
            '#': { pattern: /[0-9]/, recursive: true }
        },
        onKeyPress: function (value, e, field, options) {
            // Dispara a conversão para extenso enquanto digita
            setTimeout(function () {
                const valorFianca = $('#inputFiancaAutor1').val().trim();
                if (valorFianca) {
                    const valorExtenso = converterValorParaExtenso(valorFianca);
                    $('#inputFiancaExtAutor1').val(valorExtenso);
                }
            }, 100);
        }
    });

    // === VARIÁVEL GLOBAL ===
    let currentAutor1Id = null;

    // === FUNÇÃO PARA CONVERTER VALOR PARA EXTENSO (CORRIGIDA) ===
    function converterValorParaExtenso(valor) {
        if (!valor || valor === '0,00' || valor === '0') {
            return '';
        }

        // Remove formatação e converte para número
        const valorNumerico = parseFloat(valor.replace(/\./g, '').replace(',', '.'));

        if (isNaN(valorNumerico) || valorNumerico <= 0) {
            return '';
        }

        // Função interna para converter números (CORRIGIDA PARA MILHARES)
        function numeroParaExtenso(numero) {
            if (numero === 0) return 'zero';

            const unidades = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
            const dezAteDezenove = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
            const dezenas = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
            const centenas = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

            let extenso = '';

            // MILHARES
            if (numero >= 1000) {
                const milhares = Math.floor(numero / 1000);
                if (milhares === 1) {
                    extenso += 'mil';
                } else {
                    extenso += numeroParaExtenso(milhares) + ' mil';
                }
                numero %= 1000;
                if (numero > 0) extenso += ' ';
            }

            // CENTENAS
            if (numero >= 100) {
                const c = Math.floor(numero / 100);
                if (numero === 100) {
                    extenso += 'cem';
                } else {
                    extenso += centenas[c];
                }
                numero %= 100;
                if (numero > 0) extenso += ' e ';
            }

            // DEZENAS E UNIDADES
            if (numero >= 10 && numero <= 19) {
                extenso += dezAteDezenove[numero - 10];
            } else if (numero >= 20) {
                const d = Math.floor(numero / 10);
                const u = numero % 10;
                extenso += dezenas[d];
                if (u > 0) extenso += ' e ' + unidades[u];
            } else if (numero > 0) {
                extenso += unidades[numero];
            }

            return extenso;
        }

        // Separa reais e centavos
        const reais = Math.floor(valorNumerico);
        const centavos = Math.round((valorNumerico - reais) * 100);

        let resultado = '';

        // Converte reais
        if (reais > 0) {
            if (reais === 1) {
                resultado = numeroParaExtenso(reais) + ' real';
            } else {
                resultado = numeroParaExtenso(reais) + ' reais';
            }
        }

        // Converte centavos
        if (centavos > 0) {
            if (resultado) resultado += ' e ';
            if (centavos === 1) {
                resultado += numeroParaExtenso(centavos) + ' centavo';
            } else {
                resultado += numeroParaExtenso(centavos) + ' centavos';
            }
        }

        // Capitaliza a primeira letra
        return resultado.charAt(0).toUpperCase() + resultado.slice(1);
    }

    // ✅ FUNÇÃO CORRIGIDA: SALVAR VÍNCULO BOE-AUTOR1
    function salvarVinculoBoeAutor1() {
        const boe = $('#inputBOE').val().trim();
        const autor1Id = currentAutor1Id || $('#autor1_id').val();

        console.log('💾 SALVANDO VÍNCULO BOE-AUTOR1:', { boe, autor1Id });

        if (!boe || boe === 'N/A' || !autor1Id) {
            console.log('⚠️ Dados insuficientes para salvar vínculo:', { boe, autor1Id });
            return;
        }

        $.ajax({
            url: '/boe/vinculos/salvar',
            method: 'POST',
            data: {
                boe: boe,
                autor1_id: autor1Id,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO AUTOR1 SALVO COM SUCESSO:', response);
                } else {
                    console.error('❌ ERRO AO SALVAR VÍNCULO AUTOR1:', response.message);
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AJAX AO SALVAR VÍNCULO AUTOR1:', xhr.responseJSON);
            }
        });
    }

    // ✅ FUNÇÃO CORRIGIDA: EXCLUIR VÍNCULO ESPECÍFICO DO AUTOR1
    function excluirVinculoBoeAutor1() {
        const boe = $('#inputBOE').val().trim();

        if (!boe || boe === 'N/A') {
            console.log('⚠️ BOE não disponível para excluir vínculo autor1');
            return;
        }

        console.log('🗑️ EXCLUINDO VÍNCULO AUTOR1 DO BOE:', boe);

        $.ajax({
            url: '/boe/vinculos/excluir-autor1/' + encodeURIComponent(boe),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    console.log('✅ VÍNCULO AUTOR1 EXCLUÍDO COM SUCESSO');
                } else {
                    console.log('ℹ️ Vínculo autor1 não encontrado ou já excluído');
                }
            },
            error: function (xhr) {
                console.error('❌ ERRO AO EXCLUIR VÍNCULO AUTOR1:', xhr.responseJSON);
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
    $('#inputDataNascimentoAutor1').on('change', function () {
        $('#inputIdadeAutor1').val(calcularIdade($(this).val()));
    });

    // ✅ EVENTO: CONVERSÃO AUTOMÁTICA DA FIANÇA
    $('#inputFiancaAutor1').on('input change blur', function () {
        const valorFianca = $(this).val().trim();

        if (valorFianca) {
            const valorExtenso = converterValorParaExtenso(valorFianca);
            $('#inputFiancaExtAutor1').val(valorExtenso);
        } else {
            $('#inputFiancaExtAutor1').val('');
        }
    });

    // === GRID DE PESQUISA DE AUTOR1 ===
    function carregarGridAutor1(filtro, termo) {
        if (!termo) {
            $('#tabelaResultadosAutor1 tbody').html('<tr><td colspan="5" class="text-center">Digite um termo para pesquisa</td></tr>');
            return;
        }

        $.ajax({
            url: rotas.autor1.pesquisar,
            method: "POST",
            data: {
                filtro: filtro,
                termo: termo
            },
            success: function (response) {
                const tbody = $('#tabelaResultadosAutor1 tbody').empty();
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const row = `
                            <tr>
                                <td>${item.Nome || ''}</td>
                                <td>${item.Alcunha || ''}</td>
                                <td>${item.RG || ''}</td>
                                <td>${item.CPF || ''}</td>
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-autor1" data-id="${item.IdCad}">Selecionar</button></td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">Nenhum autor encontrado.</td></tr>');
                }
            },
            error: function (xhr) {
                const erro = xhr.responseJSON?.message || 'Erro na pesquisa';
                $('#tabelaResultadosAutor1 tbody').html('<tr><td colspan="5" class="text-center">' + erro + '</td></tr>');
                console.error('Erro na pesquisa:', xhr);
            }
        });
    }

    $('#btnPesquisarAutor1').click(function () {
        carregarGridAutor1($('#filtroAutor1').val(), $('#termoPesquisaAutor1').val().trim());
    });

    // Máscara de CPF no campo de pesquisa de autor
    $('#termoPesquisaAutor1').on('input', function() {
        if ($('#filtroAutor1').val() !== 'CPF') return;
        let val = $(this).val().replace(/\D/g, '').substring(0, 11);
        if (val.length > 0) {
            val = val.replace(/(\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3})(\d)/, '$1.$2')
                     .replace(/(\d{3}\.\d{3}\.\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(val);
        }
    });
    $('#filtroAutor1').on('change', function() {
        const c = $('#termoPesquisaAutor1');
        c.val('');
        if ($(this).val() === 'CPF') { c.attr('maxlength', 14).attr('placeholder', '000.000.000-00'); }
        else { c.removeAttr('maxlength').attr('placeholder', 'Digite para pesquisar...'); }
    });

    // === SELEÇÃO DE AUTOR1 DA GRID ===
    $(document).on('click', '.btn-selecionar-autor1', function () {
        const id = $(this).data('id');
        currentAutor1Id = id;

        $.ajax({
            url: rotas.autor1.buscar + '/' + id,
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const c = response.data;
                    $('#autor1_id').val(c.IdCad);
                    $('#inputNomeAutor1').val(c.Nome || '');
                    $('#inputAlcunhaAutor1').val(c.Alcunha || '');

                    if (c.Nascimento) {
                        const partes = c.Nascimento.split('-');
                        if (partes.length === 3) {
                            $('#inputDataNascimentoAutor1').val(`${partes[2]}/${partes[1]}/${partes[0]}`);
                        } else {
                            $('#inputDataNascimentoAutor1').val(c.Nascimento);
                        }
                    } else {
                        $('#inputDataNascimentoAutor1').val('');
                    }

                    $('#inputIdadeAutor1').val(calcularIdade($('#inputDataNascimentoAutor1').val()));
                    $('#inputEstadoCivilAutor1').val(c.EstCivil || '');
                    $('#inputNaturalidadeAutor1').val(c.Naturalidade || '');
                    $('#inputProfissaoAutor1').val(c.Profissao || '');
                    $('#inputInstrucaoAutor1').val(c.Instrucao || '');
                    $('#inputRGAutor1').val(c.RG || '');
                    $('#inputCPFAutor1').val(c.CPF || '').trigger('input');
                    $('#inputTelefoneAutor1').val(c.Telefone || '(00) 00000-0000').trigger('input');
                    $('#inputMaeAutor1').val(c.Mae || '');
                    $('#inputPaiAutor1').val(c.Pai || '');
                    $('#inputEnderecoAutor1').val(c.Endereco || '');

                    // Dados Complementares
                    $('#inputTipoPenalAutor1').val(c.TipoPenal || '');
                    $('#inputFiancaAutor1').val(c.Fianca ? parseFloat(c.Fianca).toFixed(2) : '');
                    $('#inputFiancaExtAutor1').val(c.FiancaExt || '');
                    $('#inputFiancaPagoAutor1').prop('checked', !!c.FiancaPago);
                    $('#inputParenteAutor1').val(c.Parente || '');
                    $('#inputFamiliaAutor1').val(c.Familia || '');
                    $('#inputAdvogadoAutor1').val(c.Advogado || '');
                    $('#inputJuizMandadoAutor1').val(c.JuizMandado || '');
                    $('#inputComarcaMandadoAutor1').val(c.ComarcaMandado || '');
                    $('#inputNmandadoAutor1').val(c.Nmandado || '');
                    $('#inputDataMandadoAutor1').val(c.DataMandado || '');
                    $('#inputRecolherAutor1').val(c.Recolher || '');
                    $('#inputOfJuizAutor1').val(c.OfJuiz || '');
                    $('#inputOfPromotorAutor1').val(c.OfPromotor || '');
                    $('#inputOfDefensorAutor1').val(c.OfDefensor || '');
                    $('#inputOfCustodiaAutor1').val(c.OfCustodia || '');

                    // ✅ DISPARA CONVERSÃO DA FIANÇA EXISTENTE
                    $('#inputFiancaAutor1').trigger('input');

                    setFiancaBadge($('#inputFiancaPagoAutor1').prop('checked', !!c.FiancaPago).is(':checked'));
                    $('#btnEditarAutor1, #btnExcluirAutor1').prop('disabled', false);

                    // ✅ SALVAR VÍNCULO AO SELECIONAR AUTOR1 DA GRID
                    setTimeout(salvarVinculoBoeAutor1, 300);
                } else {
                    mostrarErro(response.message || 'Erro ao buscar autor');
                }
            },
            error: function (xhr) {
                mostrarErro('Erro ao carregar autor: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
            }
        });
    });

    // ✅ FUNÇÃO PARA PREENCHER AUTOR1 VINCULADA (EXTERNA)
    window.preencherAutor1Vinculada = function (dados) {
        console.log('🔄 PREENCHENDO AUTOR1 DO VÍNCULO:', dados);

        currentAutor1Id = dados.IdCad;

        $('#autor1_id').val(dados.IdCad || '');
        $('#inputNomeAutor1').val(dados.Nome || '');
        $('#inputAlcunhaAutor1').val(dados.Alcunha || '');

        if (dados.Nascimento) {
            const dataNasc = new Date(dados.Nascimento);
            if (!isNaN(dataNasc.getTime())) {
                $('#inputDataNascimentoAutor1').val(dataNasc.toLocaleDateString('pt-BR'));

                // Calcular idade inline
                const hoje = new Date();
                let idade = hoje.getFullYear() - dataNasc.getFullYear();
                const m = hoje.getMonth() - dataNasc.getMonth();
                if (m < 0 || (m === 0 && hoje.getDate() < dataNasc.getDate())) {
                    idade--;
                }
                $('#inputIdadeAutor1').val(idade);
            }
        }

        $('#inputEstadoCivilAutor1').val(dados.EstCivil || '');
        $('#inputNaturalidadeAutor1').val(dados.Naturalidade || '');
        $('#inputProfissaoAutor1').val(dados.Profissao || '');
        $('#inputInstrucaoAutor1').val(dados.Instrucao || '');
        $('#inputRGAutor1').val(dados.RG || '');
        $('#inputCPFAutor1').val(dados.CPF || '').trigger('input');
        // Preenche com (00) 00000-0000 se vazio, conforme solicitado
        $('#inputTelefoneAutor1').val(dados.Telefone || '(00) 00000-0000').trigger('input');
        $('#inputMaeAutor1').val(dados.Mae || '');
        $('#inputPaiAutor1').val(dados.Pai || '');
        $('#inputEnderecoAutor1').val(dados.Endereco || '');

        // Dados Complementares
        $('#inputTipoPenalAutor1').val(dados.TipoPenal || '');
        $('#inputFiancaAutor1').val(dados.Fianca ? parseFloat(dados.Fianca).toFixed(2) : '');
        $('#inputFiancaExtAutor1').val(dados.FiancaExt || '');
        $('#inputFiancaPagoAutor1').prop('checked', !!dados.FiancaPago);
        $('#inputParenteAutor1').val(dados.Parente || '');
        $('#inputFamiliaAutor1').val(dados.Familia || '');
        $('#inputAdvogadoAutor1').val(dados.Advogado || '');
        $('#inputJuizMandadoAutor1').val(dados.JuizMandado || '');
        $('#inputComarcaMandadoAutor1').val(dados.ComarcaMandado || '');
        $('#inputNmandadoAutor1').val(dados.Nmandado || '');
        $('#inputDataMandadoAutor1').val(dados.DataMandado || '');
        $('#inputRecolherAutor1').val(dados.Recolher || '');
        $('#inputOfJuizAutor1').val(dados.OfJuiz || '');
        $('#inputOfPromotorAutor1').val(dados.OfPromotor || '');
        $('#inputOfDefensorAutor1').val(dados.OfDefensor || '');
        $('#inputOfCustodiaAutor1').val(dados.OfCustodia || '');

        // ✅ DISPARA CONVERSÃO DA FIANÇA EXISTENTE
        $('#inputFiancaAutor1').trigger('input');

        setFiancaBadge($('#inputFiancaPagoAutor1').prop('checked', !!dados.FiancaPago).is(':checked'));
        $('#btnEditarAutor1, #btnExcluirAutor1').prop('disabled', false);

        console.log('✅ AUTOR1 VINCULADA PREENCHIDA - ID:', currentAutor1Id);
    };

    // === LIMPAR / NOVO AUTOR1 ===
    $('#btnNovoAutor1, #btnLimparAutor1').click(function () {
        currentAutor1Id = null;
        $('#formAutor1')[0].reset();
        $('#formDadosComplementares')[0].reset();
        $('#inputIdadeAutor1').val('');
        $('#inputDataMandadoAutor1').val('');
        $('#btnEditarAutor1, #btnExcluirAutor1').prop('disabled', true);

        // ✅ REMOVIDO: Excluir vínculo ao criar novo autor (conflitava com sistema de chips)
        // excluirVinculoBoeAutor1();
    });

    // === CRUD: SALVAR E EDITAR ===
    function enviarFormularioAutor1(url, metodo) {
        const cpf = $('#inputCPFAutor1').val();
        if (!validarCPF(cpf)) {
            mostrarErro('<strong>CPF:</strong> CPF inválido ou incompleto.');
            return;
        }

        // Serializa ambos os formulários
        const formPessoal = $('#formAutor1').serializeArray();
        const formComplementar = $('#formDadosComplementares').serializeArray();

        // Combina os dados dos dois formulários
        const dataObj = {};
        formPessoal.forEach(function (item) { dataObj[item.name] = item.value; });
        formComplementar.forEach(function (item) { dataObj[item.name] = item.value; });

        // Normaliza campo de fiança paga (checkbox)
        dataObj.FiancaPago = $('#inputFiancaPagoAutor1').is(':checked') ? 1 : 0;

        // Formata a fiança para decimal
        if (dataObj.Fianca) {
            dataObj.Fianca = parseFloat(dataObj.Fianca.replace(/\./g, '').replace(',', '.'));
        }

        if (metodo === 'PUT') {
            dataObj._method = 'PUT';
        }

        $('#btnSalvarAutor1, #btnEditarAutor1').prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: dataObj,
            success: function (response) {
                if (response.success) {
                    currentAutor1Id = response.id || currentAutor1Id;
                    mostrarSucesso(metodo === 'PUT' ? 'Autor atualizado com sucesso!' : 'Autor salvo com sucesso!');

                    // ✅ SALVAR VÍNCULO APÓS SALVAR/EDITAR AUTOR1
                    setTimeout(salvarVinculoBoeAutor1, 500);

                    // Atualizar grid
                    const nome = ($('#inputNomeAutor1').val() || 'N/A').trim();
                    const alcunha = ($('#inputAlcunhaAutor1').val() || 'N/A').trim();
                    const rg = ($('#inputRGAutor1').val() || 'N/A').trim();
                    const cpfFormatado = ($('#inputCPFAutor1').val() || 'N/A').trim();
                    const id = currentAutor1Id;
                    const $tbody = $('#tabelaResultadosAutor1 tbody');

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
                                <td><button type="button" class="btn btn-sm btn-success btn-selecionar-autor1" data-id="${id}">Selecionar</button></td>
                            </tr>
                        `);

                        $tbody.prepend($novaLinha);
                        setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                    } else {
                        const $btn = $(`button.btn-selecionar-autor1[data-id="${id}"]`);
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
                                    <td><button type="button" class="btn btn-sm btn-success btn-selecionar-autor1" data-id="${id}">Selecionar</button></td>
                                </tr>
                            `);
                            $tbody.prepend($novaLinha);
                            setTimeout(() => $novaLinha.removeClass('table-success'), 2000);
                        }
                    }

                    $('#btnEditarAutor1, #btnExcluirAutor1').prop('disabled', false);
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
                $('#btnSalvarAutor1, #btnEditarAutor1').prop('disabled', false);
            }
        });
    }

    $('#btnSalvarAutor1').click(function () {
        enviarFormularioAutor1(rotas.autor1.salvar, 'POST');
    });

    $('#btnEditarAutor1').click(function () {
        const nome = $('#inputNomeAutor1').val().trim();
        const autor1Id = $('#autor1_id').val() || currentAutor1Id;

        if (!nome) {
            mostrarErro('Preencha o nome do autor antes de editar!');
            return;
        }

        if (!autor1Id) {
            mostrarErro('Selecione um autor para editar!');
            return;
        }

        console.log('✏️ EDITANDO AUTOR1 ID:', autor1Id);
        currentAutor1Id = autor1Id;
        enviarFormularioAutor1(rotas.autor1.atualizar + '/' + autor1Id, 'PUT');
    });

    // === CRUD: EXCLUIR ===
    // === CRUD: EXCLUIR ===
    $('#btnExcluirAutor1').click(function () {
        const autor1Id = $('#autor1_id').val() || currentAutor1Id;

        if (!autor1Id) {
            mostrarErro('Selecione um autor para excluir!');
            return;
        }

        window.confirmarExclusaoGenerica('Tem certeza que deseja excluir este autor?', function () {
            const autor1Id = $('#autor1_id').val() || currentAutor1Id;
            if (!autor1Id) return;

            const termoPesquisa = $('#termoPesquisaAutor1').val().trim();
            const filtro = $('#filtroAutor1').val();
            const boe = $('#inputBOE').val().trim();

            if (boe && boe !== 'N/A') {
                $.ajax({
                    url: '/boe/vinculos/excluir-autor1/' + encodeURIComponent(boe),
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (responseVinculo) {
                        console.log(responseVinculo.success ? '✅ VÍNCULO EXCLUÍDO' : 'ℹ️ Vínculo não encontrado');
                        excluirRegistroAutor1();
                    },
                    error: function (xhr) {
                        console.error('❌ ERRO AO EXCLUIR VÍNCULO:', xhr.responseJSON);
                        excluirRegistroAutor1();
                    }
                });
            } else {
                excluirRegistroAutor1();
            }

            function excluirRegistroAutor1() {
                $.ajax({
                    url: rotas.autor1.excluir + '/' + autor1Id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            $('#modalConfirmacaoGenerico').modal('hide');
                            mostrarSucesso('Autor excluído com sucesso!');

                            $('#formAutor1')[0].reset();
                            $('#formDadosComplementares')[0].reset();
                            $('#inputIdadeAutor1').val('');
                            $('#inputDataMandadoAutor1').val('');
                            $('#btnEditarAutor1, #btnExcluirAutor1').prop('disabled', true);
                            currentAutor1Id = null;

                            $(`button.btn-selecionar-autor1[data-id="${autor1Id}"]`).closest('tr').remove();

                            if ($('#tabelaResultadosAutor1 tbody tr').length === 0) {
                                $('#tabelaResultadosAutor1 tbody').html('<tr><td colspan="5" class="text-center">Nenhum autor encontrado.</td></tr>');
                            }

                            if (termoPesquisa && $('#tabelaResultadosAutor1 tbody tr td').text().includes('Nenhum')) {
                                carregarGridAutor1(filtro, termoPesquisa);
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

    function setFiancaBadge(checked) {
        const $b = $('#badgeFiancaPagoAutor1');
        if (!$b.length) return;
        if (checked) {
            $b.text('PAGA').removeClass('bg-secondary bg-danger').addClass('bg-success');
        } else {
            $b.text('NÃO PAGA').removeClass('bg-secondary bg-success').addClass('bg-danger');
        }
    }
    $('#inputFiancaPagoAutor1').on('change', function () {
        setFiancaBadge($(this).is(':checked'));
    });

    // === IMPRESSÃO DE DOCUMENTOS AUTOR1 (ASYNC VERSION) ===
    $('#btnImprimirDocumentoAutor1').click(async function () {
        const nome = $('#inputNomeAutor1').val();
        const documentoSelecionado = $('#termoDocumentoAutor1').val().trim().toUpperCase();

        if (!nome) {
            mostrarErro('Por favor, preencha o nome do autor.');
            return;
        }
        if (!documentoSelecionado) {
            mostrarErro('Por favor, selecione ou digite o nome do documento.');
            return;
        }

        if (typeof rotasImpressaoAutor1 === 'undefined') {
            mostrarErro('Rotas de impressão não configuradas.');
            return;
        }

        if (!rotasImpressaoAutor1[documentoSelecionado]) {
            mostrarErro(`Documento "${documentoSelecionado}" não está configurado!`);
            return;
        }

        const originalBtnText = $(this).html();
        $(this).html('<span class="spinner-border spinner-border-sm"></span> Gerando...').prop('disabled', true);

        try {
            // ✅ LISTAS REAIS DE DOCUMENTOS
            const documentosIndividuais = [
                "TERMO DE DECLARACAO",
                "TERMO DE DEPOIMENTO",
                "TERMO DE INTERROGATORIO",
                "AAFAI - AUTOR 1",
                "APFD - AUTOR 1",
                "NOTA DE CULPA",
                "NOTA DE CIENCIA - GARANTIAS CONSTITUCIONAIS",
                "AUTO DE APRESENTACAO E APREENSAO",
                "TERMO DE RESTITUICAO",
                "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
                "TERMO DE REPRESENTACAO",
                "TERMO DE COMPROMISSO",
                "TERMO DE LIBERACAO DE MENOR - INFRATOR",
                "LAUDO TRAUMATOLOGICO",
                "LAUDO TRAUMATOLOGICO IML",
                "CERTIDAO DE ASSINATURA INDIVIDUAL",
                "AUTO CIRCUNSTACIADO - AUTOR 1",
                "MANDADO DE PRISAO - OFICIOS",
                "MANDADO DE PRISAO - OFICIO FAMILIA",
                "APFD - OFICIO FAMILIA",
                "MANDADO DE PRISAO - RECOLHIMENTO",
                "PERICIA EM LOCAL DE CRIME"
            ];

            const documentosMultiplos = [
                "COMUNICACAO DE APFD",
                "COMUNICACAO DE APFD - UNICO OFICIO",
                "CERTIDAO DE ASSINATURA APFD"
            ];

            let dados;

            // ✅ Monta dados LEVES para documentos individuais
            if (documentosIndividuais.includes(documentoSelecionado)) {
                dados = {
                    // Dados principais do BO/APFD
                    data: $('#inputData').val(),
                    data_fato: (() => {
                        const val = $('#inputDataFato').val();
                        if (!val) return 'NÃO INFORMADO';
                        if (val.includes('-')) {
                            const [ano, mes, dia] = val.split('-');
                            return `${dia}/${mes}/${ano}`;
                        }
                        return val;
                    })(),
                    data_comp: $('#inputDataComp').val(),
                    data_ext: $('#inputDataExt').val(),
                    cidade: $('#inputCidade').val(),
                    delegado: $('#inputDelegado').val(),
                    escrivao: $('#inputEscrivao').val(),
                    delegacia: $('#inputDelegacia').val(),
                    boe: $('#inputBOE').val(),
                    ip: $('#inputIP').val(),
                    apreensao: $('#inputApreensao').val(),
                    nmandado: $('#inputNmandadoAutor1').val() || '',
                    datamandado: $('#inputDataMandadoAutor1').val() || '',
                    incidencia_penal: $('#inputIncidenciaPenal').val() || $('#inputTipoPenalAutor1').val() || '',
                    tipificacao: $('#inputTipoPenalAutor1').val() || $('#inputIncidenciaPenal').val() || '', // ✅ Prioriza Tipo Penal do Autor
                    fianca_pago: $('#inputFiancaPagoAutor1').is(':checked'),
                    fianca: $('#inputFiancaAutor1').val(),
                    fianca_ext: $('#inputFiancaExtAutor1').val(),
                    hora_fato: $('#inputHoraFato').val() || '',
                    end_fato: $('#inputEndFato').val() || '',

                    // Condutor, vítima e testemunhas
                    condutor: { nome: $('#inputNomeCondutor').val() || 'NÃO INFORMADO' },
                    vitima1: { nome: $('#inputNomeVitima1').val() || 'NÃO INFORMADO' },
                    testemunha1: { nome: (typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos && OcorrenciasApp.envolvidos.testemunhas && OcorrenciasApp.envolvidos.testemunhas[0]) ? OcorrenciasApp.envolvidos.testemunhas[0] : ($('#inputNomeTestemunha1').val() || 'NÃO INFORMADO') },
                    testemunha2: { nome: (typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos && OcorrenciasApp.envolvidos.testemunhas && OcorrenciasApp.envolvidos.testemunhas[1]) ? OcorrenciasApp.envolvidos.testemunhas[1] : ($('#inputNomeTestemunha2').val() || 'NÃO INFORMADO') },

                    // Autor principal do documento (flat + nested para compatibilidade)
                    nome: nome,
                    tipopenal: $('#inputTipoPenalAutor1').val() || '',
                    autor1: {
                        nome: nome || 'NÃO INFORMADO',
                        tipopenal: $('#inputTipoPenalAutor1').val() || '',
                        fianca_pago: $('#inputFiancaPagoAutor1').is(':checked'),
                        fianca: $('#inputFiancaAutor1').val(),
                        fianca_ext: $('#inputFiancaExtAutor1').val()
                    },
                    alcunha: $('#inputAlcunhaAutor1').val() || '',
                    nascimento: $('#inputDataNascimentoAutor1').val() || '',
                    idade: $('#inputIdadeAutor1').val() || '',
                    estcivil: $('#inputEstadoCivilAutor1').val() || '',
                    naturalidade: $('#inputNaturalidadeAutor1').val() || '',
                    rg: $('#inputRGAutor1').val() || '',
                    cpf: $('#inputCPFAutor1').val() || '',
                    profissao: $('#inputProfissaoAutor1').val() || '',
                    instrucao: $('#inputInstrucaoAutor1').val() || '',
                    telefone: $('#inputTelefoneAutor1').val() || '',
                    mae: $('#inputMaeAutor1').val() || '',
                    pai: $('#inputPaiAutor1').val() || '',
                    endereco: $('#inputEnderecoAutor1').val() || '',
                    fianca: $('#inputFiancaAutor1').val() || '',
                    fianca_ext: $('#inputFiancaExtAutor1').val() || '',
                    advogado: $('#inputAdvogadoAutor1').val() || '',
                    parente: $('#inputParenteAutor1').val() || 'NÃO INFORMADO',
                    familia: $('#inputFamiliaAutor1').val() || 'NÃO INFORMADO'
                };
            }
            // ✅ Monta dados COMPLETOS com BUSCA ASSÍNCRONA para documentos múltiplos
            else if (documentosMultiplos.includes(documentoSelecionado)) {

                // 1. Identificar todos os autores que precisam ser buscados
                const App = (typeof OcorrenciasApp !== 'undefined') ? OcorrenciasApp : (window.OcorrenciasApp || null);

                const listaAutores = (App && App.envolvidos && App.envolvidos.autores)
                    ? App.envolvidos.autores
                    : [nome]; // Fallback para apenas o atual se não tiver lista global

                const listaVinculos = (App && App.vinculos && App.vinculos.autores)
                    ? App.vinculos.autores
                    : [];

                console.log('🔍 Iniciando busca de dados para impressão:', { listaAutores, listaVinculos });

                // 2. Buscar dados de cada autor em paralelo
                const promises = listaAutores.map(async (nomeAutor, index) => {
                    const vinculo = listaVinculos[index];
                    const id = vinculo ? (vinculo.pessoa_id || vinculo.id_cad) : null;

                    // Se for o autor que está sendo editado AGORA no formulário, usamos os dados do form (mais atualizados)
                    // Verificamos pelo ID selecionado ou pelo nome se não tiver ID
                    const isCurrentForm = (id && id == $('#autor1_id').val()) || (!id && nomeAutor === $('#inputNomeAutor1').val());

                    if (isCurrentForm) {
                        return {
                            nome: $('#inputNomeAutor1').val() || 'NÃO INFORMADO',
                            tipopenal: $('#inputTipoPenalAutor1').val() || 'NÃO INFORMADO',
                            fianca: $('#inputFiancaAutor1').val() || '',
                            fianca_ext: $('#inputFiancaExtAutor1').val() || '',
                            fianca_pago: $('#inputFiancaPagoAutor1').is(':checked')
                        };
                    }

                    // Se tem ID, busca no banco
                    if (id) {
                        try {
                            const resp = await $.get(`/autor1/buscar/${id}`);
                            const d = resp.data || resp;
                            // Normalização de booleanos vindos do banco
                            const pg = (d.FiancaPago === 1 || d.FiancaPago === '1' || d.FiancaPago === true || d.fianca_pago === 1 || d.fianca_pago === true);
                            return {
                                nome: d.Nome || nomeAutor,
                                tipopenal: d.TipoPenal || 'NÃO INFORMADO',
                                fianca: d.Fianca ? parseFloat(d.Fianca).toFixed(2).replace('.', ',').replace(/\d(?=(\d{3})+\,)/g, '$&.') : '', // Formatação simples visual
                                fianca_raw: d.Fianca, // Mantém raw se o PHP precisar
                                fianca_ext: d.FiancaExt || '',
                                fianca_pago: pg
                            };
                        } catch (err) {
                            console.error(`❌ Erro ao buscar autor ${id}:`, err);
                            return { nome: nomeAutor, tipopenal: 'ERRO AO BUSCAR' };
                        }
                    }

                    // Se não tem ID, retorna só o nome (autor adicionado manualmente sem salvar?)
                    return { nome: nomeAutor, tipopenal: 'NÃO INFORMADO' };
                });

                const autoresCompletos = await Promise.all(promises);
                console.log('✅ Dados completos dos autores para impressão:', autoresCompletos);

                dados = {
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
                    nmandado: $('#inputNmandadoAutor1').val() || '',
                    datamandado: $('#inputDataMandadoAutor1').val() || '',
                    incidencia_penal: $('#inputIncidenciaPenal').val() || $('#inputTipoPenalAutor1').val() || '',
                    fianca_pago: $('#inputFiancaPagoAutor1').is(':checked'),

                    condutor: { nome: $('#inputNomeCondutor').val() || 'NÃO INFORMADO' },

                    autor1: {
                        nome: $('#inputNomeAutor1').val() || 'NÃO INFORMADO',
                        tipopenal: $('#inputTipoPenalAutor1').val() || '',
                        fianca: $('#inputFiancaAutor1').val() || '',
                        fianca_ext: $('#inputFiancaExtAutor1').val() || '',
                        fianca_pago: $('#inputFiancaPagoAutor1').is(':checked')
                    },
                    autor2: {
                        nome: $('#inputNomeAutor2').val() || 'NÃO INFORMADO',
                        tipopenal: $('#inputTipoPenalAutor2').val() || '',
                        fianca: $('#inputFiancaAutor2').val() || '',
                        fianca_ext: $('#inputFiancaExtAutor2').val() || ''
                    },
                    autor3: {
                        nome: $('#inputNomeAutor3').val() || 'NÃO INFORMADO',
                        tipopenal: $('#inputTipoPenalAutor3').val() || '',
                        fianca: $('#inputFiancaAutor3').val() || '',
                        fianca_ext: $('#inputFiancaExtAutor3').val() || ''
                    },

                    vitima1: { nome: $('#inputNomeVitima1').val() || 'NÃO INFORMADO' },
                    vitima2: { nome: $('#inputNomeVitima2').val() || 'NÃO INFORMADO' },
                    vitima3: { nome: $('#inputNomeVitima3').val() || 'NÃO INFORMADO' },

                    testemunha1: {
                        nome: (typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos && OcorrenciasApp.envolvidos.testemunhas && OcorrenciasApp.envolvidos.testemunhas[0]) ? OcorrenciasApp.envolvidos.testemunhas[0] : ($('#inputNomeTestemunha1').val() || 'NÃO INFORMADO')
                    },
                    testemunha2: {
                        nome: (typeof OcorrenciasApp !== 'undefined' && OcorrenciasApp.envolvidos && OcorrenciasApp.envolvidos.testemunhas && OcorrenciasApp.envolvidos.testemunhas[1]) ? OcorrenciasApp.envolvidos.testemunhas[1] : ($('#inputNomeTestemunha2').val() || 'NÃO INFORMADO')
                    },
                    testemunha3: { nome: $('#inputNomeTestemunha3').val() || 'NÃO INFORMADO' },

                    // Manter compatibilidade
                    nome: $('#inputNomeAutor1').val() || 'NÃO INFORMADO',
                    fianca: $('#inputFiancaAutor1').val() || '',
                    fianca_ext: $('#inputFiancaExtAutor1').val() || '',

                    // ✅ LISTA DINÂMICA DE AUTORES AGORA 100% PREENCHIDA
                    autores: autoresCompletos
                };
            }
            else {
                // Caso genérico de fallback (opcional)
                dados = {
                    nome: nome,
                    data: $('#inputData').val(),
                    boe: $('#inputBOE').val(),
                    cidade: $('#inputCidade').val(),
                    fianca: $('#inputFiancaAutor1').val() || '',
                    fianca_ext: $('#inputFiancaExtAutor1').val() || '',
                    hora_fato: $('#inputHoraFato').val() || '',
                    end_fato: $('#inputEndFato').val() || ''
                };
            }

            // ✅ USANDO O NOVO SERVIÇO CENTRALIZADO (EVITA URLs LONGAS)
            DocumentoService.gerar(rotasImpressaoAutor1[documentoSelecionado], dados);

        } catch (error) {
            console.error(error);
            mostrarErro('Erro ao preparar documento: ' + error.message);
        } finally {
            $(this).html(originalBtnText).prop('disabled', false);
        }
    });

    // === AUTOCOMPLETE PARA DOCUMENTOS ===
    (function () {
        const documentos = [
            "TERMO DE DECLARACAO",
            "TERMO DE DEPOIMENTO",
            "TERMO DE INTERROGATORIO",
            "AAFAI - AUTOR 1",
            "APFD - AUTOR 1",
            "NOTA DE CULPA",
            "NOTA DE CIENCIA - GARANTIAS CONSTITUCIONAIS",
            "AUTO DE APRESENTACAO E APREENSAO",
            "TERMO DE RESTITUICAO",
            "TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO",
            "TERMO DE REPRESENTACAO",
            "TERMO DE COMPROMISSO",
            "TERMO DE LIBERACAO DE MENOR - INFRATOR",
            "LAUDO TRAUMATOLOGICO",
            "LAUDO TRAUMATOLOGICO IML",
            "CERTIDAO DE ASSINATURA INDIVIDUAL",
            "AUTO CIRCUNSTACIADO - AUTOR 1",
            "COMUNICACAO DE APFD",
            "COMUNICACAO DE APFD - UNICO OFICIO",
            "MANDADO DE PRISAO - OFICIOS",
            "MANDADO DE PRISAO - OFICIO FAMILIA",
            "APFD - OFICIO FAMILIA",
            "MANDADO DE PRISAO - RECOLHIMENTO"
        ];

        let selectedIndex = -1;
        let sugestoesAtuais = [];
        const $inputDocumento = $('#termoDocumentoAutor1');
        const $sugestoesContainer = $('#sugestoesDocumentosAutor1');

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
