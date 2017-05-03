<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;

use Bitrix\Main\Entity\DataManager;

class AppliedChangesLogTable extends DataManager {
    public static function className() {
        return get_called_class();
    }

    public static function getFilePath() {
        return __FILE__;
    }

    public static function getTableName() {
        return 'ws_reducemigrations_apply_changes_log';
    }

    public static function getMap() {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ),
            'SETUP_LOG_ID' => array(
                'data_type' => 'integer'
            ),
            'GROUP_LABEL' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'DATE' => array(
                'data_type' => 'datetime',
                'required' => true,
            ),
            'SUBJECT' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'UPDATE_DATA' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'STATUS' => array(
                'data_type' => 'integer',
            ),
            'DESCRIPTION' => array(
                'data_type' => 'string',
                'required' => true,
            ),
            'HASH' => array(
                'data_type' => 'string'
            ),
        );
    }
}