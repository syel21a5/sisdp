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

// Configurar o editor TinyMCE
tinymce.init({
    selector: '#editor', // Montar na div id="editor"
    inline: false, // Mudado para FALSE para que a barra de ferramentas seja sempre estática/fixa!
    min_height: 800, // Altura mínima de uma página inteira
    language: 'pt_BR',
    language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/pt_BR.js', // Força o download do pt_BR (O CDNJS não possui lang por padrão)
    promotion: false, // Esconde o aviso de "Upgrade" chato do TinyMCE
    branding: false, // Remove a marca d'agua do triny no rodapé para um visual mais limpo
    plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table code help wordcount',
    // Limpos os blocos (Simples), fontes, etc., e o gerarpdf posicionado no lado DIREITO
    toolbar: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | table bullist numlist outdent indent | link image | pagebreak chars fullscreen | gerarpdf',
    menubar: 'file edit view insert format tools table help',
    font_size_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    content_style: `
        body { 
            font-family: Arial, sans-serif; 
            font-size: 14pt; 
            line-height: 1.6; 
            padding: 30px 60px; 
            max-width: 1200px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        } 
        p { margin: 0.3em 0 }
    `,
    setup: function(editor) {
        // Registrando nosso botão lindo e verde (aproveitando SVG puro do TinyMCE ou um ícone nativo)
        editor.ui.registry.addButton('gerarpdf', {
            text: 'GERAR PDF',
            icon: 'document-properties', // Ícone nativo de documento
            tooltip: 'Gerar Documento Oficial em PDF',
            onAction: function (_) {
                printDocument(); // Chama a função que já temos de criar o PDF
            }
        });

        editor.on('init', function() {
            console.log('TinyMCE Editor Inicializado com sucesso.');
            // Destaca o botão recém criado injetando via CSS um fundo verde nele para chamar a atenção
            setTimeout(() => {
                const btns = document.querySelectorAll('.tox-tbtn');
                btns.forEach(btn => {
                    // Cobre tanto o nome completo quanto truncado (GERAR...)
                    if(btn.innerText.includes('GERAR')){
                        btn.style.backgroundColor = '#198754';
                        btn.style.color = '#ffffff';
                        btn.style.fontWeight = 'bold';
                        btn.style.padding = '0 15px';
                        btn.style.borderRadius = '5px';
                        // Força a exibição do nome total GERAR PDF
                        btn.style.width = 'auto';
                        btn.style.minWidth = 'fit-content'; 
                        btn.style.padding = '0 20px';
                        btn.style.overflow = 'visible';
                        
                        // Garante que o texto interno não seja cortado
                        const textLabel = btn.querySelector('.tox-tbtn__text');
                        if (textLabel) {
                            textLabel.style.width = 'auto';
                            textLabel.style.overflow = 'visible';
                            textLabel.style.textOverflow = 'clip';
                            textLabel.style.display = 'inline-block';
                        }
                        
                        // Empurra o botão para a direita alinhando ao extremo da barra
                        btn.style.marginLeft = 'auto';
                        const iconPath = btn.querySelector('.tox-icon svg');
                        if(iconPath) iconPath.style.fill = '#ffffff';
                    }
                });
            }, 1000); // Aumentado o delay para garantir que o TinyMCE terminou de renderizar a UI
        });
        editor.on('change keyup', function() {
            updateStats();
        });
    }
});

// Função substituída pela inteligência de exportação do TinyMCE

// Função principal para gerar PDF com DOMPDF
function printDocument() {
    console.log('🟢 GERANDO PDF APFD CONDUTOR COM TINYMCE...');

    const dados = window.dadosParaImpressao || {};

    // ✅ CAPTURAR CONTEÚDO DO EDITOR TINYMCE
    let content = tinymce.activeEditor ? tinymce.activeEditor.getContent() : document.getElementById('editor').innerHTML;

    // Criar formulário para enviar ao Laravel
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/apfd-condutor';
    form.target = '_blank';
    form.style.display = 'none';

    // Adicionar CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token não encontrado!');
        Swal.fire("Atenção", 'Erro de segurança. CSRF token não encontrado.', "warning");
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


    // Adicionar todos os dados necessários - ✅ VERSÃO CORRIGIDA
    const dadosParaEnviar = {
    'orgao_emissor': 'AAAAI - CONDUTOR',
    'cidade': dados.cidade || 'NÃO INFORMADO',
    'delegacia': dados.delegacia || 'NÃO INFORMADO',
    'delegado': dados.delegado || '',
    'escrivao': dados.escrivao || '',

    // ✅✅✅ CORREÇÃO: USAR DADOS DO CONDUTOR ESPECIFICAMENTE
    'nome': dados.condutor?.nome || dados.nome || '',
    'alcunha': dados.condutor?.alcunha || dados.alcunha || '',
    'nascimento': dados.condutor?.nascimento || dados.nascimento || '',
    'idade': dados.condutor?.idade || dados.idade || '',
    'estcivil': dados.condutor?.estcivil || dados.estcivil || '',
    'naturalidade': dados.condutor?.naturalidade || dados.naturalidade || '',
    'rg': dados.condutor?.rg || dados.rg || '',
    'cpf': dados.condutor?.cpf || dados.cpf || '',
    'profissao': dados.condutor?.profissao || dados.profissao || '',
    'instrucao': dados.condutor?.instrucao || dados.instrucao || '',
    'telefone': dados.condutor?.telefone || dados.telefone || '',
    'mae': dados.condutor?.mae || dados.mae || '',
    'pai': dados.condutor?.pai || dados.pai || '',
    'endereco': dados.condutor?.endereco || dados.endereco || '',

    'boe': dados.boe || '',
    'data_ext': dados.data_ext || 'NÃO INFORMADO',
    'nmandado': dados.nmandado || 'NÃO INFORMADO',
    'datamandado': dados.datamandado || 'NÃO INFORMADO',

    // ✅ Dados das pessoas (JSON) - MANTIDO
    'condutor': JSON.stringify(dados.condutor || {}),
    'vitima1': JSON.stringify(dados.vitima1 || {}),
    'vitima2': JSON.stringify(dados.vitima2 || {}),
    'vitima3': JSON.stringify(dados.vitima3 || {}),
    'testemunha1': JSON.stringify(dados.testemunha1 || {}),
    'testemunha2': JSON.stringify(dados.testemunha2 || {}),
    'testemunha3': JSON.stringify(dados.testemunha3 || {}),
    'autor1': JSON.stringify(dados.autor1 || {}),
    'autor2': JSON.stringify(dados.autor2 || {}),
    'autor3': JSON.stringify(dados.autor3 || {})
    };

    Object.keys(dadosParaEnviar).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = dadosParaEnviar[key];
        form.appendChild(input);
    });

    // Adicionar formulário ao documento e submeter
    document.body.appendChild(form);

    form.submit();
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
