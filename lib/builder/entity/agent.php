<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Class Agent
 * @method Agent sort(int $value)
 * @method Agent userId(int $value)
 * @method Agent interval(int $value)
 * @method Agent module(string $value)
 * @method Agent callback(string $value)
 * @method Agent nextExec(\Bitrix\Main\Type\DateTime $value)
 * @package WS\ReduceMigrations\Builder\Entity
 */
class Agent extends Base {

    private $id;

    const DEFAULT_SORT = 100;

    const DEFAULT_INTERVAL = 86400;

    public function __construct($callback) {
        $this->callback($callback);
    }

    public function getMap() {
        return array(
            'sort' => 'SORT',
            'active' => 'ACTIVE',
            'module' => 'MODULE_ID',
            'callback' => 'NAME',
            'userId' => 'USER_ID',
            'isPeriod' => 'IS_PERIOD',
            'interval' => 'AGENT_INTERVAL',
            'nextExec' => 'NEXT_EXEC',
        );
    }

    /**
     * @param int $id
     * @return Agent
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

    public static function create($callback) {
        $agent = new Agent($callback);
        $agent
            ->sort(self::DEFAULT_SORT)
            ->active(true)
            ->interval(self::DEFAULT_INTERVAL)
            ->isPeriod(false)
            ->nextExec(new DateTime());

        return $agent;
    }

    /**
     * @param bool $active
     * @return Agent
     */
    public function active($active) {
        $this->setAttribute('ACTIVE', $active ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $isPeriod
     * @return Agent
     */
    public function isPeriod($isPeriod) {
        $this->setAttribute('IS_PERIOD', $isPeriod ? 'Y' : 'N');
        return $this;
    }

}