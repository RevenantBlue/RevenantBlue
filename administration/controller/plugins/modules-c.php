<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'model/modules/modules-main.php';

$modules = new Modules;

$moduleList = $modules->loadModules();
