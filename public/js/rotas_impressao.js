// ROTAS DE IMPRESSAO PARA TODAS AS ABAS - ARQUIVO JS PURO CORRIGIDO
// Arquivo: public/js/rotas_impressao.js

// ROTAS PARA A ABA INICIO
var rotasImpressaoInicio = {
    'AVALIACAO DE OBJETOS - PORTARIA': "/avaliacao-portaria-gerar",
    'AVALIACAO DE OBJETOS - AUTO': "/avaliacao-termo-gerar",
    'AVALIACAO INDIRETA DE OBJETOS - PORTARIA': "/avaliacao-indireta-portaria-gerar",
    'AVALIACAO INDIRETA DE OBJETOS - AUTO': "/avaliacao-indireta-termo-gerar",
    'AVALIACAO INDIRETA DE OBJETOS - TERMO': '/avaliacao-indireta-termo', // Added this line based on instruction
    // EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO
    'EXAME DE CONSTATACAO DE DANOS - PORTARIA': '/exame-danos-portaria-gerar', // CORRIGIDO: Rota da View
    'EXAME DE CONSTATACAO DE DANOS - TERMO': '/exame-danos-termo-gerar', // CORRIGIDO: Rota da View
    // EXAME DE CONSTATAÇÃO DE DANOS INDIRETA
    'EXAME DE CONSTATACAO DE DANOS INDIRETA - PORTARIA': '/constatacao-indireta-portaria-gerar',
    'EXAME DE CONSTATACAO DE DANOS INDIRETA - AUTO': '/constatacao-indireta-termo-gerar',
    // EXAME DE EFICIÊNCIA DE ARMA DE FOGO
    'EXAME DE EFICIENCIA DE ARMA DE FOGO - PORTARIA': '/eficiencia-arma-portaria-gerar',
    'EXAME DE EFICIENCIA DE ARMA DE FOGO - AUTO': '/eficiencia-arma-termo-gerar',
    'PERICIA EM VEICULO': '/pericia-em-veiculo/--DADOS--',
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--',
    'OFICIOS MANDADO DE PRISAO': "/numero-oficio/gerar",
    'ROL DE TESTEMUNHAS': "/rol-de-testemunhas-gerar",
    'DESPACHO DE CONCLUSAO': "/despacho-conclusao/--DADOS--"
};

// ROTAS PARA A ABA CONDUTOR (APFD)
var rotasImpressaoCondutor = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'AAFAI CONDUTOR': "/aafai-condutor/--DADOS--",
    'APFD CONDUTOR': "/apfd-condutor/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    "CERTIDAO DE ASSINATURA APFD": "/certidao-assinaturas-apfd/--DADOS--",
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--'
};

// ROTAS PARA A ABA VITIMA1 (APFD)
var rotasImpressaoVitima1 = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'AAFAI - VITIMA 1': "/aafai-vitima1/--DADOS--",
    'APFD - VITIMA 1': "/apfd-vitima1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--'
};



// ROTAS PARA A ABA TESTEMUNHA1 (APFD)
var rotasImpressaoTestemunha1 = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'AAFAI - TESTEMUNHA 1': "/aafai-testemunha1/--DADOS--",
    'APFD - TESTEMUNHA 1': "/apfd-testemunha1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--"
};

// ROTAS PARA A ABA OUTRO (APFD) - Mesmas rotas da Testemunha1
var rotasImpressaoOutro = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'AAFAI - TESTEMUNHA 1': "/aafai-testemunha1/--DADOS--",
    'APFD - TESTEMUNHA 1': "/apfd-testemunha1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--"
};



// ROTAS PARA A ABA AUTOR1 (APFD)
var rotasImpressaoAutor1 = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'INTERROGATORIO - APFD': "/interrogatorio-autor1apfd/--DADOS--",
    'AAFAI - AUTOR 1': "/aafai-autor1/--DADOS--",
    'APFD - AUTOR 1': "/apfd-autor1/--DADOS--",
    'APFD - AUTOR 1 COM FIANÇA': "/apfd-autor1-com-fianca/--DADOS--",
    'APFD - AUTOR 1 SEM FIANÇA': "/apfd-autor1-sem-fianca/--DADOS--",
    'NOTA DE CULPA': "/notadeculpa-dinamica/--DADOS--",
    'NOTA DE CIENCIA - GARANTIAS CONSTITUCIONAIS': "/notadeculpa-dinamica/--DADOS--?tipo=ciencia",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    'AUTO CIRCUNSTACIADO - AUTOR 1': "/autocircunstanciado/--DADOS--",
    'COMUNICACAO DE APFD': "/oficios-apfd-dinamico/--DADOS--",
    'COMUNICACAO DE APFD - UNICO OFICIO': "/oficios-apfd-unico/--DADOS--",
    'COMUNICACAO DE APFD - 1 AUTOR': "/oficios-apfd-dinamico/--DADOS--",
    'MANDADO DE PRISAO - OFICIOS': "/oficios-mp/--DADOS--",
    'MANDADO DE PRISAO - OFICIO FAMILIA': "/oficiofamilia-mp/--DADOS--",
    'APFD - OFICIO FAMILIA': "/oficiofamilia-apfd/--DADOS--",
    'MANDADO DE PRISAO - RECOLHIMENTO': "/recolhimento-mp/--DADOS--"
};



// ✅ ROTAS PARA A ABA INTIMAÇÃO
var rotasImpressaoIntimacao = {
    'INTIMAÇÃO PADRÃO': "/intimacao/--DADOS--",
    'EDITOR DE INTIMAÇÃO': "/intimacao/--DADOS--"
};

// OBJETO GLOBAL PARA ACESSO FACILITADO
var RotasImpressao = {
    inicio: rotasImpressaoInicio,
    condutor: rotasImpressaoCondutor,
    vitima1: rotasImpressaoVitima1,
    testemunha1: rotasImpressaoTestemunha1,
    autor1: rotasImpressaoAutor1,
    outro: rotasImpressaoOutro, // ✅ ADICIONADO
    intimacao: rotasImpressaoIntimacao // ✅ ADICIONADO
};
