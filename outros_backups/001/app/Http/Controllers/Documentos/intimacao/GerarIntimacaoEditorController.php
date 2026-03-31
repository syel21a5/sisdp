<?php
namespace App\Http\Controllers\Documentos\intimacao;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Http\Controllers\Controller;

class GerarIntimacaoEditorController extends Controller
{
    public function gerarPdfIntimacao(Request $request)
    {
        // Validar os dados recebidos
        $request->validate([
            'conteudo' => 'required',
            'delegacia' => 'sometimes|string',
            'cidade' => 'sometimes|string',
        ]);

        $dados = $request->all();

        // ✅ REMOVER BOM E CARACTERES INVÁLIDOS - SOLUÇÃO DEFINITIVA
        $dados['conteudo'] = $this->cleanContent($dados['conteudo']);

        // Configurar opções do DOMPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isPhpEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('defaultEncoding', 'UTF-8');

        $dompdf = new Dompdf($options);

        // Converter imagens para base64
        $image1Base64 = $this->imageToBase64(public_path('images/b_PE.jpg'));
        $image2Base64 = $this->imageToBase64(public_path('images/b_PCPE.png'));

        $textoRodape = '';

        // CRIAR O SEGUNDO CABEÇALHO COMO HTML (MESMO TAMANHO DO PRIMEIRO)
        $segundoCabecalho = '
        <div style="text-align: center; margin: 5px 0 3px 0; padding: 2px 0;">
            <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                <tr>
                    <td style="width: 50px; vertical-align: middle; text-align: center;">
                        ' . ($image1Base64 ? '<img src="' . $image1Base64 . '" alt="Brasão PE" style="width: 50px; height: 50px; object-fit: contain;">' : '') . '
                    </td>
                    <td style="vertical-align: middle; text-align: center;">
                        <div style="font-weight: bold; font-size: 9pt; margin-bottom: 0px; line-height: 1.0;">
                            POLÍCIA CIVIL DE PERNAMBUCO - PCPE
                        </div>
                        <div style="font-size: 7pt; margin-bottom: 0px; line-height: 1.0;">
                            Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
                        </div>
                        <div style="font-size: 7pt; margin-bottom: 0px; line-height: 1.0;">
                            Gerência de Controle Operacional do Interior - 2 – GCOI - 2
                        </div>
                        <div style="font-size: 7pt; margin-bottom: 0px; line-height: 1.0;">
                            20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC
                        </div>
                        <div style="font-weight: bold; font-size: 8pt; margin-top: 1px; line-height: 1.0;">
                            ' . ($dados['delegacia'] ?? 'NÃO INFORMADO') . ' – ' . ($dados['cidade'] ?? 'NÃO INFORMADO') . '
                        </div>
                    </td>
                    <td style="width: 50px; vertical-align: middle; text-align: center;">
                        ' . ($image2Base64 ? '<img src="' . $image2Base64 . '" alt="Brasão PCPE" style="width: 50px; height: 50px; object-fit: contain;">' : '') . '
                    </td>
                </tr>
            </table>
        </div>';

        // INSERIR O SEGUNDO CABEÇALHO NO LOCAL CORRETO
        $conteudoComSegundoCabecalho = $this->inserirSegundoCabecalho($dados['conteudo'], $segundoCabecalho);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* MARGEM MAIS COMPACTADA PARA UMA PÁGINA */
        @page {
            margin: 60px 20px 20px 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt !important;
            line-height: 1.15;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .header {
            position: fixed;
            top: -50px;
            left: 0;
            right: 0;
            text-align: center;
            margin-bottom: 2px;
            height: 55px;
        }

        .content {
            margin-top: 3px;
            padding: 0 8px;
            position: relative;
            z-index: 1;
        }

        .page-break {
            page-break-before: always;
            padding-top: 5px !important;
        }

        p {
            margin: 0.05em 0 !important;
            padding: 0 !important;
            line-height: 1.15 !important;
            text-align: justify;
        }

        .footer { display: none; }

        /* CABEÇALHO PRINCIPAL MAIS COMPACTO */
        .orgao-principal {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 0px;
            line-height: 1.0;
        }

        .orgao-secundario {
            font-size: 7pt;
            margin-bottom: 0px;
            line-height: 1.0;
        }

        .delegacia-info {
            margin-top: 1px;
            font-weight: bold;
            font-size: 8pt;
            line-height: 1.0;
        }

        /* CABEÇALHOS MAIS COMPACTOS */
        .header table {
            margin-bottom: 0 !important;
            border-collapse: collapse;
        }

        .header td {
            padding: 0 1px !important;
        }

        .header img {
            width: 50px !important;
            height: 50px !important;
            margin-top: -2px !important;
            object-fit: contain;
        }

        /* COMPACTAR MAIS O CONTEÚDO */
        .ql-editor p {
            margin: 0.03em 0 !important;
            padding: 0 !important;
            line-height: 1.1 !important;
        }

        /* ESTILOS COMPACTADOS PARA FONTES PERSONALIZADAS */
        .ql-font-arial, .ql-font-arial p, .ql-font-arial span, .ql-font-arial div, .ql-font-arial strong {
            font-family: Arial, sans-serif !important;
            font-size: 9.5pt !important;
        }

        .ql-size-10pt, .ql-size-10pt p, .ql-size-10pt span, .ql-size-10pt div, .ql-size-10pt strong {
            font-size: 7.5pt !important;
        }
        .ql-size-11pt, .ql-size-11pt p, .ql-size-11pt span, .ql-size-11pt div, .ql-size-11pt strong {
            font-size: 8.5pt !important;
        }
        .ql-size-12pt, .ql-size-12pt p, .ql-size-12pt span, .ql-size-12pt div, .ql-size-12pt strong {
            font-size: 9.5pt !important;
        }
        .ql-size-14pt, .ql-size-14pt p, .ql-size-14pt span, .ql-size-14pt div, .ql-size-14pt strong {
            font-size: 11pt !important;
        }
        .ql-size-16pt, .ql-size-16pt p, .ql-size-16pt span, .ql-size-16pt div, .ql-size-16pt strong {
            font-size: 13pt !important;
        }
        .ql-size-18pt, .ql-size-18pt p, .ql-size-18pt span, .ql-size-18pt div, .ql-size-18pt strong {
            font-size: 15pt !important;
        }
        .ql-size-20pt, .ql-size-20pt p, .ql-size-20pt span, .ql-size-20pt div, .ql-size-20pt strong {
            font-size: 17pt !important;
        }

        /* ESTILO ESPECÍFICO PARA SEÇÃO DE CRIME - FONTE 7.5pt */
        .secao-crime, .secao-crime p, .secao-crime span, .secao-crime strong {
            font-size: 7.5pt !important;
            line-height: 1.05 !important;
        }

        /* ESTILO PARA CABEÇALHO DA SEGUNDA VIA - MESMO TAMANHO DO PRIMEIRO */
        .segundo-cabecalho {
            margin: 3px 0 2px 0 !important;
            padding: 1px 0 !important;
        }

        .segundo-cabecalho table {
            margin: 0 auto !important;
        }

        .segundo-cabecalho img {
            width: 50px !important;
            height: 50px !important;
        }

        .segundo-cabecalho .orgao-principal {
            font-size: 9pt !important;
            margin-bottom: 0 !important;
        }

        .segundo-cabecalho .orgao-secundario {
            font-size: 7pt !important;
            margin-bottom: 0 !important;
        }

        .segundo-cabecalho .delegacia-info {
            font-size: 8pt !important;
            margin-top: 0 !important;
        }

        /* ESTILOS PARA REDUZIR ESPAÇAMENTOS */
        .compacto p {
            margin: 0 !important;
            padding: 0 !important;
        }

        .espacamento-reduzido {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }

        .ql-align-center {
            text-align: center !important;
        }
        .ql-align-justify {
            text-align: justify !important;
        }
        .ql-align-right {
            text-align: right !important;
        }
        .ql-align-left {
            text-align: left !important;
        }

        /* REMOVER BORDAS E ELEMENTOS INDESEJADOS */
        .ql-editor {
            border: none !important;
            padding: 0 !important;
        }
        .ql-clipboard {
            display: none !important;
        }

        /* FORÇAR UMA ÚNICA PÁGINA */
        .single-page {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="width: 50px; vertical-align: middle; text-align: center; padding: 0 1px;">
                    ' . ($image1Base64 ? '<img src="' . $image1Base64 . '" alt="Brasão PE" style="width: 50px; height: 50px; object-fit: contain; margin-top: -2px !important;">' : '') . '
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 0 1px;">
                    <div class="orgao-principal">
                        POLÍCIA CIVIL DE PERNAMBUCO - PCPE
                    </div>
                    <div class="orgao-secundario">
                        Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
                    </div>
                    <div class="orgao-secundario">
                        Gerência de Controle Operacional do Interior - 2 – GCOI - 2
                    </div>
                    <div class="orgao-secundario">
                        20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC
                    </div>
                    <div class="delegacia-info">
                        ' . ($dados['delegacia'] ?? 'NÃO INFORMADO') . ' – ' . ($dados['cidade'] ?? 'NÃO INFORMADO') . '
                    </div>
                </td>
                <td style="width: 50px; vertical-align: middle; text-align: center; padding: 0 1px;">
                    ' . ($image2Base64 ? '<img src="' . $image2Base64 . '" alt="Brasão PCPE" style="width: 50px; height: 50px; object-fit: contain; margin-top: -2px !important;">' : '') . '
                </td>
            </tr>
        </table>
    </div>

    
    <div class="content compacto single-page">' . $conteudoComSegundoCabecalho . '</div>
</body>
</html>';

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ✅✅✅ ABRIR EM NOVA ABA - CORRIGIDO
        return $dompdf->stream('intimacao-' . date('Y-m-d-His') . '.pdf', [
            'Attachment' => false
        ]);
    }

    // Função para inserir o segundo cabeçalho no local correto
    private function inserirSegundoCabecalho($conteudo, $segundoCabecalho)
    {
        $segundoCabecalhoComEspaco = '<div style="height:16px;"></div><br>' . $segundoCabecalho;
        $marcador = '<!-- SEGUNDO CABEÇALHO SERÁ INSERIDO AUTOMATICAMENTE AQUI PELO CONTROLLER -->';
        if (strpos($conteudo, $marcador) !== false) {
            return str_replace($marcador, $segundoCabecalhoComEspaco, $conteudo);
        }

        $padraoRecebedor = '/Recebedor\(a\):_{5,}<\/p>/';
        if (preg_match($padraoRecebedor, $conteudo, $matches, PREG_OFFSET_CAPTURE)) {
            $posicao = $matches[0][1] + strlen($matches[0][0]);
            return substr_replace($conteudo, $segundoCabecalhoComEspaco, $posicao, 0);
        }

        $posicaoSegundoMandado = strpos($conteudo, 'MANDADO DE INTIMAÇÃO', strpos($conteudo, 'MANDADO DE INTIMAÇÃO') + 1);
        if ($posicaoSegundoMandado !== false) {
            return substr_replace($conteudo, $segundoCabecalhoComEspaco, $posicaoSegundoMandado, 0);
        }

        return $conteudo . $segundoCabecalhoComEspaco;
    }

    // Função para converter imagem para base64
    private function imageToBase64($path)
    {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    // Função para limpar o conteúdo HTML - SOLUÇÃO DEFINITIVA
    private function cleanContent($content)
    {
        // 1. Remover BOM (Byte Order Mark) de todas as formas possíveis
        $boms = [
            pack('H*','EFBBBF'),     // UTF-8 BOM
            pack('H*','FFFE'),       // UTF-16 (little-endian) BOM
            pack('H*','FEFF'),       // UTF-16 (big-endian) BOM
            pack('H*','0000FEFF'),   // UTF-32 BOM
        ];
        foreach ($boms as $bom) {
            if (strpos($content, $bom) === 0) {
                $content = substr($content, strlen($bom));
                break;
            }
        }

        // 2. Remover qualquer caractere inválido antes da primeira tag
        $firstTagPosition = strpos($content, '<');
        if ($firstTagPosition > 0) {
            $content = substr($content, $firstTagPosition);
        }

        // 3. Remover caracteres invisíveis e inválidos no início (incluindo ?)
        $content = preg_replace('/^[\x00-\x1F\x7F]+/', '', $content); // Remove caracteres de controle
        $content = ltrim($content, "?\xEF\xBB\xBF"); // Remove ? e BOM UTF-8 explícito

        // 4. Remover espaços em branco e quebras de linha no início
        $content = preg_replace('/^[\s\r\n\t]+/', '', $content);

        // 5. Remover entidades HTML problemáticas no início
        $content = preg_replace('/^(&#63;|&quot;|<|>)+/', '', $content);

        // 6. Garantir que comece com uma tag HTML válida
        if (!preg_match('/^<[^>]+>/', $content)) {
            // Se não começar com uma tag, encontrar a primeira tag
            preg_match('/<[^>]+>/', $content, $matches, PREG_OFFSET_CAPTURE);
            if (!empty($matches)) {
                $content = substr($content, $matches[0][1]);
            }
        }

        // 7. Remover elementos indesejados do Quill
        $content = preg_replace('/<div class="ql-editor"([^>]*)>/', '', $content);
        $content = str_replace('</div>', '', $content);
        $content = preg_replace('/<span class="ql-cursor">[^<]*<\/span>/', '', $content);

        // 8. ✅ COMPACTAR CONTEÚDO - REDUZIR ESPAÇAMENTOS
        $content = preg_replace('/margin:\s*\d+px\s*\d+px\s*\d+px\s*\d+px\s*!important/', 'margin: 1px 0 !important', $content);
        $content = preg_replace('/margin-top:\s*\d+px/', 'margin-top: 1px', $content);
        $content = preg_replace('/margin-bottom:\s*\d+px/', 'margin-bottom: 1px', $content);

        // 9. ✅ REDUZIR FONTE DA SEÇÃO DE CRIME
        $content = preg_replace('/(<p[^>]*>Atenção: CRIME DE DESOBEDIÊNCIA\.<\/p>)/', '<div class="secao-crime">$1', $content);
        $content = preg_replace('/(<p[^>]*>Pena: Detenção de quinze dias a seis meses\.<\/p>)/', '$1</div>', $content);

        return trim($content);
    }
}
