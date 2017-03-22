<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;


use Bitrix\Main\Entity\DataManager;

class DbVersionReferencesTable extends DataManager {

    public static function getTableName() {
        return 'ws_migrations_db_version_references';
    }

    public static function className() {
        return get_called_class();
    }

    public static function getFilePath() {
        // fuck )))
        return __FILE__;
    }

    public static function getMap() {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ),
            'REFERENCE' => array(
                'data_type' => 'string',
                'required' => true
            ),
            'DB_VERSION' => array(
                'data_type' => 'string',
                'required' => true
            ),
            'GROUP' => array(
                'data_type' => 'string',
                'required' => true
            ),
            'ITEM_ID' => array(
                'data_type' => 'string',
                'required' => true
            )
        );
    }
}