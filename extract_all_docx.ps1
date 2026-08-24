$word = New-Object -ComObject Word.Application
$word.Visible = $false

$basePath = "I:\PHP\sisdepol.syel.com.br\Arquivos_localhost\DocsModelo_WORD\DOCSDIVERSOS\"
$files = Get-ChildItem -Path $basePath -Filter *.docx

foreach ($file in $files) {
    Write-Output "=== FILE: $($file.Name) ==="
    try {
        $doc = $word.Documents.Open($file.FullName)
        Write-Output $doc.Content.Text
        $doc.Close()
    } catch {
        Write-Output "Error opening $($file.Name): $_"
    }
}

$word.Quit()
