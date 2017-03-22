<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;


use Bitrix\Main\Entity\DataManager;
use Bitrix\Main;

class SetupLogTable extends DataManager {

    public static function className() {
        return get_called_class();
    }

    public static function getTableName() {
        return 'ws_migrations_setups_log';
    }

    /**
     * fuck ))
     * @return string|void
     */
    public static function getFilePath() {
        return __FILE__;
    }

    public static function getMap() {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true
            ),
            'DATE' => array(
                'data_type' => 'datetime',
                'required' => true,
            ),
            'USER_ID' => array(
                'data_type' => 'integer'
            )
        );
    }
}