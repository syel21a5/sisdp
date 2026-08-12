// JavaScript para Editor AAFAI - AUTOR 2 (Versão TinyMCE)

function printDocument() {
    console.log('🟢 GERANDO PDF AAFAI AUTOR 2 COM TINYMCE...');
    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    const dadosParaEnviar = {
        'orgao_emissor': 'AAFAI AUTOR 2',
        'cidade': dados.cidade || 'NÃO INFORMADO',
        'delegacia': dados.delegacia || 'NÃO INFORMADO',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'nome': pessoa.nome || '',
        'alcunha': pessoa.alcunha || '',
        'nascimento': pessoa.nascimento || '',
        'idade': pessoa.idade || '',
        'estcivil': pessoa.estcivil || '',
        'naturalidade': pessoa.naturalidade || '',
        'rg': pessoa.rg || '',
        'cpf': pessoa.cpf || '',
        'profissao': pessoa.profissao || '',
        'instrucao': pessoa.instrucao || '',
        'telefone': pessoa.telefone || '',
        'mae': pessoa.mae || '',
        'pai': pessoa.pai || '',
        'endereco': pessoa.endereco || '',
        'boe': dados.boe || '',
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'conteudo': content
    };

    DocumentoService.gerar('/termo-aafai-autor2', dadosParaEnviar);
}
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
