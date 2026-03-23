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
                            alert('Por favor, selecione apenas imagens.');
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

    // Ajustar estilo dos itens de lista (li)
    editorElement.querySelectorAll('li').forEach(li => {
        li.style.fontFamily = 'Arial, sans-serif';
        li.style.fontSize = '12.5pt';
        li.style.lineHeight = '1.6';
        li.style.textAlign = 'justify';
    });

    // Remove a classe da área de assinaturas para não afetá-la (se houver)
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

    // 9. Substituir marcador por div de quebra de página COM ESTILOS CORRETOS
    html = html.replace(/--- QUEBRA DE PÁGINA ---/g,
        '<div class="page-break" style="page-break-before: always; margin-top: 30px; height: 0; border: none;"></div>');

    console.log('Conteúdo após limpeza (primeiros 100 caracteres):', html.substring(0, 100));
    return html;
}

// ✅✅✅ FUNÇÃO PRINCIPAL CORRIGIDA ✅✅✅
function printDocument() {
    console.log('Iniciando geração de PDF - Perícia em Local de Crime...');

    const dados = window.dadosParaImpressao || {};

    // ✅ CAPTURAR CONTEÚDO DO EDITOR - FORMA CORRIGIDA
    let content = document.getElementById('editor').innerHTML;

    // ✅✅✅ LIMPAR O CONTEÚDO - SOLUÇÃO DEFINITIVA PARA A INTERROGAÇÃO
    content = cleanHtmlContent(content);

    // ✅ DEFINIR ROTA CORRETA
    const formAction = '/pericia-local-de-crime-pdf';

    // Criar formulário para enviar ao Laravel
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = formAction;
    form.target = '_blank';
    form.style.display = 'none';

    // Adicionar CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token não encontrado!');
        alert('Erro de segurança. CSRF token não encontrado.');
        return;
    }

    const csrfInput = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken.content;
    form.appendChild(csrfInput);

    // Adicionar conteúdo HTML limpo
    const conteudoInput = document.createElement('input');
    conteudoInput.type = 'hidden';
    conteudoInput.name = 'conteudo';
    conteudoInput.value = content; // Usar conteúdo limpo
    form.appendChild(conteudoInput);

    // Adicionar outros campos
    for (const key in dados) {
        if (dados.hasOwnProperty(key)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = dados[key];
            form.appendChild(input);
        }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Event listener para o botão de imprimir
document.querySelector('.btn-custom').addEventListener('click', printDocument);

// Atualizar contadores
quill.on('text-change', function() {
    const text = quill.getText();
    document.getElementById('char-count').innerText = (text.length - 1) + ' caracteres'; // -1 para remover o \n final
    
    // Contar parágrafos
    const paragraphs = document.querySelectorAll('#editor p').length;
    document.getElementById('paragraph-count').innerText = paragraphs + ' parágrafos';
    
    // Atualizar data de modificação
    const now = new Date();
    document.getElementById('last-modified').innerText = now.toLocaleTimeString();
});
