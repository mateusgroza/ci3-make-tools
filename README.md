# CI3 Make Tools

A CodeIgniter 3 development toolkit that provides convenient command-line tools for generating boilerplate code and managing project structure.

## Installation

Install the package via Composer:

```bash
composer install mateusgroza/ci3-make-tools
```

## Configuration

Add the following script to your `composer.json` file to enable the `composer make` command:

```json
{
    "scripts": {
        "make": "@php vendor/mateusgroza/ci3-make-tools/bin/make"
    }
}
```

After adding the script, you can use `composer make` as the base command for all generation tasks.

## Available Commands

### Environment Variables Generator

Create environment variables in both `env.php` and `env.php.example` (or `env.example.php`) files:

```bash
composer make env {CONSTANT_NAME} {VALUE_FOR_ENV_ONLY} "{DESCRIPTION}"
```

**Example:**
```bash
composer make env DATABASE_HOST "localhost" "Database host configuration"
```

### Migration Generator

Generate database migration files with various operations:

```bash
composer make migration {command} {table_name} {version}
```

**Parameters:**
- `command`: The migration operation (`create`, `alter`, `insert`, `update`)
- `table_name`: Name of the database table
- `version`: Migration version (defaults to last_version + 1)

**Examples:**
```bash
composer make migration create users
composer make migration alter users 002
composer make migration insert products 003
composer make migration update orders
```

### Module Generator (HMVC Projects Only)

Generate modules for HMVC (Hierarchical Model-View-Controller) projects:

```bash
composer make module {application_folder} {module_name} {submenu_name}
```

**Parameters:**
- `application_folder`: The application directory name
- `module_name`: Name of the module to create
- `submenu_name`: Name of the submenu

**Example:**
```bash
composer make module admin user_management users
```

## Usage

All commands start with `composer make` followed by the specific generator and its parameters. Make sure you have properly configured the composer script as shown in the configuration section above.

## Requirements

- PHP 7.4 or higher
- CodeIgniter 3.x
- Composer

## Contributing

Feel free to contribute to this project by submitting issues or pull requests.

## License

This project is open-sourced software licensed under the MIT license.
