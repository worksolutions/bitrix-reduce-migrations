<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Entities;


use Bitrix\Main\NotImplementedException;

abstract class BaseEntity {
    public $id;

    private $isNew = true;

    private $_errors = array();

    static private $_oneRequestsCache = array();
    /**
     * @param $props
     * @return $this
     */
    static public function create($props) {
        /** @var $model BaseEntity */
        $model = new static;
        foreach ($props as $name => $value) {
            $model->{$name} = $value;
        }
        $model->isNew = false;
        return $model;
    }

    /**
     * @param $fields
     * @return $this
     */
    static private function createByRow($fields) {
        $props = array();
        $fieldsToProps = array_flip(static::map());
        foreach ($fields as $name => $value) {
            if (!isset($fieldsToProps[$name])) {
                continue;
            }
            $name = $fieldsToProps[$name];
            $props[$name] = $value;
        }
        $props = static::modifyFromDb($props);
        return self::create($props);
    }

    private function getRawFields() {
        $result = array();
        $data = array();
        foreach (static::map() as $property => $field) {
            $data[$property] = $this->{$property};
        }
        $data = static::modifyToDb($data);
        foreach (static::map() as $property => $field) {
            $result[$field] = $data[$property];
        }

        return $result;
    }

    /**
     * @param array $params
     * @return AppliedChangesLogModel[]
     */
    static public function find($params = array()) {
        $modelToDb = static::map();
        $fReplaceList = function ($list) use ($modelToDb) {
            return array_map(function ($item) use ($modelToDb) {
                return $modelToDb[$item];
            }, $list);
        };

        if ($params['select']) {
            $params['select'] = $fReplaceList($params['select']);
        }
        if ($params['group']) {
            $pGroup = array();
            foreach ($params['group'] as $field => $value) {
                $pGroup[$modelToDb[$field]] = $value;
            }
            $params['group'] = $pGroup;
        }
        if ($params['order']) {
            $pOrder = array();
            foreach ($params['order'] as $field => $value) {
                $pOrder[$modelToDb[$field]] = $value;
            }
            $params['order'] = $pOrder;
        }

        if ($params['filter']) {
            $pFilter = array();
            foreach ($params['filter'] as $field => $value) {
                $field = preg_replace_callback("/\w+/", function ($matches) use ($modelToDb) {
                    return $modelToDb[$matches[0]];
                }, $field);
                $pFilter[$field] = $value;
            }
            $params['filter'] = $pFilter;
        }
        $dbResult = static::callGatewayMethod('getList', $params);
        $rows = $dbResult->fetchAll();
        $items = array();
        foreach ($rows as $row) {
            $items[] = self::createByRow($row);
        }
        return $items;
    }

    /**
     * @param array $params
     * @return $this
     */
    static public function findOne($params = array()) {
        $cacheKey = md5(get_called_class().serialize($params));
        if (!self::$_oneRequestsCache[$cacheKey]) {
            $params['limit'] = 1;
            $items = self::find($params);
            self::$_oneRequestsCache[$cacheKey] = $items[0];
        }
        return self::$_oneRequestsCache[$cacheKey];
    }

    /**
     * @return mixed
     * @internal param $p1
     * @internal param $p2
     * @internal param $p3
     *
     */
    static public function callGatewayMethod() {
        $params = func_get_args();
        $name = array_shift($params);
        return call_user_func_array(array(static::gatewayClass(), $name), $params);
    }

    public function delete() {
        $res = static::callGatewayMethod('delete', $this->id);
        return !(bool)$res->getErrors();
    }

    public function insert() {
        $res = static::callGatewayMethod('add', $this->getRawFields());
        $this->id = $res->getId();
        $this->_errors = $res->getErrors() ?: array();
        $this->isNew = false;
        return !(bool)$res->getErrors();
    }

    public function update() {
        $res = static::callGatewayMethod('update', $this->id, $this->getRawFields());
        $this->_errors = $res->getErrors() ?: array();
        return !(bool)$res->getErrors();
    }

    public function save() {
        return $this->isNew ? $this->insert() : $this->update();
    }

    public function getErrors() {
        return $this->_errors;
    }

    /**
     * @throws NotImplementedException
     * @return array
     */
    static protected function map() {
        throw new NotImplementedException('You should implement method `map`');
    }

    /**
     * @throws NotImplementedException
     * @return string
     */
    static protected function gatewayClass() {
        throw new NotImplementedException('You should implement method `gatewayClass`');
    }

    static protected function modifyFromDb($data) {
        return $data;
    }

    static protected function modifyToDb($data) {
        return $data;
    }
}