<?php
/** @var $APPLICATION CMain */

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$module = \WS\ReduceMigrations\Module::getInstance();

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$apply = false;
$timeFormatter = new \WS\ReduceMigrations\TimeFormatter($localization->getDataByPath('timeLang'));
if ($request->get('rollback')) {
    $module->rollbackLastBatch();
    $apply = true;
}

$skipOptional = $request->get('skipOptional') == 'Y';

if ($request->get('apply')) {
    $module->applyMigrations($skipOptional);
    $apply = true;
}

$apply && LocalRedirect($APPLICATION->GetCurUri());
$scenarios = array();
/** @var \WS\ReduceMigrations\Scenario\ScriptScenario $notAppliedScenarioClassName */
$notAppliedScenarios = $module->getNotAppliedScenarios();
foreach ($notAppliedScenarios->groupByPriority() as $priority => $scenarioList) {
    foreach ($scenarioList as $notAppliedScenarioClassName) {
        $scenarios[$priority][] = $notAppliedScenarioClassName::name();
    }
}

$lastSetupLog = \WS\ReduceMigrations\Module::getInstance()->getLastSetupLog();
if ($lastSetupLog) {
    $appliedFixes = array();
    $errorFixes = array();

    foreach ($lastSetupLog->getAppliedLogs() as $appliedLog) {
        $appliedLog->isFailed() && $errorFixes[] = $appliedLog;
        if (!$appliedLog->isFailed()) {
            if (isset($appliedFixes[$appliedLog->getName()])) {
                $appliedFixes[$appliedLog->getName()] = array(
                    'time' => $appliedFixes[$appliedLog->getName()]['time'] + $appliedLog->getTime(),
                    'count' =>  $appliedFixes[$appliedLog->getName()]['count'] + 1
                );
            } else {
                $appliedFixes[$appliedLog->getName()] = array(
                    'time' => $appliedLog->getTime(),
                    'count' =>  1
                );
            }
        }
    }
}
//--------------------------------------------------------------------------
$APPLICATION->SetTitle($localization->getDataByPath('title'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// 1C-Bitrix override variable!!
$module = \WS\ReduceMigrations\Module::getInstance();

!$scenarios && !$lastSetupLog && CAdminMessage::ShowMessage(
    array(
        "MESSAGE" => $localization->message('common.pageEmpty'),
        "TYPE" => "OK",
    )
);

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

$form->Begin(array(
    'FORM_ACTION' => $APPLICATION->GetCurUri(),
));
$form->BeginNextFormTab();
if ($scenarios) {

    $form->AddSection('migrationList', $localization->message('newChangesTitle'));
    $form->BeginCustomField('listDescription', '');
    ?>
        <tr style="font-size: 14px;">
            <td width="30%" style="padding-top: 4px;"><strong><?=$localization->message('priority.priority');?></strong></td>
            <td><?=$localization->message('list.scenarios');?></td>
        </tr>
    <?php
    $form->EndCustomField('listDescription');
    foreach ($scenarios as $priority => $list) :
        $form->BeginCustomField('list' . $priority, 'vv');
        ?>
            <tr style="font-size: 14px;">
                <td width="30%"><strong><?=$localization->message('priority.' . $priority);?></strong></td>
                <td style="color: #3591ff;">
                    <ol style="margin:1px;list-style-type: none; padding-left: 0;">
                        <?php
                        foreach ($list as $scenario) :
                            ?>
                            <li><?= $scenario ?></li>
                        <?php
                        endforeach;
                        ?>
                    </ol>
                </td>
            </tr>
        <?
        $form->EndCustomField('list' . $priority);
    endforeach;
    if (isset($scenarios[\WS\ReduceMigrations\Scenario\ScriptScenario::PRIORITY_OPTIONAL])) {
        $form->AddCheckBoxField('skipOptional', $localization->message('skipOptional'), false, 'Y', false);
    }
    $form->AddViewField('time', $localization->message('approximatelyTime'), $timeFormatter->format($notAppliedScenarios->getApproximateTime()));
}
//--------------------
if ($lastSetupLog) {
    $form->AddSection('lastSetup', $localization->message('lastSetup.sectionName', array(
        ':time:' => $lastSetupLog->date->format('d.m.Y H:i:s'),
        ':user:' => $lastSetupLog->shortUserInfo(),
    )));
    if ($appliedFixes) {
        $form->BeginCustomField('appliedList', 'vv');
        ?>
        <tr style="color: #32cd32;  font-size: 14px;">
        <td width="30%" valign="top"><b><?= $localization->getDataByPath('appliedList') ?>:</b></td>
        <td width="70%">
            <ol style="list-style-type: none; padding-left: 0px; margin-top: 0px;">
                <? foreach ($appliedFixes as $fixName => $item):
                    $fixCount = $item['count'];
                    $time = $item['time'];
                ?>
                    <li><?= $fixName ?> <?= $fixCount > 1 ? '['.$fixCount.']' : '' ?> (<?=$timeFormatter->format($time)?>)</li>
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
                    foreach ($errorFixes as $errorApply):?>
                        <li><?= $errorApply->getName() ?> <br> <?= $errorApply->getErrorMessage() ?></li><br>
                    <?endforeach; ?>
                </ol>
            </td>
        </tr>
        <?php
        $form->EndCustomField('errorList');
    }
}
$form->EndTab();
!$scenarios && !$lastSetupLog && $form->bPublicMode = true;
$form->Buttons(array('btnSave' => false, 'btnApply' => false));
$scenarios
    && $form->sButtonsContent .=
        '<input type="submit" class="adm-btn-save" name="apply" value="'.$localization->getDataByPath('btnApply').'" title="'.$localization->getDataByPath('btnApply').'"/>';
$lastSetupLog
    && $form->sButtonsContent .=
        '<input type="submit" name="rollback" value="'.$localization->getDataByPath('btnRollback').'" title="'.$localization->getDataByPath('btnRollback').'"/>';
    $form->Show();
?></form>
<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_before.php");