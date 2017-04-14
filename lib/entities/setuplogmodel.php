<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use WS\ReduceMigrations\factories\DateTimeFactory;

class SetupLogModel extends BaseEntity {
    public
        $id, $userId;
    /**
     * @var \DateTime
     */
    public $date;

    private $userData = false;

    public function __construct() {
        $this->date = DateTimeFactory::createBase();
    }

    static protected function map() {
        return array(
            'id' => 'ID',
            'date' => 'DATE',
            'userId' => 'USER_ID'
        );
    }

    static protected function gatewayClass() {
        return SetupLogTable::className();
    }

    static public function deleteById($id) {
        return self::callGatewayMethod('delete', $id);
    }

    static protected function modifyFromDb($data) {
        if ($data['date'] instanceof DateTime) {
            $timestamp = $data['date']->getTimestamp();
            $data['date'] = DateTimeFactory::createBase();
            $data['date']->setTimestamp($timestamp);
        } else {
            $data['date']= DateTimeFactory::createBase($data['date']);
        }
        return $data;
    }

    static protected function modifyToDb($data) {
        $data['date'] && $data['date'] instanceof \DateTime && $data['date'] = DateTimeFactory::createBitrix($data['date']);
        return $data;
    }

    /**
     * @return AppliedChangesLogModel[]
     */
    public function getAppliedLogs() {
        return AppliedChangesLogModel::find(array(
            'order' => array('id' => 'desc'),
            'filter' => array(
                '=setupLogId' => $this->id
            )
        ));
    }

    /**
     * @return array
     */
    private function getUserData() {
        if ($this->userData === false) {
            $this->userData = UserTable::getById($this->userId)->fetch();
        }
        return $this->userData;
    }

    public function shortUserInfo() {
        $res  = 'cli';
        if ($this->userId) {
            $data = $this->getUserData();
            $res = $data['NAME'].' '.$data['LAST_NAME'];
        }
        return $res;
    }
}