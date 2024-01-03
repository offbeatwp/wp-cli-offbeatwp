<?php
$composerAutoload = __DIR__ . '/vender/autoload.php';

if (is_dir($composerAutoload)) {
    require_once $composerAutoload;
}

if (!class_exists('WP_CLI')) {
    trigger_error('Offbeatwp-cli cannot work without WP_CLI.');
    return;
}

try {
    WP_CLI::add_command('offbeatwp', OffbeatCLI\OffbeatCommands::class);
} catch (Exception $e) {
    trigger_error($e->getMessage());
}
