<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;

class PdfService
{
    /**
     * Geração centralizada de PDF utilizando Python Playwright com fallback para DomPDF.
     * 
     * @param string $html O conteúdo HTML completo a ser renderizado.
     * @param string $filename O nome que o arquivo baixado terá.
     */
    public static function generatePdf($html, $filename = 'documento.pdf')
    {
        // NOVO: Delegar a geração do PDF para Python (Playwright) com qualidade superior
        $tempHtml = sys_get_temp_dir() . '/pdf_input_' . uniqid() . '.html';
        $tempPdf = sys_get_temp_dir() . '/pdf_output_' . uniqid() . '.pdf';
        file_put_contents($tempHtml, $html);
        
        $scriptPath = base_path('scripts/python/gerar_pdf.py');
        $pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';
        $command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempHtml) . " " . escapeshellarg($tempPdf) . " 2>&1";
        
        $output = shell_exec($command);
        $jsonStartPos = strpos($output, '{');
        if ($jsonStartPos !== false) {
             $outputJson = substr($output, $jsonStartPos);
             $result = json_decode($outputJson, true);
        } else {
             $result = null;
        }
        
        // Limpar HTML temporario apenas, PDF sera lido pela resposta
        @unlink($tempHtml);
        
        if ($result && isset($result['success']) && $result['success']) {
            // Retornar o novo PDF gerado em Python na aba
            return response()->file($result['path'], [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);
        } else {
            Log::error("Falha ao gerar PDF com Python (Usando Fallback DOMPDF): " . $output);
            
            // Fallback para o antigo DomPDF
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');
            $options->set('isPhpEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('defaultEncoding', 'UTF-8');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->stream($filename, [
                'Attachment' => false
            ]);
        }
    }

    /**
     * Função auxiliar para converter imagens em base64 e embedar no HTML para o gerador de PDF
     */
    public static function imageToBase64($path)
    {
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    /**
     * Limpa artefatos e lixos gerados pelo QuillJS ou Copy/Paste
     */
    public static function cleanContent($content)
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
        $content = preg_replace('/^(&#63;|&quot;|&lt;|&gt;)+/', '', $content);

        // 6. Garantir que comece com uma tag HTML válida
        if (!preg_match('/^<[^>]+>/', $content)) {
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
