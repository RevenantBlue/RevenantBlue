<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/plugins/plugins-main.php';

$plugins = new Plugins;

$pluginList = $plugins->loadPlugins();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'plugins' && !isset($_GET['plugin'])) {
			$optionsForPage = $users->getOptionsByGroup('plugins');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'plugins');
		}
	}
}
