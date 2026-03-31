<?php
namespace App\Http\Controllers\Documentos\PecasController;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class GerarPdf_ConstatacaoDanosIndireta_Controller extends Controller
{
    // ✅ MÉTODO PARA PORTARIA
    public function gerarPdfConstatacaoIndiretaPortaria(Request $request)
    {
        return $this->processarPdf($request, 'portaria');
    }

    // ✅ MÉTODO PARA TERMO
    public function gerarPdfConstatacaoIndiretaTermo(Request $request)
    {
        return $this->processarPdf($request, 'termo');
    }

    // ✅ MÉTODO PRIVADO UNIFICADO
    private function processarPdf(Request $request, $tipo)
    {
        // ✅ CORREÇÃO: EXTRAIR DADOS DE MANEIRA CONSISTENTE
        $dados = $request->all();

        // ✅ CORREÇÃO CRÍTICA: Se vier 'dados' como JSON, extrair e mesclar
        if (isset($dados['dados']) && is_string($dados['dados'])) {
            $dadosJson = json_decode($dados['dados'], true);
            if (is_array($dadosJson) && json_last_error() === JSON_ERROR_NONE) {
                $dados = array_merge($dados, $dadosJson);
                // Remover o campo 'dados' para não confundir
                unset($dados['dados']);

                // Atualizar o request para validação
                $request->merge($dados);
            }
        }

        // ✅ DEBUG: Verificar dados recebidos
        Log::info('Dados recebidos para PDF Constatação Indireta:', $dados);

        // Validar os dados recebidos
        $request->validate([
            'conteudo' => 'required',
            'delegacia' => 'sometimes|string',
            'cidade' => 'sometimes|string',
            'tipo_documento' => 'sometimes|string'
        ]);

        // ✅ ATUALIZAR $dados APÓS VALIDAÇÃO (pode ter mudado)
        $dados = $request->all();

        // ✅ DEFINIR TIPO CORRETO (do input ou do parâmetro)
        $tipoDocumento = $dados['tipo_documento'] ?? $tipo;

        // ✅ REMOVER BOM E CARACTERES INVÁLIDOS
        if (isset($dados['conteudo'])) {
            $dados['conteudo'] = $this->cleanContent($dados['conteudo']);
        }

        // ✅ CORREÇÃO: GARANTIR QUE CAMPOS EXISTAM
        $dados['delegacia'] = $dados['delegacia'] ?? 'NÃO INFORMADO';
        $dados['cidade'] = $dados['cidade'] ?? 'NÃO INFORMADO';
        $dados['delegado'] = $dados['delegado'] ?? 'NÃO INFORMADO';
        $dados['escrivao'] = $dados['escrivao'] ?? 'NÃO INFORMADO';
        $dados['policial_1'] = $dados['policial_1'] ?? 'NÃO INFORMADO';
        $dados['policial_2'] = $dados['policial_2'] ?? 'NÃO INFORMADO';
        $dados['data_comp'] = $dados['data_comp'] ?? 'NÃO INFORMADO';
        $dados['data_ext'] = $dados['data_ext'] ?? 'NÃO INFORMADO';

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

        // ✅ ADICIONAR NÚMERO DO BOE NO RODAPÉ
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
        /* MARGEM PADRÃO PARA TODAS AS PÁGINAS */
        @page {
            margin: 115px 30px 100px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12.5pt !important;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            text-align: center;
            margin-bottom: 5px;
            height: 90px;
        }
        .content {
            margin-top: 5px;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        .page-break {
            page-break-before: always;
            padding-top: 40px !important;
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
            font-size: 12.5pt;
            margin-bottom: 2px;
            line-height: 1.1;
        }
        .orgao-secundario {
            font-size: 10.5pt;
            margin-bottom: 1px;
            line-height: 1.1;
        }
        .delegacia-info {
            margin-top: 3px;
            font-weight: bold;
            font-size: 11.5pt;
            line-height: 1.1;
        }
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
        .ql-size-10pt, .ql-size-10pt p, .ql-size-10pt span, .ql-size-10pt div, .ql-size-10pt strong { font-size: 10pt !important; }
        .ql-size-11pt, .ql-size-11pt p, .ql-size-11pt span, .ql-size-11pt div, .ql-size-11pt strong { font-size: 11pt !important; }
        .ql-size-12pt, .ql-size-12pt p, .ql-size-12pt span, .ql-size-12pt div, .ql-size-12pt strong { font-size: 12pt !important; }
        .ql-size-14pt, .ql-size-14pt p, .ql-size-14pt span, .ql-size-14pt div, .ql-size-14pt strong { font-size: 14pt !important; }
        .ql-size-16pt, .ql-size-16pt p, .ql-size-16pt span, .ql-size-16pt div, .ql-size-16pt strong { font-size: 16pt !important; }
        .ql-size-18pt, .ql-size-18pt p, .ql-size-18pt span, .ql-size-18pt div, .ql-size-18pt strong { font-size: 18pt !important; }
        .ql-size-20pt, .ql-size-20pt p, .ql-size-20pt span, .ql-size-20pt div, .ql-size-20pt strong { font-size: 20pt !important; }
        .ql-align-center { text-align: center !important; }
        .ql-align-justify { text-align: justify !important; }
        .ql-align-right { text-align: right !important; }
        .ql-align-left { text-align: left !important; }
        .ql-editor { border: none !important; padding: 0 !important; }
        .ql-clipboard { display: none !important; }
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
                        ' . htmlspecialchars($dados['delegacia']) . ' – ' . htmlspecialchars($dados['cidade']) . '
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
    <div class="content">' . ($dados['conteudo'] ?? '') . '</div>
</body>
</html>';

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ✅✅✅ DEFINIR NOME DO ARQUIVO
        $nomeArquivo = $tipoDocumento === 'portaria'
            ? 'portaria-constatacao-indireta-' . date('Y-m-d-His') . '.pdf'
            : 'termo-constatacao-indireta-' . date('Y-m-d-His') . '.pdf';

        return $dompdf->stream($nomeArquivo, [
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

    // Função para limpar o conteúdo HTML
    private function cleanContent($content)
    {
        $boms = [
            pack('H*','EFBBBF'),
            pack('H*','FFFE'),
            pack('H*','FEFF'),
            pack('H*','0000FEFF'),
        ];
        foreach ($boms as $bom) {
            if (strpos($content, $bom) === 0) {
                $content = substr($content, strlen($bom));
                break;
            }
        }
        $firstTagPosition = strpos($content, '<');
        if ($firstTagPosition > 0) {
            $content = substr($content, $firstTagPosition);
        }
        $content = preg_replace('/^[\x00-\x1F\x7F]+/', '', $content);
        $content = ltrim($content, "?\xEF\xBB\xBF");
        $content = preg_replace('/^[\s\r\n\t]+/', '', $content);
        $content = preg_replace('/^(&#63;|&quot;|<|>)+/', '', $content);
        if (!preg_match('/^<[^>]+>/', $content)) {
            preg_match('/<[^>]+>/', $content, $matches, PREG_OFFSET_CAPTURE);
            if (!empty($matches)) {
                $content = substr($content, $matches[0][1]);
            }
        }
        $content = preg_replace('/<div class="ql-editor"([^>]*)>/', '', $content);
        $content = str_replace('</div>', '', $content);
        $content = preg_replace('/<span class="ql-cursor">[^<]*<\/span>/', '', $content);

        return trim($content);
    }
}
