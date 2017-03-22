<?php
return array(
    'title' => 'Installing the Data migrations module',
    'tab' => 'Settings',
    'description' => 'You must specify the directory location of the migration file. <b> relative to the root directory of the project. ($ SERVER [\'DOCUMENT_ROOT\']) </b>',
    'fields' => array(
        'catalog' => 'Directory path',
        'useAutotests' => 'Use auto-tests (for developers)',
        'apply' => 'Set Up'
    ),
    'errors' => array(
        'notCreateDir' => 'Unable to create directory',
        'catalogFieldEmpty' => 'Catalog field is empty'
    ),
    'section' => array(
        'disableHandlers' => 'Use synchronization'
    )
);