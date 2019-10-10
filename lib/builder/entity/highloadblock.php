<?php

namespace WS\ReduceMigrations\Builder\Entity;
use WS\ReduceMigrations\Builder\BuilderException;
use WS\ReduceMigrations\Builder\Traits\ContainUserFieldsTrait;

/**
 * Class HighLoadBlock
 *
 * @method HighLoadBlock name(string $value)
 * @method HighLoadBlock tableName(string $value)
 *
 * @package WS\ReduceMigrations\Builder\Entity
 */
class HighLoadBlock extends Base {
    use ContainUserFieldsTrait;

    private $id;

    public function __construct($name, $tableName, $id = false) {
        $this->id = $id;
        $this->name($name);
        $this->tableName($tableName);
    }

    public function getMap() {
        return array(
            'name' => 'NAME',
            'tableName' => 'TABLE_NAME',
        );
    }

    /**
     * @param int $id
     * @return HighLoadBlock
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param $code
     * @return UserField
     */
    public function addField($code) {
        return $this->addUserField($code);
    }

    /**
     * @param $code
     * @return UserField
     * @throws BuilderException
     */
    public function updateField($code) {
        if (!$this->getId()) {
            throw new BuilderException('Set higloadBlock for continue');
        }

        return $this->updateUserField($code, "HLBLOCK_{$this->getId()}");
    }

    /**
     * @return UserField[]
     */
    public function getFields() {
        return $this->getUserFields();
    }
}
