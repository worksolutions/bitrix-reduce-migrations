<?php
include __DIR__.'/prolog.php';

/** @var CMain $APPLICATION */
$APPLICATION;
$module = \WS\ReduceMigrations\Module::getInstance();
$localization = $module->getLocalization('setup');
$options = $module->getOptions();

$errors = array();

$fCreateDir = function ($dir) {
    $parts = explode('/', $dir);
    $dir = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
    foreach ($parts as $part) {
        if (!$part) {
            continue;
        }
        $dir .= '/'.$part;
        if (!mkdir($dir)) {
            return false;
        }
        chmod($dir, 0777);
    }
    return true;
};

$fSave = function ($data) use (& $errors, $module, $options, $localization, $fCreateDir) {

    $catalog = $data['catalog'];
    $catalogError = false;
    if (!$catalog) {
        $errors[] = $localization->getDataByPath('errors.catalogFieldEmpty');
        $catalogError = true;
    }
    $dir = $_SERVER['DOCUMENT_ROOT'] .$catalog;
    if (!$catalogError && !is_dir($dir) && !$fCreateDir($catalog)) {
        $catalogError = true;
        $errors[] = $localization->getDataByPath('errors.notCreateDir');
    }
    if (!$catalogError && !is_dir($dir)) {
        $errors[] = $localization->getDataByPath('errors.notCreateDir');
    } elseif(!$catalogError) {
        $catalog && $options->catalogPath = $catalog;
    }

    $options->useAutotests = (bool)$data['tests'];
};

$_POST['data'] && $fSave($_POST['data']);

$errors && CAdminMessage::ShowMessage(
    array(
        "MESSAGE" => implode(', ', $errors),
        "TYPE" => "ERROR"
    )
);
$form = new CAdminForm('form', array(
    array(
        'DIV' => 't1',
        'TAB' => $localization->getDataByPath('tab'),
    )
));
echo BeginNote();
echo $localization->getDataByPath('description');
echo EndNote();
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));
$form->BeginNextFormTab();
$form->AddEditField(
    'data[catalog]',
    $localization->getDataByPath('fields.catalog'),
    true,
    array(),
    $options->catalogPath ?: '/bitrix/php_interface/reducemigrations'
);

$form->Buttons(array('btnSave' => false, 'btnApply' => true));
$form->Show();
