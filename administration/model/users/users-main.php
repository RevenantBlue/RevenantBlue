<?php
namespace RevenantBlue\Admin;
use RevenantBlue;
use \PDO;
use \RedisConnection;

class Users extends RevenantBlue\Db {

	public  $whiteList = array(
		'id'
	  , 'first_name'
	  , 'last_name'
	  , 'username'
	  , 'email'
	  , 'activated'
	  , 'enabled'
	  , 'date_joined'
	  , 'last_login'
	  , 'role'
	);
	private $fullKey;
	private $usersTable;
	private $rolesTable;
	private $permsTable;
	private $userRolesTable;
	private $optionsTable;
	private $userOptionsTable;
	private $sessionsTable;
	private $userFriendsTable;
	private $inboxMsgsTable;
	private $sentMsgsTable;
	private $notificationsTable;
	private $notifyOptsTable;
	private $userNotifyOptsTable;
	private $forumModsTable;
	private $accountCodesTable;
	private $failedLoginsTable;


	public function __construct() {
		// Build the key for hasing passwords.
		$this->fullKey = USERS_KEY;
		
		$this->usersTable = PREFIX . 'users';
		$this->rolesTable = PREFIX . 'acl_roles';
		$this->permsTable = PREFIX . 'acl_permissions';
		$this->userRolesTable = PREFIX . 'acl_user_roles';
		$this->optionsTable = PREFIX . 'options';
		$this->userOptionsTable = PREFIX . 'users_options_to_display';
		$this->sessionsTable = PREFIX . 'sessions';
		$this->userFriendsTable = PREFIX . 'user_friends';
		$this->inboxMsgsTable = PREFIX . 'private_messages_inbox';
		$this->sentMsgsTable = PREFIX . 'private_messages_sent';
		$this->notificationsTable = PREFIX . 'user_notifications';
		$this->notifyOptsTable = PREFIX . 'notification_options';
		$this->userNotifyOptsTable = PREFIX . 'user_notification_options';
		$this->forumModsTable = PREFIX . 'forum_moderators';
		$this->accountCodesTable = PREFIX . 'users_account_codes';
		$this->failedLoginsTable = PREFIX . 'failed_logins';
	}

	public function loginUser($username, $password) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT id, username, AES_DECRYPT(password, :key) AS password, AES_DECRYPT(salt, :key) AS salt, enabled, activated
					  FROM $this->usersTable
					  WHERE username = :username AND password = AES_ENCRYPT(:password, :key)";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':password', $password, PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->execute();
			$login = $stmt->fetch(PDO::FETCH_ASSOC);
			return $login;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserSession($username) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT session_id FROM $this->sessionsTable
					  INNER JOIN $this->usersTable ON sessions.user_id = $this->usersTable.id
					  WHERE username=:username";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['session_id'];
		} catch(PDOException $e) {
		 	$this->errorLog($e);
		}
	}

	public function loadUserOverview($limit, $offset, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT id, first_name, last_name, username, email, ip, date_joined, last_login, enabled, activated
					  FROM $this->usersTable
					  ORDER BY $orderBy $sort
					  LIMIT :limit
					  OFFSET :offset";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countUserOverview() {
		if(!self::$dbh) $this->connect();
		$query = "SELECT COUNT(*) AS total_records FROM $this->usersTable";
		$stmt = self::$dbh->prepare($query);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result['total_records'];
	}

	public function loadUserOverviewByUserSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$query = "SELECT $this->usersTable.id, first_name, last_name, username, email, ip, date_joined, last_login, enabled, activated
					  FROM $this->usersTable
					  WHERE username LIKE :searchWord
					  ORDER BY $orderBy $sort
					  LIMIT :limit
					  OFFSET :offset";
			$stmt = self::$dbh->prepare($query);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countUserSearch($searchWord) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS total_records
				 FROM $this->usersTable
				 WHERE username LIKE :searchWord"
			);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserOverviewStatusFilter($limit, $offset, $enabledStatus, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT id, first_name, last_name, username, email, ip, date_joined, last_login, enabled, activated
				 FROM $this->usersTable
				 WHERE enabled = :enabled
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':enabled', $enabledStatus, PDO::PARAM_BOOL);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countUserStatus($status) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS total_records
				 FROM $this->usersTable
				 WHERE enabled = :enabled"
			);
			$stmt->bindParam(':enabled', $status, PDO::PARAM_BOOL);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserOverviewActivationFilter($limit, $offset, $activationStatus, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT id, first_name, last_name, username, email, ip, date_joined, last_login, enabled, activated
				 FROM $this->usersTable
				 WHERE activated = :activated
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':activated', $activationStatus, PDO::PARAM_BOOL);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countUserActivation($activationStatus) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS total_records
				 FROM $this->usersTable
				 WHERE activated = :activated"
			);
			$stmt->bindParam(':activated', $activationStatus, PDO::PARAM_BOOL);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserOverviewRoleFilter($limit, $offset, $roleId, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT u.id, first_name, last_name, username, email, ip, date_joined, last_login, enabled, activated
				 FROM $this->usersTable AS u
				 INNER JOIN $this->userRolesTable AS ur ON ur.user_id = u.id
				 WHERE ur.role_id = :roleId
				 ORDER BY u.$orderBy $sort
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countUserRoleFilter($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS total_records
				 FROM $this->usersTable AS u
				 INNER JOIN $this->userRolesTable AS r ON r.user_id = u.id
				 WHERE r.role_id = :roleId"
			);
			$stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['total_records'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadUserOptionsByGroup($userId, $optionGroup) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT uo.option_id FROM $this->optionsTable AS o
				 LEFT JOIN $this->userOptionsTable as uo ON o.id = uo.option_id
				 WHERE o.option_group = :optionGroup AND uo.user_id = :userId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':optionGroup', $optionGroup, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadNotificationsForUser($userId, $limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->notificationsTable
				 WHERE user_id = :userId
				 ORDER BY date_created DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countNotificationsForUser($userId, $onlyUnread = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($onlyUnread === TRUE) {
				$stmt = self::$dbh->prepare(
					"SELECT COUNT(*) AS num_of_notifications FROM $this->notificationsTable WHERE user_id = :userId AND is_read != 1"
				);
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT COUNT(*) AS num_of_notifications FROM $this->notificationsTable WHERE user_id = :userId"
				);
			}
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['num_of_notifications'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadInboxMessagesForUser($userId, $limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT u.username_alias, u.avatar, u.avatar_small, im.* FROM $this->inboxMsgsTable AS im
				 LEFT JOIN $this->usersTable as u ON im.sender_id = u.id
				 WHERE recipient_id = :userId
				 ORDER BY date_sent DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countInboxMessagesForUser($userId, $onlyUnread = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($onlyUnread === TRUE) {
				$stmt = self::$dbh->prepare(
					"SELECT COUNT(*) AS num_of_msgs FROM $this->inboxMsgsTable
					 WHERE recipient_id = :userId AND is_read = 0"
				);
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT COUNT(*) AS num_of_msgs FROM $this->inboxMsgsTable
					 WHERE recipient_id = :userId"
				);
			}
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['num_of_msgs'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function loadSentMessagesForUser($userId, $limit, $offset) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->sentMsgsTable
				 WHERE sender_id = :userId
				 ORDER BY date_sent DESC
				 LIMIT :limit
				 OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countSentMessagesForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT COUNT(*) AS numOfMsgs FROM $this->sentMsgsTable
				 WHERE sender_id = :userId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return (int)$result['numOfMsgs'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertUser($user) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->usersTable
					(first_name, last_name, username, username_alias, password, email, enabled, activated, system_email, ip, date_joined, salt)
				 VALUES
					(:firstName, :lastName, :username, :usernameAlias, AES_ENCRYPT(:pass, :key), :email, :enabled, :activated, :systemEmail, :ip, NOW(), AES_ENCRYPT(:salt, :key))"
			);
			$stmt->bindParam(':firstName', $user['firstName'], PDO::PARAM_STR);
			$stmt->bindParam(':lastName', $user['lastName'], PDO::PARAM_STR);
			$stmt->bindParam(':username', $user['username'], PDO::PARAM_STR);
			$stmt->bindParam(':usernameAlias', $user['usernameAlias'], PDO::PARAM_STR);
			$stmt->bindParam(':pass', $user['password'], PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);
			$stmt->bindParam(':enabled', $user['enabled'], PDO::PARAM_BOOL);
			$stmt->bindParam(':activated', $user['activated'], PDO::PARAM_INT);
			$stmt->bindParam(':systemEmail', $user['systemEmail'], PDO::PARAM_INT);
			$stmt->bindParam(':ip', $user['ip'], PDO::PARAM_STR);
			$stmt->bindParam(':salt', $user['salt'], PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertUserOption($userId, $optionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT IGNORE INTO $this->userOptionsTable
				   (user_id, option_id)
				 VALUES
				   (:userId, :optionId)"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':optionId', $optionId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertNotification($userId, $notification) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->notificationsTable
					(user_id, notification_content, date_created) 
				 VALUES
					(:userId, :notification, NOW())"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notification', $notification, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertPrivateMessage($message, $skipSentMsg = FALSE, $isFriendReq = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			// Insert into the inbox table
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->inboxMsgsTable
					(sender_id, recipient_id, msg_subject, msg_content, date_sent, sender_username, recipient_username, friend_request)
				 VALUES
					(:senderId, :recipientId, :subject, :content, NOW(), :senderUsername, :recipientUsername, :friendRequest)"
			);
			$stmt->bindParam(':senderId', $message['senderId'], PDO::PARAM_INT);
			$stmt->bindParam(':recipientId', $message['recipientId'], PDO::PARAM_INT);
			$stmt->bindParam(':subject', $message['subject'], PDO::PARAM_STR);
			$stmt->bindParam(':content', $message['content'], PDO::PARAM_STR);
			$stmt->bindParam(':senderUsername', $message['senderUsername'], PDO::PARAM_STR);
			$stmt->bindParam(':recipientUsername', $message['recipientUsername'], PDO::PARAM_STR);
			$stmt->bindValue(':friendRequest', $isFriendReq, PDO::PARAM_INT);
			$stmt->execute();
			// Insert into the sent message table
			if($skipSentMsg === FALSE) {
				$stmt2 = self::$dbh->prepare(
					"INSERT INTO $this->sentMsgsTable
						(sender_id, recipient_id, msg_subject, msg_content, date_sent, sender_username, recipient_username)
					 VALUES
						(:senderId, :recipientId, :subject, :content, NOW(), :senderUsername, :recipientUsername)"
				);
				$stmt2->bindParam(':senderId', $message['senderId'], PDO::PARAM_INT);
				$stmt2->bindParam(':recipientId', $message['recipientId'], PDO::PARAM_INT);
				$stmt2->bindParam(':subject', $message['subject'], PDO::PARAM_STR);
				$stmt2->bindParam(':content', $message['content'], PDO::PARAM_STR);
				$stmt2->bindParam(':senderUsername', $message['senderUsername'], PDO::PARAM_STR);
				$stmt2->bindParam(':recipientUsername', $message['recipientUsername'], PDO::PARAM_STR);
				$stmt2->execute();
				$sentMsgId = self::$dbh->lastInsertId();
			}
			self::$dbh->commit();
			if(!empty($sentMsgId)) {
				return $sentMsgId;
			}
		} catch(PDOException $e) {
			self::$dbh->rollback();
			$this->errorLog($e);
		}
	}

	public function insertNotificationOption($userId, $notificationId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT IGNORE INTO $this->userNotifyOptsTable
					(user_id, notification_id)
				 VALUES
					(:userId, :notificationId)"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertUserCode($userId, $code, $type) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->accountCodesTable
					(user_id, user_code, type, date_created)
				 VALUES
					(:userId, :code, :type, NOW())"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':code', $code, PDO::PARAM_STR);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function insertFailedLogin($username, $location, $ip) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->failedLoginsTable
				  (login_username, login_date, login_location, login_ip)
				 VALUES
				  (:username, NOW(), :location, :ip)"
			);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':location', $location, PDO::PARAM_STR);
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateUser($user) {
		try {
			if(!self::$dbh) $this->conect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->usersTable
				 SET first_name = :firstName
				 , last_name = :lastName
				 , username = :username
				 , email = :email
				 , enabled = :enabled
				 , system_email = :systemEmail
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
			$stmt->bindParam(':firstName', $user['firstName'], PDO::PARAM_STR);
			$stmt->bindParam(':lastName', $user['lastName'], PDO::PARAM_STR);
			$stmt->bindParam(':username', $user['username'], PDO::PARAM_STR);
			$stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);
			$stmt->bindParam(':enabled', $user['enabled'], PDO::PARAM_BOOL);
			$stmt->bindParam(':systemEmail', $user['systemEmail'], PDO::PARAM_BOOL);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateForumProfile($profileArr) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->usersTable
				 SET timezone           = :timezone
				   , topic_post_privacy = :topicPostPrivacy
				   , show_friends       = :showFriends
				   , authorize_friends  = :authorizeFriends
				   , birthday           = :birthday
				   , gender             = :gender
				   , location           = :location
				   , interests          = :interests
				   , about_me           = :aboutMe
				  WHERE id = :id"
			);
			$stmt->bindParam(':id', $profileArr['id'], PDO::PARAM_INT);
			$stmt->bindParam(':timezone', $profileArr['timezone'], PDO::PARAM_INT);
			$stmt->bindParam(':topicPostPrivacy', $profileArr['showPostsTopics'], PDO::PARAM_INT);
			$stmt->bindParam(':showFriends', $profileArr['showFriends'], PDO::PARAM_INT);
			$stmt->bindParam(':authorizeFriends', $profileArr['authorizeFriends'], PDO::PARAM_INT);
			$stmt->bindParam(':birthday', $profileArr['birthday'], PDO::PARAM_STR);
			$stmt->bindParam(':gender', $profileArr['gender'], PDO::PARAM_STR);
			$stmt->bindParam(':location', $profileArr['location'], PDO::PARAM_STR);
			$stmt->bindParam(':interests', $profileArr['interests'], PDO::PARAM_STR);
			$stmt->bindParam(':aboutMe', $profileArr['aboutMe'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateContacts($userId, $contacts) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable
					  SET aim_contact        = :aim
						, facebook_contact   = :facebook
						, googleplus_contact = :googleplus
						, msn_contact        = :msn
						, skype_contact      = :skype
						, twitter_contact    = :twitter
						, website_contact    = :website
					  WHERE id = :id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':aim', $contacts['aim'], PDO::PARAM_INT);
			$stmt->bindParam(':facebook', $contacts['facebook'], PDO::PARAM_STR);
			$stmt->bindParam(':googleplus', $contacts['googleplus'], PDO::PARAM_STR);
			$stmt->bindParam(':msn', $contacts['msn'], PDO::PARAM_STR);
			$stmt->bindParam(':skype', $contacts['skype'], PDO::PARAM_STR);
			$stmt->bindParam(':twitter', $contacts['twitter'], PDO::PARAM_STR);
			$stmt->bindParam(':website', $contacts['website'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateNotificationOption($userId, $notificationId, $type, $value) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->userNotifyOptsTable SET $type = :value
				 WHERE user_id = :userId AND notification_id = :notificationId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->bindParam(':value', $value, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt  = self::$dbh->prepare("DELETE FROM $this->usersTable WHERE id = :id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->userRolesTable WHERE user_id = :id");
			$stmt2->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt2->execute();
			$stmt3 = self::$dbh->prepare("DELETE FROM $this->userOptionsTable WHERE user_id = :id");
			$stmt3->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt3->execute();
			$stmt4 = self::$dbh->prepare("DELETE FROM $this->forumModsTable WHERE user_id = :id");
			$stmt4->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt4->execute();
			$stmt5 = self::$dbh->prepare("DELETE FROM $this->inboxMsgsTable WHERE recipient_id = :id");
			$stmt5->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt5->execute();
			$stmt6 = self::$dbh->prepare("DELETE FROM $this->sentMsgsTable WHERE sender_id = :id");
			$stmt6->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt6->execute();
			$stmt7 = self::$dbh->prepare("DELETE FROM $this->userFriendsTable WHERE user_id = :id");
			$stmt7->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt7->execute();
			$success = self::$dbh->commit();
			return $success;
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function deleteUserOption($userId, $optionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userOptionsTable WHERE option_id = :optionId AND user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':optionId', $optionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteAllOptionsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userOptionsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteNotification($notificationId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->notificationsTable WHERE id = :notificationId AND user_id = :userId");
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteInboxMessage($messageId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->inboxMsgsTable WHERE id = :messageId AND recipient_id = :userId");
			$stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteSentMessage($messageId, $userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->sentMsgsTable WHERE id = :messageId AND sender_id = :userId");
			$stmt->bindParam(':messageId', $messageId, PDO::PARAM_INT);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteNotificationOptionForUser($userId, $notificationId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userNotifyOptsTable WHERE id = :notificationId AND user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteNotificationOptionsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt->prepare("DELETE $this->userNotifyOptsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteCode($userId, $codeType) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->accountCodesTable WHERE user_id = :userId AND :type = type");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':type', $type, PDO::PARAM_STR);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteFailedLogin($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->failedLoginsTable WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function deleteAllFailedLogins() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"DELETE FROM $this->failedLoginsTable"
			);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumOfUsers() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS number_of_users FROM $this->usersTable");
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['number_of_users'];
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getUsernames() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT username FROM $this->usersTable");
			$users = $stmt->execute(PDO::FETCH_ASSOC);
			return $users;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserId($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT id FROM $this->usersTable WHERE username = :username");
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFirstNameById($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT first_name FROM $this->usersTable WHERE id=:id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['first_name'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getLastNameById($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT last_name FROM $this->usersTable WHERE id=:id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['last_name'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getPassword($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT AES_DECRYPT(password, :key) FROM $this->usersTable WHERE username = :username");
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserData($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->usersTable WHERE id=:id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserIp($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT ip FROM $this->usersTable WHERE username = :user");
			$stmt->bindParam(':user', $username, PDO::PARAM_STR);
			$stmt->execute();
			$userIp = $stmt->fetch(PDO::FETCH_ASSOC);
			return $userIp['ip'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSessionForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT session_id FROM $this->sessionsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$sessionId = $stmt->fetch(PDO::FETCH_ASSOC);
			return $sessionId['session_id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSessionByUsername($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT session_id FROM $this->sessionsTable
				 INNER JOIN $this->usersTable ON sessions.user_id = $this->usersTable.id
				 WHERE $this->usersTable.username = :username"
			);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['session_id'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserIdForSession($sessId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT user_id FROM $this->sessionsTable WHERE session_id = :sessId");
			$stmt->bindParam(':sessId', $sessId, PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['user_id'];
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function deleteSession($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->sessionsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function deleteSessionBySessionId($sessionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->sessionsTable WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function updateUserIp($ip, $username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET ip = :ip WHERE username = :username");
			$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getEmail($id) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT email FROM $this->usersTable WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['email'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setEmail($id, $email) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET email = :email WHERE id = :id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSalt($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT AES_DECRYPT(salt, :key) AS salt FROM $this->usersTable WHERE username=:username");
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->execute();
			$salt = $stmt->fetch(PDO::FETCH_ASSOC);
			return $salt['salt'];
		} catch(PDOException $e) {
			$this->errorlog($e);
		}
	}

	public function setSalt($username, $password, $salt) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->usersTable SET salt=AES_ENCRYPT(:salt, :key) WHERE username=:username && password=AES_ENCRYPT(:password, :key)"
			);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':password', $password, PDO::PARAM_STR);
			$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->key1, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorlog($e);
		}
	}

	public function setLastLogin($username) {
		try {
			if(!self::$dbh) $this->connect($username);
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET last_login = NOW() WHERE username=:username");
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function setNewPassword($id, $newPassword, $salt) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->usersTable
				 SET password = AES_ENCRYPT(:newPassword, :key)
				   , salt = AES_ENCRYPT(:salt, :key)
				 WHERE id = :id"
			);
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
			$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
			$stmt->bindParam(':key', $this->fullKey, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSessionById($sessionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->sessionsTable WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function setUserIdForSession($userId, $sessionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->sessionsTable SET user_id = :userId WHERE session_id = :sessionId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function setLoginStatusFrontEnd($userId, $loginStatus) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET login_frontend = :loginStatus WHERE userId = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':loginStatus', $loginStatus, PDO::PARAM_BOOL);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorlog($e);
		}
	}

	public function setLoginStatusBackEnd($userId, $loginStatus) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET login_frontend = :loginStatus WHERE userId = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':loginStatus', $loginStatus, PDO::PARAM_BOOL);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorlog($e);
		}
	}

	public function getUserStatus($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT enabled FROM $this->usersTable WHERE id=:id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['enabled'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setUserStatus($userId, $status) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET enabled = :status WHERE id = :id");
			$stmt->bindParam(':status', $status, PDO::PARAM_BOOL);
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getUserActivation($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT activated FROM $this->usersTable WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['activated'];
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function setUserActivation($userId, $activationState) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET activated = :activationState WHERE id = :userId");
			$stmt->bindParam(':activationState', $activationState, PDO::PARAM_BOOL);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getUsernameById($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT username FROM $this->usersTable WHERE id=:id");
			$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['username'];
		} catch(PDOExceptione $e) {
			$this->errorLog($e);
		}
	}

	public function getUserByUsername($username) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT id, first_name, last_name, username, username_alias, email, system_email, enabled, date_format(date_joined, '%M %d, %Y') AS date_joined, date_format(last_login, '%M %d, %Y') AS last_login
				 FROM $this->usersTable WHERE username = :username"
			);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserByEmail($email) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT id, first_name, last_name, username, username_alias, email, system_email, enabled, date_format(date_joined, '%M %d, %Y') AS date_joined, date_format(last_login, '%M %d, %Y') AS last_login
				 FROM $this->usersTable WHERE email = :email");
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserByAlias($alias) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT id, first_name, last_name, username, username_alias, email, system_email, enabled, date_format(date_joined, '%M %d, %Y') AS date_joined, date_format(last_login, '%M %d, %Y') AS last_login
				 FROM $this->usersTable WHERE username_alias = :alias");
			$stmt->bindParam(':alias', $alias, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUsersByRoleId($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userRolesTable WHERE role_id = :roleId");
			$stmt->bindParam(":roleId", $roleId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNumberOfUsersByRoleId($roleId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS numOfUsers FROM $this->userRolesTable WHERE role_id = :roleId");
			$stmt->bindParam(":roleId", $roleId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['numOfUsers'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUsernameSearch($limit, $offset, $searchWord, $orderBy, $sort) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				 "SELECT username
				  FROM $this->usersTable
				  WHERE username LIKE :searchWord
				  ORDER BY $orderBy $sort
				  LIMIT :limit
				  OFFSET :offset"
			);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_COLUMN);
			return $result;
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUsersWithBackendAccess() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT DISTINCT u.id, u.first_name, u.last_name, u.username
				 FROM $this->usersTable AS u
				 LEFT JOIN $this->userRolesTable AS r ON r.user_id = u.id
				 LEFT JOIN $this->permsTable AS p ON r.role_id = p.role_id
				 WHERE p.module_id = 53 AND p.allow = 1"
			);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOptionsByGroup($optionGroup) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->optionsTable WHERE option_group = :optionGroup");
			$stmt->bindParam(':optionGroup', $optionGroup, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOptionsByLocation($optionLocation) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->optionsTable WHERE option_location = :optionLocation");
			$stmt->bindParam(':optionLocation', $optionLocation, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOptionValueForUser($userId, $optionId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userOptionsTable WHERE user_id = :userId AND option_id = :optionId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':optionId', $optionId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['option_value'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setOptionValueForUser($userId, $optionId, $optionValue) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->userOptionsTable SET option_value = :optionValue
				 WHERE user_id = :userId AND option_id = :optionId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':optionId', $optionId, PDO::PARAM_INT);
			$stmt->bindParam(':optionValue', $optionValue, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserForumPostCount($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT forum_post_count FROM $this->usersTable WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['forum_post_count'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function incrementForumPostCount($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET forum_post_count = forum_post_count + 1 WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function decrementForumPostCount($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET forum_post_count = forum_post_count - 1 WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	public function incrementProfileViews($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET forum_profile_views = forum_profile_views + 1 WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setLastActivity($userId, $sessionId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET last_activity = NOW() WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			if(!empty($sessionId)) {
				$stmt2 = self::$dbh->prepare("UPDATE $this->sessionsTable SET last_activity = NOW() WHERE session_id = :sessionId");
				$stmt2->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
				$stmt2->execute();
			}
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getUserAvatar($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT avatar, avatar_small FROM $this->usersTable WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setUserAvatar($userId, $avatars) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"UPDATE $this->usersTable
				 SET avatar            = :avatar
				   , avatar_small      = :avatarSmall
				   , avatar_path       = :avatarPath
				   , avatar_small_path = :avatarSmallPath
				 WHERE id = :userId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':avatar', $avatars['normal'], PDO::PARAM_STR);
			$stmt->bindParam(':avatarSmall', $avatars['small'], PDO::PARAM_STR);
			$stmt->bindParam(':avatarPath', $avatars['normalPath'], PDO::PARAM_STR);
			$stmt->bindParam(':avatarSmallPath', $avatars['smallPath'], PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getOnlineUser($userId, $secondsSinceActive) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->sessionsTable
				 WHERE user_id = :userId AND last_activity >= :currentTime - :seconds");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':seconds', $secondsSinceActive, PDO::PARAM_INT);
			$stmt->bindValue(':currentTime', time(), PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSessions() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->sessionsTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function addFriend($userId, $friendId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("INSERT IGNORE INTO $this->userFriendsTable (user_id, friend_id) VALUES (:userId, :friendId)");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("INSERT IGNORE INTO $this->userFriendsTable (user_id, friend_id) VALUES (:friendId, :userId)");
			$stmt2->bindParam(':friendId', $friendId, PDO::PARAM_INT);
			$stmt2->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function removeFriend($userId, $friendId) {
		try {
			if(!self::$dbh) $this->connect();
			self::$dbh->beginTransaction();
			$stmt = self::$dbh->prepare("DELETE FROM $this->userFriendsTable WHERE user_id = :userId AND friend_id = :friendId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':friendId', $friendId, PDO::PARAM_INT);
			$stmt->execute();
			$stmt2 = self::$dbh->prepare("DELETE FROM $this->userFriendsTable WHERE user_id = :friendId AND friend_id = :userId");
			$stmt2->bindParam(':friendId', $friendId, PDO::PARAM_INT);
			$stmt2->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt2->execute();
			return self::$dbh->commit();
		} catch(PDOException $e) {
			self::$dbh->rollBack();
			$this->errorLog($e);
		}
	}

	public function getFriendsForUser($userId, $limit, $orderBy = 'u.username', $sort = 'ASC') {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT u.id, u.username, u.username_alias, u.avatar, u.avatar_small
				 FROM $this->userFriendsTable as uf
				 LEFT JOIN $this->usersTable as u ON uf.friend_id = u.id
				 WHERE uf.user_id = :userId
				 ORDER BY $orderBy $sort
				 LIMIT :limit
				 OFFSET 0"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function countFriendsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT COUNT(*) AS num_of_friends FROM $this->userFriendsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return (int)$stmt->fetch(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function updateSetting($userId, $setting, $value) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->usersTable SET $setting = :value WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':value', $value, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNotificationOptionByName($optionName) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->notifyOptsTable WHERE notification_name = :optionName");
			$stmt->bindParam(':optionName', $optionName, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNotificationOptions() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->notifyOptsTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNotificationOptionsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->userNotifyOptsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNotificationOptionIdsForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT notification_id FROM $this->userNotifyOptsTable WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getNotificationOptionForUser($userId, $notificationId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"SELECT * FROM $this->userNotifyOptsTable
				 WHERE user_id = :userId AND notification_id = :notificationId"
			);
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setNotificationAsRead($notificationId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->notificationsTable SET is_read = 1 WHERE id = :notificationId");
			$stmt->bindParam(':notificationId', $notificationId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setNotificationsAsReadForUser($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->notificationsTable SET is_read = 1 WHERE user_id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getInboxMsg($msgId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->inboxMsgsTable WHERE id = :msgId");
			$stmt->bindParam(':msgId', $msgId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getSentMsg($msgId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->sentMsgsTable WHERE id = :msgId");
			$stmt->bindParam(':msgId', $msgId, PDO::PARAM_INT);
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setInboxMsgAsRead($msgId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->inboxMsgsTable SET is_read = 1 WHERE id = :msgId");
			$stmt->bindParam(':msgId', $msgId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getMessageSentDate($sentMsgId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT date_sent FROM $this->sentMsgsTable WHERE id = :sentMsgId");
			$stmt->bindParam(':sentMsgId', $sentMsgId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['date_sent'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFriendAuthorization($userId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT authorize_friends FROM $this->usersTable WHERE id = :userId");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['authorize_friends'];
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getUserAccountCode($userId, $codeType) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->accountCodesTable WHERE user_id = :userId AND type = :codeType");
			$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
			$stmt->bindParam(':codeType', $codeType, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getFailedLogins($afterDate) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->failedLoginsTable WHERE login_date > :afterDate");
			$stmt->bindParam(':afterDate', $afterDate, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function setSessionSection($sessionId, $sessionSection) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->sessionsTable SET section = :sessionSection WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->bindParam(':sessionSection', $sessionSection, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function setSessionUserData($sessionId, $userData) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("UPDATE $this->sessionsTable SET user_data_json = :userData WHERE session_id = :sessionId");
			$stmt->bindParam(':sessionId', $sessionId, PDO::PARAM_STR);
			$stmt->bindParam(':userData', $userData, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}

	public function getActiveSessionsBySection($section, $timestamp, $activityLimit, $loggedIn = FALSE) {
		try {
			if(!self::$dbh) $this->connect();
			if($loggedIn) {
				$stmt = self::$dbh->prepare(
					"SELECT * FROM $this->sessionsTable
					 WHERE section = :section AND (user_id != '' OR user_id IS NOT NULL) AND last_activity + :activityLimit >= :timestamp
					 GROUP BY user_id"
				);
			} else {
				$stmt = self::$dbh->prepare(
					"SELECT * FROM $this->sessionsTable
					 WHERE section = :section AND (user_id = '' OR user_id IS NULL) AND last_activity + :activityLimit >= :timestamp
					 GROUP BY user_id"
				);
			}
			$stmt->bindParam(':section', $section, PDO::PARAM_STR);
			$stmt->bindParam(':activityLimit', $activityLimit, PDO::PARAM_INT);
			$stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e){
			$this->errorLog($e);
		}
	}
}
