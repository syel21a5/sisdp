/**
 * JS para Termo de Interrogatório utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE INTERROGATÓRIO...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'INTERROGATÓRIO',
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
        'data_ext': dados.data_ext || 'NÃO INFORMADO',
        'conteudo': content
    };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar('/termo-de-interrogatorio', dadosParaEnviar);

    // 🎯 Salvar a oitiva no banco (pessoa + BOE)
    salvarOitivaAutomaticamente(dados, content, 'conteudo-interrogatorio');
}

/**
 * 🎯 Salva o texto da oitiva no banco (apfd_pessoas_detalhes.interrogatorio)
 */
function salvarOitivaAutomaticamente(dados, conteudoHtml, campoConteudoId) {
    const pessoaId = dados._pessoa_id || '';
    const boe = dados._boe || dados.boe || '';
    const papel = dados._papel || '';

    if (!pessoaId || !boe || !papel) {
        console.log('ℹ️ Sem metadados de oitiva (pessoa/boe/papel), não será salva no banco.');
        return;
    }

    let textoOitiva = '';
    try {
        const el = document.getElementById(campoConteudoId);
        if (el) {
            textoOitiva = el.innerHTML.trim();
        } else {
            textoOitiva = conteudoHtml.trim();
        }
    } catch (e) {
        textoOitiva = conteudoHtml;
    }

    const limpo = textoOitiva.replace(/<[^>]*>/g, '').trim();
    if (!limpo || limpo.toUpperCase().includes('ESCREVER AQUI')) {
        console.log('ℹ️ Oitiva vazia (nada digitado), não será salva.');
        return;
    }

    $.ajax({
        url: '/apfd/detalhes/salvar',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
            cadprincipal_id: 0,
            pessoa_id: pessoaId,
            papel: papel,
            boe: boe,
            interrogatorio: textoOitiva,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (resp) {
            console.log('✅ Oitiva salva no banco:', resp);
        },
        error: function (xhr) {
            console.error('❌ Erro ao salvar oitiva:', xhr.responseText);
        }
    });
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
