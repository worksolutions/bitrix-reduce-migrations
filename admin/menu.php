<?php
global $USER;
if (!$USER->isAdmin()) {
    return array();
}
CModule::IncludeModule('ws.reducemigrations');
$loc = \WS\ReduceMigrations\Module::getInstance()->getLocalization('menu');
$inputUri = '/bitrix/admin/ws_reducemigrations.php?lang=' . LANGUAGE_ID . '&q=';
return array(
    array(
        'parent_menu' => 'global_menu_settings',
        'sort' => 500,
        'text' => $loc->getDataByPath('title'),
        'title' => $loc->getDataByPath('title'),
        'module_id' => 'ws_reducemigrations',
        'icon' => '',
        'items_id' => 'ws_reducemigrations_menu',
        'items' => array(
            array(
                'text' => $loc->getDataByPath('apply'),
                'url' => $inputUri.'main',
            ),
            array(
                'text' => $loc->getDataByPath('createScenario'),
                'url' => $inputUri.'createScenario'
            ),
            array(
                'text' => $loc->getDataByPath('log'),
                'url' => $inputUri.'log',
                'more_url' => array($inputUri.'detail')
            )
        )
    )
);
