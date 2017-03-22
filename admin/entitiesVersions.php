<?php
/** @var $localization \WS\ReduceMigrations\Localization */
$localization;

/** @var \WS\ReduceMigrations\Module $module */
$module = \WS\ReduceMigrations\Module::getInstance();

/** @var CMain $APPLICATION */
$APPLICATION->SetTitle($localization->getDataByPath('title'));
$sTableID = "ws_migrations_versions_table";
$oSort = new CAdminSorting($sTableID, "date", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array("id" => "reference", "content" => $localization->getDataByPath('fields.reference'), "default"=>true),
    array("id" => "versions", "content" => $localization->getDataByPath('fields.versions'), "default" => true),
    array("id" => "destination", "content" => $localization->getDataByPath('fields.destination'), "default" => true),
);
$platformVersion = $module->getPlatformVersion();
$lAdmin->AddHeaders($arHeaders);

$rsData = \WS\ReduceMigrations\Entities\DbVersionReferencesTable::getList(array('limit' => 500));
$arData = array();
while ($iData = $rsData->fetch()) {
    !$arData[$iData['REFERENCE']] && $arData[$iData['REFERENCE']] = array(
        'reference' => $iData['REFERENCE'],
        'versions' => array()
    );

    $arData[$iData['REFERENCE']]['versions'][] = array(
        'id' => $iData['ITEM_ID'],
        'version' => $iData['DB_VERSION']
    );

    $iData['DB_VERSION'] == $platformVersion->getValue() && $arData[$iData['REFERENCE']]['data'] = array(
        'id' => $iData['ITEM_ID'],
        'subject' => $iData['GROUP']
    );
}
$rsData = new CAdminResult($arData, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint($localization->getDataByPath('messages.pages')));

$versionsList = $platformVersion->getMapVersions();

$fCreateUrlFromEntity = function ($type, $id) {
    $urlTemplate = '';
    switch ($type) {
        case 'iblock':
            $arIblock = CIBlock::GetArrayByID($id);
            $type = $arIblock['IBLOCK_TYPE_ID'];
            $urlTemplate = '/bitrix/admin/iblock_edit.php?type='.$type.'&ID='.$id. '&lang=' . LANGUAGE_ID;
            break;
        case 'iblockProperty':
            $arProperty = CIBlockProperty::GetByID($id)->Fetch();
            $iblockId = $arProperty['IBLOCK_ID'];
            $arIblock = CIBlock::GetArrayByID($iblockId);
            $type = $arIblock['IBLOCK_TYPE_ID'];
            $urlTemplate = '/bitrix/admin/iblock_edit.php?type='.$type.'&ID='.$iblockId. '&lang=' . LANGUAGE_ID;
            break;
        case 'iblockSection':
            $arSection = CIBlockSection::GetByID($id)->Fetch();
            $iblockId = $arSection['IBLOCK_ID'];
            $urlTemplate = '/bitrix/admin/iblock_section_edit.php?IBLOCK_ID='.$iblockId.'&ID='.$id. '&lang=' . LANGUAGE_ID;
            break;
    }
    return $urlTemplate;
};

$fGetVersionsHtml = function ($versions) use (& $versionsList) {

    $strings = array_map(function ($version) use ($versionsList) {
        return "({$version['id']}) - ". ($versionsList[$version['version']] ?: $version['version']);
    }, $versions);

    return implode('<br />', $strings);
};

while ($rowData = $rsData->NavNext()) {
    $row = $lAdmin->AddRow($rowData['reference']);
    $row->AddField('reference', $rowData['reference']);
    $row->AddViewField('versions', $fGetVersionsHtml($rowData['versions']));
    $row->AddViewField('destination',
        '<a href="'.$fCreateUrlFromEntity($rowData['data']['subject'], $rowData['data']['id']).'">['
            .$rowData['data']['id'].'] '.$localization->message('subjects.'.$rowData['data']['subject'])
        .'</a>'
    );
}

if ($_REQUEST["mode"] == "list") {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
} else {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
}

$lAdmin->CheckListMode();
$lAdmin->DisplayList();

if ($_REQUEST["mode"] == "list")  {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
} else {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
