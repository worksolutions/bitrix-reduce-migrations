<?php

namespace WS\ReduceMigrations\Builder\Entity;


class EnumVariant extends Base {

    private $id;

    public function __construct($value, $data = array()) {
        foreach ($data as $key => $item) {
            $this->setAttribute($key, $item);
        }
        if ($data['ID']) {
            $this->setId($data['ID']);
        }
        $this->value($value);
    }

    /**
     * @param int $id
     * @return EnumVariant
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
     * @param string $value
     *
     * @return EnumVariant
     */
    public function value($value) {
        $this->setAttribute('VALUE', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return EnumVariant
     */
    public function xmlId($value) {
        $this->setAttribute('XML_ID', $value);
        $this->setAttribute('EXTERNAL_ID', $value);
        return $this;
    }

    /**
     * @param string $value
     *
     * @return EnumVariant
     */
    public function sort($value) {
        $this->setAttribute('SORT', $value);
        return $this;
    }

    /**
     * @param bool $value
     *
     * @return EnumVariant
     */
    public function byDefault($value = true) {
        $this->setAttribute('DEF', $value ? 'Y' : 'N');
        return $this;
    }

    /**
     * @return EnumVariant
     */
    public function markDeleted() {
        $this->setAttribute('DEL', 'Y');
        return $this;
    }

    public function needToDelete() {
        return $this->getAttribute('DEL');
    }


}