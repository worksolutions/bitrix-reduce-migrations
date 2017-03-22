<?php
global $USER;
if (!$USER->isAdmin()) {
    return array();
}
$loc = \WS\ReduceMigrations\Module::getInstance()->getLocalization('menu');
$inputUri = '/bitrix/admin/ws_migrations.php?lang=' . LANGUAGE_ID . '&q=';
return array(
    array(
        'parent_menu' => 'global_menu_settings',
        'sort' => 500,
        'text' => $loc->getDataByPath('title'),
        'title' => $loc->getDataByPath('title'),
        'module_id' => 'ws.migrations',
        'icon' => '',
        'items_id' => 'ws_migrations_menu',
        'items' => array(
            array(
                'text' => $loc->getDataByPath('apply'),
                'url' => $inputUri.'main',
            ),
            array(
                'text' => $loc->getDataByPath('automigrations'),
                'items_id' => 'ws_migrations_menu_auto',
                'items' => array(
                    array(
                        'text' => $loc->getDataByPath('changeversion'),
                        'url' => $inputUri.'changeversion',
                    ),
                    array(
                        'text' => $loc->getDataByPath('entitiesVersions'),
                        'url' => $inputUri.'entitiesVersions'
                    ),
                ),
            ),
            array(
                'text' => $loc->getDataByPath('createScenario'),
                'url' => $inputUri.'createScenario'
            ),
            array(
                'text' => $loc->getDataByPath('diagnostic'),
                'url' => $inputUri.'diagnostic'
            ),
            array(
                'text' => $loc->getDataByPath('log'),
                'url' => $inputUri.'log',
                'more_url' => array($inputUri.'detail')
            )
        )
    )
);
