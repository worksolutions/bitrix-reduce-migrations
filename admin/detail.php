<?php

use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;

$fDiff = function ($array1, $array2) use (& $fDiff) {
    foreach($array1 as $key => $value) {
        if(is_array($value)) {
            if(!isset($array2[$key])) {
                $difference[$key] = $value;
            } elseif(!is_array($array2[$key])) {
                $difference[$key] = $value;
            } else {
                $new_diff = $fDiff($value, $array2[$key]);
                if($new_diff != false) {
                    $difference[$key] = $new_diff;
                }
            }
        } elseif(!isset($array2[$key]) || $array2[$key] != $value) {
            $difference[$key] = $value;
        }
    }
    return !isset($difference) ? 0 : $difference;
};

/** @var $localization \WS\ReduceMigrations\Localization */
$localization;
$module = \WS\ReduceMigrations\Module::getInstance();

$serviceLabels = array(
    '~reference',
    '~property_list_values',
    'Reference fix',
    'reference',
    'group',
    'dbVersion',
    'Insert reference'
);

$label = $_GET['label'];
$type  = $_GET['type'];
$data = array();
switch ($type) {
    case 'applied':
        /** @var AppliedChangesLogModel[] $models */
        $models = AppliedChangesLogModel::find(array('filter' => array('=groupLabel' => $label)));
        $models[0] && $arTitle = array(
            '#date' => $models[0]->date->format('d.m.Y'),
            '#source' => $models[0]->source,
            '#deployer' => $models[0]->getSetupLog()->shortUserInfo()
        );
        $data = array_map(function (AppliedChangesLogModel $model) {
            return array(
                'description' => $model->description,
                'updateData' => $model->updateData,
                'originalData' => $model->originalData
            );
        }, $models);
        break;
    case 'new':
        $module->applyAnotherReferences();
        $allFixes = $module->getNotAppliedFixes();
        $fixes = array();
        /** @var CollectorFix $fix */
        foreach ($allFixes as $fix) {
            if ($fix->getLabel() != $label) {
                continue;
            }
            $fixes[] = $fix;
        }
        if ($fix) {
            $time = str_replace(".json", "", $label);
            $fDate = FormatDate("d.m.Y", $time);
            $arTitle = array(
                '#date' => $fDate,
                '#source' => $fix->getOwner(),
                '#deployer' => $localization->message("message.nobody")
            );
        }
        foreach ($fixes as $fix) {
            $data[] = array(
                'description' => $fix->getName(),
                'updateData' => $fix->getUpdateData(),
                'originalData' => $module->getSnapshotDataByFix($fix)
            );
        }
        break;
    default:
        throw new HttpRequestException;
}
/** @var CMain $APPLICATION */
$APPLICATION->SetTitle($localization->message('title',$arTitle));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$tabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => $localization->getDataByPath('tabs.diff'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('tabs.diff'),
    ),
    array(
        "DIV" => "edit2",
        "TAB" => $localization->getDataByPath('tabs.final'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('tabs.final')
    )
);
$hasOriginalData = false;
foreach ($data as $iData) {
    if ($iData['originalData']) {
        $hasOriginalData = true;
        break;
    }
}
if ($hasOriginalData) {
    $tabs[] = array(
        "DIV" => "edit3",
        "TAB" => $localization->getDataByPath('tabs.merge'),
        "ICON" => "iblock",
        "TITLE" => $localization->getDataByPath('tabs.merge')
    );
}
$tabControl = new CAdminTabControl('ws_maigrations_label_detail', $tabs);
$fSection = function ($label) use ($serviceLabels, $localization) {
    if (in_array($label, $serviceLabels, true)) {
        $label = $localization->message('serviceLabels.'.$label);
    }
    ?>
    <tr class="heading">
        <td colspan="2">
            <?=$label?>
            <a href="#" style="text-decoration: none; border-bottom: 1px dashed;" class="show"><?=$localization->getDataByPath('message.show')?></a>
            <a href="#" style="text-decoration: none; border-bottom: 1px dashed; display: none;" class="hide"><?=$localization->getDataByPath('message.hide')?></a>
        </td>
    </tr>
    <?php
};
$fRow = function ($label, $value) use ($serviceLabels, $localization) {
    if (in_array($label, $serviceLabels, true)) {
        $label = $localization->message('serviceLabels.'.$label);
    }
    ?>
    <tr>
        <td width="30%" valign="top"><b><?=$label?>:</b></td>
        <td width="60%"><?=is_array($value) ? \Bitrix\Main\Diag\Debug::dump($value) : $value?></td>
    </tr>
    <?php
};
ShowNote($localization->getDataByPath('description'));
$tabControl->Begin();
$tabControl->BeginNextTab();
foreach ($data as $iData) {
    if (!$iData['originalData']) {
        $diff = $iData['updateData'];
    }
    if (!$iData['updateData']) {
        $diff = $iData['originalData'];
    }
    if ($iData['updateData'] && $iData['originalData']) {
        $diff = $fDiff($iData['updateData'], $iData['originalData']);
    }
    if (is_scalar($iData['updateData'])) {
        $diff = array('ID' => $iData['updateData']);
    }
    if (!array_filter($diff)) {
        continue;
    }
    $fSection($iData['description']);
    foreach ($diff as $field => $value) {
        if (!$value) {
            continue;
        }
        if (is_array($value)) {
            if (!array_filter($value)) {
                continue;
            }
            $fRow($field);
            foreach ($value as $subValueField => $subValue) {
                if (!$subValue) {
                    continue;
                }
                $fRow($subValueField, $subValue);
            }
            continue;
        }
        $fRow($field, $value);
    }
}

$tabControl->BeginNextTab();
foreach ($data as $iData) {
    if (!$iData['updateData']) {
        continue;
    }
    if (is_scalar($iData['updateData'])) {
        $iData['updateData'] = array('ID' => $iData['updateData']);
    }
    $fSection($iData['description']);
    foreach ($iData['updateData'] as $field => $value) {
        if (!$value) {
            continue;
        }
        if (is_array($value)) {
            $fRow($field, '');
            foreach ($value as $subValueField => $subValue) {
                if (!$subValue) {
                    continue;
                }
                $fRow($subValueField, $subValue);
            }
            continue;
        }
        $fRow($field, $value);
    }
}
$tabControl->BeginNextTab();
foreach ($data as $iData) {
    if (!$iData['originalData']) {
        continue;
    }
    if (is_scalar($iData['originalData'])) {
        $iData['originalData'] = array('ID' => $iData['originalData']);
    }
    $fSection($iData['description']);
    foreach ($iData['originalData'] as $field => $value) {
        if (!$value) {
            continue;
        }
        if (is_array($value)) {
            $fRow($field, '');
            foreach ($value as $subValueField => $subValue) {
                if (!$subValue) {
                    continue;
                }
                $fRow($subValueField, $subValue);
            }
            continue;
        }
        $fRow($field, $value);
    }
}
$tabControl->EndTab();
$tabControl->Buttons();
$tabControl->End();
CJSCore::Init(array('jquery'));
?>
<script type="text/javascript">
    $(function () {
        function Section(header, rows) {
            this.rows = rows;
            this.header = header;

            var This = this;
            $(header).find('a.hide').click(function (e) {
                e.preventDefault();
                This.hide();
            });
            $(header).find('a.show').click(function (e) {
                e.preventDefault();
                This.show();
            });
        }

        Section.prototype.show = function () {
            $(this.rows).each(function () {
                $(this).show();
            });
            $(this.header).find('a.hide').show();
            $(this.header).find('a.show').hide();
        };

        Section.prototype.hide = function () {
            $(this.rows).each(function () {
                $(this).hide();
            });
            $(this.header).find('a.hide').hide();
            $(this.header).find('a.show').show();
        };

        $('tr.heading').each(function () {
            var rows = [];
            var el = $(this).next();
            while (true) {
                if ($(el).is(".heading")) {
                    break;
                }
                if (!$(el).is('tr')) {
                    break;
                }
                rows.push(el);
                el = $(el).next();
            }
            var sec = new Section(this, rows);
            sec.hide();
        });
    });
</script>
