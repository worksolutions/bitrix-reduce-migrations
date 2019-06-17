<?php
global $APPLICATION, $errors;
$localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('setup');
$options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
$module = \WS\ReduceMigrations\Module::getInstance();

$errors && CAdminMessage::ShowMessage(
    array(
        "MESSAGE" => implode(', ', $errors),
        "TYPE" => "ERROR",
    )
);

$form = new CAdminForm('ew', array(
    array(
        'DIV' => 't1',
        'TAB' => $localization->getDataByPath('tab'),
    ),
));

echo BeginNote();
echo $localization->getDataByPath('description');
echo EndNote();

$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri(),
));
$form->BeginNextFormTab();
$form->AddEditField('data[catalog]', $localization->getDataByPath('fields.catalog'), true, array(), $options->catalogPath ?: '/bitrix/php_interface/reducemigrations');


$form->Buttons(array('btnSave' => false, 'btnApply' => true));
$form->Show();
