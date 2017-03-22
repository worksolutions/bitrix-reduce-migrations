<?php
global $APPLICATION, $errors;
$localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('setup');
$options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
$module = \WS\ReduceMigrations\Module::getInstance();

$errors && CAdminMessage::ShowMessage(
    array(
        "MESSAGE" => implode(', ', $errors),
        "TYPE" => "ERROR"
    )
);

$form = new CAdminForm('ew', array(
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
