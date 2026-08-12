// JavaScript para Editor APFD - AUTOR 3 (Versão TinyMCE)

function printDocument() {
    console.log('🟢 GERANDO PDF APFD AUTOR 3 COM TINYMCE...');
    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    const dadosParaEnviar = {
        'orgao_emissor': 'APFD AUTOR 3',
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
        'instrucao': (dados.instrucao || '').toUpperCase(),
        'telefone': dados.telefone || '',
        'mae': dados.mae || '',
        'pai': dados.pai || '',
        'endereco': dados.endereco || '',
        'parente': dados.parente || dados.Parente || 'NÃO INFORMADO',
        'familia': dados.familia || dados.Familia || 'NÃO INFORMADO',
        'boe': dados.boe || '',
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'conteudo': content
    };

    DocumentoService.gerar('/termo-apfd-autor3', dadosParaEnviar);
}

document.addEventListener('DOMContentLoaded', function() {
    DocumentoService.initTinyMCE('#editor', printDocument);
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
