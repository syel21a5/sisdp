/**
 * JS para Certidão de Assinaturas Individual utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE CERTIDÃO DE ASSINATURA INDIVIDUAL...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'CERTIDÃO DE ASSINATURA INDIVIDUAL',
        'cidade': dados.cidade || 'Afogados da Ingazeira',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'nome': dados.nome || '',
        'boe': dados.boe || '',
        'ip': dados.ip || '',
        'data_ext': dados.data_ext || '',
        'conteudo': content
    };

    // Usar DocumentoService para envio seguro via POST
    // Nota: O endpoint no controller é /certidao-assinaturas-individual
    DocumentoService.gerar('/certidao-assinaturas-individual', dadosParaEnviar);
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
