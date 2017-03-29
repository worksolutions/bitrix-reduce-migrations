<?php

namespace WS\ReduceMigrations\Builder\Entity;
use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\BuilderException;

/**
 * Class Property
 * private @property int id
 * @property int iblockId
 * @property string name
 * @property string active
 * @property int sort
 * @property string code
 * @property string propertyType
 * @property string listType
 * @property string userType
 * @property string xmlId
 * @property string multiple
 * @property string searchable
 * @property string filtrable
 * @property string isRequired
 * @property dateTime dateUpdate
 * @property int rowCount
 * @property int colCount
 * @property int multipleCnt
 * @property string fileType
 * @property int linkIblockId
 * @property string withDescription
 * @property int version
 * @property string hint
 * @package WS\ReduceMigrations\Builder\Entity
 */
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

    public function __construct($name, $data = array()) {
        $this->name = $name;
        $this->dateUpdate = new DateTime();
        $this->enumVariants = array();
        $this->setSaveData($data);
    }

    /**
     * @return array
     */
    public function getMap() {
        return array(
            'id' => 'ID',
            'iblockId' => 'IBLOCK_ID',
            'name' => 'NAME',
            'code' => 'CODE',
            'active' => 'ACTIVE',
            'sort' => 'SORT',
            'propertyType' => 'PROPERTY_TYPE',
            'userType' => 'USER_TYPE',
            'listType' => 'LIST_TYPE',
            'fileType' => 'FILE_TYPE',
            'xmlId' => 'XML_ID',
            'multiple' => 'MULTIPLE',
            'searchable' => 'SEARCHABLE',
            'filtrable' => 'FILTRABLE',
            'isRequired' => 'IS_REQUIRED',
            'dateUpdate' => 'TIMESTAMP_X',
            'rowCount' => 'ROW_COUNT',
            'colCount' => 'COL_COUNT',
            'multipleCnt' => 'MULTIPLE_CNT',
            'linkIblockId' => 'LINK_IBLOCK_ID',
            'withDescription' => 'WITH_DESCRIPTION',
            'version' => 'VERSION',
            'hint' => 'HINT',
        );
    }

    /**
     * @param string $code
     * @return Property
     */
    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    /**
     * @param bool $active
     * @return Property
     */
    public function setActive($active) {
        $this->active = $active ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param int $value
     * @return Property
     */
    public function setSort($value = 500) {
        $this->sort = $value;
        return $this;
    }

    /**
     * @param string $propertyType
     * @param string|bool $userType
     * @return Property
     */
    public function setType($propertyType, $userType = false) {
        $this->propertyType = $propertyType;
        if (!$userType) {
            $this->userType = '';
            return $this;
        }
        $type = explode(':', $userType);
        if (count($type) == 2) {
            $this->propertyType = $type[0];
            $this->userType = $type[1];
        } else {
            $this->userType = $type[0];
        }
        return $this;
    }

    /**
     * @param string $listType
     * @return Property
     */
    public function setListType($listType) {
        $this->listType = $listType;
        return $this;
    }

    /**
     * @param string $xmlId
     * @return Property
     */
    public function setXmlId($xmlId) {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @param bool $multiple
     * @return Property
     */
    public function setMultiple($multiple) {
        $this->multiple = $multiple ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $searchable
     * @return Property
     */
    public function setSearchable($searchable) {
        $this->searchable = $searchable ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $filtrable
     * @return Property
     */
    public function setFiltrable($filtrable) {
        $this->filtrable = $filtrable ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param bool $isRequired
     * @return Property
     */
    public function setIsRequired($isRequired) {
        $this->isRequired = $isRequired ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param DateTime $dateUpdate
     * @return Property
     */
    public function setDateUpdate($dateUpdate) {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @param int $rowCount
     * @return Property
     */
    public function setRowCount($rowCount) {
        $this->rowCount = $rowCount;
        return $this;
    }

    /**
     * @param int $colCount
     * @return Property
     */
    public function setColCount($colCount) {
        $this->colCount = $colCount;
        return $this;
    }

    /**
     * @param int $multipleCnt
     * @return Property
     */
    public function setMultipleCnt($multipleCnt) {
        $this->multipleCnt = $multipleCnt;
        return $this;
    }

    /**
     * @param string $fileType
     * @return Property
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;
        return $this;
    }

    /**
     * @param int $linkIblockId
     * @return Property
     */
    public function setLinkIblockId($linkIblockId) {
        $this->linkIblockId = $linkIblockId;
        return $this;
    }

    /**
     * @param bool $withDescription
     * @return Property
     */
    public function setWithDescription($withDescription) {
        $this->withDescription = $withDescription ? 'Y' : 'N';
        return $this;
    }

    /**
     * @param int $version
     * @return Property
     */
    public function setVersion($version = 1) {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $hint
     * @return Property
     */
    public function setHint($hint) {
        $this->hint = $hint;
        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
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
        $this->enumVariants[] = $variant;
        return $variant;
    }

    /**
     * @param $name
     * @return UserField
     */
    public function removeEnum($name) {
        $data = $this->findEnum($name);
        $variant = new EnumVariant($name, $data);
        $variant->del = 'Y';
        $this->enumVariants[] = $variant;
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
            throw new BuilderException('Enum for update not found');
        }
        return $res;
    }

    /**
     * @return EnumVariant[]
     */
    public function getEnumVariants() {
        return $this->enumVariants;
    }

    /**
     * @param int $id
     * @return Property
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
}
