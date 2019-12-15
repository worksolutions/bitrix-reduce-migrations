Использование билдеров
===============

* [1. Работа с инфоблоками. IblockBuilder](#IblockBuilder).
* [2. Работа с таблицами. TableBuilder](#TableBuilder).
* [3. Работа с формами. FormBuilder](#FormBuilder).
* [4. Работа c событиями. EventsBuilder](#EventsBuilder).
* [5. Работа c агентами. AgentBuilder](#AgentBuilder).
* [6. Работа c HighLoad инфоблоками. HighLoadBlockBuilder](#HighLoadBlockBuilder).


### 1. Работа с инфоблоками. ```IblockBuilder``` <a name="IblockBuilder"></a>

##### Создание типа инфоблока

Пример добавления типа инфоблока с помощью объекта ```IblockBuilder```

```php
<?php

use WS\ReduceMigrations\Builder\Entity\IblockType;
use WS\ReduceMigrations\Builder\IblockBuilder;

$builder = new IblockBuilder();
$builder->createIblockType('content', function (IblockType $type) {
    $type
        ->inRss(false)
        ->sort(100)
        ->sections('Y')
        ->lang(
            [
                'ru' => [
                    'NAME' => 'Контент',
                    'SECTION_NAME' => 'Разделы',
                    'ELEMENT_NAME' => 'Элементы',
                ],
            ]
        );
});
```

Метод ```createIblockType()``` возвращает объект типа ```IblockType``` и принимает два аргумента:
* ```type``` - строковый идентификатор типа инфоблока
* ```callback``` - функция обратного вызова, принимает аргумент объект типа ```IblockType```.

Методы класса ```IblockType```:
* ```inRss(boolean)``` - экспорт в RSS. Аргумент типа ```boolean``` - по умолчанию ```true```
* ```sections(string)``` - настройка возможности добавления разделов инфоблока. Возможные заначения ```'Y'``` или ```'N'```
* ```lang(array)``` - устанавливает языкозависимые названия и заголовки объектов. Принимает аргумент типа ```array```
* ```sort(int)``` - устанавливает значение сортировки. Принимает значение типа ```int```

Также для настроки типа инфоблока можно воспользоватся методом ```setAttribute()```

```php
<?php

use WS\ReduceMigrations\Builder\Entity\IblockType;
use WS\ReduceMigrations\Builder\IblockBuilder;

$builder = new IblockBuilder();
$builder->createIblockType('content', function (IblockType $type) {
    $type->setAttribute(
        'lang',
        [
            'ru' => [
                'NAME' => 'Контент',
                'SECTION_NAME' => 'Разделы',
                'ELEMENT_NAME' => 'Элементы',
            ],
        ]
    );
});
```
Аргументы метода:
* ```name``` - название поля. Тип ```string``` 
* ```value``` - значение сохраняемого поля

##### Добавление инфоблока

Пример добавления инфоблока с помощью объекта ```IblockBuilder```

```php
<?php

use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\Entity\UserField;
use WS\ReduceMigrations\Builder\IblockBuilder;

$builder = new IblockBuilder();
$iblock = $builder->createIblock('content', 'Новости', function (Iblock $iblock) {
    $iblock
        ->siteId('s1')
        ->sort(100)
        ->code('content')
        ->groupId(['2' => 'R']);
    
    $iblock->setAttribute('DETAIL_PAGE_URL', '#SITE_DIR#/news/#CODE#');
    
    $iblock
        ->setAttribute('FIELDS', [
            'CODE' => [
                'IS_REQUIRED' => 'Y',
                'DEFAULT_VALUE' => [
                    'UNIQUE' => 'Y',
                    'TRANSLITERATION' => 'Y',
                    'TRANS_LEN' => '100',
                    'TRANS_CASE' => 'L',
                    'TRANS_SPACE' => '_',
                    'TRANS_OTHER' => '_',
                    'TRANS_EAT' => 'Y',
                    'USE_GOOGLE' => 'N',
                ]
            ],
            'DETAIL_TEXT_TYPE' => [
                'DEFAULT_VALUE' => 'html'
            ]
        ]);
    
    // Добавление свойства типа 'Строка'
    $iblock
        ->addProperty('Внешняя ссылка')
        ->code('REFERENCE')
        ->typeString()
        ->sort(100);
    
    // Добавление свойства типа 'Привязка к елементам'
    $iblock
        ->addProperty('Товары')
        ->code('GOODS')
        ->multiple()
        ->required()
        ->typeElement($iblockId);
    
    // Добавление свойства типа 'Список'
    $property = $iblock
        ->addProperty('Тип')
        ->code('TYPE')
        ->typeDropdown();
    $property->addEnum('Новость дня')->xmlId('news_day');
    $property->addEnum('Акция')->xmlId('news_action');
    
    // Добавление свойства разделов типа 'Строка'
    $iblock
        ->addSectionField('uf_reference')
        ->label(['ru' => 'Внешняя ссылка'])
        ->type(UserField::TYPE_STRING);

    // Добавление свойства разделов типа 'Да/Нет'
    $iblock
        ->addSectionField('uf_show_advert')
        ->label(['ru' => 'Отображать рекламный блок'])
        ->type(UserField::TYPE_BOOLEAN);

    // Добавление свойства разделов типа 'Список'
    $field = $iblock
        ->addSectionField('uf_display_type')
        ->sort(10)
        ->label(['ru' => 'Тип отображения'])
        ->type(UserField::TYPE_ENUMERATION);

    $field->addEnum('Таблица')->xmlId('table');
    $field->addEnum('Строки')->xmlId('lines');
    $field->addEnum('Только заголовки')->xmlId('titles');
});
```

Метод ```createIblock()``` возвращает объект типа ```Iblock``` и принимает два аргумента:
* ```type``` - строковый идентификатор типа инфоблока
* ```callback``` - функция обратного вызова, принимает аргумент объект типа ```Iblock```. 
Используется для настройки инфоблока и добавления своиств

Методы класса ```Iblock```:
* ```sort(int)``` - устанавливает значение сортировки. Принимает значение типа ```int```
* ```siteId(string)``` - устанавливает строковый идентификатор сайта. Принимает значение типа ```string```
* ```code(string)``` -  устанавливает символьный код инфоблока. Принимает значение типа ```string```
* ```groupId(array)``` - настройки доступа. Принимает значение типа ```array```
* ```addProperty(string)``` - добавление свойства. Принимает значение типа ```string```. Возвращает объект типа ```Property```
    * ```typeString()``` - свойство типа 'Строка'
    * ```typeDropdown()``` - свойство типа 'Список'
    * ```typeDropdown(iblockId)``` - свойство типа 'Привязка к элементам'
    * ```typeHtml()``` - свойство типа 'HTML/текст'
    * ```typeFile()``` - свойство типа 'HTML/текст'
        * ```->fileType(string)``` - строковое значение типа файлов ```'png, jpg, gif'```
    * ```typeDateTime()``` - свойство типа 'Дата/Время'
    * ```typeDate()``` - свойство типа 'Дата'
    * ```typeVideo()``` - свойство типа 'Видео'
    * ```typeVideo()``` - свойство типа 'Видео'
    * ```typeCheckbox()``` - свойство типа 'Checkbox'
* ```addSectionField(name)``` - добавление свойства разделов. Принимает значение типа ```string```. Возвращает объект типа ```UserField```
    * ```label(name)``` - строковое название поля. Принимает значение типа ```string```
    * ```type(type)``` - строковое значение типа. Принимает значение типа ```string```
    * ```required(boolean)``` - устанавливает обязательность поля. Принимает значение типа ```boolean```
    * ```multiple(boolean)``` - устанавливает множественное значение для поля. Принимает значение типа ```boolean```

Также для настройки инфоблока можно воспользоватся методом setAttribute()

##### Обновление инфоблока

Пример обновления типа инфоблока с помощью объекта ```IblockBuilder```
```php
<?php

use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\IblockBuilder;

$builder = new IblockBuilder();
$builder->updateIblock($id, function (Iblock $iblock) {
    $iblock
        ->addProperty('Страна')
        ->code('COUNTRY')
        ->typeElement($iblock->getId());
});
```

##### Обновление инфоблока по ссылке

```php
<?php

$builder = new \WS\ReduceMigrations\Builder\IblockBuilder();
$builder->updateIblockByPointer(
    \WS\ReduceMigrations\Builder\IblockPointer::byCode(DOMAIN_IBLOCK_NEWS),
    function (\WS\ReduceMigrations\Builder\Entity\Iblock $iblock) {
        $prop = $iblock->addProperty('Тип')
            ->code('type')
            ->typeDropdown();

        $prop->addEnum('Главная новость')->xmlId('main');
        $prop->addEnum('Срочная новость')->xmlId('hot');
        $prop->addEnum('Эксклюзив')->xmlId('exclusive');
    }
);
```

### 2. Работа с таблицами. ```TableBuilder``` <a name="TableBuilder"></a>

##### Добавление таблицы

```php
<?php

use WS\ReduceMigrations\Builder\Entity\Table;
use WS\ReduceMigrations\Builder\TableBuilder;

$tableBuilder = new TableBuilder();
$tableBuilder->create('favorite_table', function (Table $table) {
    $table->integer('ID')
        ->autoincrement()
        ->primary();
    $table->string('NAME');
    $table->integer('ELEMENT_ID')
        ->required()
        ->unique();
    $table->datetime('DATE_CREATE')->autoincrement();
});
```
Метод ```create()``` возвращает объект типа ```Table``` и принимает два аргумента:
* ```type``` - строковое название таблицы в БД
* ```callback``` - функция обратного вызова, принимает аргумент объект типа ```Table```. 

Методы класса ```Table```:
* ```integer(name)``` - добавляет поле типа ```int```. Принимает значение типа ```string```
* ```string(name)``` - добавляет поле типа ```string```. Принимает значение типа ```string```
* ```text(name)``` - добавляет поле типа ```text```. Принимает значение типа ```string```
* ```datetime(name)``` - добавляет поле типа ```datetime```. Принимает значение типа ```string```
* ```boolean(name)``` - добавляет поле типа ```boolean```. Принимает значение типа ```string```
* ```float(name)``` - добавляет поле типа ```float```. Принимает значение типа ```string```
* ```date(name)``` - добавляет поле типа ```date```. Принимает значение типа ```string```

Перечисленные методы классса создают и возвращают объект типа ```FieldWrapper```. Спомошью объекта ```FieldWrapper``` 
можно задавать надстройки для полей: 

* ```primary()``` - устанавливает идентификатор записи (первичный ключ)
* ```autoincrement()``` - автоматическая запись уникального значения
* ```unique()``` - уникальность значения поля
* ```required()``` - определяет обязательность заполнения

##### Удаление таблицы

```php
<?php

use WS\ReduceMigrations\Builder\TableBuilder;

$tableBuilder = new TableBuilder();
$tableBuilder->drop('favorite_table');
```

Метод ```drop(name)``` удаляет таблицу из базы данных по названию таблицы
 
##### Добавление колонки в таблицу

```php
<?php

use WS\ReduceMigrations\Builder\TableBuilder;

$tableBuilder = new TableBuilder();
$tableBuilder->addColumn('favorite_table', 'user_id', 'int');
```

Метод ```addColumn(table, name, type)``` добавляет колонку в таблицу, принимает следующие аргументы:
* ```table``` - строковое название таблицы в базе данных
* ```name``` - строковое название поля
* ```type``` - строковое название типа поля (```text, varchar(100), date```)

##### Удаление колонки из таблицы

```php
<?php

use WS\ReduceMigrations\Builder\TableBuilder;

$tableBuilder = new TableBuilder();
$tableBuilder->dropColumn('favorite_table', 'user_id');
```

Метод ```dropColumn(table, name)``` удаляет колонку из таблицы базы данных:
* ```table``` - строковое название таблицы в базе данных
* ```name``` - строковое название поля

### 3. Работа с формами. ```FormBuilder``` <a name="FormBuilder"></a>

##### Добавление формы

```php
<?php

use WS\ReduceMigrations\Builder\FormBuilder;
use WS\ReduceMigrations\Builder\Entity\Form;
use WS\ReduceMigrations\Builder\Entity\FormField;

$builder = new FormBuilder();
$newForm = $builder->addForm('bids_form', 'bids_form', function (Form $form) {
      $form
          ->arSiteId(['s1'])
          ->sort(100)
          ->description('Description text')
          ->useCaptcha(true)
          ->arGroup(['2' => 10])
          ->arMenu(['ru' => 'Заявка посетителя', 'en' => 'Bid visitor'])
          ->descriptionType('html');

    $form
        ->addField('question')
        ->fieldType(FormField::FIELD_TYPE_INTEGER)
        ->sort(33)
        ->active(false)
        ->required()
        ->title('title')
        ->arFilterAnswerText(['dropdown'])
        ->arFilterAnswerValue(['dropdown'])
        ->arFilterUser(['dropdown'])
        ->arFilterField(['integer'])
        ->comments('comment')
        ->addAnswer('Привет мир!');

    $form
        ->addField('testField')
        ->asField()
        ->title('test')
    ;

    $form
        ->addStatus('status')
        ->arGroupCanDelete([2])
        ->byDefault(true);
});
```
Метод ```addForm()``` создает и возвращает объект типа ```Form```.

Аргументы метода:
* ```name``` - строковое название формы
* ```callback``` - функция обратного вызова, принимает аргумент типа ```Form```.

Описание методов класса ```Form```:
* ```arSiteId(array)``` - устанавливает привязку к сайтам. Принимает значение типа ```array```
* ```sort(int)``` - устанавливает сортировку формы. Принимает значение типа ```int```
* ```description(string)``` - устанавливает описание формы. Принимает значение типа ```string```
* ```descriptionType(string)``` - устанавливает тип описания формы. Принимает значение типа ```string```
* ```useCaptcha(boolean)``` - использавать captcha. Принимает значение типа ```boolean```
* ```arGroup(array)``` - настройки доступа. Принимает значение типа ```array```
* ```arMenu(array)``` - настройка пуктов меню в административном разделе. Принимает значение типа ```array```
* ```addField(name)``` - создает поле формы. Принимает значение типа ```string```. Возвращает объект типа ```FormField```
* ```statEvent1(string)``` - устанавливает идентификатор события. Принимает значение типа ```string```
* ```setAttribute(name, value)``` - устанавливает значение формы по идентификатору события.

Описание методов класса ```FormField```
* ```fieldType(string)``` - задает тип поля формы (```FormField::FIELD_TYPE_INTEGER, FormField::FIELD_TYPE_TEXT, 
FormField::FIELD_TYPE_DATE```)
* ```required()``` - устанавливает обязательность поля
* ```active(boolean)``` - устанавливает активность поля. Принимает значение типа ```boolean```
* ```title(string)``` - устанавливает заголовок поля. Принимает значение типа ```string```
* ```comments(string)``` - устанавливает служебный комментарий. Принимает значение типа ```string```
* ```arFilterField(array)``` - устанавливает фильтры поля. Принимает значение типа ```array```
* ```addAnswer(string)``` - добавление вопроса. Принимает значение типа ```string```. Возвращает объект
 типа ```FormAnswer```
* ```arFilterUser(array)``` - устанавливает фильтры для вводимого значения. Принимает значение типа ```array```
* ```arFilterAnswerText(array)```  - устанавливает значения для параметра [ANSWER_TEXT]. Принимает значение типа ```array```
* ```arFilterAnswerValue(array)```  - устанавливает значения для параметра [ANSWER_VALUE]. Принимает значение типа ```array```
* ```filterTitle(string)```  - устанавливает подпись к полю фильтра. Принимает значение типа ```string```
* ```inResultsTable(boolean)```  - устанавливает флаг показать в HTML-таблице результатов. Принимает значение типа ```boolean```
* ```inExcelTable(boolean)```  - устанавливает флаг показать в Excel-таблице результатов. Принимает значение типа ```boolean```

##### Обновление формы

```php
<?php

use WS\ReduceMigrations\Builder\FormBuilder;
use WS\ReduceMigrations\Builder\Entity\Form;

$builder = new FormBuilder();
$updatedForm = $builder->updateForm('bids_form', function (Form $form) {
    $form->name('Заявка');
    $field = $form
        ->updateField('question')
        ->active(true)
        ->required(false);

    $field->removeAnswer('Привет мир!');

    $field
        ->addAnswer('Ваше имя')
        ->value('name');
});
```
Метод ```updateForm(sid, name)``` используется для обнавления параметров формы:
* ```sid``` - строковый идентификатор формы
* ```callback``` - функция обратного вызова, принимает аргумент типа ```Form```.

##### Удаление формы

```php
<?php

use WS\ReduceMigrations\Builder\FormBuilder;

$builder = new FormBuilder();
$builder->removeForm('bids_form');
```
Метод ```removeForm(sid)``` используется для удаления формы:
* ```sid``` - строковый идентификатор формы


### 4. Работа c событиями. ```EventsBuilder``` <a name="EventsBuilder"></a>

##### Добавление события

```php
<?php

use WS\ReduceMigrations\Builder\EventsBuilder;
use WS\ReduceMigrations\Builder\Entity\EventType;
use WS\ReduceMigrations\Builder\Entity\EventMessage;

$builder = new EventsBuilder();
$builder->createEventType('NEW_ADD_ACTION', 'ru', function (EventType $event) {
    $event
        ->name('Новая заявка')
        ->sort(10)
        ->description('#ACTION# - action');
    
    // Добавление шаблона
    $event
        ->addEventMessage('#EMAIL_FROM#', '#EMAIL_TO#', 's1')
        ->subject('Новая заявка')
        ->body('Поступила новая заявка #ACTION#!')
        ->bodyType(EventMessage::BODY_TYPE_HTML)
        ->active();
});
```
Метод ```createEventType()``` создает и возвращает объект типа ```EventType```.
Аргументы метода:
* ```type``` - строковое значение типа события
* ```lid``` - строковый идентификатор языка сайта
* ```callback``` - функция обратного вызова, принимает аргумент типа ```EventType```.

Описание методов класса ```EventType```:
* ```name(string)``` - устанавливает значение имя события. Принимает значение типа ```string```
* ```sort(int)``` - устанавливает значение сортировки. Принимает значение типа ```int```
* ```description(string)``` - описание события. Принимает значение типа ```string```
* ```addEventMessage(string)``` - описание события. Принимает значение типа ```string```. Возвращает объект типа ```EventMessage```

Описание методов класса ```EventMessage```:
* ```subject(string)``` - устанавливает тему сообщения. Принимает значение типа ```string```
* ```body(string)``` - устанавливает текст сообщения. Принимает значение типа ```string```
* ```bodyType(string)``` - устанавливает тип текста сообщения. Принимает значение типа ```string``` 
(```EventMessage::BODY_TYPE_HTML, EventMessage::BODY_TYPE_TEXT```)
* ````active(bolean)```` - устанавливает активность шаблона. Принимает значение типа ```boolean```

##### Обновление события

```php
<?php

use WS\ReduceMigrations\Builder\EventsBuilder;
use WS\ReduceMigrations\Builder\Entity\EventType;

$builder = new EventsBuilder();
$builder->updateEventType('NEW_ADD_ACTION', 'ru', function (EventType $type) {
    $type->name('Новая заявка');
    foreach ($type->loadEventMessages() as $message) {
        $message->bcc('#BCC#');
    }
});
```
Метод ```updateEventType()``` обновляет и возвращает объект типа ```EventType```.
Аргументы метода:
* ```type``` - строковое значение типа события
* ```lid``` - строковый идентификатор языка сайта
* ```callback``` - функция обратного вызова, принимает аргумент типа ```EventType```.

Описание методов класса ```EventMessage```:
* ```loadEventMessages()``` - возвращает все шаблоны события

### 5. Работа c агентами. ```AgentBuilder``` <a name="AgentBuilder"></a>

##### Добавление агента

```php
<?php

use Bitrix\Main\Type\DateTime;
use WS\ReduceMigrations\Builder\AgentBuilder;
use WS\ReduceMigrations\Builder\Entity\Agent;

$date = new DateTime();
$date->add('+1 day');
$builder = new AgentBuilder();
$obAgent = $builder->addAgent('abs(0);', function (Agent $agent) use ($date) {
    $agent
        ->sort(23)
        ->active(true)
        ->nextExec($date);
});
```
Метод ```addAgent()``` добавляет и возвращает объект типа ```Agent```.

Аргументы метода:
* ```agetFunction``` - строковое значение функции агента. Принимает значение типа ```string```
* ```callback``` - функция обратного вызова, принимает аргумент типа ```Agent```.

Описание метов класса ```Agent```:
* ```sort(int)``` - устанавливает значение сортировки. Принимает значение типа ```int```
* ````active(bolean)```` - устанавливает активность агента. Принимает значение типа ```boolean```
* ````nextExec(date)```` - устанавливает дату следующего запуска. Принимает значение типа ```date```

##### Добавление агента

```php
<?php

use WS\ReduceMigrations\Builder\AgentBuilder;
use WS\ReduceMigrations\Builder\Entity\Agent;

$builder = new AgentBuilder();
$builder->updateAgent('abs(0);', function (Agent $agent) {
    $agent
        ->active(false)
        ->isPeriod(true);
});
```
Метод ```updateAgent()``` обновляет и возвращает объект типа ```Agent```.

### 6. Работа c HighLoad инфоблоками. ```HighLoadBlockBuilder``` <a name="HighLoadBlockBuilder"></a>

##### Добавление инфоблока

```php
<?php 

use WS\ReduceMigrations\Builder\HighLoadBlockBuilder;
use WS\ReduceMigrations\Builder\Entity\HighLoadBlock;
use WS\ReduceMigrations\Builder\Entity\UserField;

$builder = new HighLoadBlockBuilder();
$block = $builder->addHLBlock('account_social', 'account_social_highloadblock', function (HighLoadBlock $block) {
    $prop = $block
        ->addField('uf_type')
        ->sort(10)
        ->label(['ru' => 'Тип'])
        ->type(UserField::TYPE_ENUMERATION);

    $prop->addEnum('vk')->xmlId('vk');
    $prop->addEnum('twitter')->xmlId('twitter');

    $block
        ->addField('uf_active')
        ->label(['ru' => 'Активность'])
        ->type(UserField::TYPE_BOOLEAN);

    $block
        ->addField('uf_date_create')
        ->label(['ru' => 'Дата создания'])
        ->type(UserField::TYPE_DATETIME);
    
    $block
        ->addField('uf_reference')
        ->label(['ru' => 'Внешняя ссылка'])
        ->type(UserField::TYPE_STRING);

    $block
        ->addField('uf_icon')
        ->label(['ru' => 'Иконка'])
        ->type(UserField::TYPE_IBLOCK_ELEMENT)
        ->required(true);
});
```
Метод ```addHLBlock()``` добавляет и возвращает объект типа ```HighLoadBlock```.

Аргументы метода:
* ```name``` - строковое значение название инфоблока. Принимает значение типа ```string```
* ```tableName``` - строковое значение название таблицы инфоблока. Принимает значение типа ```string```
* ```callback``` - функция обратного вызова, принимает аргумент типа ```HighLoadBlock```.

Описание методов класса ```HighLoadBlock```:
* ```addField(name)``` - строковое название поля в таблице. Принимает значение типа ```string```
* ```label(name)``` - строковое название поля. Принимает значение типа ```string```
* ```type(type)``` - строковое значение типа. Принимает значение типа ```string```. Возвращает объект типа ```UserField```.
* ```required(boolean)``` - устанавливает обязательность поля. Принимает значение типа ```boolean```. Возвращает объект типа ```UserField```.
* ```multiple(boolean)``` - устанавливает множественное значение для поля. Принимает значение типа ```boolean```. Возвращает объект типа ```UserField```.

##### Обновление инфоблока

```php
<?php 

use WS\ReduceMigrations\Builder\HighLoadBlockBuilder;
use WS\ReduceMigrations\Builder\Entity\HighLoadBlock;

$builder = new HighLoadBlockBuilder();
$builder = new HighLoadBlockBuilder();
$block = $builder->updateHLBlock('account_social_highloadblock', function (HighLoadBlock $block) {
    $block->name('TestBlock2');
    $prop = $block
        ->updateField('uf_reference')
        ->multiple(true)
        ->required(true);
    $prop->updateEnum('Тест1')->xmlId('test1');
    $prop->removeEnum('Тест2');
});
```
Метод ```updateHLBlock()``` обновляет и возвращает объект типа ```HighLoadBlock```.
