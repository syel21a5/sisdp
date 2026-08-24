// ROTAS DE IMPRESSAO PARA TODAS AS ABAS - ARQUIVO JS PURO CORRIGIDO
// Arquivo: public/js/rotas_impressao.js

// ROTAS PARA A ABA INICIO
var rotasImpressaoInicio = {
    // ROTAS UNIFICADAS
    'AVALIACAO DE OBJETOS': "/avaliacao-completa-gerar",
    'AVALIACAO INDIRETA DE OBJETOS': "/avaliacao-indireta-completa-gerar",
    'EXAME DE CONSTATACAO DE DANOS E AVALIACAO': "/exame-danos-completa-gerar",
    'EXAME DE CONSTATACAO DE DANOS INDIRETA': "/constatacao-indireta-completa-gerar",
    'EXAME DE EFICIENCIA DE ARMA DE FOGO': "/eficiencia-arma-completa-gerar",

    // ROTAS EXISTENTES
    'PERICIA EM VEICULO': '/pericia-em-veiculo',
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--',
    'EXAME DE EFICIENCIA DE ARMA DE FOGO (COM DISPARO)': '/exame-arma-com-disparo',
    'PERICIA PAPILOSCOPICA EM VEICULO': '/pericia-papiloscopica-veiculo',
    'PERICIA PAPILOSCOPICA EM LOCAL DE CRIME': '/pericia-papiloscopica-local-crime',
    'PERICIA DE CONFRONTACAO PAPILOSCOPICA': '/pericia-papiloscopica-confrontacao',
    'PERICIA PAPILOSCOPICA EM PESSOA': '/pericia-papiloscopica-pessoa',
    'PERICIA PAPILOSCOPICA EM OBJETO': '/pericia-papiloscopica-objeto',
    'EXAME PRELIMINAR DE ENTORPECENTES': '/exame-preliminar-entorpecentes',
    'PERICIA DEFINITIVA EM ENTORPECENTES': '/pericia-definitiva-entorpecentes',
    'ROL DE TESTEMUNHAS': "/rol-de-testemunhas-gerar",
    'DESPACHO DE CONCLUSAO': "/despacho-conclusao-gerar",
    'PERICIA DE ARROMBAMENTO': '/pericia-arrombamento',
    'OFICIO DE PRONTUARIO HOSPITALAR': '/oficio-prontuario-hospitalar',
    'PERICIA DOCUMENTOSCOPICA': '/pericia-documentoscopica',
    'PERICIA DE LOCAL - LUMINOL': '/pericia-local',
    'PERICIA DE INFORMATICA': '/pericia-informatica',
    'TERMO DE AUTORIZACAO PARA EXTRACAO DE DADOS': '/termo-autorizacao-dados',
    'OFICIO COMUNICACAO A ADVOGADO': '/oficio-comunica-advogado',
    'OFICIO ENCAMINHAR VEICULO CIRETRAN': '/oficio-encaminhar-veiculo',
    'CERTIDAO DE MIDIAS EM NUVEM DRIVE': '/certidao-midias-drive',
    'COMUNICACAO INTERNA (C.I.) - REMESSA DE PROCEDIMENTOS': '/ci-remessa-procedimentos',
    'COMUNICACAO INTERNA (C.I.) - REMESSA DE OBJETOS E DOCUMENTOS': '/ci-remessa-objetos-documentos',
    'COMUNICACAO INTERNA (C.I.) - ADMINISTRATIVA / GENÉRICA': '/ci-generica',
    'COMUNICACAO INTERNA (C.I.) - ENCAMINHAMENTO AO IITB': '/ci-encaminhamento-iitb',
    'OFICIO PARA INSTITUICAO FINANCEIRA / BANCO': '/oficio-banco',
    'OFICIO SOLICITANDO CERTIDAO DE OBITO': '/oficio-certidao-obito',
    'OFICIO DE ENCAMINHAMENTO DE CADAVER AO IML': '/oficio-iml',
    'OFICIO REQUISITANDO APRESENTACAO DE POLICIAL MILITAR': '/oficio-pm',
    'CERTIDAO DE COMPARECIMENTO': '/certidao-comparecimento',
    'CERTIDAO DE COMUNICACAO A FAMILIA DO PRESO': '/certidao-comunica-familia-preso',
    'RECIBO DE ENTREGA DE PRESO (CONDUTOR)': '/recibo-preso',
    'RECIBO DE PROCEDIMENTO (CUSTODIA/DESEC)': '/recibo-procedimento',
    'AUTO DE DEPOSITO FIEL': '/auto-deposito-fiel',
    'TERMO DE GUARDA E ENTREGA DE VEICULO': '/termo-guarda-veiculo',
    'TERMO DE APREENSAO': '/termo-apreensao-novo',
    'CAPA DE BOC (FLAGRANTE / PORTARIA)': '/capa-boc',
    'CAPA DE TCO (FLAGRANTE / PORTARIA)': '/capa-tco',
    'ORDEM DE SERVICO - INTIMACAO': '/ordem-servico-intimacao',
    'OFICIO DE REMESSA DE PROCEDIMENTO': '/oficio-remessa'
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
    "LAUDO TRAUMATOLOGICO": "/pericia-traumatologico/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    "CERTIDAO DE ASSINATURA APFD": "/certidao-assinaturas-apfd/--DADOS--",
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--',
    'EXAME PRELIMINAR DE ENTORPECENTES': '/exame-preliminar-entorpecentes',
    'PERICIA DEFINITIVA EM ENTORPECENTES': '/pericia-definitiva-entorpecentes'
};

// ROTAS PARA A ABA VITIMA1 (APFD)
var rotasImpressaoVitima1 = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'AAFAI - VITIMA': "/aafai-vitima1/--DADOS--",
    'APFD - VITIMA': "/apfd-vitima1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO": "/pericia-traumatologico/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    'PERICIA EM LOCAL DE CRIME': '/pericia-local-de-crime/--DADOS--',
    'PERICIA PAPILOSCOPICA EM LOCAL DE CRIME': '/pericia-papiloscopica-local-crime',
    'PERICIA DE CONFRONTACAO PAPILOSCOPICA': '/pericia-papiloscopica-confrontacao',
    'PERICIA PAPILOSCOPICA EM PESSOA': '/pericia-papiloscopica-pessoa',
    'PERICIA PAPILOSCOPICA EM OBJETO': '/pericia-papiloscopica-objeto'
};



// ROTAS PARA A ABA TESTEMUNHA1 (APFD)
var rotasImpressaoTestemunha1 = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'AAFAI - TESTEMUNHA': "/aafai-testemunha1/--DADOS--",
    'APFD - TESTEMUNHA': "/apfd-testemunha1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO": "/pericia-traumatologico/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--"
};

// ROTAS PARA A ABA OUTRO (APFD) - Mesmas rotas da Testemunha1
var rotasImpressaoOutro = {
    'TERMO DE DECLARACAO': "/declaracao/--DADOS--",
    'TERMO DE DEPOIMENTO': "/depoimento/--DADOS--",
    'AAFAI - TESTEMUNHA': "/aafai-testemunha1/--DADOS--",
    'APFD - TESTEMUNHA': "/apfd-testemunha1/--DADOS--",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO": "/pericia-traumatologico/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--"
};



// ROTAS PARA A ABA AUTOR1 (APFD)
var rotasImpressaoAutor1 = {
    'TERMO DE INTERROGATORIO': "/interrogatorio/--DADOS--",
    'INTERROGATORIO - APFD': "/interrogatorio-autor1apfd/--DADOS--",
    'AAFAI - AUTOR': "/aafai-autor1/--DADOS--",
    'APFD - AUTOR': "/apfd-autor1/--DADOS--",
    'APFD - AUTOR COM FIANÇA': "/apfd-autor1-com-fianca/--DADOS--",
    'APFD - AUTOR SEM FIANÇA': "/apfd-autor1-sem-fianca/--DADOS--",
    'NOTA DE CULPA': "/notadeculpa-dinamica/--DADOS--",
    'NOTA DE CIENCIA - GARANTIAS CONSTITUCIONAIS': "/notadeculpa-dinamica/--DADOS--?tipo=ciencia",
    'AUTO DE APRESENTACAO E APREENSAO': "/auto-apreensao/--DADOS--",
    'TERMO DE RESTITUICAO': "/documentos/termo-restituicao/--DADOS--",
    'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "/documentos/termo-renuncia-representacao/--DADOS--",
    'TERMO DE REPRESENTACAO': "/documentos/termo-representacao/--DADOS--",
    'TERMO DE COMPROMISSO': "/documentos/termo-compromisso-juizo/--DADOS--",
    'TERMO DE LIBERACAO DE MENOR - INFRATOR': "/liberacao-infrator/--DADOS--",
    "LAUDO TRAUMATOLOGICO": "/pericia-traumatologico/--DADOS--",
    "LAUDO TRAUMATOLOGICO IML": "/termo-traumatologico-iml/--DADOS--",
    "CERTIDAO DE ASSINATURA INDIVIDUAL": "/certidao-assinaturas-individual/--DADOS--",
    'AUTO CIRCUNSTACIADO - AUTOR': "/autocircunstanciado/--DADOS--",
    'PERICIA PAPILOSCOPICA EM PESSOA': '/pericia-papiloscopica-pessoa',
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
