<?php
return array(
    'title' => 'Deleting data of Reduce Migrations module',
    'tab' => 'Delete options',
    'description' => "When you delete data , the project will not be able to work with the current migrations.\n"
        ."You will need to initialize the mechanism of migration once again , starting with the current version .\n"
        ."Information to remove : tables migration , customization , migration catalog (if used versioning system"
        ."should register changes )",
    'fields' => array(
        'removeAll' => 'Erase migration data'
    )
);