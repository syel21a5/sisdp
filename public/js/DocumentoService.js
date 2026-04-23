/**
 * DocumentoService.js
 * Centraliza a lógica de geração de documentos (PDF/Editor) do SisDP.
 * Resolve o problema de URLs Longas (Erro 414) usando POST + Cache Session.
 */
const DocumentoService = {

    /**
     * ✅ CAMPOS QUE DEVEM SER CAIXA ALTA (Requisito Legal)
     */
    _camposUpperCase: ['nome', 'alcunha', 'mae', 'pai', 'profissao', 'naturalidade', 'estcivil', 'instrucao', 'endereco'],

    /**
     * ✅ MAPA DE ALIASES para encontrar campos independente do nome
     */
    _mapCampos: {
        'nome': ['Nome', 'nome', 'name'],
        'alcunha': ['Alcunha', 'alcunha', 'apelido', 'Apelido'],
        'nascimento': ['Nascimento', 'nascimento', 'data_nascimento', 'DataNascimento'],
        'idade': ['Idade', 'idade'],
        'rg': ['RG', 'rg', 'Rg'],
        'cpf': ['CPF', 'cpf', 'Cpf'],
        'pai': ['Pai', 'pai'],
        'mae': ['Mae', 'mae', 'Mãe'],
        'endereco': ['Endereco', 'endereco', 'Endereço', 'endereço'],
        'profissao': ['Profissao', 'profissao', 'Profissão', 'profissão'],
        'naturalidade': ['Naturalidade', 'naturalidade'],
        'estcivil': ['EstCivil', 'estcivil', 'estado_civil', 'EstadoCivil'],
        'instrucao': ['Instrucao', 'instrucao', 'Instrução', 'grau_instrucao', 'escolaridade', 'Escolaridade'],
        'telefone': ['Telefone', 'telefone', 'tel'],
        'tipopenal': ['TipoPenal', 'tipopenal', 'tipo_penal', 'incidencia_penal'],
        'fianca': ['Fianca', 'fianca', 'Fiança'],
        'fianca_ext': ['FiancaExt', 'fianca_ext', 'Fiança_ext'],
        'fianca_pago': ['FiancaPago', 'fianca_pago']
    },

    /**
     * ✅ Extrai um valor de um objeto tentando múltiplos nomes de campo
     */
    _extrairValor: function(obj, campo) {
        if (!obj) return '';
        const aliases = this._mapCampos[campo] || [campo];
        for (const alias of aliases) {
            const val = obj[alias];
            if (val !== undefined && val !== null && String(val).trim() !== '') {
                let resultado = String(val).trim();
                if (this._camposUpperCase.includes(campo)) {
                    resultado = resultado.toUpperCase();
                }
                return resultado;
            }
        }
        return '';
    },

    /**
     * ✅ Normaliza um objeto de pessoa (chip ou form data) para formato padrão
     */
    _normalizarPessoa: function(envolvidoChip) {
        if (!envolvidoChip) return {};

        // 1. Extrair dados do chip (pode vir como array de {name,value} ou objeto)
        let chipDados = {};
        if (envolvidoChip.dados) {
            if (Array.isArray(envolvidoChip.dados)) {
                envolvidoChip.dados.forEach(f => {
                    if (f.name && f.value !== undefined) chipDados[f.name] = f.value;
                });
            } else if (typeof envolvidoChip.dados === 'object') {
                chipDados = { ...envolvidoChip.dados };
            }
        }

        // 2. Mesclar propriedades diretas do chip (SEM sobrescrever com 'dados')
        Object.keys(envolvidoChip).forEach(key => {
            if (key !== 'dados' && key !== '$$hashKey') {
                chipDados[key] = envolvidoChip[key];
            }
        });

        // 3. Normalizar cada campo usando o mapa de aliases
        const norm = {};
        const camposPessoa = ['nome', 'alcunha', 'nascimento', 'idade', 'rg', 'cpf',
            'pai', 'mae', 'endereco', 'profissao', 'naturalidade', 'estcivil',
            'instrucao', 'telefone', 'tipopenal', 'fianca', 'fianca_ext', 'fianca_pago'];

        camposPessoa.forEach(campo => {
            norm[campo] = this._extrairValor(chipDados, campo);
        });

        // 4. Normalizar data de nascimento (YYYY-MM-DD → DD/MM/YYYY)
        if (norm.nascimento && norm.nascimento.includes('-')) {
            const p = norm.nascimento.split('-');
            if (p.length === 3) norm.nascimento = `${p[2]}/${p[1]}/${p[0]}`;
        }

        return norm;
    },

    /**
     * ✅ Lê dados de um envolvido diretamente dos campos do formulário (fallback)
     * @param {string} tipo - 'Autor1', 'Vitima1', 'Testemunha1', 'Condutor', 'Outro'
     */
    _lerFormularioEnvolvido: function(tipo) {
        // O Condutor usa IDs sem sufixo (#inputAlcunha) exceto o nome (#inputNomeCondutor)
        const isCondutor = tipo === 'Condutor';
        const isOutro = tipo === 'Outro';
        const sufixo = isCondutor ? '' : tipo;

        const lerCampo = (prefixo, sufx) => {
            const el = $(`#${prefixo}${sufx}`);
            return el.length ? (el.val() || '').trim() : '';
        };

        const nomeSufixo = isCondutor ? 'Condutor' : (isOutro ? 'Outro' : tipo);
        const resultado = {
            nome: lerCampo('inputNome', nomeSufixo),
            alcunha: lerCampo('inputAlcunha', sufixo),
            nascimento: lerCampo('inputDataNascimento', sufixo),
            idade: lerCampo('inputIdade', sufixo),
            rg: lerCampo('inputRG', sufixo),
            cpf: lerCampo('inputCPF', sufixo),
            mae: lerCampo('inputMae', sufixo),
            pai: lerCampo('inputPai', sufixo),
            endereco: lerCampo('inputEndereco', sufixo),
            profissao: lerCampo('inputProfissao', sufixo),
            naturalidade: lerCampo('inputNaturalidade', sufixo),
            estcivil: lerCampo('inputEstadoCivil', sufixo),
            instrucao: lerCampo('inputInstrucao', sufixo),
            telefone: lerCampo('inputTelefone', sufixo)
        };

        // Aplicar CAIXA ALTA
        this._camposUpperCase.forEach(campo => {
            if (resultado[campo]) resultado[campo] = resultado[campo].toUpperCase();
        });

        // Normalizar data de nascimento
        if (resultado.nascimento && resultado.nascimento.includes('-')) {
            const p = resultado.nascimento.split('-');
            if (p.length === 3) resultado.nascimento = `${p[2]}/${p[1]}/${p[0]}`;
        }

        return resultado;
    },

    /**
     * ✅ Mescla dois objetos de pessoa: B preenche campos vazios de A
     */
    _mesclarPessoa: function(base, fallback) {
        if (!fallback) return base || {};
        if (!base) return fallback || {};
        const resultado = { ...base };
        Object.keys(fallback).forEach(campo => {
            if ((!resultado[campo] || resultado[campo].trim() === '') && fallback[campo] && fallback[campo].trim() !== '') {
                resultado[campo] = fallback[campo];
            }
        });
        return resultado;
    },

    /**
     * Coleta todos os dados globais do sistema (Chips + Campos do Formulário)
     * Centraliza a lógica de normalização e CAIXA ALTA.
     */
    capturarDadosGlobais: function() {
        console.log('🔍 [DocumentoService] Capturando dados globais...');

        const dados = {
            data: $('#inputData').val() || '',
            data_comp: $('#inputDataComp').val() || '',
            data_ext: $('#inputDataExt').val() || '',
            cidade: ($('#inputCidade').val() || '').toUpperCase(),
            delegado: ($('#inputDelegado').val() || '').toUpperCase(),
            escrivao: ($('#inputEscrivao').val() || '').toUpperCase(),
            delegacia: ($('#inputDelegacia').val() || '').toUpperCase(),
            boe: $('#inputBOE').val() || '',
            apreensao: $('#inputApreensao').val() || '',
            ip: $('#inputIP').val() || '',
            boe_pm: $('#inputBOEPM').val() || '',
            policial_1: ($('#inputPolicial1').val() || '').toUpperCase(),
            policial_2: ($('#inputPolicial2').val() || '').toUpperCase(),
            local_fato: ($('#inputEndFato').val() || '').toUpperCase(),
            hora_fato: $('#inputHoraFato').val() || '',
            natureza: ($('#inputNatureza').val() || '').toUpperCase(),
            incidencia_penal: ($('#inputNatureza').val() || '').toUpperCase(),
            data_fato: (() => {
                const d = $('#inputDataFato').val();
                if (d && d.includes('-')) {
                    const p = d.split('-');
                    return `${p[2]}/${p[1]}/${p[0]}`;
                }
                return d || '';
            })()
        };

        dados.vitimas = [];
        dados.autores = [];
        dados.testemunhas = [];
        dados.outros = [];
        dados.condutores = [];
        dados.condutor = null;

        // ==========================================
        // FASE 1: Ler dados dos CHIPS (fonte primária)
        // ==========================================
        if (window.envolvidosChips) {
            // Condutores
            (window.envolvidosChips.condutores || []).forEach((c, i) => {
                const n = this._normalizarPessoa(c);
                if (n.nome) {
                    dados.condutores.push(n);
                    if (i === 0) {
                        dados.condutor = n;
                    }
                }
            });
            // Vítimas
            (window.envolvidosChips.vitimas || []).forEach((v, i) => {
                const n = this._normalizarPessoa(v);
                if (n.nome) {
                    dados.vitimas.push(n);
                    dados[`vitima${i + 1}`] = n;
                }
            });
            // Autores
            (window.envolvidosChips.autores || []).forEach((a, i) => {
                const n = this._normalizarPessoa(a);
                if (n.nome) {
                    dados.autores.push(n);
                    dados[`autor${i + 1}`] = n;
                }
            });
            // Testemunhas
            (window.envolvidosChips.testemunhas || []).forEach((t, i) => {
                const n = this._normalizarPessoa(t);
                if (n.nome) {
                    dados.testemunhas.push(n);
                    dados[`testemunha${i + 1}`] = n;
                }
            });
            // Outros
            (window.envolvidosChips.outros || []).forEach(o => {
                const n = this._normalizarPessoa(o);
                if (n.nome) dados.outros.push(n);
            });
        }

        // ==========================================
        // FASE 2: FALLBACK — Ler campos do formulário
        // Se os chips não tinham dados, tenta pegar dos inputs visíveis
        // ==========================================
        const tiposEnvolvido = [
            { tipo: 'Autor', max: 3, arr: 'autores', key: 'autor' },
            { tipo: 'Vitima', max: 3, arr: 'vitimas', key: 'vitima' },
            { tipo: 'Testemunha', max: 3, arr: 'testemunhas', key: 'testemunha' }
        ];

        tiposEnvolvido.forEach(cfg => {
            for (let i = 1; i <= cfg.max; i++) {
                const sufixo = cfg.tipo + i;
                const chave = cfg.key + i;
                const dadosForm = this._lerFormularioEnvolvido(sufixo);

                if (dadosForm.nome) {
                    const idx = i - 1;
                    if (dados[cfg.arr][idx]) {
                        // Chip existe: mescla (form preenche vazios do chip)
                        dados[cfg.arr][idx] = this._mesclarPessoa(dados[cfg.arr][idx], dadosForm);
                        dados[chave] = dados[cfg.arr][idx];
                    } else {
                        // Chip não existe: usa form como fonte
                        dados[cfg.arr].push(dadosForm);
                        dados[chave] = dadosForm;
                    }
                }
            }
        });

        // Condutor fallback
        const condutorForm = this._lerFormularioEnvolvido('Condutor');
        if (condutorForm.nome) {
            if (dados.condutor) {
                dados.condutor = this._mesclarPessoa(dados.condutor, condutorForm);
            } else {
                dados.condutor = condutorForm;
                dados.condutores.push(condutorForm);
            }
        }

        // Outro fallback
        const outroForm = this._lerFormularioEnvolvido('Outro');
        if (outroForm.nome) {
            if (dados.outros.length === 0) {
                dados.outros.push(outroForm);
            } else {
                dados.outros[0] = this._mesclarPessoa(dados.outros[0], outroForm);
            }
        }

        // Copiar nome principal (condutor tem prioridade, depois primeiro autor)
        if (dados.condutor && dados.condutor.nome) {
            Object.assign(dados, dados.condutor);
        } else if (dados.autores.length > 0 && dados.autores[0].nome) {
            Object.assign(dados, dados.autores[0]);
        } else if (dados.vitimas.length > 0 && dados.vitimas[0].nome) {
            Object.assign(dados, dados.vitimas[0]);
        }

        dados.lista_vitimas = dados.vitimas;

        console.log('✅ [DocumentoService] Dados globais capturados:', {
            condutores: dados.condutores.length,
            autores: dados.autores.length,
            vitimas: dados.vitimas.length,
            testemunhas: dados.testemunhas.length
        });

        return dados;
    },
    /**
     * Gera um documento salvando os dados no backend e abrindo a rota com UUID.
     * @param {string} urlBase - A URL da rota (ex: /apfd-condutor/--DADOS--)
     * @param {Object} dados - O objeto com todos os dados do formulário
     */
    gerar: function(urlBase, dados) {
        if (!urlBase || !dados) {
            console.error('DocumentoService: URL ou Dados ausentes.');
            return;
        }

        console.log('📤 [DocumentoService] Iniciando geração dinâmica para:', urlBase);

        // Verifica se é uma requisição para abrir o Editor (Usa cache via UUID)
        if (urlBase.includes('--DADOS--')) {
            this.gerarEditorComCache(urlBase, dados);
        } else {
            // É uma requisição para gerar o PDF Final via POST
            this.enviarPostPdfFormulario(urlBase, dados);
        }
    },

    /**
     * Envia os dados para a sessão usando AJAX, depois abre a URL final
     */
    gerarEditorComCache: function(urlBase, dados) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Preparando Documento...',
                text: 'Aguarde um momento enquanto organizamos os dados.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        $.ajax({
            url: '/documentos/salvar-sessao',
            type: 'POST',
            data: JSON.stringify(dados),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.uuid) {
                    console.log('✅ [DocumentoService] Sessão criada:', response.uuid);
                    const urlFinal = urlBase.replace('--DADOS--', response.uuid);
                    
                    if (typeof Swal !== 'undefined') Swal.close();
                    
                    window.open(urlFinal, "_blank");
                } else {
                    this.tratarErro('O servidor não retornou um identificador válido.');
                }
            }.bind(this),
            error: function(xhr) {
                console.error('❌ [DocumentoService] Erro Ajax:', xhr.responseText);
                this.tratarErro('Erro de conexão com o servidor ao preparar o documento.');
            }.bind(this)
        });
    },

    /**
     * Gera e submete um formulário dinâmico para as Rotas POST (Geração de PDF)
     */
    enviarPostPdfFormulario: function(url, dados) {
        console.log('📄 [DocumentoService] Submetendo dados para gerar PDF (POST):', url);
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';
        
        // CSRF Token do Laravel
        const tokenStr = $('meta[name="csrf-token"]').attr('content');
        if (tokenStr) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = tokenStr;
            form.appendChild(csrfInput);
        }
        
        // Inserir inputs invisíveis com os dados
        for (const key in dados) {
            if (dados.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                
                // Tratar objetos (ex: lista de vítimas) para enviar como string JSON
                if (dados[key] !== null && typeof dados[key] === 'object') {
                    input.value = JSON.stringify(dados[key]);
                } else {
                    input.value = dados[key] || '';
                }
                
                form.appendChild(input);
            }
        }
        
        document.body.appendChild(form);
        form.submit();
        
        // Limpeza do DOM após o envio
        setTimeout(() => document.body.removeChild(form), 100);
    },

    tratarErro: function(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Atenção', msg, 'error');
        } else {
            alert(msg);
        }
    },

    /**
     * Inicializa o TinyMCE com configurações padrão para todos os documentos.
     * @param {string} selector - Seletor do elemento (ex: '#editor')
     * @param {Function} onPrint - Função a ser chamada ao clicar em Gerar PDF
     */
    initTinyMCE: function(selector, onPrint) {
        tinymce.init({
            selector: selector,
            language: 'pt_BR',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/pt_BR.js',
            promotion: false,
            branding: false,
            min_height: 800,
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table help',
            toolbar: 'undo redo | pastetext removeformat | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | table bullist numlist outdent indent | link image | pagebreak charmap fullscreen | gerarpdf',
            menubar: 'file edit view insert format tools table help',
            font_size_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            
            // ✅ CONFIGURAÇÕES DE COLAGEM (MÁGICA CONTRA O WORD)
            paste_as_text: false, // Permite manter negrito/tabelas
            paste_postprocess: function(plugin, args) {
                // args.node é o fragmento invisível com os dados do Word antes de ir pro editor
                var elementos = args.node.querySelectorAll("*");
                for (var i = 0; i < elementos.length; i++) {
                    // Removemos apenas as formatações chatas do Word que quebram o layout
                    elementos[i].style.fontFamily = "";
                    elementos[i].style.fontSize = "";
                    elementos[i].style.lineHeight = "";
                    elementos[i].style.color = "";
                    elementos[i].style.backgroundColor = "";
                    elementos[i].style.margin = ""; // Remove margens malucas de parágrafos
                }
            },
            
            content_style: `
                html {
                    background-color: #e0e0e0;
                }
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12.5pt; 
                    line-height: 1.6; 
                    padding: 30px 60px; 
                    max-width: 850px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    box-shadow: 0 0 15px rgba(0,0,0,0.15);
                } 
                p { margin: 0.3em 0 }
                
                /* Visualização de Quebra de Página no Editor */
                .mce-pagebreak {
                    cursor: default;
                    display: block;
                    border: 0;
                    width: 100%;
                    height: 5px;
                    border-top: 2px dashed #999;
                    margin-top: 30px;
                    margin-bottom: 30px;
                    page-break-before: always;
                }
                
                .mce-pagebreak::after {
                    content: "--- QUEBRA DE PÁGINA ---";
                    display: block;
                    text-align: center;
                    font-size: 10pt;
                    color: #666;
                    margin-top: 5px;
                    font-style: italic;
                }
            `,
            setup: function(editor) {
                editor.ui.registry.addButton('gerarpdf', {
                    text: 'GERAR PDF',
                    icon: 'document-properties',
                    tooltip: 'Gerar Documento Oficial em PDF',
                    onAction: function (_) {
                        if (onPrint) onPrint();
                    }
                });

                editor.on('init', function() {
                    setTimeout(() => {
                        const btns = document.querySelectorAll('.tox-tbtn');
                        btns.forEach(btn => {
                            if(btn.innerText.includes('GERAR')){
                                btn.style.backgroundColor = '#198754';
                                btn.style.color = '#ffffff';
                                btn.style.fontWeight = 'bold';
                                btn.style.padding = '0 15px';
                                btn.style.borderRadius = '5px';
                                btn.style.width = 'auto';
                                btn.style.minWidth = 'fit-content';
                                btn.style.marginLeft = 'auto';
                                const iconPath = btn.querySelector('.tox-icon svg');
                                if(iconPath) iconPath.style.fill = '#ffffff';
                            }
                        });
                    }, 1000);
                });
            }
        });
    }
};
