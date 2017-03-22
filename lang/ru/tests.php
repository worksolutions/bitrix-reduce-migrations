<?php
return array(
    'run' => array(
        'name' => '������� �������. ������ ��������',
        'report' => array(
            'completed' => '������� ��������',
            'assertions' => '��������'
        )
    ),
    'cases' => array(
        \WS\ReduceMigrations\Tests\Cases\FixTestCase::className() => array(
            'name' => '������������ �������� ���������',
            'description' => '�������� �������� ��������� ��� ��������� ��������� ���������� �������',
            'errors' => array(
                'not create iblock id' => '�� ������ ������������� ���������. :lastError',
                'not create property iblock id' => '�� ������� �������� ���������. :lastError',
                'not create section iblock id' => '�� ������� ������ ���������. :lastError',
                'last log records need been update process' => '���������� �������� ���� ������ ���� ������� ����������',
                'iblock not registered after update' => '�������� ����������������� � ����������, ��� :actual, ����� :need',
                'property iblock not registered after update' => '�������� ������������������ � ����������, �������� - :original, �������� - :actual',
                'section iblock not registered after update' => '������ ������������������ � ����������, �������� - :original, �������� - :actual',
                'links expected count' => '������ ������ ���� :count',
                'error update result' => '��������� ���������� �������������',
                'having one fixing updates' => '������� ����� �������� ����������',
                'fixing name change' => '�������� �� ��������� �����',
                'iblock must be removed from the database' => '�������� ������ ���� ������ �� ��',
                'uninstall entries must be: section, property information, iblock' => '������ ���� ������ ��������: ������, ��������, ��������',
                'should be uninstall entries: Section' => '������ ���� ������ ��������: ������',
                'should be uninstall entries: Property' => '������ ���� ������ ��������: �������� ���������',
                'should be uninstall entries: Iblock' => '������ ���� ������ ��������: ��������',
                'data pack when you remove the section must be an identifier' => '������� ���������� ��� �������� ������ ������ ���� �������������, � ��� - :value',
                'data pack when you remove the property must be an identifier' => '������� ���������� ��� �������� �������� ��������� ������ ���� �������������, � ��� - :value',
                'data pack when you remove the iblock must be an identifier' => '������� ���������� ��� �������� ��������� ������ ���� �������������, � ��� - :value',
                'data should be stored remotely information block' => '������ �������� ������ ���������� ���������',
                'should be in an amount of writable' => '������ ���� ��������� ������ � ����������: :count',
                'logging process should be - Disposal' => '������������� ������� ������ ���� - ���������',
                'information block data to be restored' => '������ ��������� ������ ���� �������������',
                'iblock restored identifier changed' => '�������� ������������, ������������� �������',
                'must present properties of reduced information iblock' => '������ �������������� �������� ���������������� ��������� - :iblockId',
                'must present sections of reduced information iblock' => '������ �������������� ������(�������) ���������������� ���������  - :iblockId',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\InstallTestCase::className() => array(
            'name' => '������������ ��������� ���������',
            'description' => '',
            'errors' => array(
                'number of links to the information block and the information block entries must match' => '���������� ������ �� ���������� � ������� ���������� ������ ���������',
                'number of links on the properties of information blocks and records must match' => '���������� ������ �� ��������� ���������� � ������� ������ ���������',
                'number of links to information block sections and records must match' => '���������� ������ �� �������� ���������� � ������� ������ ���������',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\UpdateTestCase::className() => array(
            'name' => '���������� ���������',
            'description' => '������������ ���������� ��������� �������� ���������',
            'errors' => array(
                'record IB must be present' => '������ �� ������ ��������������',
                'not also recording information block' => '�� ���������� ������ ���������',
                'unavailable identifier of the new information block' => '���������� ������������� ������ ���������',
                'added properties not available information block' => '���������� ����������� �������� ��������������� �����, �� ID - :iblockId',
                'added sections not available information block' => '���������� ����������� ������ ��������������� �����',
                'inconsistencies initialization name' => '���������� ����������� ������ ��������������� �����',
                'name information block has not changed' => '��� ��������� �� ����������',
                'section should not be' => '������ ���� �� ������',
                'in the information block is only one property' => '� ��������� �������� ������ ���� ��������',
                'iblock not been deleted' => '�������� �� ��� ������',
                'iblock exists' => '�������� ����������',
                'requires fixations adding links' => '���������� ������� �������� ���������� ������',
                'when upgrading recorded only links' => '��� ���������� �������������� ������ ������',
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\RollbackTestCase::className() => array(
            'name' => '����� ���������',
            'description' => '',
            'errors' => array(
            )
        ),
        \WS\ReduceMigrations\Tests\Cases\IblockBuilderCase::className() => array(
            'name' => '���� IblockBuilder',
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