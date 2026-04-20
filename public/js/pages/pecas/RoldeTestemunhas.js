/**
 * JS para Rol de Testemunhas utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE ROL DE TESTEMUNHAS...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'ROL DE TESTEMUNHAS / INFORMANTES',
        'cidade': dados.cidade || 'Afogados da Ingazeira',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'boe': dados.boe || '',
        'data_ext': dados.data_ext || '',
        'data_comp': dados.data_comp || '',
        'ip': dados.ip || '',
        'conteudo': content
    };

    // Usar DocumentoService para envio seguro via POST
    // Nota: O endpoint foi padronizado no controller para /rol-de-testemunhas-
    DocumentoService.gerar('/rol-de-testemunhas-', dadosParaEnviar);
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar TinyMCE com o DocumentoService
    DocumentoService.initTinyMCE('#editor', printDocument);

    // Atualizar estatísticas periodicamente
    setInterval(updateStats, 1000);
});

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
