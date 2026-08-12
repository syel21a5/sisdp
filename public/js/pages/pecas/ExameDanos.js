/**
 * JS para Exame de Danos e Constatação utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE EXAME DE DANOS/CONSTATAÇÃO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Detectar se é Portaria ou Termo
    let tipoDocumento = window.tipoDocumentoGlobal;
    let isPortaria = false;

    if (tipoDocumento) {
        isPortaria = tipoDocumento === 'portaria';
    } else {
        isPortaria = window.location.pathname.includes('portaria');
        tipoDocumento = isPortaria ? 'portaria' : 'termo';
    }

    // Definir endpoint (prioriza a rota definida na view)
    let endpoint = window.rotaPdf;
    if (!endpoint) {
        endpoint = isPortaria ? '/exame-danos-portaria' : '/exame-danos-termo';
    }

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'EXAME_DANOS',
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
        'tipo_documento': tipoDocumento
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
