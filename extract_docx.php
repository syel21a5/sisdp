<?php
$files = [
    'ExameEficienciaArmaComDisparo.docx',
    'ExameEficienciaArmaSemDisparo.docx',
    'PericiaPapiloscopicaEmVeiculo.docx'
];

$dir = "I:\\PHP\\sisdepol.syel.com.br\\Arquivos_localhost\\DocsModelo_WORD\\ModPericias\\";
$outDir = "I:\\PHP\\sisdepol.syel.com.br\\Arquivos_localhost\\DocsModelo_WORD\\ModPericias\\extracted\\";

if (!file_exists($outDir)) {
    mkdir($outDir, 0777, true);
}

foreach ($files as $file) {
    $zip = new ZipArchive;
    $path = $dir . $file;
    if ($zip->open($path) === TRUE) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($xml) {
            // Very basic text extraction just to see the template
            $text = strip_tags($xml, '<w:p><w:t><w:br>');
            $text = preg_replace('/<w:p[^>]*>/', "\n\n", $text);
            $text = preg_replace('/<w:br[^>]*>/', "\n", $text);
            $text = strip_tags($text);
            
            $outFile = $outDir . str_replace('.docx', '.txt', $file);
            file_put_contents($outFile, $text);
            echo "Extracted $file to .txt\n";
        }
    } else {
        echo "Failed to open $file\n";
    }
}
