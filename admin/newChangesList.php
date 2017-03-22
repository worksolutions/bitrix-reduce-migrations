<?php

use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;

/** @var CMain $APPLICATION */
$sTableID = 'ws_tools_new_changes_list';
$lAdmin = new CAdminList($sTableID);
$module = \WS\ReduceMigrations\Module::getInstance();

$arHeaders = array(
    array("id" => "date", "content" => $localization->getDataByPath('fields.date'), "default"=>true),
    array("id" => "description", "content" => $localization->getDataByPath('fields.description'), "default" => true),
    array("id" => "source", "content" => $localization->getDataByPath('fields.source'), "default" => true),
);
$lAdmin->AddHeaders($arHeaders);

/** @var CollectorFix[] $fixes */
$fixes = $module->getNotAppliedFixes();

$rowsData = array();
array_walk($fixes, function (CollectorFix $fix) use (& $rowsData, $localization) {
    if (in_array($fix->getName(), array('Insert reference', 'References updates', 'Reference fix'))) {
        return;
    }
    $row = & $rowsData[$fix->getLabel()];
    if(!$row) {
        $time = str_replace(".json", "", $fix->getLabel());
        $row = array(
            'label' => $fix->getLabel(),
            'date' => FormatDate("Q " . $localization->message("message.ago"), $time),
            'source' => $fix->getOwner() ?: $fix->getDbVersion()
        );
    }
    $row['description'] = $row['description'] ? implode("<br />", array($row['description'], $fix->getName())) : $fix->getName();
});

$rsData = new CAdminResult(null, $sTableID);
$rsData->InitFromArray($rowsData);
$rsData->NavStart(500);

while ($rowData = $rsData->NavNext()) {
    $row = $lAdmin->AddRow($rowData['label'], $rowData);
    $row->AddViewField('description', $rowData['description']);
    $row->AddActions(array(
        array(
            "ICON" => "view",
            "TEXT" => $localization->message('message.view'),
            "ACTION" => $lAdmin->ActionRedirect("ws_migrations.php?q=detail&label=".$rowData['label'].'&type=new'. '&lang=' . LANGUAGE_ID),
            "DEFAULT" => true
        )
    ));
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

$lAdmin->CheckListMode();
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
