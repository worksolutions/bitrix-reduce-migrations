<?php

/** @var $APPLICATION CMain */
/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$module = \WS\ReduceMigrations\Module::getInstance();

$fileName = '';
$hasError = false;
if ($_POST['save'] != "" && $_POST['name']) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // �������� ������
    $templateContent = file_get_contents(__DIR__.'/../data/scenarioTemplate.tpl');
    $arReplace = array(
        '#class_name#' => $className = 'ws_m_'.time().'_'.CUtil::translit($name, LANGUAGE_ID),
        '#name#' => addslashes($name),
        '#description#' => addslashes($description),
        '#db_version#' => $module->getPlatformVersion()->getValue(),
        '#owner#' => $module->getPlatformVersion()->getOwner()
    );
    $classContent = str_replace(array_keys($arReplace), array_values($arReplace), $templateContent);
    $fileName = $className.'.php';
    try {
        $fileName = $module->putScriptClass($fileName, $classContent);
    } catch (Exception $e) {
        $hasError = true;
    }
}

if ($_POST['save'] != "" && !$_POST['name']) {
    $hasError = true;
}
$APPLICATION->SetTitle($localization->getDataByPath('title'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// ��������� ���������
$fileName && CAdminMessage::ShowNote($localization->message('path-to-file', array('#path#' => $fileName)));
$hasError && CAdminMessage::ShowMessage(array("MESSAGE" => $localization->message('save-file-error'), "TYPE" => "ERROR"));
?><form method="POST" action="<?=$APPLICATION->GetCurUri()?>" ENCTYPE="multipart/form-data" name="save"><?
$form = new CAdminForm('ws_maigrations_create_scenario', array(
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
$form->EndTab();
    $form->Buttons(array('btnSave' => false, 'btnApply' => false));
    $form->sButtonsContent = '<input type="submit" class="adm-btn-save" name="save" value="'.$localization->getDataByPath('button.create').'" title="'.$localization->getDataByPath('run').'"/>';
$form->Show();
?></form>
