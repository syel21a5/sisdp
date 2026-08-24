/**
 * JS para Recibo de Entrega de Preso utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE RECIBO DE ENTREGA DE PRESO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    const endpoint = '/recibo-preso-pdf';

    // Dados para envio via POST
    const dadosParaEnviar = { ...dados, 'conteudo': content };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar(endpoint, dadosParaEnviar);
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
    
    // TinyMCE native wordcount
    const wordCount = tinymce.activeEditor.plugins.wordcount ? tinymce.activeEditor.plugins.wordcount.body.getWordCount() : 0;

    const charCountEl = document.getElementById('char-count');
    const wordCountEl = document.getElementById('word-count');
    const paragraphCountEl = document.getElementById('paragraph-count');
    const lastModEl = document.getElementById('last-modified');

    if (charCountEl) charCountEl.textContent = charCount + ' caracteres';
    if (wordCountEl) wordCountEl.textContent = wordCount + ' palavras';
    if (paragraphCountEl) paragraphCountEl.textContent = paragraphCount + ' parágrafos';
    if (lastModEl) lastModEl.textContent = new Date().toLocaleTimeString();
}
