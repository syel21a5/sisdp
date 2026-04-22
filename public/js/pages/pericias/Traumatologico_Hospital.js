/**
 * JS para Laudo Traumatológico Hospitalar utilizando TinyMCE e PDF Dinâmico
 */

// Inicializar TinyMCE
tinymce.init({
    selector: '#editor',
    height: 800,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'pagebreak'
    ],
    toolbar: 'undo redo | blocks | ' +
    'bold italic underline strikethrough | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'table pagebreak | removeformat | help',
    pagebreak_separator: '<!-- pagebreak -->',
    content_style: `
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12pt; 
            line-height: 1.3;
            padding: 40px;
        }
        p { margin-bottom: 8px; margin-top: 0; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        table td { border: 1px solid black; padding: 6px 8px; vertical-align: top; }
        .pagebreak { page-break-before: always; border: 1px dashed #ccc; height: 10px; text-align: center; margin: 20px 0; background: #f8f9fa; display: block; content: '--- QUEBRA DE PÁGINA ---'; }
    `,
    setup: function(editor) {
        editor.on('init', function() {
            updateStats();
        });
        editor.on('keyup', function() {
            updateStats();
        });
        editor.on('change', function() {
            updateStats();
        });
        
        // Adiciona atalho Ctrl+Enter para quebra de página
        editor.addShortcut('ctrl+13', 'Insert Page Break', function() {
            editor.execCommand('mcePageBreak');
        });
    }
});

function printDocument() {
    console.log('Iniciando geração de PDF...');

    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor.getContent();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/pericia-traumatologico';
    form.target = '_blank';
    form.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token não encontrado!');
        return;
    }

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken.content;
    form.appendChild(csrfInput);

    // Substituir o pagebreak do TinyMCE pela div que o DomPDF entende
    content = content.replace(/<!-- pagebreak -->/g, '<div class="page-break" style="page-break-before: always; height: 0; margin: 0; padding: 0;"></div>');

    const conteudoInput = document.createElement('input');
    conteudoInput.type = 'hidden';
    conteudoInput.name = 'conteudo';
    conteudoInput.value = content;
    form.appendChild(conteudoInput);

    const dadosParaEnviar = {
        'orgao_emissor': 'HOSPITALAR',
        'cidade': dados.cidade || '',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'nome': dados.nome || '',
        'nascimento': dados.nascimento || '',
        'idade': dados.idade || '',
        'rg': dados.rg || '',
        'cpf': dados.cpf || '',
        'mae': dados.mae || '',
        'pai': dados.pai || '',
        'endereco': dados.endereco || '',
        'boe': dados.boe || '',
        'data_comp': dados.data_comp || ''
    };

    Object.keys(dadosParaEnviar).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = dadosParaEnviar[key];
        form.appendChild(input);
    });

    document.body.appendChild(form);

    // Mostrar loading no botão
    const printButton = document.querySelector('button.btn-custom');
    if (printButton) {
        const originalText = printButton.innerHTML;
        printButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando PDF...';
        printButton.disabled = true;

        setTimeout(() => {
            printButton.innerHTML = originalText;
            printButton.disabled = false;
        }, 3000);
    }

    form.submit();
}

function updateStats() {
    if (!tinymce.activeEditor) return;
    
    const editorContent = tinymce.activeEditor.getContent({format: 'text'});
    const charCount = editorContent.length;
    const body = tinymce.activeEditor.getBody();
    const paragraphCount = body.querySelectorAll('p').length;

    const charCountEl = document.getElementById('char-count');
    const paragraphCountEl = document.getElementById('paragraph-count');
    const lastModEl = document.getElementById('last-modified');

    if (charCountEl) charCountEl.textContent = charCount + ' caracteres';
    if (paragraphCountEl) paragraphCountEl.textContent = paragraphCount + ' parágrafos';
    if (lastModEl) lastModEl.textContent = new Date().toLocaleTimeString();
}

document.addEventListener('DOMContentLoaded', function() {
    const printButton = document.querySelector('button.btn-custom');
    if (printButton) {
        printButton.addEventListener('click', printDocument);
    }
});
