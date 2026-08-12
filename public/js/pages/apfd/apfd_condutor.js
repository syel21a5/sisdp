// No início do arquivo apfd_condutor.js, adicione:
console.log('🟢 APFD CONDUTOR - JavaScript específico carregado');

// Identificar número de autores pela URL
const currentPath = window.location.pathname;

if (currentPath.includes('3autores')) {
    window.documentType = 'APFD_CONDUTOR_3AUTORES';
    console.log('🟢 Versão: APFD Condutor 3 Autores');
} else if (currentPath.includes('2autores')) {
    window.documentType = 'APFD_CONDUTOR_2AUTORES';
    console.log('🟢 Versão: APFD Condutor 2 Autores');
} else {
    window.documentType = 'APFD_CONDUTOR_1AUTOR';
    console.log('🟢 Versão: APFD Condutor 1 Autor');
}
// FIM DA ADIÇÃO


console.log('🟢 APFD CONDUTOR - JavaScript específico carregado');
window.documentType = 'APFD_CONDUTOR';

// Configurar o editor TinyMCE usando DocumentoService
DocumentoService.initTinyMCE('#editor', printDocument);

// Função principal para gerar PDF com DOMPDF
function printDocument() {
    console.log('🟢 GERANDO PDF APFD CONDUTOR COM TINYMCE...');

    const dados = window.dadosParaImpressao || {};
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    // Adicionar todos os dados necessários
    const dadosParaEnviar = {
        'orgao_emissor': 'APFD CONDUTOR',
        'cidade': dados.cidade || 'NÃO INFORMADO',
        'delegacia': dados.delegacia || 'NÃO INFORMADO',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',

        // ✅ DADOS DO CONDUTOR
        'nome': dados.condutor?.nome || dados.nome || '',
        'alcunha': dados.condutor?.alcunha || dados.alcunha || '',
        'nascimento': dados.condutor?.nascimento || dados.nascimento || '',
        'idade': dados.condutor?.idade || dados.idade || '',
        'estcivil': dados.condutor?.estcivil || dados.estcivil || '',
        'naturalidade': dados.condutor?.naturalidade || dados.naturalidade || '',
        'rg': dados.condutor?.rg || dados.rg || '',
        'cpf': dados.condutor?.cpf || dados.cpf || '',
        'profissao': dados.condutor?.profissao || dados.profissao || '',
        'instrucao': (dados.condutor?.instrucao || dados.instrucao || '').toUpperCase(),
        'telefone': dados.condutor?.telefone || dados.telefone || '',
        'mae': dados.condutor?.mae || dados.mae || '',
        'pai': dados.condutor?.pai || dados.pai || '',
        'endereco': dados.condutor?.endereco || dados.endereco || '',

        'boe': dados.boe || '',
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'nmandado': dados.nmandado || 'NÃO INFORMADO',
        'datamandado': dados.datamandado || 'NÃO INFORMADO',

        // Mantém as listas para compatibilidade com o Blade/Controller
        'condutor': JSON.stringify(dados.condutor || {}),
        'vitima1': JSON.stringify(dados.vitima1 || {}),
        'vitima2': JSON.stringify(dados.vitima2 || {}),
        'vitima3': JSON.stringify(dados.vitima3 || {}),
        'testemunha1': JSON.stringify(dados.testemunha1 || {}),
        'testemunha2': JSON.stringify(dados.testemunha2 || {}),
        'testemunha3': JSON.stringify(dados.testemunha3 || {}),
        'autor1': JSON.stringify(dados.autor1 || {}),
        'autor2': JSON.stringify(dados.autor2 || {}),
        'autor3': JSON.stringify(dados.autor3 || {}),
        'conteudo': content
    };

    DocumentoService.gerar('/apfd-condutor', dadosParaEnviar);
}

// ✅ CONFIGURAÇÃO INICIAL
document.addEventListener('DOMContentLoaded', function() {
    // Agora o botão Gerar PDF vive DENTRO do editor!
    console.log('DOM Carregado, o botão do PDF agora está interno no TinyMCE.');
});

// =============================================
// JAVASCRIPT MOVIDO DO BLADE PARA AQUI
// =============================================

// Configuração dos dados para impressão
window.dadosParaImpressao = window.dadosParaImpressao || {};

// Contador de caracteres e parágrafos
function updateStats() {
    const editorContent = document.getElementById('editor').textContent;
    const charCount = editorContent.length;
    const paragraphCount = document.querySelectorAll('#editor p').length;

    document.getElementById('char-count').textContent = charCount + ' caracteres';
    document.getElementById('paragraph-count').textContent = paragraphCount + ' parágrafos';
    document.getElementById('last-modified').textContent = new Date().toLocaleTimeString();
}

// Adicionar tooltips aos botões
function setupTooltips() {
    // Agora o TinyMCE gerencia seus próprios tooltips localizados PT-BR
}

// Configurar atalhos de teclado 
function setupKeyboardShortcuts() {
    // O TinyMCE possui atalhos nativos, por exemplo Ctrl+Enter pode ser escutado via event listener:
    if (tinymce.activeEditor) {
        tinymce.activeEditor.addShortcut('ctrl+13', 'Adicionar quebra de página', function () {
            tinymce.activeEditor.execCommand('mcePageBreak');
        });
    }
}

// Inicialização completa
function initializeEditor() {
    console.log('Dados carregados:', window.dadosParaImpressao);

    // Atualizar estatísticas a cada alteração
    setInterval(updateStats, 1000);
    updateStats();

    // Configurar tooltips
    setupTooltips();

    // Configurar atalhos de teclado
    setupKeyboardShortcuts();
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', initializeEditor);
