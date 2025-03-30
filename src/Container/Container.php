<?php

namespace Nurymbet\Core\Container;

use Symfony\Component\VarDumper\VarDumper;

class Container
{
    public static function load(): void
    {
        $config = Config::getInstance();
        $modules = $config->get('modules_folder_path', null);
        $bootstrapFile = $config->get('bootstrap_file_name', null);

        if (isset($modules)) {
            foreach (scandir($modules) as $module) {
                if ($module === '.' || $module === '..') continue;

                $bootstrap = $modules . '/' . $module . '/' . $bootstrapFile;

                if (file_exists($bootstrap) && is_file($bootstrap)) {
                    require_once $bootstrap;
                }
            }
        }
    }
}
