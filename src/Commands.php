<?php
namespace OffbeatCLI;

use WP_CLI;
use WP_CLI_Command;

class Commands extends WP_CLI_Command
{

    /**
     * Create fresh offbeat theme
     *
     * @subcommand init-theme
     */
    public function initTheme($args, $assocArgs)
    {
        if (!isset($args[0])) {
            WP_CLI::error('Define the slug for your theme');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-0_-]+$/', $args[0])) {
            WP_CLI::error('Theme slug is not valid, only use alphanumeric characters and _ (underscore) and - (dash) are allowed.');
            exit;
        }

        $themeSlug         = $args[0];
        $newThemeDirectory = get_theme_root() . "/{$themeSlug}";

        if (!isset($assocArgs['force']) && is_dir($newThemeDirectory)) {
            WP_CLI::error("Folder ({$newThemeDirectory}) already exists");
            exit;
        }

        if (isset($assocArgs['force'])) {
            exec("rm -rf {$newThemeDirectory}");
        }

        mkdir($newThemeDirectory);

        $version   = (isset($assocArgs['version'])) ? $assocArgs['version'] : 'master';
        $githubUrl = 'https://github.com/offbeatwp/offbeatwp.git';

        exec("git clone {$githubUrl} {$newThemeDirectory} -b {$version}");
        exec("rm -rf {$newThemeDirectory}/.git");
        exec("composer install -d {$newThemeDirectory}");

        WP_CLI::log('Activate Theme');
        switch_theme($themeSlug);

        WP_CLI::success('Done');
    }

    /**
     * Make service
     *
     * @subcommand make-service
     */
    public function makeService($args, $assocArgs)
    {
        $name = isset($args[0]) ? $args[0] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your service");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        $name      = ucfirst($name);
        $directory = get_template_directory() . '/app/Services/';

        if (!is_dir($directory)) {
            WP_CLI::error("Path does not exists ({$path})");
            exit;
        }

        $classname = $name . 'Service';
        $path      = $directory . $classname . '.php';

        if (file_exists($path)) {
            WP_CLI::error("Service already exists");
            exit;
        }

        $serviceFile = fopen($path, 'w');

        $namespace = 'App\Services';

        $serviceFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Service.txt');
        $serviceFileContent = str_replace('{{ namespace }}', $namespace, $serviceFileContent);
        $serviceFileContent = str_replace('{{ classname }}', $classname, $serviceFileContent);

        fwrite($serviceFile, $serviceFileContent);
        fclose($serviceFile);

        WP_CLI::success("Service created in {$path}\n");
        WP_CLI::log("Add this to config/services.php:\n");
        WP_CLI::log("\\{$namespace}\\{$classname}::class,\n");
    }

    /**
     * Make post model
     *
     * @subcommand make-postmodel
     */
    public function makePostModel($args, $assocArgs)
    {
        $name     = isset($args[0]) ? $args[0] : null;
        $posttype = isset($assocArgs['post_type']) ? $assocArgs['post_type'] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your service");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        if (empty($posttype)) {
            WP_CLI::error("Please define a posttype with --posttype=\"\"");
            exit;
        }

        $name      = ucfirst($name);
        $directory = get_template_directory() . '/app/Models/';

        $namespace = "App\Models";
        $classname = $name . 'Model';

        $path = $directory . $classname . '.php';

        if (isset($assocArgs['module'])) {
            $module = $assocArgs['module'];

            $modulePath = get_template_directory() . '/modules/' . $module . '/';

            if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^[0-9]/', $module) || !is_dir($modulePath)) {
                WP_CLI::error("Module does not exists");
                exit;
            }

            $directory = $modulePath . '/Models/';

            if (!is_dir($directory)) {
                mkdir($directory);
            }

            $namespace = "Modules\\{$module}\Models";
            $path      = $directory . $classname . '.php';
        }

        if (!is_dir($directory)) {
            WP_CLI::error("Path does not exists ({$path})");
            exit;
        }

        if (file_exists($path)) {
            WP_CLI::error("Model already exists");
            exit;
        }

        $modelFile = fopen($path, 'w');

        $modelFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/PostModel.txt');
        $modelFileContent = str_replace('{{ namespace }}', $namespace, $modelFileContent);
        $modelFileContent = str_replace('{{ classname }}', $classname, $modelFileContent);
        $modelFileContent = str_replace('{{ post_type }}', $posttype, $modelFileContent);

        fwrite($modelFile, $modelFileContent);
        fclose($modelFile);

        WP_CLI::success("Model created in {$path}\n");
    }


    /**
     * Make term model
     *
     * @subcommand make-termmodel
     */
    public function makeTermModel($args, $assocArgs)
    {
        $name     = isset($args[0]) ? $args[0] : null;
        $taxonomy = isset($assocArgs['taxonomy']) ? $assocArgs['taxonomy'] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your service");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        if (empty($taxonomy)) {
            WP_CLI::error("Please define a taxonomy with --taxonomy=\"\"");
            exit;
        }

        $name      = ucfirst($name);
        $directory = get_template_directory() . '/app/Models/';

        $namespace = "App\Models";
        $classname = $name . 'Model';

        $path = $directory . $classname . '.php';

        if (isset($assocArgs['module'])) {
            $module = $assocArgs['module'];

            $modulePath = get_template_directory() . '/modules/' . $module . '/';

            if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^[0-9]/', $module) || !is_dir($modulePath)) {
                WP_CLI::error("Module does not exists");
                exit;
            }

            $directory = $modulePath . '/Models/';

            if (!is_dir($directory)) {
                mkdir($directory);
            }

            $namespace = "Modules\\{$module}\Models";
            $path      = $directory . $classname . '.php';
        }

        if (!is_dir($directory)) {
            WP_CLI::error("Path does not exists ({$path})");
            exit;
        }

        if (file_exists($path)) {
            WP_CLI::error("Model already exists");
            exit;
        }

        $modelFile = fopen($path, 'w');

        $modelFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/TermModel.txt');
        $modelFileContent = str_replace('{{ namespace }}', $namespace, $modelFileContent);
        $modelFileContent = str_replace('{{ classname }}', $classname, $modelFileContent);
        $modelFileContent = str_replace('{{ taxonomy }}', $taxonomy, $modelFileContent);

        fwrite($modelFile, $modelFileContent);
        fclose($modelFile);

        WP_CLI::success("Model created in {$path}\n");
    }

    /**
     * Make controller
     *
     * @subcommand make-controller
     */
    public function makeController($args, $assocArgs)
    {
        $name     = isset($args[0]) ? $args[0] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your service");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        $name      = ucfirst($name);
        $directory = get_template_directory() . '/app/Controllers/';

        $namespace = "App\Controllers";
        $classname = $name . 'Controller';

        $path = $directory . $classname . '.php';

        if (isset($assocArgs['module'])) {
            $module = $assocArgs['module'];

            $modulePath = get_template_directory() . '/modules/' . $module . '/';

            if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^[0-9]/', $module) || !is_dir($modulePath)) {
                WP_CLI::error("Module does not exists");
                exit;
            }

            $directory = $modulePath . '/Controllers/';

            if (!is_dir($directory)) {
                mkdir($directory);
            }

            $namespace = "Modules\\{$module}\Controllers";
            $path      = $directory . $classname . '.php';
        }

        if (!is_dir($directory)) {
            WP_CLI::error("Path does not exists ({$path})");
            exit;
        }

        if (file_exists($path)) {
            WP_CLI::error("Controller already exists");
            exit;
        }

        $modelFile = fopen($path, 'w');

        $modelFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Controller.txt');
        $modelFileContent = str_replace('{{ namespace }}', $namespace, $modelFileContent);
        $modelFileContent = str_replace('{{ classname }}', $classname, $modelFileContent);

        fwrite($modelFile, $modelFileContent);
        fclose($modelFile);

        WP_CLI::success("Controller created in {$path}\n");
    }

    /**
     * Make module
     *
     * @subcommand make-module
     */
    public function makeModule($args, $assocArgs)
    {
        $name     = isset($args[0]) ? $args[0] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your module");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        $name      = ucfirst($name);
        $directory = get_template_directory() . '/modules/' . $name;

        if (is_dir($directory)) {
            WP_CLI::error("Module already exists ({$path})");
            exit;
        }

        mkdir($directory);
        mkdir($directory . "/Controllers");
        mkdir($directory . "/Models");
        mkdir($directory . "/views");

        $namespace = "Modules\\{$name}";
        $classname = $name;

        $path = $directory . "/" . $classname . '.php';

        $moduleFile = fopen($path, 'w');

        $moduleFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Module.txt');
        $moduleFileContent = str_replace('{{ namespace }}', $namespace, $moduleFileContent);
        $moduleFileContent = str_replace('{{ classname }}', $classname, $moduleFileContent);

        fwrite($moduleFile, $moduleFileContent);
        fclose($moduleFile);

        WP_CLI::success("Module created in {$path}\n");
        WP_CLI::log("Add this module to config/services.php:\n");
        WP_CLI::log("\\{$namespace}\\{$classname}::class,\n");
    }

    /**
     * Make component
     *
     * @subcommand make-component
     */
    public function makeComponent($args, $assocArgs)
    {
        $name     = isset($args[0]) ? $args[0] : null;
        $supports = isset($assocArgs['supports']) ? $assocArgs['supports'] : null;

        if (empty($name)) {
            WP_CLI::error("Please define a name for your component");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9 ]/', $name)) {
            WP_CLI::error("Name contains not supported characters");
            exit;
        }

        if (preg_match('/^[0-9]/', $name)) {
            WP_CLI::error("Name can not start with a number");
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9 ,]/', $supports)) {
            WP_CLI::error("Supports contains not valid charaters");
            exit;
        }

        $name      = ucfirst($name);
        $classname = implode('', array_map('ucfirst', explode(' ', $name)));

        $directory = get_template_directory() . "/components/{$classname}/";

        $namespace = "Components\\{$classname}";

        $path = $directory . $classname . '.php';

        if (isset($assocArgs['module'])) {
            $module = $assocArgs['module'];

            $modulePath = get_template_directory() . '/modules/' . $module . '/';

            if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^[0-9]/', $module) || !is_dir($modulePath)) {
                WP_CLI::error("Module does not exists");
                exit;
            }

            $directory = $modulePath . 'Components/';

            if (!is_dir($directory)) {
                mkdir($directory);
            }

            $directory = $directory . "{$classname}/";

            $namespace = "Modules\\{$module}\Components\\{$classname}";
            $path      = $directory . $classname . '.php';
        }

        if (!is_dir($directory)) {
            mkdir($directory);
        }

        if (file_exists($path)) {
            WP_CLI::error("Component already exists");
            exit;
        }

        $componentFile = fopen($path, 'w');

        $componentFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Component.txt');
        $componentFileContent = str_replace('{{ namespace }}', $namespace, $componentFileContent);
        $componentFileContent = str_replace('{{ classname }}', $classname, $componentFileContent);
        $componentFileContent = str_replace('{{ name }}', $name, $componentFileContent);

        $slug = strtolower(str_replace(' ', '-', $name));
        $componentFileContent = str_replace('{{ slug }}', $slug, $componentFileContent);

        if (!empty($supports)) {
            $supports = explode(',', $supports);
            $supports = array_map('trim', $supports);
            $supports = array_filter($supports);

            if (!empty($supports)) {
                $supports = '\'' . explode('\',\'', $supports) . '\'';
            }
        }

        if (empty($supports)) {
            $supports = '';
        }

        $componentFileContent = str_replace('{{ supports }}', $supports, $componentFileContent);

        fwrite($componentFile, $componentFileContent);
        fclose($componentFile);

        $viewsDirectory = $directory . 'views/';

        mkdir($viewsDirectory);
        $componentViewFile = fopen($viewsDirectory . 'component.twig', 'w');

        fwrite($componentViewFile, "Component: {$name}");

        fclose($componentViewFile);

        WP_CLI::success("Component created in {$path}\n");
    }
}
