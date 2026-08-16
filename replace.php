<?php
$content = file_get_contents('I:\PHP\sisdepol.syel.com.br\resources\views\pecas\exames_preliminares\Auto_Avaliacao_Completa.blade.php');
$content = preg_replace('/\{\{\s*\\$dadosArray\[\''([^\']+)\''\]\s*\?\?\s*\''([^\']+)\''\s*\}\}/', '{!! !empty(\[''\'']) ? htmlspecialchars(\[''\'']) : ''<span style="background-color: #ffff00;">\</span>'' !!}', $content);
file_put_contents('I:\PHP\sisdepol.syel.com.br\resources\views\pecas\exames_preliminares\Auto_Avaliacao_Completa.blade.php', $content);

