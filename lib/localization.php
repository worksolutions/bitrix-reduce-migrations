<?php

namespace WS\ReduceMigrations;

/**
 * @author <sokolovsky@worksolutions.ru>
 */
class Localization {

    /**
     * @var Options
     */
    private $data;

    public function __construct($data) {
        $this->data = new Options($data);
    }

    /**
     * @return Options
     */
    private function getData() {
        return $this->data;
    }

    /**
     * @param string $path @see Options
     * @param array $replace
     * @return mixed
     */
    public function message($path, $replace = null) {
        $m = $this->getData()->get($path, '');
        $result = $m ?: $path;
        if (is_array($replace)) {
            $result = str_replace(array_keys($replace), array_values($replace), $m);
        }
        return $result;
    }

    /**
     * @param string $path @see Options
     * @return Localization
     */
    public function fork($path) {
        return new static($this->getData()->get($path));
    }

    public function getDataByPath($path) {
        return $this->getData()->get($path, '');
    }
}
