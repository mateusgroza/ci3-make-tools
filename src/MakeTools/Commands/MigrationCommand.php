<?php

namespace MakeTools\Commands;

class MigrationCommand {
    public static function run(array $args): void {
        $projectRoot = array_shift($args); // Pega o diretório raiz do projeto
        
        if (count($args) < 2) {
            echo "⚠️  Attention: Usage:\n";
            echo "  make migration create tableName\n";
            echo "  make migration alter tableName [version]\n";
            exit(1);
        }

        $action = strtolower($args[0]);
        $table = strtolower($args[1]);
        $version = $args[2] ?? null;

        switch ($action) {
            case 'create':
                self::create($projectRoot, $table);
                break;

            case 'alter':
                self::alter($projectRoot, $table, $version);
                break;

            case 'insert':
                self::insert($projectRoot, $table);
                break;

            case 'update':
                self::update($projectRoot, $table);
                break;

            default:
                echo "❌  Error: Unknown action: $action. Available actions: create, alter, insert, update\n";
                exit(2);
        }
    }

    public static function create(string $projectRoot, string $table): void {
        $migrationDir = $projectRoot . '/migrations';
        $existingCreate = glob("{$migrationDir}/*_create_{$table}.php");

        if (!empty($existingCreate)) {
            echo "❌  Error: Migration to create table '{$table}' already exists.\n";
            echo "Existing file: " . basename($existingCreate[0]) . "\n";
            exit(3);
        }

        $timestamp = self::getAvailableTimestamp($migrationDir);

        $filename = "{$timestamp}_create_{$table}.php";
        $className = "Migration_Create_" . self::studlyCase($table);

        $content = self::getCreateContent($className, $table);

        self::writeMigrationFile($migrationDir, $filename, $content);
    }

    public static function alter(string $projectRoot, string $table, ?string $version = null): void {
        $migrationDir = $projectRoot . '/migrations';

        if ($version !== null) {
            $version = str_pad($version, 3, '0', STR_PAD_LEFT);
            $versionFile = glob("$migrationDir/*_alter_{$table}_{$version}.php");

            if (!empty($versionFile)) {
                echo "❌  Error: Migration alter version '{$version}' for table '{$table}' already exists.\n";
                echo "Existing file: " . basename($versionFile[0]) . "\n";
                exit(4);
            }
        } else {
            // Find last version and increment
            $pattern = "/^\d+_alter_{$table}_(\d{3})\.php$/";
            $lastVersion = 0;

            foreach (glob("$migrationDir/*_alter_{$table}_*.php") as $file) {
                if (preg_match($pattern, basename($file), $matches)) {
                    $num = (int)$matches[1];
                    if ($num > $lastVersion) {
                        $lastVersion = $num;
                    }
                }
            }
            $version = str_pad($lastVersion + 1, 3, '0', STR_PAD_LEFT);
        }

        $timestamp = self::getAvailableTimestamp($migrationDir);

        $filename = "{$timestamp}_alter_{$table}_{$version}.php";
        $className = "Migration_Alter_" . self::studlyCase($table) . "_{$version}";

        $content = self::getAlterContent($className, $table);

        self::writeMigrationFile($migrationDir, $filename, $content);
    }

    public static function insert(string $projectRoot, string $table): void {
        $migrationDir = $projectRoot . '/migrations';

        // Busca versões anteriores
        $pattern = "/^\d+_insert_{$table}_(\d{3})\.php$/";
        $existing = glob("{$migrationDir}/*_insert_{$table}_*.php");

        $lastVersion = 0;
        $previousContent = null;

        foreach ($existing as $file) {
            if (preg_match($pattern, basename($file), $matches)) {
                $version = (int)$matches[1];
                if ($version > $lastVersion) {
                    $lastVersion = $version;
                    $previousContent = file_get_contents($file);
                }
            }
        }

        $newVersion = str_pad($lastVersion + 1, 3, '0', STR_PAD_LEFT);
        $timestamp = self::getAvailableTimestamp($migrationDir);
        $filename = "{$timestamp}_insert_{$table}_{$newVersion}.php";
        $className = "Migration_Insert_" . self::studlyCase($table) . "_{$newVersion}";

        $content = self::getInsertContent($className, $table, $previousContent);

        self::writeMigrationFile($migrationDir, $filename, $content);
    }
    
    public static function update(string $projectRoot, string $table): void {
        $migrationDir = $projectRoot . '/migrations';

        // Busca versões anteriores
        $pattern = "/^\d+_update_{$table}_(\d{3})\.php$/";
        $existing = glob("{$migrationDir}/*_update_{$table}_*.php");

        $lastVersion = 0;
        $previousContent = null;

        foreach ($existing as $file) {
            if (preg_match($pattern, basename($file), $matches)) {
                $version = (int)$matches[1];
                if ($version > $lastVersion) {
                    $lastVersion = $version;
                    $previousContent = file_get_contents($file);
                }
            }
        }

        $newVersion = str_pad($lastVersion + 1, 3, '0', STR_PAD_LEFT);
        $timestamp = self::getAvailableTimestamp($migrationDir);
        $filename = "{$timestamp}_update_{$table}_{$newVersion}.php";
        $className = "Migration_Update_" . self::studlyCase($table) . "_{$newVersion}";

        $content = self::getUpdateContent($className, $table, $previousContent);

        self::writeMigrationFile($migrationDir, $filename, $content);
    }

    private static function getCreateContent(string $className, string $table): string {
        return <<<PHP
            <?php

            defined('BASEPATH') OR exit('No direct script access allowed');

            class {$className} extends CI_Migration {

                public function __construct() {
                    parent::__construct();
                }
    
                public function up() {
                    \$this->dbforge->add_field([
                        'id' => ['type' => 'SERIAL', 'null' => FALSE],
                    ]);

                    \$this->dbforge->add_key('id', TRUE);
                    \$this->dbforge->create_table('{$table}');

                    \$this->db->query("COMMENT ON TABLE {$table} IS 'Table Comment'");
                }
    
                public function down() {
                    \$this->dbforge->drop_table('{$table}');
                }
            }
            PHP;
    }

    private static function getAlterContent(string $className, string $table): string {
        return <<<PHP
            <?php

            defined('BASEPATH') OR exit('No direct script access allowed');

            class {$className} extends CI_Migration {
                
                public function __construct() {
                    parent::__construct();
                }
                
                public function up() {
                    \$this->dbforge->add_column('{$table}', [
                        'new_column' => [
                            'type' => 'VARCHAR',
                            'constraint' => 255,
                            'null' => TRUE,
                        ],
                    ]);

                    \$this->db->query("COMMENT ON COLUMN {$table}.new_column IS 'Column description'");
                }

                public function down() {
                    \$this->dbforge->drop_column('{$table}', 'new_column');
                }
            }
            PHP;
    }

    private static function getInsertContent(string $className, string $table, ?string $previousContent): string {
        return <<<PHP
            <?php

            defined('BASEPATH') OR exit('No direct script access allowed');

            class $className extends CI_Migration {

                public function __construct() {
                    parent::__construct();
                }

                public function up() {                
                    \$data = [];

                    \$this->db->insert('$table', \$data);
                }

                public function down() {
                }
            }
            PHP;
    }

    private static function getUpdateContent(string $className, string $table): string {
        return <<<PHP
            <?php

            defined('BASEPATH') OR exit('No direct script access allowed');

            class {$className} extends CI_Migration {

                public function __construct() {
                    parent::__construct();
                }

                public function up() {
                    \$set = [];

                    \$where = [];

                    \$this->db->update('{$table}', \$set, \$where);
                }

                public function down() {
                }
            }
            PHP;
    }
        
    private static function getAvailableTimestamp(string $migrationDir): string {
        $timestamp = date('YmdHi');
        while (true) {
            $exists = glob("{$migrationDir}/{$timestamp}_*.php");
            if (empty($exists)) {
                break; // timestamp is available
            }

            $dt = \DateTime::createFromFormat('YmdHi', $timestamp);
            $dt->modify('+1 minute');
            $timestamp = $dt->format('YmdHi');
        }

        return $timestamp;
    }

    private static function writeMigrationFile(string $migrationDir, string $filename, string $content): void {
        if (!is_dir($migrationDir)) {
            mkdir($migrationDir, 0755, true);
            echo "✔️  Migrations directory created at: {$migrationDir}\n";
        }

        $path = "{$migrationDir}/{$filename}";
        file_put_contents($path, $content);

        echo "✔️  Migration created: {$filename}\n";
        echo "Path: {$path}\n";
    }

    private static function studlyCase(string $value): string {
        return str_replace(' ', '', $value);
    }
}