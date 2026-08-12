// JavaScript para Editor APFD - VÍTIMA 3 (Versão TinyMCE)

function printDocument() {
    console.log('🟢 GERANDO PDF APFD VÍTIMA 3 COM TINYMCE...');
    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/termo-apfd-vitima3';
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

    const dadosParaEnviar = {
        'orgao_emissor': 'APFD VÍTIMA 3',
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
