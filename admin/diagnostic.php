<?php

$context = Bitrix\Main\Context::getCurrent();
/** @var \Bitrix\Main\HttpRequest $request */
$request = $context->getRequest();

$tester = \WS\ReduceMigrations\Module::getInstance()->useDiagnostic();
if ($request->isPost()) {
    $post = $request->getPostList()->toArray();
    $run = (bool)$post['run'];
    $run && $tester->run();
}
$lastResult = $tester->getLastResult();
/** @var $localization \WS\ReduceMigrations\Localization */
$localization;

$APPLICATION->SetTitle($localization->getDataByPath('title'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?><form id="ws_maigrations_import" method="POST" action="<?=$APPLICATION->GetCurUri()?>" ENCTYPE="multipart/form-data" name="apply"><?
$form = new CAdminForm('ws_maigrations_diagnostic', array(
    array(
        "DIV" => "edit1",
        "TAB" => $localization->getDataByPath('title'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('title'),
    )
));
$form->SetShowSettings(false);
$module = \WS\ReduceMigrations\Module::getInstance();
$form->BeginPrologContent();
CAdminMessage::ShowNote($localization->getDataByPath('description'));
$form->EndPrologContent();
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));

$form->BeginNextFormTab();
$form->BeginCustomField('version', 'vv');
?>
    <tr>
        <td width="30%"><b><?=$localization->getDataByPath('last.result')?>:</b></td>
        <td width="60%" style="padding-bottom: 4px;"><?=$lastResult->isSuccess() ? $localization->message('last.success') : $localization->message('last.fail')?> [<?=$lastResult->getTime()?>]</td>
    </tr>
<?php
    if (!$lastResult->isSuccess()):
?>

    <tr>
        <td width="30%"><?=$localization->getDataByPath('last.description')?>:</td>
        <td width="60%">
<?php
        $strings = array();
        foreach ($lastResult->getMessages() as $message) {
            $strings[] = $message->getText();
        }
        echo implode('<br />', $strings);
?>
        </td>
    </tr>
<?php
endif;
?>
    <tr>
        <td></td>
        <td>
            <input type="hidden" value="Y" name="run" />
        </td>
    </tr><?
$form->EndCustomField('version');
$form->BeginNextFormTab();
$form->Buttons(array('btnSave' => false, 'btnApply' => false));
$form->sButtonsContent = '<input type="submit" class="adm-btn-save" name="submit" value="'.$localization->getDataByPath('run').'" title="'.$localization->getDataByPath('run').'"/>';

    $form->Show();
?></form>