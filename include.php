<?php
$lib = __DIR__.'/lib';

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

function jsonToArray($json) {
    global $APPLICATION;
    $value = json_decode($json, true);
    $value = $APPLICATION->ConvertCharsetArray($value, "UTF-8", LANG_CHARSET);

    return $value;
}

function arrayToJson($data) {
    global $APPLICATION;
    $data = $APPLICATION->ConvertCharsetArray($data, LANG_CHARSET, "UTF-8");

    return json_encode($data);
}
