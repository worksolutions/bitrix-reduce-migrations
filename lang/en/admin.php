<?php
return array(
    'main' => array(
        'title' => 'Migrations management',
        'list' => array(
            'auto' => 'New auto migrations',
            'scenarios' => 'New scenarios'
        ),
        'version' => 'The current version of the database',
        'change_link' => 'change version',
        'errorList' => 'Unsuccessful applied migrations',
        'appliedList' => 'Successful applied migrations',
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
            'error' => 'Incorrect platform version'
        )
    ),
    'changeversion' => array(
        'pageTitle' => 'Platform versions',
        'title' => 'Current platform version',
        'version' => 'HASH',
        'setup' => 'setup',
        'owner' => 'Signature',
        'button_change' => 'Change HASH',
        'description' => "Each project area has a unique identifier (HASH) to synchronize data.",
        'dialog' => array(
            'title' => 'Set the name of the project owner\'s version'
        ),
        'otherVersions' => array(
            'tab' => 'Other versions of the project'
        )
    ),
    'newChangesList' => array(
        'fields' => array(
            'date' => 'Date create',
            'description' => 'Description',
            'source' => 'Source',
        ),
        'message' => array(
            "ago" => 'back',
            'view' => 'detail'
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
    'entitiesVersions' => array(
        'title' => 'References list',
        'fields' => array(
            'reference' => 'HASH',
            'versions' => 'Other versions Ids',
            'destination' => 'Reference'
        ),
        'messages' => array(
            'pages' => 'Pages'
        ),
        'subjects' => array(
            'iblock' => 'Information block',
            'iblockProperty' => 'Information block property',
            'iblockSection' => 'Information block section',
        )
    ),
    'createScenario' => array(
        'title' => 'The script scenario',
        'field' => array(
            'name' => 'Title',
            'description' => 'Description'
        ),
        'path-to-file' => 'Class file migration is #path#',
        'save-file-error' => 'An error occured save file',
        'button' => array(
            'create' => 'Create migration scenario'
        )
    ),
    'diagnostic' => array(
        'title' => 'Platform diagnostic',
        'description' => 'Diagnostics status, problem-solving tips',
        'last' => array(
            'description' => 'Description',
            'result' => 'Result',
            'success' => 'Success',
            'fail' => 'Fail'
        ),
        'run' => 'Run diagnostic',
    ),
    'log' => array(
        'title' => 'Updates log',
        'fields' => array(
            'updateDate' => 'Date',
            'description' => 'Update features',
            'source' => 'Source',
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