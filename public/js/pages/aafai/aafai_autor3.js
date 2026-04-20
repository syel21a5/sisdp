// JavaScript para Editor AAFAI - AUTOR 3 (Versão TinyMCE)

function printDocument() {
    console.log('🟢 GERANDO PDF AAFAI AUTOR 3 COM TINYMCE...');
    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/termo-aafai-autor3';
    form.target = '_blank';
    form.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) return console.error('CSRF token não encontrado!');

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken.content;
    form.appendChild(csrfInput);

    const conteudoInput = document.createElement('input');
    conteudoInput.type = 'hidden';
    conteudoInput.name = 'conteudo';
    conteudoInput.value = content;
    form.appendChild(conteudoInput);

    const pessoa = dados.autor3 || {};
    const dadosParaEnviar = {
        'orgao_emissor': 'AAFAI AUTOR 3',
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
        'data_ext': dados.data_ext || 'NÃO INFORMADO'
    };

    Object.keys(dadosParaEnviar).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = dadosParaEnviar[key];
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
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
