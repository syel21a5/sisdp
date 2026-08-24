/**
 * JS para Exame de Eficiência de Arma de Fogo (Com Disparo) utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE EXAME DE EFICIÊNCIA DE ARMA (COM DISPARO)...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    const endpoint = '/exame-arma-com-disparo-pdf';

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'EFICIENCIA_ARMA',
        'cidade': dados.cidade || 'Afogados da Ingazeira',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'policial_1': dados.policial_1 || 'NÃO INFORMADO',
        'policial_2': dados.policial_2 || 'NÃO INFORMADO',
        'data_comp': dados.data_comp || '',
        'nome': dados.nome || '',
        'boe': dados.boe || '',
        'apreensao': dados.apreensao || '',
        'data_ext': dados.data_ext || '',
        'conteudo': content,
        'tipo_documento': 'completa'
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
