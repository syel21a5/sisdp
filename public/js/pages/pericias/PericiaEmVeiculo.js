/**
 * JS para Perícia em Veículo utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE PERÍCIA EM VEÍCULO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    const endpoint = '/pericia-em-veiculo-pdf';

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'URPOC',
        'cidade': dados.cidade || 'Afogados da Ingazeira',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'data_comp': dados.data_comp || '',
        'boe': dados.boe || '',
        'ip': dados.ip || '',
        'sei': dados.sei || '',
        'veiculo_descricao': dados.veiculo_descricao || '',
        'conteudo': content
    };

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

    const charCountEl = document.getElementById('char-count');
    const paragraphCountEl = document.getElementById('paragraph-count');
    const lastModEl = document.getElementById('last-modified');

    if (charCountEl) charCountEl.textContent = charCount + ' caracteres';
    if (paragraphCountEl) paragraphCountEl.textContent = paragraphCount + ' parágrafos';
    if (lastModEl) lastModEl.textContent = new Date().toLocaleTimeString();
}