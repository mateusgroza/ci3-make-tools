<?php

namespace MakeTools\Commands;

class EnvCommand {
    public static function run(array $args): void {
        $projectRoot = array_shift($args); // Pega o diretório raiz do projeto
        
        if (count($args) < 1) {
            Echo "⚠️  Attention: Usage: make env NOME_CONSTANTE [valor] [comentário]\n";
            exit(1);
        }
        
        $constName = strtoupper($args[0]);
        $value = $args[1] ?? '';
        $comment = $args[2] ?? '';
        
        if (strtoupper($value) === 'NULL') {
            $value = '';
        }
        
        // Arquivos no projeto principal
        $files = [
            $projectRoot . '/env.php.example',
        ];

        $files = [];

        // Env local
        if (!file_exists($projectRoot . '/env.php')) {
            echo "⚠️  Attention: File not found: " . $projectRoot . '/env.php' . "\n";
            exit(2);
        }
        $files[] = $projectRoot . '/env.php';

        // Env example
        if (file_exists($projectRoot . '/env.example.php')) {
            $files[] = $projectRoot . '/env.example.php';
        } elseif (file_exists($projectRoot . '/env.php.example')) {
            $files[] = $projectRoot . '/env.php.example';
        } else {
            echo "⚠️  Attention: File not found: " . $projectRoot . '/env.php.example' . "\n";
            exit(3);
        }
        
        foreach ($files AS $file) {
            if (!file_exists($file)) {
                echo "❌  Error: File not found: $file\n";
                exit(4);
            }
        
            $content = file_get_contents($file);
            $pattern = '/(\$variaveis\s*=\s*\[)(.*?)(\];)/s';
        
            if (!preg_match($pattern, $content, $matches)) {
                echo "❌  Error: Cannot locate the array \$variaveis in file $file\n";
                exit(5);
            }
        
            $arrayBody = $matches[2];
        
            if (preg_match("/'{$constName}'\s*=>/", $arrayBody)) {
                echo "⚠️  Attention: Constant '{$constName}' already exists in file " . basename($file) . "\n";
                exit(6);
            }
        
            $valToInsert = ($file === $projectRoot . '/application/config/env.php') ? $value : '';
            $valToInsert = addslashes($valToInsert);
        
            $insertion = "\n\n";
            if ($comment !== '') {
                $insertion .= "    // {$comment}\n";
            }
            $insertion .= "    '{$constName}' => '{$valToInsert}',";
        
            $newArrayBody = rtrim($arrayBody) . $insertion . "\n";
            $newContent = $matches[1] . $newArrayBody . $matches[3];
            $content = preg_replace($pattern, $newContent, $content);
        
            file_put_contents($file, $content);
            echo "✔️  Constant '{$constName}' added to file " . basename($file) . "\n";
        }
    }
}