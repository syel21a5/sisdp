/**
 * JS para Ofícios APFD Dinâmico utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE OFÍCIOS APFD...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'APFD OFICIOS DINAMICO',
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
        'ip': dados.ip || '',
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'parente': dados.parente || 'NÃO INFORMADO',
        'familia': dados.familia || 'NÃO INFORMADO',
        'nmandado': dados.nmandado || 'NÃO INFORMADO',
        'datamandado': dados.datamandado || 'NÃO INFORMADO',
        'incidencia_penal': dados.incidencia_penal || dados.natureza || '',
        'numero_oficio_juiz': dados.numero_oficio_juiz || 'NÃO GERADO',
        'numero_oficio_promotor': dados.numero_oficio_promotor || 'NÃO GERADO',
        'numero_oficio_defensor': dados.numero_oficio_defensor || 'NÃO GERADO',
        'conteudo': content,

        // Dados estruturados
        'condutor': dados.condutor || {},
        'vitima1': dados.vitima1 || {},
        'vitima2': dados.vitima2 || {},
        'vitima3': dados.vitima3 || {},
        'testemunha1': dados.testemunha1 || {},
        'testemunha2': dados.testemunha2 || {},
        'testemunha3': dados.testemunha3 || {},
        'autor1': dados.autor1 || {},
        'autor2': dados.autor2 || {},
        'autor3': dados.autor3 || {},
        'autores': dados.autores || []
    };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar('/oficios-apfd-dinamico', dadosParaEnviar);
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
