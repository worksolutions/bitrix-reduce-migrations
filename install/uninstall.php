<?php
global $APPLICATION, $errors;
$localization = \WS\ReduceMigrations\Module::getInstance()->getLocalization('uninstall');
$options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
$form = new CAdminForm('ew', array(
    array(
        'DIV' => 't1',
        'TAB' => $localization->getDataByPath('tab'),
    )
));
ShowMessage(array(
    'MESSAGE' => $localization->getDataByPath('description'),
    'TYPE' => 'OK'
));

$errors && ShowError(implode(', ', $errors));
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));
$form->BeginNextFormTab();
$form->AddCheckBoxField('data[removeAll]', $localization->getDataByPath('fields.removeAll'), true, "Y", false);
$form->BeginCustomField('data[remove]', '');
?>
    <tr id="tr_DATA_REMOVE" style="display: none;">
        <td></td>
        <td><input type="hidden" name="data[remove]" value="Y"/></td>
    </tr>
<?php
$form->EndCustomField('data[remove]');
$form->Buttons(array('btnSave' => false, 'btnApply' => true));
$form->Show();