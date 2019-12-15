<?php

namespace WS\ReduceMigrations\Builder;

use Bitrix\Main\Application;
use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\Entity\IblockType;
use WS\ReduceMigrations\Builder\Entity\Property;
use WS\ReduceMigrations\Builder\Traits\OperateUserFieldEntityTrait;

class IblockBuilder {
    use OperateUserFieldEntityTrait;

    public function __construct() {
        \CModule::IncludeModule('iblock');
    }

    /**
     * @param string $type
     * @param \Closure $callback
     *
     * @return IblockType
     * @throws BuilderException
     */
    public function createIblockType($type, $callback) {
        $iblockType = new IblockType($type);
        $callback($iblockType);
        $gateway = new \CIBlockType();
        $res = $gateway->Add($iblockType->getData());
        if (!$res) {
            throw new BuilderException($gateway->LAST_ERROR);
        }
        return $iblockType;
    }

    /**
     * @param string $type
     * @param \Closure $callback
     *
     * @return IblockType
     * @throws BuilderException
     */
    public function updateIblockType($type, $callback) {
        $iblockType = new IblockType($type);
        $iblockType->markClean();
        $callback($iblockType);
        if ($iblockType->isDirty()) {
            $gateway = new \CIBlockType();
            $res = $gateway->Update($type, $iblockType->getData());
            if (!$res) {
                throw new BuilderException($gateway->LAST_ERROR);
            }
        }

        return $iblockType;
    }

    /**
     * @param string $type
     *
     * @throws BuilderException
     */
    public function removeIblockType($type) {
        $isSuccess = \CIBlockType::Delete($type);
        if (!$isSuccess) {
            throw new BuilderException("Error occurred removing iblockType `$type`");
        }
    }
    /**
     * @param string $iblockType
     * @param string $name
     * @param \Closure $callback
     *
     * @throws BuilderException
     * @return Iblock
     */
    public function createIblock($iblockType, $name, $callback) {
        $iblock = Iblock::create($iblockType, $name);
        $callback($iblock);
        $this->commitIblock($iblock);

        return $iblock;
    }


    /**
     * @param integer $id
     * @param \Closure $callback
     *
     * @return Iblock
     * @throws BuilderException
     */
    public function updateIblock($id, $callback) {
        $iblockData = \CIBlock::GetList(array(), array('ID' => $id, 'CHECK_PERMISSIONS' => 'N'))->Fetch();
        if (!$iblockData) {
            throw new BuilderException("Iblock `{$id}` not found");
        }
        $iblock = new Iblock();
        $iblock->setId($iblockData['ID']);
        $iblock->markClean();
        $callback($iblock);

        $this->commitIblock($iblock);

        return $iblock;
    }

    /**
     * @param IblockPointer $pointer
     * @param \Closure $callback
     * @return Iblock
     * @throws BuilderException
     */
    public function updateIblockByPointer(IblockPointer $pointer, $callback) {
        return $this->updateIblock($this->getIblockIdByPointer($pointer), $callback);
    }

    /**
     * @param Iblock $iblock
     *
     * @return Iblock
     * @throws BuilderException
     */
    private function commitIblock($iblock) {

        $connection = Application::getConnection();
        try {
            $connection->startTransaction();
            $data = $iblock->getData();
            $cIblock = new \CIBlock();
            $isSuccess = true;
            if ($iblock->getId() > 0) {
                $iblock->isDirty() && $isSuccess = $cIblock->Update($iblock->getId(), $data);
                $iblockId = $iblock->getId();
            } else {
                $isSuccess = $cIblock->Add($data);
                $iblockId = $isSuccess;
            }

            if (!$isSuccess) {
                throw new BuilderException($cIblock->LAST_ERROR);
            }

            $iblock->setId($iblockId);

            $this->commitProperties($iblock);
            $this->commitSectionFields($iblock);

            $connection->commitTransaction();
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            throw new BuilderException($e->getMessage());
        }

        return $iblock;
    }

    /**
     * @param Iblock $iblock
     *
     * @throws BuilderException
     */
    private function commitProperties($iblock) {

        foreach ($iblock->getDeleteProperties() as $property) {
            \CIBlockProperty::Delete($property->getId());
        }
        $propertyGateway = new \CIBlockProperty();
        foreach ($iblock->getUpdateProperties() as $property) {
            if ($property->isDirty()) {
                $isSuccess = $propertyGateway->Update($property->getId(), $property->getData());
                if (!$isSuccess) {
                    throw new BuilderException($propertyGateway->LAST_ERROR);
                }
            }

            $this->commitEnum($property);
        }
        foreach ($iblock->getProperties() as $property) {
            $property->setAttribute('IBLOCK_ID', $iblock->getId());
            $propertyId = $propertyGateway->Add($property->getData());
            if (!$propertyId) {
                throw new BuilderException($propertyGateway->LAST_ERROR);
            }
            $property->setId($propertyId);
            $this->commitEnum($property);
        }

    }

    /**
     * @param Property $property
     * @throws BuilderException
     */
    private function commitEnum($property) {
        $obEnum = new \CIBlockPropertyEnum;
        foreach ($property->getEnumVariants() as $key => $variant) {
            if ($variant->needToDelete() == 'Y' && $variant->getId() > 0) {
                $obEnum->Delete($variant->getId());
                continue;
            }
            if ($variant->getId() > 0) {
                if ($variant->isDirty() && !$obEnum->Update($variant->getId(), $variant->getData())) {
                    throw new BuilderException("Failed to update enum '{$variant->getAttribute('VALUE')}'");
                }
                continue;
            }

            if (!$obEnum->Add(array_merge($variant->getData(), array('PROPERTY_ID' => $property->getId())))) {
                throw new BuilderException("Failed to add enum '{$variant->getAttribute('VALUE')}'");
            }
        }

    }

    /**
     * @param Iblock $iblock
     *
     * @throws BuilderException
     */
    private function commitSectionFields($iblock) {
        $this->commitUserFields($iblock->getSectionFields(), "IBLOCK_{$iblock->getId()}_SECTION");
    }

    /**
     * @param IblockPointer $pointer
     * @return integer
     * @throws BuilderException
     */
    public function getIblockIdByPointer(IblockPointer $pointer) {
        $filter = array();

        if ($pointer->getType() === IblockPointer::TYPE_ID) {
            $filter = array(
                'ID' => (int) $pointer->getValue()
            );
        }
        if ($pointer->getType() === IblockPointer::TYPE_CODE) {
            $filter = array(
                'CODE' => (string) $pointer->getValue(),
            );
        }
        if ($pointer->getType() === IblockPointer::TYPE_NAME) {
            $filter = array(
                'NAME' => (string) $pointer->getValue(),
            );
        }

        if (!$filter) {
            throw new BuilderException('Type of pointer is not found');
        }
        $arIblock = \CIBlock::GetList(array(), $filter, false)->Fetch();
        if (!$arIblock) {
            throw new BuilderException("Iblock `{$pointer->getValue()}` doesn't exists");
        }

        return (int)$arIblock['ID'];
    }

    /**
     * @param $iblockType
     * @param $name
     *
     * @return bool
     * @throws BuilderException
     */
    public function removeIblock($iblockType, $name) {
        $dbRes = \CIBlock::GetList(null, array(
            'NAME' => $name,
            'TYPE' => $iblockType,
            'CHECK_PERMISSIONS' => 'N',
        ));
        $count = $dbRes->SelectedRowsCount();
        if ($count == 0) {
            throw new BuilderException('Iblock not found');
        }

        if ($count > 1) {
            throw new BuilderException("There is a {$count} iblock with type={$iblockType} name={$name}");
        }
        $iblock = $dbRes->Fetch();

        return $this->removeIblockById($iblock['ID']);
    }

    /**
     * @param IblockPointer $pointer
     * @return bool
     * @throws BuilderException
     */
    public function removeIblockByPointer(IblockPointer $pointer) {
        $id = $this->getIblockIdByPointer($pointer);
        return $this->removeIblockById($id);
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws BuilderException
     */
    public function removeIblockById($id) {

        $result = \CIBlock::Delete($id);
        if (!$result) {
            throw new BuilderException("Error occurred removing iblock with id={$id}");
        }
        return true;
    }

}
