<?php
$lib = __DIR__ . DIRECTORY_SEPARATOR . 'lib';

spl_autoload_register(function ($class) use ($lib) {
    $class = ltrim($class, '\\');
    $pieces = explode('\\', $class);
    if ($pieces[0].$pieces[1] != 'WSReduceMigrations') {
        return false;
    }
    array_shift($pieces);
    array_shift($pieces);

    $className = array_pop($pieces);
    $fileName = $className.'.php';
    $path = $lib.DIRECTORY_SEPARATOR.strtolower(implode(DIRECTORY_SEPARATOR, $pieces).DIRECTORY_SEPARATOR.$fileName);
    if (!file_exists($path)) {
        return false;
    }
    require $path;
    return true;
});
