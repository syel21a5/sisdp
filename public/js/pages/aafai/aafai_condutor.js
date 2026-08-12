// JavaScript para Editor AAFAI - CONDUTOR (Versão TinyMCE)

function printDocument() {
    console.log('🟢 GERANDO PDF AAFAI CONDUTOR COM TINYMCE...');
    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    // Criar payload estruturado exatamente como o controlador do AAFAI espera
    // No AAFAI, o controlador processa os dados do Condutor diretamente do Request
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/termo-aafai-condutor';
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

    // Mapear dados do condutor (AAFAI espera os dados no nível raiz do POST)
    const condutor = dados.condutor || {};
    const dadosParaEnviar = {
        'orgao_emissor': 'AAFAI CONDUTOR',
        'cidade': dados.cidade || 'NÃO INFORMADO',
        'delegacia': dados.delegacia || 'NÃO INFORMADO',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'nome': condutor.nome || '',
        'alcunha': condutor.alcunha || '',
        'nascimento': condutor.nascimento || '',
        'idade': condutor.idade || '',
        'estcivil': condutor.estcivil || '',
        'naturalidade': condutor.naturalidade || '',
        'rg': condutor.rg || '',
        'cpf': condutor.cpf || '',
        'profissao': condutor.profissao || '',
        'instrucao': condutor.instrucao || '',
        'telefone': condutor.telefone || '',
        'mae': condutor.mae || '',
        'pai': condutor.pai || '',
        'endereco': condutor.endereco || '',
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
