<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Reference;


use WS\ReduceMigrations\Entities\DbVersionReferencesTable;

class ReferenceController {
    const GROUP_IBLOCK = 'iblock';
    const GROUP_IBLOCK_PROPERTY = 'iblockProperty';
    const GROUP_IBLOCK_SECTION = 'iblockSection';
    const GROUP_IBLOCK_PROPERTY_LIST_VALUES = 'iblockPropertyListValues';

    /**
     * @var string
     */
    private $platformVersionValue;

    /**
     * @var callable
     */
    private $_onRegister;

    /**
     * @var callable
     */
    private $_onRemove;

    public function __construct($currentDbVersion) {
        $this->platformVersionValue = $currentDbVersion;
    }

    /**
     * @param ReferenceItem $item
     * @return $this
     * @throws \Exception
     */
    public function registerItem(ReferenceItem $item) {
        !$item->dbVersion && $item->dbVersion = $this->platformVersionValue;

        if ($item->reference) {
            $hasRefByVersion = DbVersionReferencesTable::getList(array(
                'filter' => array(
                    '=REFERENCE' => $item->reference,
                    '=DB_VERSION' => $item->dbVersion
                )
            ))->fetch();
            $hasRefByVersion && DbVersionReferencesTable::delete($hasRefByVersion['ID']);
        }

        $hasItem = DbVersionReferencesTable::getList(array(
            'filter' => array(
                '=DB_VERSION' => $item->dbVersion,
                '=GROUP' => $item->group,
                '=ITEM_ID' => $item->id
            )
        ))->fetch();
        if ($hasItem) {
            throw new \Exception('Item '.$item->group.' ('.$item->id.') by version '.$item->dbVersion.' been registered before');
        }

        $onRegister = $this->_onRegister;
        $onRegister && $item->dbVersion == $this->platformVersionValue && $onRegister($item);

        !$item->reference && $item->reference = $this->createReferenceStringValue($item->dbVersion.$item->group.$item->id);

        DbVersionReferencesTable::add(array(
            'REFERENCE' => $item->reference,
            'DB_VERSION' => $item->dbVersion,
            'GROUP' => $item->group,
            'ITEM_ID' => $item->id
        ));
        return $this;
    }

    /**
     * Remove current version item
     * @param $id
     * @param $group
     * @param string|null $dbVersion
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @return bool
     */
    public function removeItemById($id, $group, $dbVersion = null) {
        try {
            $item = $this->getItemById($id, $group, $dbVersion);
        } catch (\Exception $e) {
            return false;
        }
        if (!$item) {
            return false;
        }
        $res = DbVersionReferencesTable::getList(array(
            'filter' => array(
                '=DB_VERSION' => $dbVersion ?: $this->platformVersionValue,
                '=GROUP' => $group,
                '=ITEM_ID' => $id
            )
        ))->fetch();
        if (!$res) {
            return false;
        }
        $deleteResult = DbVersionReferencesTable::delete($res['ID']);
        $onRemove = $this->_onRemove;
        $onRemove && $item->dbVersion == $this->platformVersionValue && $onRemove($item);
        return $deleteResult->isSuccess();
    }

    /**
     * removes all reference by item id
     *
     * @param int $itemId
     * @param string $group
     * @param string|null $dbVersion
     * @return bool
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     */
    public function removeReference($itemId, $group, $dbVersion = null) {
        $item = $this->getItemById($itemId, $group, $dbVersion);
        if (!$item) {
            return false;
        }
        $dbRes = DbVersionReferencesTable::getList(array(
            'filter' => array(
                '=REFERENCE' => $item->reference,
                "=DB_VERSION" => $dbVersion
            )
        ));
        $onRemove = $this->_onRemove;
        while ($arItem = $dbRes->fetch()) {
            $deleteResult = DbVersionReferencesTable::delete($arItem['ID']);
            if (!$deleteResult->isSuccess()) {
                return false;
            }
            $onRemove && $arItem['DB_VERSION'] == $this->platformVersionValue && $onRemove($item);
        }
        return true;
    }



    private function _initItemByDBData(array $data) {
        $item = new ReferenceItem();
        $item->reference = $data['REFERENCE'];
        $item->dbVersion = $data['DB_VERSION'];
        $item->group = $data['GROUP'];
        $item->id = $data['ITEM_ID'];
        return $item;
    }

    /**
     * @param string $reference
     * @param string $group
     * @param null $rightDbVersion
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     */
    public function tryCreateItemByReference($reference, $group, $rightDbVersion = null) {
        try {
            $this->getItemCurrentVersionByReference($reference);
            return true;
        } catch (\Exception $e) {
            $filter = array(
                '=REFERENCE' => $reference,
                '=GROUP' => $group
            );
            $rightDbVersion && $filter['DB_VERSION'] = $rightDbVersion;
            $dbResItems = DbVersionReferencesTable::getList(array(
                'filter' => $filter
            ));
            $itemId = null;
            while ($arItem = $dbResItems->fetch()) {
                if ($itemId == null) {
                    $itemId = $arItem['ITEM_ID'];
                    continue;
                }
                if ($itemId != $arItem['ITEM_ID']) {
                    return false;
                }
            }
            try {
                $item = $this->_initItemByDBData(
                    array(
                        'REFERENCE' => $reference,
                        'DB_VERSION' => $this->platformVersionValue,
                        'GROUP' => $group,
                        'ITEM_ID' => $itemId
                    )
                );
                $this->registerItem($item);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * @param $value
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @return null|ReferenceItem
     */
    public function getItemCurrentVersionByReference($value) {
        if (!$value) {
            return null;
        }

        $res = DbVersionReferencesTable::getList(array(
            'filter' => array(
                '=REFERENCE' => $value,
                '=DB_VERSION' => $this->platformVersionValue
            )
        ));
        if (!$data = $res->fetch()) {
            throw new \Exception("Reference `$value` not registered in current version");
        }
        return $this->_initItemByDBData($data);
    }

    public function getReferenceValue($id, $group, $dbVersion = null) {
        $item = $this->getItemById($id, $group, $dbVersion);
        if (!$item) {
            throw new \Exception('References item not exists by '.var_export(array('id' => $id, 'group' => $group, 'dbVersion' => $dbVersion), true));
        }
        return $item->reference;
    }

    /**
     * @param $id
     * @param $group
     * @param $dbVersion
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Exception
     * @return null|ReferenceItem
     */
    public function getItemById($id, $group, $dbVersion = null) {
        $res = DbVersionReferencesTable::getList(array(
            'filter' => array(
                '=ITEM_ID' => (int) $id,
                '=DB_VERSION' => $dbVersion ?: $this->platformVersionValue,
                '=GROUP' => $group
            )
        ));
        if (!$data = $res->fetch()) {
            throw new \Exception('References item not exists by '.var_export(array('id' => $id, 'group' => $group, 'dbVersion' => $dbVersion), true));
        }
        return $this->_initItemByDBData($data);
    }

    /**
     * @param $id
     * @param $group
     * @param $dbVersion
     * @return mixed
     */
    public function getItemId($id, $group, $dbVersion = null) {
        $item = $this->getItemById($id, $group, $dbVersion);
        return $item->id;
    }

    /**
     * @param $id
     * @param $group
     * @param string|null $dbVersion
     * @return bool
     */
    public function hasItemId($id, $group, $dbVersion = null) {
        try {
            $item = $this->getItemById($id, $group, $dbVersion);
            return (bool) $item;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Inject function callable by register new reference element
     * @param $callback
     * @return $this
     */
    public function onRegister($callback) {
        if (!is_callable($callback)) {
            return ;
        }
        $this->_onRegister = $callback;
        return $this;
    }

    /**
     * Register function callable by remove reference element (for current version)
     * @param $callback
     * @return $this
     */
    public function onRemove($callback) {
        if (!is_callable($callback)) {
            return ;
        }
        $this->_onRemove = $callback;
        return $this;
    }

    public function getCurrentIdByOtherVersion($id, $group, $dbVersion) {
        $reference = $this->getReferenceValue($id, $group, $dbVersion);
        if (!$reference) {
            throw new \Exception('References not exists by '.var_export(array('id' => $id, 'group' => $group, 'dbVersion' => $dbVersion), true));
        }
        try {
            $item = $this->getItemCurrentVersionByReference($reference);
        } catch (\Exception $e) {
            return null;
        }
        return $item->id;
    }

    public function setupNewVersion($version) {
        if (!$version) {
            throw new \Exception('Clone version empty');
        }
        $res = DbVersionReferencesTable::getList(array(
            'filter' => array(
                'DB_VERSION' => $this->platformVersionValue
            )
        ));
        $this->platformVersionValue = $version;
        while ($itemData = $res->fetch()) {
            $item = $this->_initItemByDBData($itemData);
            $item->dbVersion = $version;
            $this->registerItem($item);
        }
        return true;
    }

    /**
     * @return ReferenceItem[]
     */
    public function getItems() {
        $dbRes = DbVersionReferencesTable::getList();
        $res = array();
        while ($itemData = $dbRes->fetch()) {
            $res[] = $this->_initItemByDBData($itemData);
        }
        return $res;
    }

    public function deleteAll() {
        $dbRes = DbVersionReferencesTable::getList();
        while ($itemData = $dbRes->fetch()) {
            DbVersionReferencesTable::delete($itemData['ID']);
        }
    }

    /**
     * @param null $group
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getReferences($group = null) {
        $filter = array();
        $group && $filter['=GROUP'] = $group;
        $dbRes = DbVersionReferencesTable::getList(array(
            'filter' => $filter,
            'group' => 'REFERENCE'
        ));
        $res = array();
        while ($item = $dbRes->fetch()) {
            $res[] = $item['REFERENCE'];
        }
        return $res;
    }

    public function createReferenceStringValue($id) {
        return md5($id);
    }

    /**
     * @param string $dbVersion
     */
    public function fitVersion($dbVersion) {
        $stableVersionItems = DbVersionReferencesTable::getList(array(
            'filter' => array(
                "=DB_VERSION" => $dbVersion
            )
        ))->fetchAll();
        $currentVersionItems = DbVersionReferencesTable::getList(array(
            'filter' => array(
                "=DB_VERSION" => $this->platformVersionValue
            )
        ))->fetchAll();


        foreach ($stableVersionItems as $stableVersionItem) {
            foreach ($currentVersionItems as & $currentVersionItem) {
                if ($currentVersionItem['REFERENCE'] != $stableVersionItem['REFERENCE']) {
                    continue;
                }
                if ($currentVersionItem['ITEM_ID'] == $stableVersionItem['ITEM_ID']) {
                    unset($currentVersionItem);
                    break;
                }

                $this->removeItemById($currentVersionItem['ITEM_ID'], $currentVersionItem['GROUP']);

                $stableVersionItem['DB_VERSION'] = $this->platformVersionValue;
                $item = $this->_initItemByDBData($stableVersionItem);
                $this->registerItem($item);
                unset($currentVersionItem);
                break;
            }
        }

        $restOfCurrentVersionItems = array_filter($currentVersionItems);
        foreach ($restOfCurrentVersionItems as $restItem) {
            $this->removeItemById($restItem['ITEM_ID'], $restItem['GROUP']);
        }
    }
}
