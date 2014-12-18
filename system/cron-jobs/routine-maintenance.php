<?php
// Since this script is called from the command line
// we need to set the directories so we can load our configuration and mail-queue object.

$webRoot = dirname(dirname(dirname(__FILE__)));
$cwd = dirname(dirname(__FILE__));

require_once $webRoot . '/config.php';
require_once DIR_DATABASE . 'db.php';
require_once DIR_DATABASE . 'redis.php';
require_once DIR_ADMIN . 'model/config/config-main.php';
require_once DIR_SYSTEM . 'startup.php';
require_once DIR_ADMIN . 'model/common/backup.php';

// Prune active user list
$minimumActiveTime = time() - (int)$globalSettings['active_user_limit'];

$rdh = $redis->loadRedisHandler();

$rdh->zremrangebyscore(PREFIX . 'frontend-anon-users-online', 0, $minimumActiveTime);
$rdh->zremrangebyscore(PREFIX . 'frontend-logged-users-online', 0, $minimumActiveTime);
$rdh->zremrangebyscore(PREFIX . 'backend-logged-users-online', 0, $minimumActiveTime);
