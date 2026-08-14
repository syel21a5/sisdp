const htmlTemp = `
Inquirida pela Autoridade Policial passou a responder: QUE,
TERMO DE DECLARAÇÃO
LERISVANIO CLERISVALDO NUNES BRASIL

Aos Trinta e Um dias...
NOME: LERISVANIO...
Inquirida pela Autoridade Policial acerca dos fatos, disse o que se segue: QUE, A TIA DO RAPAZ ESTAVA PRESENTE AO JOGO DE FUTEBOL . Nada mais havendo a acrescentar, lido e achado conforme...
AUTORIDADE POLICIAL:
INTERROGADO:
ESCRIVÃO:
. Nada mais havendo a acrescentar, lido e achado conforme...
AUTORIDADE POLICIAL:
DEPOENTE:
ESCRIVÃO:
`;

let textoOitiva = '';
const matchStart = htmlTemp.match(/QUE[,:]/gi);

if (matchStart) {
    const lastStart = htmlTemp.lastIndexOf(matchStart[matchStart.length - 1]);
    const regexFim = /\.?\s*Nada mais (havendo|disse|declarou|a acrescentar)/i;
    const substring = htmlTemp.substring(lastStart);
    const matchFim = substring.match(regexFim);
    
    if (matchFim) {
        let extracted = substring.substring(matchStart[matchStart.length - 1].length, matchFim.index);
        extracted = extracted.replace(/^\s*<span\s+id="conteudo-[^"]+"[^>]*>/i, '');
        extracted = extracted.replace(/<\/span>\s*$/i, '');
        textoOitiva = extracted.trim();
    } else {
        textoOitiva = "FIM NAO ENCONTRADO";
    }
} else {
    textoOitiva = "START NAO ENCONTRADO";
}

console.log("TEXTO EXTRAIDO:");
console.log(textoOitiva);
