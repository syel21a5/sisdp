/**
 * JS para Perícia Papiloscópica em Local de Crime utilizando TinyMCE 6 e DocumentoService
 */

function printDocument() {
    console.log('🟢 GERANDO PDF DE PERÍCIA PAPILOSCÓPICA EM LOCAL DE CRIME...');
    
    const dados = window.dadosParaImpressao || {};
    const content = tinymce.activeEditor.getContent();

    const endpoint = '/pericia-papiloscopica-local-crime-pdf';

    // Dados para envio via POST
    const dadosParaEnviar = {
        'orgao_emissor': 'PERICIA',
        'cidade': dados.cidade || 'Afogados da Ingazeira',
        'delegacia': dados.delegacia || '',
        'delegado': dados.delegado || '',
        'escrivao': dados.escrivao || '',
        'data_comp': dados.data_comp || '',
        'nome': dados.nome || '',
        'boe': dados.boe || '',
        'apreensao': dados.apreensao || '',
        'data_ext': dados.data_ext || '',
        'conteudo': content
    };

    // Usar DocumentoService para envio seguro via POST
    DocumentoService.gerar(endpoint, dadosParaEnviar);
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar TinyMCE com o DocumentoService
    DocumentoService.initTinyMCE('#editor', printDocument);

    // Atualizar estatísticas periodicamente
    setInterval(updateStats, 1000);

    // Lógica do Modal de Seleção Múltipla de Vítimas
    setTimeout(verificarVitimas, 1500);
});

function verificarVitimas() {
    const dados = window.dadosParaImpressao || {};
    
    // Se a página veio preenchida com dados flat da Vítima 1 (via aba Vítima 1)
    if (dados.vitima1_nome && dados.vitima1_nome !== '[NOME DA VÍTIMA 1]' && dados.vitima1_nome !== '') {
        return; 
    }
    
    // Se veio da aba Vítima 1 mas usando a estrutura de objeto da vitima1
    if (dados.vitima1 && dados.vitima1.nome && dados.vitima1.nome !== '[NOME DA VÍTIMA 1]' && dados.vitima1.nome !== '') {
        return;
    }

    // 1. Tentar pegar as vítimas diretamente dos dados locais enviados pelo formulário (Chips)
    let vitimasLocais = [];
    if (dados.vitimas && Array.isArray(dados.vitimas) && dados.vitimas.length > 0) {
        vitimasLocais = dados.vitimas;
    } else if (dados.lista_vitimas && Array.isArray(dados.lista_vitimas) && dados.lista_vitimas.length > 0) {
        vitimasLocais = dados.lista_vitimas;
    }

    if (vitimasLocais.length > 0) {
        console.log('✅ Vítimas encontradas nos dados locais:', vitimasLocais);
        mostrarModalVitimas(vitimasLocais);
        return;
    }

    // 2. Se não encontrou localmente, busca da API usando o BOE (caso o procedimento já esteja salvo)
    if (!dados.boe || dados.boe === 'N/A') {
        console.log('⚠️ Sem BOE ou sem vítimas locais para buscar.');
        return;
    }

    console.log('🔍 Buscando vítimas do banco de dados para o BOE:', dados.boe);
    fetch(`/boe/dados-extraidos/${dados.boe}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.dados && data.dados.vitimas && data.dados.vitimas.length > 0) {
                mostrarModalVitimas(data.dados.vitimas);
            } else {
                console.log('⚠️ Nenhuma vítima encontrada no banco de dados para este BOE.');
                Swal.fire({
                    title: 'Nenhuma Vítima Encontrada',
                    text: 'Não detectamos vítimas cadastradas neste BOE. Você pode digitar os dados diretamente no editor.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Erro ao buscar vítimas:', error);
        });
}

function mostrarModalVitimas(vitimas) {
    // Cria o HTML com as opções de checkboxes
    let checkboxesHtml = '<div style="text-align: left; max-height: 250px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 5px;">';
    vitimas.forEach((v, index) => {
        const docInfo = v.cpf ? `CPF: ${v.cpf}` : (v.rg ? `RG: ${v.rg}` : 'Sem documentos');
        checkboxesHtml += `
            <div class="form-check mb-3" style="font-size: 15px;">
                <input class="form-check-input vitima-checkbox" type="checkbox" value="${index}" id="vitima_${index}" checked style="width: 1.2em; height: 1.2em;">
                <label class="form-check-label" for="vitima_${index}" style="margin-left: 8px; cursor: pointer;">
                    <strong>${v.nome}</strong><br>
                    <span class="text-muted" style="font-size: 13px;">${docInfo} | Nasc: ${v.nascimento || v.data_nascimento || 'Não informado'}</span>
                </label>
            </div>
        `;
    });
    checkboxesHtml += '</div>';

    Swal.fire({
        title: 'Selecionar Vítimas',
        html: `
            <p style="font-size: 15px; margin-bottom: 15px;">Selecione abaixo as vítimas que deseja qualificar no Ofício:</p>
            ${checkboxesHtml}
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Inserir Selecionadas',
        cancelButtonText: 'Digitar Manualmente',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const selecionados = [];
            const checkboxes = document.querySelectorAll('.vitima-checkbox:checked');
            checkboxes.forEach(cb => {
                selecionados.push(vitimas[cb.value]);
            });
            if (selecionados.length === 0) {
                Swal.showValidationMessage('Selecione pelo menos uma vítima ou clique em Cancelar para digitar.');
                return false;
            }
            return selecionados;
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            injetarVitimasNoEditor(result.value);
        }
    });
}

function injetarVitimasNoEditor(vitimasSelecionadas) {
    if (!tinymce.activeEditor) return;

    let htmlVitimas = '';
    vitimasSelecionadas.forEach(v => {
        const nome = (v.nome || '').toUpperCase();
        const nascimento = v.nascimento || v.data_nascimento || 'NÃO INFORMADO';
        const idade = v.idade ? `${v.idade} ANOS` : 'NÃO INFORMADO';
        const rg = v.rg || 'NÃO INFORMADO';
        const cpf = v.cpf || 'NÃO INFORMADO';
        const mae = (v.mae || '').toUpperCase() || 'NÃO INFORMADO';
        const pai = (v.pai || '').toUpperCase() || 'NÃO INFORMADO';
        const endereco = (v.endereco || '').toUpperCase() || 'NÃO INFORMADO';

        htmlVitimas += `
            <p style="text-align: justify; line-height: 1.5; margin-left: 30px;">
                <strong>${nome}</strong><br>
                <strong>NASCIMENTO:</strong> ${nascimento} 
                <strong>IDADE:</strong> ${idade}<br>
                <strong>RG:</strong> ${rg} 
                <strong>CPF:</strong> ${cpf}<br>
                <strong>MÃE:</strong> ${mae}<br>
                <strong>PAI:</strong> ${pai}<br>
                <strong>END. RESIDENCIAL:</strong> ${endereco}
            </p>
            <div style="height: 12px;"></div>
        `;
    });

    let content = tinymce.activeEditor.getContent();
    
    // Expressão regular para encontrar o bloco padrão de vítima e substituir
    const regexBlocoAntigo = /<p[^>]*>\s*<strong>(?:<span[^>]*>)?\[NOME DA VÍTIMA 1\](?:<\/span>)?<\/strong>[\s\S]*?END\. RESIDENCIAL:.*?<\/p>/i;
    
    if (regexBlocoAntigo.test(content)) {
        content = content.replace(regexBlocoAntigo, htmlVitimas);
        tinymce.activeEditor.setContent(content);
        
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Vítimas inseridas com sucesso!',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        // Se não encontrar o bloco padrão, insere no cursor
        tinymce.activeEditor.execCommand('mceInsertContent', false, htmlVitimas);
    }
}
