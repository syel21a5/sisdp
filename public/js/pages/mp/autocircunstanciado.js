/**
 * JS para Auto Circunstanciado utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE AUTO CIRCUNSTANCIADO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    const horaFatoVal = (document.querySelector('#inputHoraFato')?.value || dados.hora_fato || dados.hora || 'NÃO INFORMADO');
    const endFatoVal = (document.querySelector('#inputEndFato')?.value || dados.end_fato || dados.endereco || 'NÃO INFORMADO');

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'AUTO CIRCUNSTANCIADO - AUTOR 1',
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
        'nmandado': dados.nmandado || 'NÃO INFORMADO',
        'datamandado': dados.datamandado || 'NÃO INFORMADO',
        'hora_fato': horaFatoVal,
        'end_fato': endFatoVal,
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
        'autor3': dados.autor3 || {}
    };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar('/autocircunstanciado', dadosParaEnviar);
}

function preencherPlaceholders() {
    const dados = window.dadosParaImpressao || {};
    const editorEl = document.getElementById('editor');
    if (!editorEl) return;
    
    let html = editorEl.innerHTML;
    const horaFato = (document.querySelector('#inputHoraFato')?.value || dados.hora_fato || dados.hora || 'NÃO INFORMADO');
    const endFato = (document.querySelector('#inputEndFato')?.value || dados.end_fato || dados.endereco || 'NÃO INFORMADO');
    const dataVal = dados.data_ext || 'NÃO INFORMADO';
    const nMandadoVal = dados.nmandado || 'NÃO INFORMADO';
    const autorIdx = (window.autorAlvo && Number(window.autorAlvo)) ? Number(window.autorAlvo) : 1;
    const intimadoVal = (dados[`autor${autorIdx}`]?.nome || dados.nome || 'NÃO INFORMADO');
    const test1Val = (dados.testemunha1?.nome || 'NÃO INFORMADO');
    const test2Val = (dados.testemunha2?.nome || 'NÃO INFORMADO');

    const map = {
        '\\[DATA\\]': dataVal !== 'NÃO INFORMADO' ? `<strong>${dataVal}</strong>` : dataVal,
        '\\[CIDADE\\]': dados.cidade || 'NÃO INFORMADO',
        '\\[NUMERO MANDADO\\]': nMandadoVal !== 'NÃO INFORMADO' ? `<strong>${nMandadoVal}</strong>` : nMandadoVal,
        '\\[HORA\\]': horaFato,
        'ENDEREÇO DO MANDADO': endFato,
        'ENDEREÇO DO FATO': endFato,
        '\\[ENDEREÇO DO FATO\\]': endFato,
        '\\[INTIMADO\\]': intimadoVal !== 'NÃO INFORMADO' ? `<strong>${intimadoVal}</strong>` : intimadoVal,
        '\\[TESTEMUNHA_1\\]': test1Val !== 'NÃO INFORMADO' ? `<strong>${test1Val}</strong>` : test1Val,
        '\\[TESTEMUNHA_2\\]': test2Val !== 'NÃO INFORMADO' ? `<strong>${test2Val}</strong>` : test2Val
    };
    
    Object.keys(map).forEach(k => { 
        const re = new RegExp(k, 'g'); 
        html = html.replace(re, map[k]); 
    });
    
    editorEl.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    // Preencher placeholders antes de inicializar o TinyMCE
    preencherPlaceholders();

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
