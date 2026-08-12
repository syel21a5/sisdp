// JavaScript para Editor APFD - VÍTIMA 1 (Versão TinyMCE)

// Função principal para gerar PDF com DOMPDF
function printDocument() {
    console.log('🟢 GERANDO PDF APFD VÍTIMA 1 COM TINYMCE...');

    const dados = window.dadosParaImpressao || {};

    // ✅ CAPTURAR CONTEÚDO DO EDITOR TINYMCE
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    // Criar formulário para enviar ao Laravel
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/termo-apfd-vitima1';
    form.target = '_blank';
    form.style.display = 'none';

    // Adicionar CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token não encontrado!');
        return;
    }

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken.content;
    form.appendChild(csrfInput);

    // Adicionar conteúdo HTML limpo
    const conteudoInput = document.createElement('input');
    conteudoInput.type = 'hidden';
    conteudoInput.name = 'conteudo';
    conteudoInput.value = content;
    form.appendChild(conteudoInput);

    // Adicionar todos os dados necessários
    const dadosParaEnviar = {
        'orgao_emissor': 'APFD VÍTIMA 1',
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

// ✅ CONFIGURAÇÃO INICIAL
document.addEventListener('DOMContentLoaded', function() {
    console.log('Iniciando Editor Vítima 1...');
    
    // Inicializar TinyMCE usando nosso serviço central
    DocumentoService.initTinyMCE('#editor', printDocument);

    // Atualizar estatísticas a cada segundo
    setInterval(updateStats, 1000);
});

// Contador de caracteres e parágrafos
function updateStats() {
    if (!tinymce.activeEditor) return;

    const editorContent = tinymce.activeEditor.getContent({format: 'text'});
    const charCount = editorContent.length;
    
    // Pegar parágrafos
    const body = tinymce.activeEditor.getBody();
    const paragraphCount = body.querySelectorAll('p').length;

    const charCountEl = document.getElementById('char-count');
    const paragraphCountEl = document.getElementById('paragraph-count');
    const lastModEl = document.getElementById('last-modified');

    if (charCountEl) charCountEl.textContent = charCount + ' caracteres';
    if (paragraphCountEl) paragraphCountEl.textContent = paragraphCount + ' parágrafos';
    if (lastModEl) lastModEl.textContent = new Date().toLocaleTimeString();
}
