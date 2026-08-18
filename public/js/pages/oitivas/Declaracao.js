/**
 * JS para Termo de Declaração utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE DECLARAÇÃO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'DECLARAÇÃO',
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
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'conteudo': content
    };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar('/termo-de-declaracao', dadosParaEnviar);
}

/**
 * 🎯 Chamado pelo botão "SALVAR OITIVA" — delega ao DocumentoService.
 */
function salvarOitivaBotao() {
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();
    console.log('🔍 [DEBUG SAVE] dados._pessoa_id:', dados._pessoa_id, 'dados._boe:', dados._boe, 'dados._papel:', dados._papel);
    console.log('🔍 [DEBUG SAVE] HTML do editor (últimos 500 chars):', content.substring(content.length - 500));
    // Procura "se segue" no HTML pra verificar se a regex vai encontrar
    const idxSS = content.toLowerCase().indexOf('se segue');
    const idxNM = content.toLowerCase().indexOf('nada mais');
    console.log('🔍 [DEBUG SAVE] "se segue" em idx:', idxSS, '/ "nada mais" em idx:', idxNM);
    if (typeof DocumentoService !== 'undefined' && DocumentoService.salvarOitiva) {
        DocumentoService.salvarOitiva(dados, content, 'conteudo-declaracao');
    } else {
        alert('Serviço de salvamento não disponível.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    DocumentoService.initTinyMCE('#editor', printDocument, salvarOitivaBotao);
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
