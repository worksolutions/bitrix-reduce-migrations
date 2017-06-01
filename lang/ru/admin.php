<?php
return array(
    'main' => array(
        'title' => 'Обновление платформы',
        'list' => array(
            'scenarios' => 'Сценарии'
        ),
        'priority' => array(
            'priority' => 'Приоритет миграции:',
            'high' => 'Высокий:',
            'medium' => 'Средний:',
            'optional' => 'Опциональный:',
        ),
        'skipOptional' => 'Пропустить миграции типа "Опциональный"',
        'errorList' => 'Ошибки',
        'appliedList' => 'Список обновлений',
        'approximatelyTime' => 'Примерное время применения миграций:',
        'timeLang' => array(
            'minutes' => 'мин.',
            'seconds' => 'сек.'
        ),
        'btnRollback' => 'Отменить последнее обновление',
        'btnApply' => 'Обновить',
        'lastSetup' => array(
            'sectionName' => 'Последнее обновление :time: - :user:'
        ),
        'common' => array(
            'listEmpty' => 'Список пуст',
            'reference-fix' => 'Синхронизация связей',
            'pageEmpty' => 'Данных для обновления пока нет'
        ),
        'newChangesDetail' => 'подробно',
        'newChangesTitle' => 'Список изменений',
        'errorWindow' => 'Информация об ошибке',
        'diagnostic' => 'Ошибки <a href=":url:">диагностирования</a>, применение миграций возможно только после исправления',
        'platformVersion' => array(
            'ok' => 'Владелец платформы',
            'error' => 'Не установлен владелец платформы',
            'setup' => 'Установить',
        )
    ),
    'applyError' => array(
        'message' => 'Сообщение',
        'data' => 'Данные',
        'trace' => 'Стек вызова',
        'error' => array(
            'modelNotExists' => 'Данных по записи id=:id: не существует'
        )
    ),
    'createScenario' => array(
        'title' => 'Сценарий обновления',
        'field' => array(
            'name' => 'Название',
            'priority' => 'Приоритет',
            'time' => 'Примерное время выполнения миграции(секунды)',
        ),
        'priority' => array(
            'high' => 'Высокий',
            'medium' => 'Средний',
            'optional' => 'Опциональный',
        ),
        'path-to-file' => 'Класс миграции находится в файле #path#',
        'save-file-error' => 'Ошибка сохранения файла',
        'button' => array(
            'create' => 'Создать сценарий'
        )
    ),
    'log' => array(
        'title' => 'Журнал обновлений',
        'fields' => array(
            'updateDate' => 'Дата',
            'description' => 'Состав обновления',
            'hash' => 'Хэш миграции',
            'owner' => 'Владелец',
            'dispatcher' => 'Обновил'
        ),
        'messages' => array(
            'InsertReference' => 'Вставка ссылки стороннего источника',
            'view' => 'Анализ изменений',
            'pages' => 'Страницы',
            'actualization' => 'Актуализация источников',
            'descriptionMoreLink' => 'подробно',
            'errorWindow' => 'Информация об ошибке'
        )
    ),
    'detail' => array(
        'title' => '#date. #source. Обновил - #deployer',
        'tabs' => array(
            'diff' => 'Изменения',
            'final' => 'Данные обновления',
            'merge' => 'Данные до обновления'
        ),
        'message' => array(
            'nobody' => 'Обновление площадки еще не произошло',
            'show' => 'показать данные',
            'hide' => 'скрыть данные',
        ),
        'serviceLabels' => array(
            '~reference' => 'HASH',
            '~property_list_values' => 'VALUES',
            'Reference fix' => 'Регистрация ссылки сущности со стороней платформы',
            'Insert reference' => 'Новая ссылка сущности',
            'reference' => 'HASH',
            'group' => 'Группа сущности (обработчик)',
            'dbVersion' => 'Версия платфомы'
        )
    ),
    'cli' => array(
        'common' => array(
            'reference-fix' => 'Синхронизация связей'
        ),
    ),
);
