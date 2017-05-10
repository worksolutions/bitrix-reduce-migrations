<?php
return array(
    'main' => array(
        'title' => 'Migrations management',
        'list' => array(
            'scenarios' => 'New scenarios'
        ),
        'priority' => array(
            'priority' => 'Migration priority:',
            'high' => 'High:',
            'medium' => 'Medium:',
            'optional' => 'Optional:',
        ),
        'skipOptional' => 'Skip migrations with type "Optional"',
        'errorList' => 'Unsuccessful applied migrations',
        'appliedList' => 'Successful applied migrations',
        'approximatelyTime' => 'Approximately time of migrations',
        'timeLang' => [
            'minutes' => 'min',
            'seconds' => 'sec'
        ],
        'btnRollback' => 'Undo last change',
        'btnApply' => 'Apply',
        'lastSetup' => array(
            'sectionName' => 'Last update :time: - :user:'
        ),
        'common' => array(
            'listEmpty' => 'List is empty',
            'reference-fix' => 'References synchronizing',
            'pageEmpty' => 'Data for update don`t exists yet'
        ),
        'newChangesDetail' => 'Changes list',
        'newChangesTitle' => 'New changes',
        'errorWindow' => 'Error info',
        'diagnostic' => 'Errors <a href=":url:"> </a> diagnosis, the use of the migration is possible only after the correction',
        'platformVersion' => array(
            'ok' => 'Platform version',
            'error' => 'Incorrect platform version',
            'setup' => 'Setup',
        )
    ),
    'applyError' => array(
        'message' => 'Message',
        'data' => 'Data',
        'trace' => 'Call stack',
        'error' => array(
            'modelNotExists' => 'Data for record id =: id: does not exist'
        )
    ),
    'createScenario' => array(
        'title' => 'The script scenario',
        'field' => array(
            'name' => 'Title',
            'priority' => 'Priority',
            'time' => 'Approximately migration time(seconds)',
        ),
        'priority' => array(
            'high' => 'High',
            'medium' => 'Medium',
            'optional' => 'Optional',
        ),
        'path-to-file' => 'Class file migration is #path#',
        'save-file-error' => 'An error occured save file',
        'button' => array(
            'create' => 'Create migration scenario'
        )
    ),
    'log' => array(
        'title' => 'Updates log',
        'fields' => array(
            'updateDate' => 'Date',
            'description' => 'Update features',
            'hash' => 'Migration hash',
            'dispatcher' => 'Update by'
        ),
        'messages' => array(
            'InsertReference' => 'Insert other reference',
            'view' => 'Analysis of changes',
            'pages' => 'Pages',
            'actualization' => 'Actualization sources',
            'descriptionMoreLink' => 'detail',
            'errorWindow' => 'Error information'
        )
    ),
    'detail' => array(
        'title' => '#date. #source. Update by - #deployer',
        'tabs' => array(
            'diff' => 'Features',
            'final' => 'Update result',
            'merge' => 'Data before update'
        ),
        'message' => array(
            'nobody' => 'The site is not updated',
            'show' => 'show data',
            'hide' => 'hide data',
        ),
        'serviceLabels' => array(
            '~reference' => 'HASH',
            '~property_list_values' => 'VALUES',
            'Reference fix' => 'Register links with the essence of the platform',
            'Insert reference' => 'The new entity reference',
            'reference' => 'HASH',
            'group' => 'Group entity ( the handler )',
            'dbVersion' => 'Platform version'
        )
    ),
    'cli' => array(
        'common' => array(
            'reference-fix' => 'References synchronizing'
        ),
    ),
);