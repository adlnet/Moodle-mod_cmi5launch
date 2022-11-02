<?php
/*
    Copyright 2014 Rustici Software

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
*/

if (file_exists('vendor/autoload.php')) {
    // prefer the composer autoloader
    require_once('vendor/autoload.php');
}
else if (!class_exists('cmi5\\Version')) {
    cmi5_register_autoloader('cmi5\\', 'src');
}

/**
 * Register a namespace autoloader for the cmi5 library
 *
 * A source filepath will be generated based on the current directory.
 *
 * @param string $namespace a valid namespace, include trailing backslashes ('\\')
 * @param string $directory a directory name, not a filepath
 */
function cmi5_register_autoloader($namespace, $directory) {
    spl_autoload_register(function($className) use ($namespace, $directory) {
        if (stripos($className, $namespace) === false) {
            return;
        }
        $sourceDir = __DIR__ . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR;
        $fileName  = str_replace([$namespace, '\\'], [$sourceDir, DIRECTORY_SEPARATOR], $className) . '.php';
        if (is_readable($fileName)) {
            include $fileName;
        }
    });
}
