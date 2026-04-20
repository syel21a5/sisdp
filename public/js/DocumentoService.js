/**
 * DocumentoService.js
 * Centraliza a lógica de geração de documentos (PDF/Editor) do SisDP.
 * Resolve o problema de URLs Longas (Erro 414) usando POST + Cache Session.
 */
const DocumentoService = {
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
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table code help wordcount',
            toolbar: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | table bullist numlist outdent indent | link image | pagebreak chars fullscreen | gerarpdf',
            menubar: 'file edit view insert format tools table help',
            font_size_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            content_style: `
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 14pt; 
                    line-height: 1.6; 
                    padding: 30px 60px; 
                    max-width: 1200px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                } 
                p { margin: 0.3em 0 }
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
