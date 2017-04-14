<?php

namespace WS\ReduceMigrations;

/**
 * @author <sokolovsky@worksolutions.ru>
 */
class Options implements \Serializable, \ArrayAccess {

    private $data = array();

    public function __construct(array $data = null) {
        $data && ($this->data = $data);
    }

    /**
     * @param string $path
     * @param mixed $default
     *
     * @throws \Exception
     * @return mixed
     */
    public function get($path, $default = null) {

        $usesAliases = array();
        $rPath = preg_replace_callback('/\[.*?\]/', function ($matches) use (& $usesAliases) {
            $key = trim($matches[0], '[]');
            $alias = str_replace('.', '_', $key);
            $usesAliases[$alias] = $key;

            return '.' . $alias;
        }, $path);

        $arPath = explode('.', $rPath);
        $data = $this->data;
        while (($pathItem = array_shift($arPath)) !== null) {
            if ($usesAliases[$pathItem]) {
                $pathItem = $usesAliases[$pathItem];
                unset($usesAliases[$pathItem]);
            }

            if ($data instanceof self) {
                $data = $data->toArray();
            }
            if (!isset($data[$pathItem])) {
                if (!is_null($default)) {
                    return $default;
                }
                throw new \Exception("Value by path `$path` not exist");
            }
            $data = $data[$pathItem];
        }

        return $data;
    }

    /**
     * @param string $path
     * @param null|mixed $default
     *
     * @throws \Exception
     * @return $this
     */
    public function getAsObject($path, $default = null) {
        $res = $this->get($path, $default);
        if (!is_array($res)) {
            throw new \Exception("Return value as object not available");
        }

        return new static($this->get($path));
    }

    /**
     * @param \ArrayAccess|array $mergedOptions
     *
     * @return Options
     */
    public function merge($mergedOptions) {
        if (is_object($mergedOptions) && $mergedOptions instanceof Options) {
            $mergedOptions = $mergedOptions->toArray();
        }
        foreach ($mergedOptions as $path => $value) {
            $this->set($path, $value);
        }

        return $this;
    }

    /**
     * @param string $path
     * @param mixed $value
     *
     * @throws \Exception
     * @return Options
     */
    public function set($path, $value) {
        $arPath = explode('.', $path);
        $data = &$this->data;
        while (($key = array_shift($arPath)) !== null) {
            if (empty($arPath)) {
                $key ? $data[$key] = $value : $data[] = $value;
            } else {
                if (!$key) {
                    throw new \Exception('Need last iterated by path. Available: ' . $path);
                }
                if (!isset($data[$key])) {
                    $data[$key] = array();
                }
                $data = &$data[$key];
            }
        }

        return $this;
    }

    public function __invoke() {
        $args = func_get_args();
        switch (count($args)) {
            case 1:
                return $this->get($args[0]);
                break;
            case 2:
                return $this->set($args[0], $args[1]);
                break;
        }
    }

    public function toArray() {
        return $this->data;
    }

    public function serialize() {
        return serialize($this->data);
    }

    public function unserialize($serialized) {
        $this->data = unserialize($serialized);
    }

    public function offsetExists($offset) {
        try {
            $this->get($offset);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);

        return $value;
    }

    public function offsetUnset($offset) {
        $this->set($offset, null);
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}
