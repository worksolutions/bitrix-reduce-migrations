<?php

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$id = (int) $_GET['id'];
$model = \WS\ReduceMigrations\Entities\AppliedChangesLogModel::findOne(array('filter' => array('=id' => $id)));
if (!$model) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
    ShowError($localization->message('error.modelNotExists', array(
        ':id:' => $id
    )));
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}
/** @var CMain $APPLICATION */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
?>
<table style="border-spacing: 0px 20px;">
    <tr>
        <td width="30%" style="vertical-align: top">
            <b><?=$localization->message('message')?></b>
        </td>
        <td>
            <?ShowError(sprintf('%s %s', $model->getName(), $model->getErrorMessage()))?>
        </td>
    </tr>
</table>
<script type="text/javascript">
    $(function () {
        var
            $argsLinks = $("a.args-link"),
            getArgContent = function ($link) {
                return $link.siblings().filter('p:first');
            };

        $argsLinks.click(function (e) {e.preventDefault()});

        $argsLinks.on('click', function () {
            getArgContent($(this)).toggle();
        });
    });
</script>
<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
