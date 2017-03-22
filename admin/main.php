<?php
/** @var $APPLICATION CMain */

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$module = \WS\ReduceMigrations\Module::getInstance();
/** @var \WS\ReduceMigrations\PlatformVersion $platformVersion */
$platformVersion = \WS\ReduceMigrations\Module::getInstance()->getPlatformVersion();

$apply = false;
if ($_POST['rollback']) {
    $module->rollbackLastChanges();
    $apply = true;
}

$diagnosticTester = $module->useDiagnostic();

if ($_POST['apply'] && $diagnosticTester->run()) {
    $module->applyNewFixes();
    $apply = true;
}

$isDiagnosticValid = $diagnosticTester
    ->getLastResult()
    ->isSuccess();

$apply && LocalRedirect($APPLICATION->GetCurUri());

$fixes = array();
$notAppliedFixes = $module->getNotAppliedFixes();
foreach ($notAppliedFixes as $fix) {
    $name = $fix->getName();
    if ($name == 'Reference fix') {
        $name = $localization->message('common.reference-fix');
    }
    $fixes[$name]++;
}
$scenarios = array();
foreach ($module->getNotAppliedScenarios() as $notAppliedScenarioClassName) {
    $scenarios[] = $notAppliedScenarioClassName::name();
}

$lastSetupLog = \WS\ReduceMigrations\Module::getInstance()->getLastSetupLog();
if ($lastSetupLog) {
    $appliedFixes = array();
    $errorFixes = array();

    foreach ($lastSetupLog->getAppliedLogs() as $appliedLog) {
        !$appliedLog->success && $errorFixes[] = $appliedLog;
        $appliedLog->success && $appliedFixes[$appliedLog->description]++;
    }
}
//--------------------------------------------------------------------------
$APPLICATION->SetTitle($localization->getDataByPath('title'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// 1C-Bitrix override variable!!
$module = \WS\ReduceMigrations\Module::getInstance();

!$fixes && !$scenarios && !$lastSetupLog && CAdminMessage::ShowMessage(
    array(
        "MESSAGE" => $localization->message('common.pageEmpty'),
        "TYPE" => "OK"
    )
);

CAdminMessage::ShowMessage(array(
    'MESSAGE' => $localization->getDataByPath('platformVersion.'.($platformVersion->isValid() ? 'ok' : 'error'))
        .' <a href="/bitrix/admin/ws_migrations.php?q=changeversion&lang=' . LANGUAGE_ID . '">'.($platformVersion->getOwner() ?: $platformVersion->getValue()).'</a>',
    'TYPE' => $platformVersion->isValid() ? 'OK' : 'ERROR',
    'HTML' => true,
));
?><form method="POST" action="<?=$APPLICATION->GetCurUri()?>" ENCTYPE="multipart/form-data" name="apply"><?
$form = new CAdminForm('ws_maigrations_main', array(
    array(
        "DIV" => "edit1",
        "TAB" => $localization->getDataByPath('title'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('title'),
    ) ,
));

$form->SetShowSettings(false);

if (!$isDiagnosticValid) {
    $form->BeginPrologContent();
    $mess =  $localization->message('diagnostic', array(
        ':url:' => '/bitrix/admin/ws_migrations.php?q=diagnostic&lang=' . LANGUAGE_ID
    ));
    $adminMessage = new CAdminMessage(array('HTML' => "Y", 'MESSAGE' => $mess));
    echo $adminMessage->Show();
    $form->EndPrologContent();
}
$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri()
));
$form->BeginNextFormTab();
if ($fixes || $scenarios) {
    $form->BeginCustomField('list', 'vv');
    if ($fixes) {
        ?>
        <tr style="color: #3591ff; font-size: 14px;">
            <td width="30%" valign="top"><b><?= $localization->getDataByPath('list.auto') ?>:</b></td>
            <td width="70%">
                <? if ($fixes): ?>
                    <ol style="margin-top: 0px; list-style-type: none; padding-left: 0px;">
                        <? foreach ($fixes as $fixName => $fixCount): ?>
                            <li><?= $fixName ?> [<?= $fixCount ?>]</li>
                        <? endforeach; ?>
                        <li><a href="#" style="color: #242E32; text-decoration: none; border-bottom: 1px dashed #242E32" id="newChangesViewLink"><?= $localization->getDataByPath('newChangesDetail') ?></a></li>
                    </ol>
                    <?
                else: ?>
                    <b><?= $localization->message('common.listEmpty') ?></b>
                <? endif; ?>
            </td>
        </tr>
    <?php
    }
    if ($scenarios) {
    ?>
        <tr style="color: #3591ff; font-size: 14px;">
            <td width="30%" valign="top"><b><?= $localization->getDataByPath('list.scenarios') ?>:</b></td>
            <td width="70%">
                <? if ($scenarios): ?>
                    <ol style="margin-top: 0px; list-style-type: none; padding-left: 0px;">
                        <? foreach ($scenarios as $scenario): ?>
                            <li><?= $scenario ?></li>
                        <? endforeach; ?>
                    </ol>
                    <?
                else: ?>
                    <b><?= $localization->message('common.listEmpty') ?></b>
                <? endif; ?>
            </td>
        </tr>
            <?
    }
    $form->EndCustomField('list');
}
//--------------------
if ($lastSetupLog) {
    $form->AddSection('lastSetup', $localization->message('lastSetup.sectionName', array(
        ':time:' => $lastSetupLog->date->format('d.m.Y H:i:s'),
        ':user:' => $lastSetupLog->shortUserInfo()
    )));
    if ($appliedFixes) {
        $form->BeginCustomField('appliedList', 'vv');
        ?>
        <tr style="color: #32cd32;  font-size: 14px;">
        <td width="30%" valign="top"><b><?= $localization->getDataByPath('appliedList') ?>:</b></td>
        <td width="70%">
            <ol style="list-style-type: none; padding-left: 0px; margin-top: 0px;">
                <? foreach ($appliedFixes as $fixName => $fixCount):
                    if ($fixName == 'Insert reference' || $fixName == 'References updates') {
                        $fixName = $localization->message('common.reference-fix');
                    }
                ?>
                    <li><?= $fixName ?> <?= $fixCount > 1 ? '['.$fixCount.']' : '' ?></li>
                <?endforeach; ?>
            </ol>
        </tr>
        <?php
        $form->EndCustomField('appliedList');
    }
    //--------------------
    if($errorFixes) {
        $form->BeginCustomField('errorList', 'vv');
        ?>
        <tr style="color: #ff0000; font-size: 14px;">
            <td width="30%" valign="top"><?= $localization->getDataByPath('errorList') ?>:</td>
            <td width="60%">
                <ol style="list-style-type: none; padding-left: 0px; margin-top: 0px;">
                    <?php
                    /** @var \WS\ReduceMigrations\Entities\AppliedChangesLogModel $errorApply */
                    foreach ($errorFixes as $errorApply):
                        $errorData = \WS\ReduceMigrations\jsonToArray($errorApply->description) ?: $errorApply->description;
                        if (is_scalar($errorData)) {
                            ?>
                            <li><?= $errorData ?></li><?
                        }
                        if (is_array($errorData)) {
                            ?>
                            <li><a href="#" class="apply-error-link"
                                      data-id="<?= $errorApply->id ?>"><?= $errorData['message'] ?></a></li><?
                        }
                        ?>
                    <?endforeach; ?>
                </ol>
            </td>
        </tr>
        <?php
        $form->EndCustomField('errorList');
    }
}
$form->EndTab();
!$fixes && !$scenarios && !$lastSetupLog && $form->bPublicMode = true;
$form->Buttons(array('btnSave' => false, 'btnApply' => false));
$isDiagnosticValid && ($fixes || $scenarios)
    && $form->sButtonsContent .=
        '<input type="submit" class="adm-btn-save" name="apply" value="'.$localization->getDataByPath('btnApply').'" title="'.$localization->getDataByPath('btnApply').'"/>';
$lastSetupLog
    && $form->sButtonsContent .=
        '<input type="submit" name="rollback" value="'.$localization->getDataByPath('btnRollback').'" title="'.$localization->getDataByPath('btnRollback').'"/>';
    $form->Show();
?></form>
<script type="text/javascript">
    $(function () {
        var $chLink = $(document.getElementById('newChangesViewLink'));
        var $applyErrorLinks = $('a.apply-error-link');
        $($chLink, $applyErrorLinks).click(function (e) {e.preventDefault();});

        $chLink.on("click", function () {
            (new BX.CDialog({
                'title': "<?=$localization->message("newChangesTitle")?>",
                'content_url': '/bitrix/admin/ws_migrations.php?q=newChangesList&lang=<?=LANGUAGE_ID;?>',
                'width': 900,
                'height': 130,
                'buttons': [BX.CAdminDialog.btnClose],
                'resizable': false
            })).Show();
        });

        $applyErrorLinks.on("click", function () {
            var id = $(this).data('id');
            (new BX.CDialog({
                'title': "<?=$localization->message("errorWindow")?>",
                'content_url': '/bitrix/admin/ws_migrations.php?q=applyError&id='+id + '&lang=<?=LANGUAGE_ID;?>',
                'width': 900,
                'buttons': [BX.CAdminDialog.btnClose],
                'resizable': false
            })).Show();
        });
    });
</script>