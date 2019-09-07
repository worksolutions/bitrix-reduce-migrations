<?php

namespace WS\ReduceMigrations\Builder;

class IblockPointer {

    const TYPE_NAME = 'by_name';
    const TYPE_ID = 'by_id';
    const TYPE_CODE = 'by_code';
    
    private $type;
    private $value;

    /**
     * IblockPointer constructor.
     * @param string $type
     * @param mixed $value
     */
    private function __construct($type, $value) {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @param $name
     * @return IblockPointer
     */
    public static function byName($name) {
        return new static(self::TYPE_NAME, $name);
    }

    /**
     * @param $id
     * @return IblockPointer
     */
    public static function byId($id) {
        return new static(self::TYPE_ID, $id);
    }

    /**
     * @param $code
     * @return IblockPointer
     */
    public static function byCode($code) {
        return new static(self::TYPE_CODE, $code);
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}
