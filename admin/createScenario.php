<?php

/** @var $APPLICATION CMain */
/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$module = \WS\ReduceMigrations\Module::getInstance();
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$fileName = '';
$hasError = false;
if ($_POST['save'] != "" && $_POST['name']) {
    $name = trim($request->get('name'));
    $priority = trim($request->get('priority'));
    $time = trim($request->get('time'));

    try {
        $fileName = $module->createScenario($name, $priority, $time);
    } catch (Exception $e) {
        $hasError = true;
    }
}

if ($_POST['save'] != "" && !$_POST['name']) {
    $hasError = true;
}
$APPLICATION->SetTitle($localization->getDataByPath('title'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$fileName && CAdminMessage::ShowNote($localization->message('path-to-file', array('#path#' => $fileName)));
$hasError && CAdminMessage::ShowMessage(array("MESSAGE" => $localization->message('save-file-error'), "TYPE" => "ERROR"));
?><form method="POST" action="<?=$APPLICATION->GetCurUri()?>" ENCTYPE="multipart/form-data" name="save"><?
$form = new CAdminForm('ws_reducemigrations_create_scenario', array(
    array(
        "DIV" => "edit1",
        "TAB" => $localization->getDataByPath('title'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('title'),
    )
));

$form->SetShowSettings(false);
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));
$form->BeginNextFormTab();
$form->AddEditField('name', $localization->message('field.name'), true, array('size' => 80));

$priorities = array();
foreach (\WS\ReduceMigrations\Scenario\ScriptScenario::getPriorities() as $priority) {
    $priorities[$priority] = $localization->message('priority.' . $priority);
}

$form->AddDropDownField('priority', $localization->message('field.priority'), true, $priorities);
$form->AddEditField('time', $localization->message('field.time'), false, array('size' => 3));

$form->EndTab();
    $form->Buttons(array('btnSave' => false, 'btnApply' => false));
    $form->sButtonsContent = '<input type="submit" class="adm-btn-save" name="save" value="'.$localization->getDataByPath('button.create').'" title="'.$localization->getDataByPath('run').'"/>';
$form->Show();
?></form>
<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_before.php");
