<?php

namespace MakeTools\Commands;

class ModuleCommand {
    public static function run(array $args): void {
        $projectRoot = array_shift($args); // Pega o diretório raiz do projeto
        
        if (count($args) < 3) {
            echo "⚠️  Usage: make module <base_folder> <ModuleName> <mainMenu> <subMenu>\n";
            echo "Example: make module app Blog \"Blog Module\"\n";
            echo "Available base folders: app, application\n";
            exit(1);
        }

        $baseFolder = strtolower($args[0]);  // ex: app, api, webhook, cron, cli, application
        $moduleName = strtolower($args[1]);
        $subMenu = strtolower($args[2]);

        $moduleTitle = $args[2];
        $moduleClass = self::studlyCase($moduleName);

        $basePath = "{$projectRoot}/{$baseFolder}/modules/{$moduleName}";

        // Verifica se o módulo já existe
        if (is_dir($basePath)) {
            echo "❌ Error: Module '{$moduleName}' already exists in {$baseFolder}/modules/\n";
            exit(2);
        }

        // Cria estrutura de pastas
        $folders = [
            "{$basePath}/controllers",
            "{$basePath}/models",
            "{$basePath}/views",
            "{$basePath}/snippets/css",
            "{$basePath}/snippets/js",
        ];

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
                echo "✔️  Folder '{$folder}' created.\n";
            }
        }

        // Controller
        $controllerContent = self::generateController($moduleClass, $moduleName, $subMenu);
        file_put_contents("{$basePath}/controllers/{$moduleClass}.php", $controllerContent);

        // Model
        $modelContent = self::generateModel($moduleClass);
        file_put_contents("{$basePath}/models/{$moduleClass}_model.php", $modelContent);

        // View básica
        $viewContent = self::generateView($moduleTitle);
        file_put_contents("{$basePath}/views/{$subMenu}.php", $viewContent);

        // Arquivos CSS e JS vazios
        file_put_contents("{$basePath}/snippets/css/{$subMenu}.css", '');
        file_put_contents("{$basePath}/snippets/js/{$subMenu}.js", '');

        echo "\n✔️  Module '{$moduleTitle}' created successfully!\n";
        echo "   Path: {$basePath}\n";
        echo "   Controller: {$moduleClass}.php\n";
        echo "   Model: {$moduleClass}_model.php\n";
        echo "   View: {$subMenu}.php\n\n";
    }

    private static function generateController(string $className, string $moduleName, string $subMenu): string {
        $nameMenu = strtolower($className);
        return <<<PHP
            <?php

            if (!defined('BASEPATH')) exit('No direct script access allowed');

            class $className extends MY_Controller {

                public function __construct() {
                    parent::__construct();
                }

                public function $subMenu() {
                    \$this->data['fim']['js'][] = modulo_js('$moduleName.js');
                    \$this->data['inicio']['css'][] = modulo_css('$moduleName.css');
                    \$this->data['carregar']['interna'][] = '$subMenu';
                    \$this->data['menu_esquerda']['principal'] = '$nameMenu';
                    \$this->data['menu_esquerda']['secundario'] = '$nameMenu/{$subMenu}';
                    \$this->templates->padrao(\$this->data);
                }
            }
            PHP;
    }

    private static function generateModel(string $className): string {
        return <<<PHP
            <?php
    
            if (!defined('BASEPATH')) exit('No direct script access allowed');

            class {$className}_model extends CI_Model {

                public function __construct() {
                    parent::__construct();
                }

            }
            PHP;
    }

    private static function generateView(string $moduleTitle): string {
        return "Hello World";
    }

    private static function studlyCase(string $value): string {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }
}