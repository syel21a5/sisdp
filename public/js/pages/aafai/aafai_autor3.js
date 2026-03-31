// Configurar o editor Quill com módulos expandidos
const quill = new Quill('#editor', {
    modules: {
        toolbar: {
            container: '#toolbar',
            handlers: {
                // Handler para quebra de página
                'page-break': function() {
                    const range = this.quill.getSelection();
                    if (range) {
                        // insere marcador visível no editor
                        this.quill.insertText(range.index, '\n\n--- QUEBRA DE PÁGINA ---\n\n');
                        this.quill.setSelection(range.index + 30);
                    }
                },

                // Handler para caixa alta/baixa
                'text-case': function() {
                    const range = this.quill.getSelection();
                    if (range && range.length > 0) {
                        const text = this.quill.getText(range);
                        let newText = text;

                        // Alternar entre maiúsculas, minúsculas e capitalizado
                        if (text === text.toUpperCase()) {
                            // Se já está maiúsculo, vai para minúsculo
                            newText = text.toLowerCase();
                        } else if (text === text.toLowerCase()) {
                            // Se está minúsculo, vai para capitalizado
                            newText = text.replace(/\w\S*/g, function(txt) {
                                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                            });
                        } else {
                            // Se está misturado, vai para maiúsculo
                            newText = text.toUpperCase();
                        }

                        this.quill.deleteText(range);
                        this.quill.insertText(range.index, newText);
                        this.quill.setSelection(range.index, newText.length);
                    }
                },
                link: function(value) {
                    if (value) {
                        const range = this.quill.getSelection();
                        if (range == null || range.length == 0) return;
                        let preview = this.quill.getText(range);
                        if (preview.length > 20) preview = preview.substring(0, 20) + '...';

                        const url = prompt('Insira o URL do link:', 'https://');
                        if (url) {
                            this.quill.formatText(range, 'link', url);
                        }
                    } else {
                        this.quill.format('link', false);
                    }
                },
                image: function() {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.click();

                    input.onchange = () => {
                        const file = input.files[0];
                        if (/^image\//.test(file.type)) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const range = quill.getSelection(true);
                                quill.insertEmbed(range.index, 'image', e.target.result);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            Swal.fire("Atenção", 'Por favor, selecione apenas imagens.', "warning");
                        }
                    };
                }
            }
        }
    },
    theme: 'snow',
    // REMOVER 'font' e 'size' dos formats
    formats: ['bold', 'italic', 'underline', 'strike', 'link', 'image', 'list', 'bullet', 'indent', 'align', 'color', 'background']
});

// ✅ DEFINIR FONTE PADRÃO COMO ARIAL 12.5pt DIRETAMENTE NO CSS
// REMOVER formatação de fonte e tamanho do Quill

// Ajusta o espaçamento inicial
function adjustInitialSpacing() {
    const editorElement = document.getElementById('editor');
    editorElement.style.lineHeight = '1.6';
    editorElement.style.padding = '0 30px';
    editorElement.classList.add('preservar-espacamento');

    // Aplicar fonte Arial 12.5pt a todos os parágrafos
    editorElement.querySelectorAll('p').forEach(p => {
        p.style.margin = '0.3em 0';
        p.style.padding = '0';
        p.style.lineHeight = '1.6';
        p.style.fontFamily = 'Arial, sans-serif';
        p.style.fontSize = '12.5pt';
        p.classList.add('preservar-espacamento');
    });

    // Preservar o estilo do título (mantém 20pt)
    const titulo = editorElement.querySelector('p:first-child strong');
    if (titulo) {
        titulo.style.fontSize = '16pt';
    }

    // Preservar o estilo do nome (mantém 14pt)
    const nome = editorElement.querySelector('p:nth-child(2) strong');
    if (nome) {
        nome.style.fontSize = '14pt';
    }

    // Remove a classe da área de assinaturas para não afetá-la
    const assinaturaArea = document.querySelector('.assinatura-area');
    if (assinaturaArea) {
        assinaturaArea.classList.remove('preservar-espacamento');
        assinaturaArea.querySelectorAll('p').forEach(p => {
            p.classList.remove('preservar-espacamento');
        });
    }
}

adjustInitialSpacing();

// ✅✅✅ FUNÇÃO CORRIGIDA PARA LIMPAR CONTEÚDO HTML ANTES DO ENVIO ✅✅✅
function cleanHtmlContent(html) {
    console.log('Conteúdo original (primeiros 100 caracteres):', html.substring(0, 100));

    // 1. Remover BOM UTF-8 (se presente no início)
    if (html.charCodeAt(0) === 0xFEFF) {
        html = html.substring(1);
        console.log('BOM UTF-8 removido.');
    }

    // 2. Remover qualquer caractere inválido antes da primeira tag
    const firstTagMatch = html.match(/<[^>]*>/);
    if (firstTagMatch && firstTagMatch.index > 0) {
        html = html.substring(firstTagMatch.index);
        console.log('Caracteres inválidos antes da primeira tag removidos.');
    }

    // 3. Remover interrogações soltas no início
    html = html.replace(/^(\?+|&#63;|&quot;|&lt;|&gt;)+/, '');

    // 4. Remover espaços em branco, quebras de linha e tabs no início
    html = html.trimStart();

    // 5. PRESERVAR TABS E ESPAÇOS - converter tabs para espaços não quebráveis
    html = html.replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;');

    // 6. Adicionar classe para preservar espaçamento
    html = html.replace(/<div id="editor"/, '<div id="editor" class="preservar-espacamento"');

    // 7. Adicionar classe de quebra de página para melhor controle
    html = html.replace(/<div id="editor"/, '<div id="editor" class="preservar-espacamento" style="position: relative;"');

    // 8. Adicionar margem superior explícita para conteúdo após quebras de página
    html = html.replace(/<p style="/g, '<p style="margin-top: 5px; ');

    // 9. Substituir marcador por div de quebra de página
    html = html.replace(/--- QUEBRA DE PÁGINA ---/g,
        '<div class="page-break"></div>');

    console.log('Conteúdo após limpeza (primeiros 100 caracteres):', html.substring(0, 100));
    return html;
}

// Função principal para gerar PDF com DOMPDF
function printDocument() {
    console.log('Iniciando geração de PDF...');

    const dados = window.dadosParaImpressao || {};

    // ✅ CAPTURAR CONTEÚDO DO EDITOR - FORMA CORRIGIDA
    let content = document.getElementById('editor').innerHTML;

    // ✅✅✅ LIMPAR O CONTEÚDO - SOLUÇÃO DEFINITIVA PARA A INTERROGAÇÃO
    content = cleanHtmlContent(content);

    // Criar formulário para enviar ao Laravel
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/termo-aafai-autor3';
    form.target = '_blank';
    form.style.display = 'none';

    // Adicionar CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token não encontrado!');
        Swal.fire("Atenção", 'Erro de segurança. CSRF token não encontrado.', "warning");
        return;
    }

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken.content;
    form.appendChild(csrfInput);

    // Adicionar conteúdo HTML limpo
    const conteudoInput = document.createElement('input');
    conteudoInput.type = 'hidden';
    conteudoInput.name = 'conteudo';
    conteudoInput.value = content;
    form.appendChild(conteudoInput);

    // Adicionar todos os dados necessários
    const dadosParaEnviar = {
        'orgao_emissor': 'AAFAI AUTOR 3',
        'cidade': dados.cidade || 'NÃO INFORMADO',
        'delegacia': dados.delegacia || 'NÃO INFORMADO',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'nome': dados.nome || '',
        'alcunha': dados.alcunha || '',
        'nascimento': dados.nascimento || '',
        'idade': dados.idade || '',
        'estcivil': dados.estcivil || '',
        'naturalidade': dados.naturalidade || '',
        'rg': dados.rg || '',
        'cpf': dados.cpf || '',
        'profissao': dados.profissao || '',
        'instrucao': dados.instrucao || '',
        'telefone': dados.telefone || '',
        'mae': dados.mae || '',
        'pai': dados.pai || '',
        'endereco': dados.endereco || '',
        'boe': dados.boe || '',
        'ip': dados.ip || '',
        'data_ext': dados.data_ext || 'NÃO INFORMADO'
    };

    Object.keys(dadosParaEnviar).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = dadosParaEnviar[key];
        form.appendChild(input);
    });

    // Adicionar formulário ao documento e submeter
    document.body.appendChild(form);

    // Mostrar loading
    const printButton = document.querySelector('button.btn-custom');
    const originalText = printButton.textContent;
    printButton.textContent = 'Gerando PDF...';
    printButton.disabled = true;

    form.submit();

    // Restaurar botão após 3 segundos (caso falhe)
    setTimeout(() => {
        printButton.textContent = originalText;
        printButton.disabled = false;
    }, 3000);
}

// ✅ CONFIGURAÇÃO INICIAL CORRIGIDA (SEM FONTES)
document.addEventListener('DOMContentLoaded', function() {
    const printButton = document.querySelector('button.btn-custom');
    if (printButton) {
        printButton.textContent = 'Gerar PDF';
        printButton.addEventListener('click', printDocument);
    }

    // Remover qualquer caractere inválido que possa ter sido inserido pelo Quill
    setTimeout(() => {
        const editorContent = document.getElementById('editor').innerHTML;
        if (editorContent.indexOf('?') === 0 || editorContent.indexOf('&#63;') === 0) {
            document.getElementById('editor').innerHTML = editorContent.replace(/^(\?+|&#63;)+/, '');
            console.log('Caracteres inválidos removidos do editor na inicialização.');
        }
    }, 500);
});

// =============================================
// JAVASCRIPT MOVIDO DO BLADE PARA AQUI
// =============================================

// Configuração dos dados para impressão
window.dadosParaImpressao = window.dadosParaImpressao || {};

// Contador de caracteres e parágrafos
function updateStats() {
    const editorContent = document.getElementById('editor').textContent;
    const charCount = editorContent.length;
    const paragraphCount = document.querySelectorAll('#editor p').length;

    document.getElementById('char-count').textContent = charCount + ' caracteres';
    document.getElementById('paragraph-count').textContent = paragraphCount + ' parágrafos';
    document.getElementById('last-modified').textContent = new Date().toLocaleTimeString();
}

// Adicionar tooltips aos botões
function setupTooltips() {
    const tooltips = {
        'ql-bold': 'Negrito (Ctrl+B)',
        'ql-italic': 'Itálico (Ctrl+I)',
        'ql-underline': 'Sublinhado (Ctrl+U)',
        'ql-link': 'Inserir link',
        'ql-image': 'Inserir imagem',
        'ql-clean': 'Limpar formatação',
        'ql-page-break': 'Quebra de Página (Ctrl+Enter)',
        'ql-text-case': 'Alternar Maiúsculas/Minúsculas (Shift+F3)'
    };

    Object.keys(tooltips).forEach(className => {
        const element = document.querySelector(`.${className}`);
        if (element) {
            element.setAttribute('title', tooltips[className]);
        }
    });
}

// Configurar atalhos de teclado
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter para quebra de página
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const range = quill.getSelection();
            if (range) {
                quill.insertText(range.index, '\n\n--- QUEBRA DE PÁGINA ---\n\n');
                quill.setSelection(range.index + 30);
            }
        }

        // Shift+F3 para alternar caixa
        if (e.shiftKey && e.key === 'F3') {
            e.preventDefault();
            const range = quill.getSelection();
            if (range && range.length > 0) {
                const text = quill.getText(range);
                let newText = text;

                if (text === text.toUpperCase()) {
                    newText = text.toLowerCase();
                } else if (text === text.toLowerCase()) {
                    newText = text.replace(/\w\S*/g, function(txt) {
                        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                    });
                } else {
                    newText = text.toUpperCase();
                }

                quill.deleteText(range);
                quill.insertText(range.index, newText);
                quill.setSelection(range.index, newText.length);
            }
        }
    });
}

// Inicialização completa
function initializeEditor() {
    console.log('Dados carregados:', window.dadosParaImpressao);

    // Atualizar estatísticas a cada alteração
    setInterval(updateStats, 1000);
    updateStats();

    // Configurar tooltips
    setupTooltips();

    // Configurar atalhos de teclado
    setupKeyboardShortcuts();

    // Configurar botão de gerar PDF
    const printButton = document.querySelector('button.btn-custom');
    if (printButton) {
        printButton.textContent = 'Gerar PDF';
        printButton.addEventListener('click', printDocument);
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', initializeEditor);
