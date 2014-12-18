<?php
function getBrowser() {
	$browser = array(
		'version'   => '0.0.0',
		'majorver'  => 0,
		'minorver'  => 0,
		'build'     => 0,
		'name'      => 'unknown',
		'useragent' => ''
	);
	
	$browsers = array('firefox', 'msie', 'opera', 'chrome', 'safari', 'mozilla', 'seamonkey', 'konqueror', 'netscape', 'gecko', 'navigator', 'mosaic', 'lynx', 'amaya', 'omniweb', 'avant', 'camino', 'flock', 'aol');
	
	if(isset($_SERVER['HTTP_USER_AGENT'])) {
		$browser['useragent'] = $_SERVER['HTTP_USER_AGENT'];
		$user_agent = strtolower($browser['useragent']);
		foreach($browsers as $_browser) {
			if (preg_match("/($_browser)[\/ ]?([0-9.]*)/", $user_agent, $match)) {
				$browser['name'] = $match[1];
				$browser['version'] = $match[2];
				$versionParts = explode('.', $browser['version']);
				$browser['majorver'] = isset($versionParts[0]) ? $versionParts[0] : '';
				$browser['minorver'] = isset($versionParts[1]) ? $versionParts[1] : '';
				$browser['build'] = isset($versionParts[2]) ? $versionParts[2] : '';
				break;
			}
		}
	}
	return $browser;
}