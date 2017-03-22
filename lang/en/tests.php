<?php
return array(
    'run' => array(
        'name' => 'WorkSolutions. Migrations module',
        'report' => array(
            'completed' => 'Completed',
            'assertions' => 'Assertions'
        )
    ),
    'cases' => array(
        \WS\ReduceMigrations\Tests\Cases\FixTestCase::className() => array(
            'name' => 'Test fix changes',
            'description' => 'Test fix changes when change structure of subject area',
            'errors' => array(
                'not create iblock id' => 'Not created iblock identifier. :lastError',
                'not create property iblock id' => 'Not created iblock property. :lastError',
                'not create section iblock id' => 'Not create iblock section. :lastError',
                'last log records need been update process' => 'Last log records need been update process',
                'iblock not registered after update' => 'Iblock not register in update, actual :actual, need :need',
                'property iblock not registered after update' => 'Property not register in update, original - :original, actual - :actual',
                'section iblock not registered after update' => 'Section not create in update, original - :original, actual - :actual',
                'links expected count' => 'Links must be :count',
                'error update result' => 'Result updates negative',
                'having one fixing updates' => 'Having one fixing updates',
                'fixing name change' => 'Fixing on change name',
                'iblock must be removed from the database' => 'Iblock must be removed from database',
                'uninstall entries must be: section, property information, iblock' => 'Must be removing records: section, property, iblock',
                'should be uninstall entries: Section' => 'Must be removing records: section',
                'should be uninstall entries: Property' => 'Must be removing records: iblock property',
                'should be uninstall entries: Iblock' => 'Must be removing records: iblock',
                'data pack when you remove the section must be an identifier' => 'Update data when remove section must be id, actual - :value',
                'data pack when you remove the property must be an identifier' => 'Update data when remove iblock property must be id, actual - :value',
                'data pack when you remove the iblock must be an identifier' => 'Update data when remove iblock must be id, actual - :value',
                'data should be stored remotely information block' => 'Must be store data remotely iblock',
                'should be in an amount of writable' => 'Must be records in count: :count',
                'logging process should be - Disposal' => 'Journal process must be - remove',
                'information block data to be restored' => 'Iblock data must be restored',
                'iblock restored identifier changed' => 'Iblock restored, identifier changed',
                'must present properties of reduced information iblock' => 'Must be present properties restored iblock - :iblockId',
                'must present sections of reduced information iblock' => 'Must be present sections restored iblock - :iblockId',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\InstallTestCase::className() => array(
            'name' => 'Test setup process',
            'description' => '',
            'errors' => array(
                'number of links to the information block and the information block entries must match' => 'Number of links to the information block and the information block entries must match',
                'number of links on the properties of information blocks and records must match' => 'Number of links on the properties of information blocks and records must match',
                'number of links to information block sections and records must match' => 'Number of links to information block sections and records must match',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\UpdateTestCase::className() => array(
            'name' => 'Test update changes',
            'description' => 'Test update changes according fixations',
            'errors' => array(
                'record IB must be present' => 'Record of iblock must be present',
                'not also recording information block' => 'Not added iblock record',
                'unavailable identifier of the new information block' => 'Identifier of new iblock is not available',
                'added properties not available information block' => 'New iblock properties is not available, iblock ID - :iblockId',
                'added sections not available information block' => 'New iblock sections is not available, iblock id - :iblockId',
                'inconsistencies initialization name' => 'Inconsistencies initialization name',
                'name information block has not changed' => 'Name of information block is not changed',
                'section should not be' => 'Section must not be',
                'in the information block is only one property' => 'In the information block is only one property',
                'iblock not been deleted' => 'iblock not been deleted',
                'iblock exists' => 'Iblock exists',
                'requires fixations adding links' => 'Requires fixations adding links',
                'when upgrading recorded only links' => 'When upgrading recorded only links',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\RollbackTestCase::className() => array(
            'name' => 'Rollback changes',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\IblockBuilderCase::className() => array(
            'name' => 'IblockBuilder test',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\HighLoadBlockBuilderCase::className() => array(
            'name' => 'HighLoadBlockBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\AgentBuilderCase::className() => array(
            'name' => 'AgentBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\EventsBuilderCase::className() => array(
            'name' => 'EventsBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\FormBuilderCase::className() => array(
            'name' => 'FormBuilder',
            'description' => '',
            'errors' => array(
            )
        ),
    )
);