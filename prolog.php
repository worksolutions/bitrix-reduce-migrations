<?php
CModule::IncludeModule('ws.migrations');
define("ADMIN_MODULE_NAME", \WS\ReduceMigrations\Module::getName());
CJSCore::Init(array('window', 'jquery', 'dialog'));