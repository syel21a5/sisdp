<?php
namespace App\Http\Controllers\Documentos\OitivasController;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Http\Controllers\Controller;

class GerarInterrogatorioController extends Controller
{
    public function gerarPdfInterrogatorio(Request $request)
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

        // ✅ ADICIONAR NÚMERO DO BOE NO RODAPÉ - CORRIGIDO
        $textoRodape = '
        <div style="line-height: 1.4;">
            RUA VALDEVINO JOSÉ PRAXEDES, nº S/N, MANOELA VALADARES, AFOGADOS DA INGAZEIRA-PE<br>
            Telefone: (87) 3838-8777 | (87) 3838-8778 | (87) 3838-8780
        </div>';

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* MARGEM PADRÃO PARA TODAS AS PÁGINAS - AJUSTADA */
        @page {
            margin: 125px 30px 100px 30px; /* AUMENTADO: Margem superior de 105px para 125px */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12.5pt !important; /* ALTERADO: De 12pt para 12.5pt */
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .header {
            position: fixed;
            top: -100px; /* AJUSTADO: De -95px para -115px (empurra cabeçalho para baixo) */
            left: 0;
            right: 0;
            text-align: center;
            margin-bottom: 5px;
            height: 90px; /* AUMENTADO: Altura do cabeçalho */
        }
        .content {
            margin-top: 15px;
            padding: 0 20px;
            /* Garantir que o conteúdo não sobreponha o cabeçalho */
            position: relative;
            z-index: 1;
        }
        /* ESTILO ADICIONADO: Controlar espaçamento em quebras de página */
        .page-break {
            page-break-before: always;
            padding-top: 40px !important; /* Espaço extra no início de novas páginas */
        }
        p {
            margin: 0.4em 0;
            padding: 0;
            line-height: 1.6;
            text-align: justify;
        }
        .footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .orgao-principal {
            font-weight: bold;
            font-size: 12.5pt; /* ALTERADO: De 12pt para 12.5pt */
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .orgao-secundario {
            font-size: 10.5pt; /* ALTERADO: De 10pt para 10.5pt */
            margin-bottom: 1px;
            line-height: 1.1;
        }
        .delegacia-info {
            margin-top: 3px;
            font-weight: bold;
            font-size: 11.5pt; /* ALTERADO: De 11pt para 11.5pt */
            line-height: 1.1;
        }
        /* COMPACTAR MAIS O CABEÇALHO */
        .header table {
            margin-bottom: 0 !important;
        }
        .header td {
            padding: 0 5px !important;
        }
        .header img {
            width: 85px !important;
            height: 85px !important;
            margin-top: -3px !important;
            object-fit: contain;
        }
        /* Estilos para preservar as fontes personalizadas na impressão */
        .ql-font-arial, .ql-font-arial p, .ql-font-arial span, .ql-font-arial div, .ql-font-arial strong {
            font-family: Arial, sans-serif !important;
        }
        .ql-font-times-new-roman, .ql-font-times-new-roman p, .ql-font-times-new-roman span, .ql-font-times-new-roman div, .ql-font-times-new-roman strong {
            font-family: "Times New Roman", Times, serif !important;
        }
        .ql-font-courier-new, .ql-font-courier-new p, .ql-font-courier-new span, .ql-font-courier-new div, .ql-font-courier-new strong {
            font-family: "Courier New", monospace !important;
        }
        .ql-font-georgia, .ql-font-georgia p, .ql-font-georgia span, .ql-font-georgia div, .ql-font-georgia strong {
            font-family: Georgia, serif !important;
        }
        .ql-font-verdana, .ql-font-verdana p, .ql-font-verdana span, .ql-font-verdana div, .ql-font-verdana strong {
            font-family: Verdana, sans-serif !important;
        }
        .ql-font-segoe-ui, .ql-font-segoe-ui p, .ql-font-segoe-ui span, .ql-font-segoe-ui div, .ql-font-segoe-ui strong {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif !important;
        }
        /* Estilos para preservar os tamanhos personalizados na impressão */
        .ql-size-10pt, .ql-size-10pt p, .ql-size-10pt span, .ql-size-10pt div, .ql-size-10pt strong {
            font-size: 10pt !important;
        }
        .ql-size-11pt, .ql-size-11pt p, .ql-size-11pt span, .ql-size-11pt div, .ql-size-11pt strong {
            font-size: 11pt !important;
        }
        .ql-size-12pt, .ql-size-12pt p, .ql-size-12pt span, .ql-size-12pt div, .ql-size-12pt strong {
            font-size: 12pt !important;
        }
        .ql-size-14pt, .ql-size-14pt p, .ql-size-14pt span, .ql-size-14pt div, .ql-size-14pt strong {
            font-size: 14pt !important;
        }
        .ql-size-16pt, .ql-size-16pt p, .ql-size-16pt span, .ql-size-16pt div, .ql-size-16pt strong {
            font-size: 16pt !important;
        }
        .ql-size-18pt, .ql-size-18pt p, .ql-size-18pt span, .ql-size-18pt div, .ql-size-18pt strong {
            font-size: 18pt !important;
        }
        .ql-size-20pt, .ql-size-20pt p, .ql-size-20pt span, .ql-size-20pt div, .ql-size-20pt strong {
            font-size: 20pt !important;
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
        /* Remover bordas e elementos indesejados */
        .ql-editor {
            border: none !important;
            padding: 0 !important;
        }
        .ql-clipboard {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0 5px;">
                    ' . ($image1Base64 ? '<img src="' . $image1Base64 . '" alt="Brasão PE" style="width: 85px; height: 85px; object-fit: contain; margin-top: -10px !important;">' : '') . '
                </td>
                <td style="vertical-align: middle; text-align: center; padding: 0 5px;">
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
                <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0 5px;">
                    ' . ($image2Base64 ? '<img src="' . $image2Base64 . '" alt="Brasão PCPE" style="width: 85px; height: 85px; object-fit: contain; margin-top: -10px !important;">' : '') . '
                </td>
            </tr>
        </table>
    </div>
    <div class="footer">
        ' . $textoRodape . '
    </div>
    <div class="content">' . $dados['conteudo'] . '</div>
</body>
</html>';

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ✅✅✅ ABRIR EM NOVA ABA - CORRIGIDO
        return $dompdf->stream('interrogatorio-' . date('Y-m-d-His') . '.pdf', [
            'Attachment' => false
        ]);
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

        return trim($content);
    }
}
