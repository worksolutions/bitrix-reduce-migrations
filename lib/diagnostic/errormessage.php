<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Diagnostic;

/**
 * Class ErrorMessage
 *
 * @package WS\ReduceMigrations\Diagnostic
 */
class ErrorMessage {


    private $group;
    private $item;
    private $type;
    private $text;

    /**
     * @param string $group
     * @param string $item
     * @param string $type
     * @param string $text
     */
    public function __construct($group, $item, $type, $text) {
        $this->group = $group;
        $this->item = $item;
        $this->type = $type;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getItem() {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @param $data
     * @return ErrorMessage
     */
    static public function unpack($data) {
        return new static(
            $data['group'],
            $data['item'],
            $data['type'],
            $data['text']
        );
    }

    /**
     * @return array
     */
    public function toArray() {
        return array(
            'group' => $this->group,
            'item' => $this->item,
            'type' => $this->type,
            'text' => $this->text
        );
    }
}