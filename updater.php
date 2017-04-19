<?php
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CUpdater $updater */
$updater;
/**
 * Error message for processing update
 * @var string $errorMessage
 */
$fAddErrorMessage = function ($mess) use ($updater){
    $updater->errorMessage[] = $mess;
};
//=====================================================

$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/';
// install file platform version
$uploadDir = $docRoot . \COption::GetOptionString("main", "upload_dir", "upload");
$modulePath = rtrim($docRoot, '/').$updater->kernelPath.'/modules/'.$updater->moduleID;
$updatePath = $docRoot.$updater->curModulePath;

$isInstalled = \Bitrix\Main\ModuleManager::isModuleInstalled($updater->moduleID);