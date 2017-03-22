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
$errorData = \WS\ReduceMigrations\jsonToArray($model->description) ?: array('message' => $model->description);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
?>
<table style="border-spacing: 0px 20px;">
    <tr>
        <td width="30%" style="vertical-align: top">
            <b><?=$localization->message('message')?></b>
        </td>
        <td>
            <?ShowError($errorData['message'])?>
        </td>
    </tr>
<?php
    if ($errorData['data']):
?>
    <tr>
        <td width="30%" style="vertical-align: top">
            <b><?=$localization->message('data')?></b>
        </td>
        <td>
            <?highlight_string(var_export($errorData['data'], true))?>
        </td>
    </tr>
<?php
    endif;
?>
<?php
    if ($errorData['trace']):
?>
    <tr>
        <td width="30%" style="vertical-align: top">
            <b><?=$localization->message('trace')?></b>
        </td>
        <td>
            <ul>
            <?php
                foreach ($errorData['trace'] as $k => $value):
            ?>
                <li style="margin-bottom: 10px;">
                    <?='('.$k.') '.$value['file'].':'.$value['line']?>
                    <br />
                    <?=$value['class'].$value['type'].$value['function']?>
                    <br />
                    <a href="#" class="args-link"><?=$localization->message('arguments')?></a>
                    <p style="display: none;"><?highlight_string(var_export($value['args'], true))?></p>
                </li>
            <?php
                endforeach;
            ?>
            </ul>
        </td>
    </tr>
<?php
    endif;
?>
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
