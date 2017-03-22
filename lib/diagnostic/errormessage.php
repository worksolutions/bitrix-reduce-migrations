<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Diagnostic;

/**
 * Class ErrorMessage is container for errors handle
 *
 * @package WS\Migrations\Diagnostic
 */
class ErrorMessage {

    const TYPE_ITEM_HAS_NOT_REFERENCE = 'item-has-not-reference';
    const TYPE_REFERENCE_WITHOUT_ITEM = 'reference-without-item';

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