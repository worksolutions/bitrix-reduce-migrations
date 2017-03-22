<?php

$context = Bitrix\Main\Context::getCurrent();
/** @var \Bitrix\Main\HttpRequest $request */
$request = $context->getRequest();

/** @var \WS\ReduceMigrations\PlatformVersion $platformVersion */
$platformVersion = \WS\ReduceMigrations\Module::getInstance()->getPlatformVersion();
if ($request->isPost()) {
    $post = $request->getPostList()->toArray();
    $post = \Bitrix\Main\Text\Encoding::convertEncodingArray($post, "UTF-8", $context->getCulture()->getCharset());
    if ($post['changeversion']) {
        \WS\ReduceMigrations\Module::getInstance()->runRefreshVersion();
    }
    if ($post['ownersetup']) {
        $platformVersion->setOwner($post['ownersetup']['owner']);
        $options = \WS\ReduceMigrations\Module::getInstance()->getOptions();
        exit();
    }
}
/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$APPLICATION->SetTitle($localization->getDataByPath('pageTitle'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?><form id="ws_maigrations_import" method="POST" action="<?=
$APPLICATION->GetCurUri()?>" ENCTYPE="multipart/form-data" name="apply"><?
$form = new CAdminForm('ws_maigrations_import', array(
    array(
        "DIV" => "edit1",
        "TAB" => $localization->getDataByPath('title'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('title'),
    ),
    array(
        "DIV" => "edit2",
        "TAB" => $localization->getDataByPath('otherVersions.tab'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('otherVersions.tab')
    )
));
$module = \WS\ReduceMigrations\Module::getInstance();
$form->BeginPrologContent();
    CAdminMessage::ShowNote($localization->getDataByPath('description'));
$form->EndPrologContent();
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));

$form->BeginNextFormTab();
$form->BeginCustomField('version', 'vv');
$color = "green";
!$platformVersion->isValid() && $color = "red";
?>
    <tr style="color: <?=$color?>;">
        <td width="45%"><?=$localization->getDataByPath('version')?>:</td>
        <td width="50%"><b><?=$platformVersion->getValue()?></b></td>
    </tr>
    <tr style="color: <?=$color?>;">
        <td width="45%"><?=$localization->getDataByPath('owner')?>:</td>
        <td width="55%"><b><?=$platformVersion->getOwner()?></b> [<a id="ownerSetupLink" href="#"><?=$localization->getDataByPath('setup')?></a>]</td>
    </tr>
    <tr>
        <td style="padding-top: 10px;" colspan="2" align="center"><input type="submit" name="changeversion" value="<?=$localization->getDataByPath('button_change')?>"></td>
    </tr><?
$form->EndCustomField('version');
$form->BeginNextFormTab();
$form->BeginCustomField('owner', 'ww');
foreach ($module->getOptions()->getOtherVersions() as $version => $owner) {
    ?>
        <tr>
            <td width="30%"><?=$owner?>:</td>
            <td width="60%"><b><?=$version?></b></td>
        </tr>
    <?
}
$form->EndCustomField('owner');
$form->Buttons();
$form->Show();
CJSCore::Init(array('jquery'));
$jsParams = array(
    'owner' => array(
        'label' => $localization->getDataByPath('owner'),
        'value' => $platformVersion->getOwner() ?: ''
    ),
    'dialog' => array(
        'title' => $localization->getDataByPath('dialog.title')
    )
);
?>
</form>
<script type="text/javascript">
    (function (params) {

        BX.ready(function () {
            var $ownerLink = $(document.getElementById('ownerSetupLink'));
            var save = {};
            $.extend(save, BX.CDialog.btnSave);

            save.action = function (event) {
                BX.ajax.post('?q=changeversion', dialog.GetParameters(), function () {
                    BX.reload();
                });
            };

            var dialog = new BX.CDialog({
                'title': params.dialog.title,
                'content': '<form id="ownerSetupLinkForm"><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><td width="40%" text-align="right">'+params.owner.label+':</td><td width="60%" align="left"><input type="text" id="owner" name="ownersetup[owner]" value="'+params.owner.value+'"></td></tr></table></form>',
                'width': 500,
                'height': 70,
                'buttons': [save, BX.CAdminDialog.btnCancel],
                'resizable': false
            });

            var useFormHandler = false;
            $ownerLink.click(function (e) {
                e.preventDefault();
                dialog.Show();
                if (!useFormHandler) {
                    $('#ownerSetupLinkForm').submit(function (e) {
                        e.preventDefault();
                        save.action();
                    });
                    useFormHandler = true;
                }
            });
        });
    })(<?=CUtil::PhpToJsObject($jsParams)?>)
</script>
