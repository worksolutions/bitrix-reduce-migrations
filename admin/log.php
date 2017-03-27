<?php

use WS\ReduceMigrations\Entities\AppliedChangesLogModel;

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;

/** @var \WS\ReduceMigrations\Module $module */
$module = \WS\ReduceMigrations\Module::getInstance();

/** @var CMain $APPLICATION */
$APPLICATION->SetTitle($localization->getDataByPath('title'));
$sTableID = "ws_reducemigrations_log_table";
$oSort = new CAdminSorting($sTableID, "date", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array("id" => "updateDate", "content" => $localization->getDataByPath('fields.updateDate'), "default"=>true),
    array("id" => "description", "content" => $localization->getDataByPath('fields.description'), "default" => true),
    array("id" => "hash", "content" => $localization->getDataByPath('fields.hash'), "default" => true),
    array("id" => "owner", "content" => $localization->getDataByPath('fields.owner'), "default" => true),
    array("id" => "dispatcher", "content" => $localization->getDataByPath('fields.dispatcher'), "default" => true)
);
$lAdmin->AddHeaders($arHeaders);

$models = AppliedChangesLogModel::find(array(
    'limit' => 500,
    'order' => array(
        'groupLabel' => 'desc'
    )
));

$rowsData = array();
$versions = $module->getPlatformVersion()->getMapVersions();
array_walk($models, function (AppliedChangesLogModel $model) use (& $rowsData, $versions) {
    if (in_array($model->description, array('Insert reference', 'References updates'))) {
        return;
    }
    $row = & $rowsData[$model->groupLabel];
    if(!$row) {
        $row = array(
            'label' => $model->groupLabel,
            'updateDate' => $model->date->format('d.m.Y H:i:s'),
            'hash' => substr($model->hash, 0, 8),
            'owner' => $model->owner,
            'dispatcher' => $model->getSetupLog() ? $model->getSetupLog()->shortUserInfo() : ''
        );
    }
    $row['description'] = $row['description'] ? implode("<br />", array($row['description'], $model->description)) : $model->description;
    if ($model->isFailed()) {
        $row['error'][] = array(
            'data' => \WS\ReduceMigrations\jsonToArray($model->description) ?: array('message' => $model->description),
            'id' => $model->id
        );
    }
});

$rsData = new CAdminResult(null, $sTableID);
$rsData->InitFromArray($rowsData);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint($localization->getDataByPath('messages.pages')));
while ($rowData = $rsData->NavNext()) {
    $row = $lAdmin->AddRow($rowData['label'], $rowData);
    $description = $rowData['description'];
    if ($rowData['error']) {
        $description = array();
        foreach ($rowData['error'] as $rowErrorData) {
            $description[] = '<a href="#" data-id="'.$rowErrorData['id'].'" class="error-link">'.$rowErrorData['data']['message'].'</a>';
        }
        $description = implode('<br />', $description);
    }

    $row->AddViewField('description', $description);
    $row->AddActions(array(
        array(
            "ICON" => "view",
            "TEXT" => $localization->message('messages.view'),
            "ACTION" => $lAdmin->ActionRedirect("ws_reducemigrations.php?q=detail&label=".$rowData['label'].'&type=applied'. '&lang=' . LANGUAGE_ID),
            "DEFAULT" => true
        )
    ));
}
if ($_REQUEST["mode"] == "list") {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
} else {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
}

$lAdmin->CheckListMode();
$lAdmin->DisplayList();
?>
<script type="text/javascript">
    $(function () {
        var $errorLink = $('.error-link');
        $errorLink.click(function (e) {e.preventDefault();});
        $errorLink.on("click", function () {
            var id = $(this).data('id');
            (new BX.CDialog({
                'title': "<?=$localization->message("messages.errorWindow")?>",
                'content_url': '/bitrix/admin/ws_reducemigrations.php?q=applyError&id='+id + '&lang=' . LANGUAGE_ID,
                'width': 900,
                'buttons': [BX.CAdminDialog.btnClose],
                'resizable': false
            })).Show();
        });
    });
</script>
<?php

if ($_REQUEST["mode"] == "list")  {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
} else {
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

