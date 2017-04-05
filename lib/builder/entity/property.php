<?php

namespace WS\ReduceMigrations\Builder\Entity;

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\BuilderException;


class Property extends Base {

    const TYPE_STRING = 'S';
    const TYPE_NUMBER = 'N';
    const TYPE_FILE = 'F';
    const TYPE_LIST = 'L';
    const TYPE_ELEMENT = 'E';
    const TYPE_GROUP = 'G';

    const USER_TYPE_HTML = 'S:HTML';
    const USER_TYPE_VIDEO = 'S:video';
    const USER_TYPE_DATE = 'S:Date';
    const USER_TYPE_DATETIME = 'S:DateTime';
    const USER_TYPE_YANDEX_MAP = 'S:map_yandex';
    const USER_TYPE_GOOGLE_MAP = 'S:map_google';
    const USER_TYPE_USER = 'S:UserID';
    const USER_TYPE_SECTION_AUTO = 'G:SectionAuto';
    const USER_TYPE_TOPIC_ID = 'S:TopicID';
    const USER_TYPE_SKU = 'E:SKU';
    const USER_TYPE_FILE_MAN = 'S:FileMan';
    const USER_TYPE_ELIST = 'E:EList';
    const USER_TYPE_ELEMENT_XML_ID = 'S:ElementXmlID';
    const USER_TYPE_E_AUTOCOMPLETE = 'E:EAutocomplete';
    const USER_TYPE_DIRECTORY = 'S:directory';
    const USER_TYPE_SEQUENCE = 'N:Sequence';
    /** @var  EnumVariant[] */
    private $enumVariants;
    private $id;

    public function __construct($name, $propertyData = array()) {
        foreach ($propertyData as $code => $value) {
            $this->setAttribute($code, $value);
        }
        if ($propertyData['ID']) {
            $this->setId($propertyData['ID']);
        }
        $this->name($name);
        $this->dateUpdate(new DateTime());
        $this->enumVariants = array();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Property
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $name
     * @return Property
     */
    public function name($name) {
        $this->setAttribute('NAME', $name);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->getAttribute('NAME');
    }

    /**
     * @param bool $active
     * @return Property
     */
    public function active($active = true) {
        $active = $active ? 'Y' : 'N';
        $this->setAttribute('ACTIVE', $active);
        return $this;
    }

    /**
     * @param string $propertyType
     * @param string|bool $userType
     * @return Property
     */
    public function type($propertyType, $userType = false) {
        if (!$userType) {
            $this
                ->setAttribute('PROPERTY_TYPE', $propertyType)
                ->setAttribute('USER_TYPE', $userType);

            return $this;
        }
        $type = explode(':', $userType);
        if (count($type) == 2) {
            $this
                ->setAttribute('PROPERTY_TYPE', $type[0])
                ->setAttribute('USER_TYPE', $type[1]);
        } else {
            $this
                ->setAttribute('PROPERTY_TYPE', $type[0])
                ->setAttribute('USER_TYPE', '');
        }
        return $this;
    }

    /**
     * @param string $listType
     * @return Property
     */
    public function listType($listType) {
        $this->setAttribute('LIST_TYPE', $listType);
        return $this;
    }

    /**
     * @param string $xmlId
     * @return Property
     */
    public function xmlId($xmlId) {
        $this->setAttribute('XML_ID', $xmlId);
        return $this;
    }

    /**
     * @param bool $searchable
     * @return Property
     */
    public function searchable($searchable) {
        $this->setAttribute('SEARCHABLE', $searchable ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param bool $filterable
     * @return Property
     */
    public function filterable($filterable) {
        $this->setAttribute('FILTRABLE', $filterable);
        return $this;
    }

    /**
     * @param DateTime $dateUpdate
     * @return Property
     */
    public function dateUpdate($dateUpdate) {
        $this->setAttribute('TIMESTAMP_X', $dateUpdate);
        return $this;
    }

    /**
     * @param int $rowCount
     * @return Property
     */
    public function rowCount($rowCount) {
        $this->setAttribute('ROW_COUNT', $rowCount);
        return $this;
    }

    /**
     * @param int $colCount
     * @return Property
     */
    public function colCount($colCount) {
        $this->setAttribute('COL_COUNT', $colCount);
        return $this;
    }

    /**
     * @param int $multipleCnt
     * @return Property
     */
    public function multipleCnt($multipleCnt) {
        $this->setAttribute('MULTIPLE_CNT', $multipleCnt);
        return $this;
    }

    /**
     * @param string $fileType
     * @return Property
     */
    public function fileType($fileType) {
        $this->setAttribute('FILE_TYPE', $fileType);
        return $this;
    }

    /**
     * @param int $linkIblockId
     * @return Property
     */
    public function linkIblockId($linkIblockId) {
        $this->setAttribute('LINK_IBLOCK_ID', $linkIblockId);
        return $this;
    }

    /**
     * @param bool $withDescription
     * @return Property
     */
    public function withDescription($withDescription) {
        $this->setAttribute('WITH_DESCRIPTION', $withDescription ? 'Y' : 'N');
        return $this;
    }

    /**
     * @param int $version
     * @return Property
     */
    public function version($version = 1) {
        $this->setAttribute('VERSION', $version);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeString() {
        $this->type(self::TYPE_STRING);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeNumber() {
        $this->type(self::TYPE_NUMBER);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeFile() {
        $this->type(self::TYPE_FILE);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeDropdown() {
        $this
            ->type(self::TYPE_LIST)
            ->listType('L');
        return $this;
    }

    /**
     * @return Property
     */
    public function typeCheckbox() {
        $this
            ->type(self::TYPE_LIST)
            ->listType('C');
        return $this;
    }

    /**
     * @param integer $linkIblockId
     *
     * @return Property
     */
    public function typeElement($linkIblockId) {
        $this
            ->type(self::TYPE_ELEMENT)
            ->linkIblockId($linkIblockId);
        return $this;
    }

    /**
     * @param integer $linkIblockId
     *
     * @return Property
     */
    public function typeSection($linkIblockId) {
        $this
            ->type(self::TYPE_GROUP)
            ->linkIblockId($linkIblockId);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeHtml() {
        $this->type(self::TYPE_STRING, self::USER_TYPE_HTML);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeDate() {
        $this->type(self::TYPE_STRING, self::USER_TYPE_DATE);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeDateTime() {
        $this->type(self::TYPE_STRING, self::USER_TYPE_DATETIME);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeUser() {
        $this->type(self::TYPE_STRING, self::USER_TYPE_USER);
        return $this;
    }

    /**
     * @return Property
     */
    public function typeVideo() {
        $this->type(self::TYPE_STRING, self::USER_TYPE_VIDEO);
        return $this;
    }

    /**
     * @param bool $multiple
     * @return Property
     */
    public function multiple($multiple = true) {
        $multiple = $multiple ? 'Y' : 'N';
        $this->setAttribute('MULTIPLE', $multiple);
        return $this;
    }

    /**
     * @param bool $isRequired
     * @return Property
     */
    public function required($isRequired = true) {
        $required = $isRequired ? 'Y' : 'N';
        $this->setAttribute('IS_REQUIRED', $required);
        return $this;
    }

    /**
     * @param int $value
     * @return Property
     */
    public function sort($value = 500) {
        $this->setAttribute('SORT', $value);
        return $this;
    }

    /**
     * @param string $code
     * @return Property
     */
    public function code($code) {
        $this->setAttribute('CODE', $code);
        return $this;
    }

    /**
     * @param string $hint
     * @return Property
     */
    public function hint($hint) {
        $this->setAttribute('HINT', $hint);
        return $this;
    }

    /**
     * @param $name
     * @return EnumVariant
     */
    public function addEnum($name) {
        $variant = new EnumVariant($name);
        $this->enumVariants[] = $variant;
        return $variant;
    }

    /**
     * @param $name
     * @return EnumVariant
     */
    public function updateEnum($name) {
        $data = $this->findEnum($name);
        $variant = new EnumVariant($name, $data);
        $this->enumVariants[$variant->getId()] = $variant;
        return $variant;
    }

    /**
     * @param $name
     * @return Property
     */
    public function removeEnum($name) {
        $data = $this->findEnum($name);
        $variant = new EnumVariant($name, $data);
        $variant->markDeleted();
        $this->enumVariants[$variant->getId()] = $variant;
        return $this;
    }

    private function findEnum($name) {
        if (!$this->getId()) {
            throw new BuilderException('Save Property before update enum');
        }
        $res = \CIBlockPropertyEnum::GetList(null, array(
            'PROPERTY_ID' => $this->id,
            'VALUE' => $name,
        ))->Fetch();
        if (empty($res)) {
            throw new BuilderException("Enum `$name` not found");
        }
        return $res;
    }

    /**
     * @return EnumVariant[]
     */
    public function getEnumVariants() {
        return $this->enumVariants;
    }

}
