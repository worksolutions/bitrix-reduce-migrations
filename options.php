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

    foreach ($module->getSubjectHandlers() as $handler) {
        $handlerClass = get_class($handler);

        $handlerClassValue = (bool)$data['handlers'][$handlerClass];
        $handlerClassValue && $module->enableSubjectHandler($handlerClass);
        !$handlerClassValue && $module->disableSubjectHandler($handlerClass);
    }
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
$form->AddEditField('data[catalog]', $localization->getDataByPath('fields.catalog'), true, array(), $options->catalogPath ?: '/migrations');
$form->AddSection('disableHandlers', $localization->getDataByPath('section.disableHandlers'));

foreach ($module->getSubjectHandlers() as $handler) {
    $form->AddCheckBoxField('data[handlers]['.get_class($handler).']', $handler->getName(), true, '1', $options->isEnableSubjectHandler(get_class($handler)));
}

$form->Buttons(array('btnSave' => false, 'btnApply' => true));
$form->Show();