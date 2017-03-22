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
        $fCreateUrlFromEntity = function ($type, $id) {
            $urlTemplate = '';
            switch ($type) {
                case 'iblock':
                    $arIblock = CIBlock::GetArrayByID($id);
                    $type = $arIblock['IBLOCK_TYPE_ID'];
                    $urlTemplate = '/bitrix/admin/iblock_edit.php?type='.$type.'&ID='.$id . '&lang=' . LANGUAGE_ID;
                    break;
                case 'iblockProperty':
                    $arProperty = CIBlockProperty::GetByID($id)->Fetch();
                    $iblockId = $arProperty['IBLOCK_ID'];
                    $arIblock = CIBlock::GetArrayByID($iblockId);
                    $type = $arIblock['IBLOCK_TYPE_ID'];
                    $urlTemplate = '/bitrix/admin/iblock_edit.php?type='.$type.'&ID='.$iblockId . '&lang=' . LANGUAGE_ID;
                    break;
                case 'iblockPropertyListValues':
                    $arValue = \Bitrix\Iblock\PropertyEnumerationTable::getList(array('filter' => array('=ID' => $id)))->Fetch();
                    $arProperty = CIBlockProperty::GetByID($arValue['PROPERTY_ID'])->Fetch();
                    $iblockId = $arProperty['IBLOCK_ID'];
                    $arIblock = CIBlock::GetArrayByID($iblockId);
                    $type = $arIblock['IBLOCK_TYPE_ID'];
                    $urlTemplate = '/bitrix/admin/iblock_edit.php?type='.$type.'&ID='.$iblockId . '&lang=' . LANGUAGE_ID;
                    break;
                case 'iblockSection':
                    $arSection = CIBlockSection::GetByID($id)->Fetch();
                    $iblockId = $arSection['IBLOCK_ID'];
                    $urlTemplate = '/bitrix/admin/iblock_section_edit.php?IBLOCK_ID='.$iblockId.'&ID='.$id . '&lang=' . LANGUAGE_ID;
                    break;
            }
            return $urlTemplate;
        };
        $strings = array();
        foreach ($lastResult->getMessages() as $message) {
            if ($message->getType() == \WS\ReduceMigrations\Diagnostic\ErrorMessage::TYPE_ITEM_HAS_NOT_REFERENCE) {
                $url = $fCreateUrlFromEntity($message->getGroup(), $message->getItem());
                if ($url) {
                    $strings[] = '<a href="'.$url.'">'.$message->getText().'</a>';
                    continue;
                }
            }
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