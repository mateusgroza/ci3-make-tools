<?php

namespace MakeTools;

use MakeTools\Commands\EnvCommand;
use MakeTools\Commands\MigrationCommand;
use MakeTools\Commands\ModuleCommand;

class Generator {
    public static function run(array $args): void {
        if (count($args) < 2) {
            echo "Usage: make <command> [arguments]\n";
            return;
        }

        if (php_sapi_name() !== 'cli') {
            exit("This script can only be run from the command line.\n");
        }

        // Obtém o diretório raiz do projeto que está usando o pacote
        $projectRoot = getcwd(); // Ou: dirname(__DIR__, 3) para subir até o projeto principal
        
        $command = strtolower($args[1]);
        $args = array_slice($args, 2);

        // Passa o $projectRoot como primeiro argumento para os comandos
        array_unshift($args, $projectRoot);

        switch ($command) {
            case 'env':
                EnvCommand::run($args);
                break;

            case 'migration':
                MigrationCommand::run($args);
                break;

            case 'module':
                ModuleCommand::run($args);
                break;

            default:
                echo "Unknown command: $command\n";
                echo "Available commands: env, migration, module\n";
                break;
        }
    }
}