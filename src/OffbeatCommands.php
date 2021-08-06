<?php

namespace OffbeatCLI;

use RuntimeException;
use WP_CLI;
use WP_CLI\ExitException;
use WP_CLI_Command;

class OffbeatCommands extends WP_CLI_Command
{
	private const ARG_FORCE = 'force';
	public const ARG_POST = 'post_type';
	public const ARG_TAX = 'taxonomy';
	/**
	 * Create fresh offbeat theme
	 *
	 * @subcommand init-theme
	 * @throws ExitException
	 */
	public function initTheme(?array $args, ?array $assocArgs): void
	{
		if (!isset($args[0])) {
			WP_CLI::error('Define the slug for your theme');
			exit;
		}

		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $args[0])) {
			WP_CLI::error('Theme slug is not valid, only use alphanumeric characters and _ (underscore) and - (dash) are allowed.');
			exit;
		}

		$themeSlug = $args[0];
		$newThemeDirectory = get_theme_root() . "/{$themeSlug}";

		if (!isset($assocArgs[self::ARG_FORCE]) && is_dir($newThemeDirectory)) {
			WP_CLI::error("Folder ({$newThemeDirectory}) already exists");
			exit;
		}

		if (isset($assocArgs[self::ARG_FORCE])) {
			exec("rm -rf {$newThemeDirectory}");
		}

		if (!mkdir($newThemeDirectory) && !is_dir($newThemeDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $newThemeDirectory));
		}

		$version = $assocArgs['version'] ?? 'master';
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
	 * @throws ExitException
	 */
	public function makeService($args): void
	{
		$name = OffbeatCommandHelper::filterName($args[0]);
		$directory = get_template_directory() . '/app/Services/';

		if (!is_dir($directory)) {
			WP_CLI::error("Path does not exists ({$directory})");
			exit;
		}

		$classname = $name . 'Service';
		$path = $directory . $classname . '.php';

		if (file_exists($path)) {
			WP_CLI::error("Service already exists");
			exit;
		}

		$serviceFile = fopen($path, 'wb');

		$namespace = 'App\Services';

		$serviceFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Service.txt');
		$serviceFileContent = str_replace(['{{ namespace }}', '{{ classname }}'], [$namespace, $classname], $serviceFileContent);

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
	 * @throws ExitException
	 */
	public function makePostModel(?array $args, ?array $assocArgs): void
	{
		OffbeatCommandHelper::makeCustomType($args, $assocArgs, self::ARG_POST);
	}

	/**
	 * Make term model
	 *
	 * @subcommand make-termmodel
	 * @throws ExitException
	 */
	public function makeTermModel(?array $args, ?array $assocArgs): void
	{
		OffbeatCommandHelper::makeCustomType($args, $assocArgs, self::ARG_TAX);
	}

	/**
	 * Make controller
	 *
	 * @subcommand make-controller
	 * @throws ExitException
	 */
	public function makeController(?array $args, ?array $assocArgs): void
	{
		$name = OffbeatCommandHelper::filterName($args[0]);
		$directory = get_template_directory() . '/app/Controllers/';

		$namespace = "App\Controllers";
		$classname = $name . 'Controller';

		$path = $directory . $classname . '.php';

		if (isset($assocArgs['module'])) {
			$module = $assocArgs['module'];

			$modulePath = get_template_directory() . '/modules/' . $module . '/';

			if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^\d/', $module) || !is_dir($modulePath)) {
				WP_CLI::error("Module does not exists");
				exit;
			}

			$directory = $modulePath . '/Controllers/';

			if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
			}

			$namespace = "Modules\\{$module}\Controllers";
			$path = $directory . $classname . '.php';
		}

		if (!is_dir($directory)) {
			WP_CLI::error("Path does not exists ({$path})");
			exit;
		}

		if (file_exists($path)) {
			WP_CLI::error("Controller already exists");
			exit;
		}

		$modelFile = fopen($path, 'wb');

		$modelFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Controller.txt');
		$modelFileContent = str_replace(['{{ namespace }}', '{{ classname }}'], [$namespace, $classname], $modelFileContent);

		fwrite($modelFile, $modelFileContent);
		fclose($modelFile);

		WP_CLI::success("Controller created in {$path}\n");
	}

	/**
	 * Make module
	 *
	 * @subcommand make-module
	 * @throws ExitException
	 */
	public function makeModule($args): void
	{
		$name = OffbeatCommandHelper::filterName($args[0]);
		$directory = get_template_directory() . '/modules/' . $name;

		if (is_dir($directory)) {
			WP_CLI::error("Module already exists ({$directory})");
			exit;
		}

		if (!mkdir($directory) && !is_dir($directory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
		}
		if (!mkdir($concurrentDirectory = $directory . "/Controllers") && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		if (!mkdir($concurrentDirectory = $directory . "/Models") && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		if (!mkdir($concurrentDirectory = $directory . "/views") && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}

		$namespace = "Modules\\{$name}";
		$classname = $name;

		$path = $directory . "/" . $classname . '.php';

		$moduleFile = fopen($path, 'wb');

		$moduleFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Module.txt');
		$moduleFileContent = str_replace(['{{ namespace }}', '{{ classname }}'], [$namespace, $classname], $moduleFileContent);

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
	 * @throws ExitException
	 */
	public function makeComponent(?array $args, ?array $assocArgs): void
	{
		$name = OffbeatCommandHelper::filterName($args[0]);

		$supports = $assocArgs['supports'] ?? null;
		if (preg_match('/[^a-zA-Z0-9 ,]/', $supports)) {
			WP_CLI::error("Supports contains not valid charaters");
			exit;
		}

		$classname = implode('', array_map('ucfirst', explode(' ', $name)));

		$directory = get_template_directory() . "/components/{$classname}/";

		$namespace = "Components\\{$classname}";

		$path = $directory . $classname . '.php';

		if (isset($assocArgs['module'])) {
			$module = $assocArgs['module'];

			$modulePath = get_template_directory() . '/modules/' . $module . '/';

			if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^\d/', $module) || !is_dir($modulePath)) {
				WP_CLI::error("Module does not exists");
				exit;
			}

			$directory = $modulePath . 'Components/';

			if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
				throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
			}

			$directory .= "{$classname}/";

			$namespace = "Modules\\{$module}\Components\\{$classname}";
			$path = $directory . $classname . '.php';
		}

		if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
		}

		if (file_exists($path)) {
			WP_CLI::error("Component already exists");
			exit;
		}

		$componentFile = fopen($path, 'wb');

		$componentFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/Component.txt');
		$componentFileContent = str_replace(['{{ namespace }}', '{{ classname }}', '{{ name }}'], [$namespace, $classname, $name], $componentFileContent);

		$slug = strtolower(str_replace(' ', '-', $name));
		$componentFileContent = str_replace('{{ slug }}', $slug, $componentFileContent);

		if (!empty($supports)) {
			$supports = explode(',', $supports);
			$supports = array_map('trim', $supports);
			$supports = array_filter($supports);
		}

		if (empty($supports)) {
			$supports = '';
		}

		$componentFileContent = str_replace('{{ supports }}', $supports, $componentFileContent);

		fwrite($componentFile, $componentFileContent);
		fclose($componentFile);

		$viewsDirectory = $directory . 'views/';

		if (!mkdir($viewsDirectory) && !is_dir($viewsDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $viewsDirectory));
		}
		$componentViewFile = fopen($viewsDirectory . 'component.twig', 'wb');

		fwrite($componentViewFile, "Component: {$name}");

		fclose($componentViewFile);

		WP_CLI::success("Component created in {$path}\n");
	}
}
