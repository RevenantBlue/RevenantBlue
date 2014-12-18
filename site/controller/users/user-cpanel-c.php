<?php
namespace RevenantBlue\Site;

require_once(DIR_APPLICATION . 'controller/common/site-c.php');
require_once(DIR_APPLICATION . 'model/forums/forums-main.php');

$defaultAvatar = HTTP_AVATARS . 'default/' . $globalSettings['avatar_location']['value'];
$defaultSmallAvatar = HTTP_AVATARS . 'default/' . $globalSettings['small_avatar_location']['value'];

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'user-cpanel') {
			
			$breadcrumbs = array (
				'0' => array(
					'title' => 'User Control Panel ',
					'url'   => HTTP_SERVER . 'cpanel'
				)
			);
			
			if(isset($_GET['verify'])) {
				$emailVerified = $config->verifyCode($_GET['verify'], 'verify email');
				if($emailVerified) {
					// Activated the user.
					$accountActivated = $users->setUserActivation($emailVerified['user_id'], 1);
					// Send account activated email.
					$userValidation = new UserValidation;
					$userData = $users->getUserData($emailVerified['user_id']);
					// Include the swift mailer library
					require_once(DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php');
					$emailTemplate = $userValidation->parseEmailTemplate('account-activation', $userData['id']);
					$userValidation->sendUserEmail(
						$emailTemplate['subject']
					  , $emailTemplate['body']
					  , $userData['email']
					  , $userData['first_name']
					  , $userData['last_name']
					  , $globalSettings['system_email']['value']
					  , $globalSettings['site_name']['value']
					);
					// Delete the user code.
					$config->deleteCode($emailVerified['user_id'], $emailVerified['user_code']);
				} else {
					header('Location: ' . HTTP_SERVER, TRUE, 302);
					exit;
				}
			} elseif(isset($_GET['pwreset'])) {
				// Handle password reset requests from users.
				$pwReset = $config->verifyCode($_GET['pwreset'], 'password reset');
				if(!$pwReset) {
					header('Location: ' . HTTP_SERVER, TRUE, 302);
					exit;
				}
			} elseif(isset($_GET['approval'])) {
				$approval = $config->verifyCode($_GET['approval'], 'admin approval');
				if(!$approval) {
					header('Location: ' . HTTP_SERVER, TRUE, 302);
					exit;
				}
			} elseif(isset($_GET['cancel'])) {

			} else {
				// If no user is logged in send them back to the homepage.
				if(empty($_SESSION['userId'])) {
					header('Location: ' . HTTP_SERVER, TRUE, 302);
					exit;
				}
				
				$user = $users->getUserData($_SESSION['userId']);
				
				// Avatar template
				$avatarTemplate = $config->getImageTemplate($globalSettings['avatar_template']['value']);
				
				$avatarWidth = $avatarTemplate['template_width'];
				$avatarHeight = $avatarTemplate['template_height'];
				
				// Avatar max image size.
				$maxAvatarSize = $globalSettings['max_avatar_size']['value']; 
				
				// Configure timezone options

				
				// Configure birthday options.
				$bdayMonth = !empty($user['birthday']) ? (int)date('n', strtotime($user['birthday'])) : '';
				$bdayDay = !empty($user['birthday']) ? (int)date('j', strtotime($user['birthday'])) : '';
				$bdayYear = !empty($user['birthday']) ? (int)date('Y', strtotime($user['birthday'])) : '';
				//var_dump($bdayDay);
				//var_dump(date('F d, Y', strtotime($user['birthday'])));
				//exit;
				$birthMonths = array(
					'1'  => 'January',
					'2'  => 'February',
					'3'  => 'March',
					'4'  => 'April',
					'5'  => 'May',
					'6'  => 'June',
					'7'  => 'July',
					'8'  => 'August',
					'9'  => 'September',
					'10' => 'October',
					'11' => 'November',
					'12' => 'December'
				);
				$birthDays = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
				$birthYears = array();
				$currentYear = date('Y', time());
				for($x = $currentYear; $x >= $currentYear - 102; $x--) {
					$birthYears[] = $x;
				}
				
				// Get the user's notification options
				$notificationOptions = $users->getNotificationOptionsForUser($_SESSION['userId']);
				foreach($notificationOptions as $notificationOpt) {
					$notiOpts[$notificationOpt['notification_id']] = $notificationOpt;
				}
				
				// Load notifications
				$numOfNotifications = $users->countNotificationsForUser($_SESSION['userId']);
				$notifications = $users->loadNotificationsForUser($_SESSION['userId'], $numOfNotifications, 0);
				
				// Set unread notifications to read.
				if(isset($_GET['tab']) && $_GET['tab'] === 'notifications') {
					foreach($notifications as $notification) {
						if($notification['is_read'] != 1) {
							$users->setNotificationAsRead($notification['id']);
						}
					}
				}
				// Load messages
				
				// Inbox
				$numOfInboxMsgs = $users->countInboxMessagesForUser($_SESSION['userId']);
				$inboxMessages = $users->loadInboxMessagesForUser($_SESSION['userId'], $numOfInboxMsgs, 0);
				
				$numOfFriends = (int)$users->countFriendsForUser($_SESSION['userId']);
				$friends = $users->getFriendsForUser($_SESSION['userId'], $numOfFriends);
				$friendUsernames = getFriendUsernames($friends);
				
				// Sent
				$numOfSentMsgs = $users->countSentMessagesForUser($_SESSION['userId']);
				$sentMessages = $users->loadSentMessagesForUser($_SESSION['userId'], $numOfSentMsgs, 0);
				
				// If the user came to the control panel wanting to send a message to another user
				if(!empty($_GET['sendTo'])) {
					$sendToUsername = $users->getUsernameById($_GET['sendTo']);
				}
			}
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	if(isset($_POST['updateGeneral'])) {
		$generalSettings['email'] = isset($_POST['email']) ? $_POST['email'] : '';
		$generalSettings['firstName'] = isset($_POST['firstName']) ? $_POST['firstName'] : '';
		$generalSettings['lastName'] = isset($_POST['lastName']) ? $_POST['lastName'] : '';
		
		$validation = new GlobalValidation;
		$generalSettings['email'] = $validation->validateEmail($generalSettings['email'], TRUE, TRUE);
		$generalSettings['firstName'] = $validation->validateString($generalSettings['firstName'], 255, 'Your first name cannot be longer than 255 characters'); 
		$generalSettings['lastName'] = $validation->validateString($generalSettings['lastName'], 255, 'Your last name cannot be longer than 255 characters');
		
		if(empty($validation->errors)) {
			$users->updateSetting($_SESSION['userId'], 'email', $generalSettings['email']);
			$users->updateSetting($_SESSION['userId'], 'first_name', $generalSettings['firstName']);
			$users->updateSetting($_SESSION['userId'], 'last_name', $generalSettings['lastName']);
			
			$_SESSION['success'] = 'General settings updated successfully';
			
			header('Location: ' . HTTP_CPANEL, TRUE, 302);
			exit;
		} else {
			$_SESSION['errors'] = $validation->errors;
			header('Location: ' . HTTP_CPANEL, TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['updateProfile'])) {
		// Require the validation controller.
		require_once(DIR_APPLICATION . 'controller/forums/forum-validation.php');

		$profile['timezone'] = isset($_POST['timezone']) ? $_POST['timezone'] : '';
		$profile['showFriends'] = isset($_POST['showFriends']) ? 1 : 0;
		$profile['authorizeFriends'] = isset($_POST['authorizeFriends']) ? 1 : 0;
		$profile['privacy']['show_posts_topics'] = isset($_POST['privacy']['show_posts_topics']) ? $_POST['privacy']['show_posts_topics'] : '';
		$profile['birthday']['month'] = isset($_POST['birthMonth']) ? $_POST['birthMonth'] : '';
		$profile['birthday']['day'] = isset($_POST['birthDay']) ? $_POST['birthDay'] : '';
		$profile['birthday']['year'] = isset($_POST['birthYear']) ? $_POST['birthYear'] : '';
		$profile['gender'] = isset($_POST['gender']) ? $_POST['gender'] : '';
		$profile['location'] = isset($_POST['location']) ? $_POST['location'] : ''; 
		$profile['interests'] = isset($_POST['interests']) ? $_POST['interests'] : ''; 
		$profile['contacts']['aim'] = isset($_POST['aim']) ? $_POST['aim'] : ''; 
		$profile['contacts']['facebook'] = isset($_POST['facebook']) ? $_POST['facebook'] : ''; 
		$profile['contacts']['googleplus'] = isset($_POST['googleplus']) ? $_POST['googleplus'] : ''; 
		$profile['contacts']['msn'] =  isset($_POST['msn']) ? $_POST['msn'] : ''; 
		$profile['contacts']['skype'] = isset($_POST['skype']) ? $_POST['skype'] : ''; 
		$profile['contacts']['twitter'] = isset($_POST['twitter']) ? $_POST['twitter'] : ''; 
		$profile['contacts']['website'] = isset($_POST['website']) ? $_POST['website'] : ''; 
		$profile['aboutMe'] = isset($_POST['aboutMe']) ? $_POST['aboutMe'] : '';
		
		$profileValidation = new ForumProfileValidation($profile);
		// Check for errors.
		if(empty($profileValidation->errors)) {
			$profileUpdated = $users->updateForumProfile($profileValidation->profile);
			$contactsUpdated = $users->updateContacts($profileValidation->id, $profileValidation->contacts);
			// Update the user's timezone
			$_SESSION['timezone'] = $profileValidation->timezone;
			
			$_SESSION['success'] = 'Profile updated successfully';
			header('Location: ' . HTTP_CPANEL . 'profile', TRUE, 302);
			exit;
		} else {
			$_SESSION['errors'] = $profileValidation->errors;
			header('Location: ' . HTTP_CPANEL . 'profile', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['updateNotificationOpts'])) {
		if(!empty($_POST['notify']) && is_array($_POST['notify']) && !empty($_POST['emailNotify']) && is_array($_POST['emailNotify'])) {
			// Get the current option ids and insert any missing ones.
			$currentOptionIds = $users->getNotificationOptionIdsForUser($_SESSION['userId']);
			
			// Update the system notification options
			foreach($_POST['notify'] as $notificationId => $status) {
				// Insert any missing options
				if(empty($currentOptionIds) || !in_array($notificationId, $currentOptionIds)) {
					$users->insertNotificationOption($_SESSION['userId'], $notificationId);
				}
				// Update the email notifications
				if($status === 'on') {
					$users->updateNotificationOption($_SESSION['userId'], $notificationId, 'by_system', 1);
				} elseif($status === 'off') {
					$users->updateNotificationOption($_SESSION['userId'], $notificationId, 'by_system', '');
				}
			}
			// Update the email notification options
			foreach($_POST['emailNotify'] as $notificationId => $status) {
				// Insert any missing options
				if(empty($currentOptionIds) || !in_array($notificationId, $currentOptionIds)) {
					$users->insertNotificationOption($_SESSION['userId'], $notificationId);
				}
				// Update the email notifications
				if($status === 'on') {
					echo $users->updateNotificationOption($_SESSION['userId'], $notificationId, 'by_email', 1);
				} elseif($status === 'off') {
					$users->updateNotificationOption($_SESSION['userId'], $notificationId, 'by_email', '');
				}
			}

			$_SESSION['success'] = 'Your notification options have been updated.';
			header('Location: ' . HTTP_CPANEL . 'notification-opts', TRUE, 302);
			exit;
		} else {
			header('Location: ' . HTTP_CPANEL . 'notification-opts', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['appRequest'])) {
		$appReq = json_decode($_POST['appRequest']);
		if(isset($appReq->type) && isset($appReq->action) && $appReq->type === 'cpanel') {
			if($appReq->action === 'send-pm') {
				// Get the id of the recipient.
				$recipId = $users->getUserId($appReq->recipient);
				// If there is no user id send an error
				if(!empty($recipId)) {
					$validation = new GlobalValidation;
					// Get highest ranked role, true means to return the role's id instead of its name.
					$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
					// Get the content filter.
					$contentFormat = $config->getContentFilterForRole($highestRoleId);
					
					$appReq->subject = $validation->validateString($appReq->subject, 255, 'The subject cannot be more than 255 characters');
					$appReq->content = $validation->validateContent($appReq->content, $contentFormat['format_id']);
					$appReq->recipientUsername = $users->getUsernameById($recipId);
					
					if(empty($validation->errors)) {
						
						$message = array(
							'senderId'          => $_SESSION['userId']
						  , 'recipientId'       => $recipId
						  , 'subject'           => $appReq->subject
						  , 'content'           => $appReq->content
						  , 'senderUsername'    => $_SESSION['username']
						  , 'recipientUsername' => $appReq->recipientUsername 
						);
						
						// Insert the private message into the database and return the sent message id to send back to the user.
						$appReq->sentMsgId = $users->insertPrivateMessage($message);
						
						// Check if the receiving user wants to be notified of a message.
						$option = $users->getNotificationOptionByName('pm_received');
						$notification = $users->getNotificationOptionForUser($recipId, $option['id']);
						// Make a system notification
						if($notification['by_system'] == 1) {
							$users->insertNotification($recipId, $_SESSION['username'] . ' has sent you a message.');
						}
						
						// Send an email notification
						if($notification['by_email'] == 1) {
							require_once(DIR_APPLICATION . 'controller/users/users-validation.php');
							$recipientData = $users->getUserData($recipId);
							
							$userValidation = new UserValidation;
							
							$pMessage = array(
								'from'    => $_SESSION['username'],
								'subject' => $appReq->subject,
								'message' => $appReq->content
							);
							
							$emailTemplate = $userValidation->parseEmailTemplate('pm-received', $recipId, '', $pMessage);
							$userValidation->sendUserEmail(
								$emailTemplate['subject']
							  , $emailTemplate['body']
							  , $recipientData['email']
							  , $recipientData['first_name']
							  , $recipientData['last_name']
							  , $globalSettings['system_email']['value']
							  , $globalSettings['site_name']['value']
							  , $recipId
							);
						}
						
						$appReq->messageSent = TRUE;
						$appReq->subject = hsc($appReq->subject);
						$appReq->dateSent = nicetime($users->getMessageSentDate($appReq->sentMsgId));
					} else {
						$appReq->error = $validation->errors[0];
					}
				} else {
					$appReq->error = 'The recipient you entered does not exist.';
				}
			} elseif($appReq->action === 'check-if-user-exists') {
				$recipientUsername = $users->getUserByUsername($appReq->recipient);
				
				if(!empty($recipientUsername)) {
					$appReq->userExists = 1;
				}
			} elseif($appReq->action === 'delete-messages') {
				if(!empty($appReq->inboxMsgIds) && is_array($appReq->inboxMsgIds)) {
					foreach($appReq->inboxMsgIds as $inboxMsgId) {
						$appReq->inboxMsgsDeleted[$inboxMsgId] = $users->deleteInboxMessage((int)$inboxMsgId, $_SESSION['userId']);
					}
				}
				if(!empty($appReq->sentMsgIds) && is_array($appReq->sentMsgIds)) {
					foreach($appReq->sentMsgIds as $sentMsgId) {
						$appReq->sentMsgsDeleted[$sentMsgId] = $users->deleteSentMessage((int)$sentMsgId, $_SESSION['userId']);
					}
				}
			} elseif($appReq->action === 'delete-notifications') {
				if(!empty($appReq->notificationIds) && is_array($appReq->notificationIds)) {
					foreach($appReq->notificationIds as $notificationId) {
						$appReq->notificationsDeleted[$notificationId] = $users->deleteNotification($notificationId, $_SESSION['userId']);
					}
				}
			} elseif($appReq->action === 'accept-friend-request') {
				if(!empty($appReq->messageId)) {
					$inboxMsg = $users->getInboxMsg($appReq->messageId);
					if(!empty($inboxMsg) && !empty($_SESSION['username']) && $inboxMsg['recipient_username'] === $_SESSION['username']) {
						$friendAdded = $users->addFriend($inboxMsg['sender_id'], $inboxMsg['recipient_id']);
						// If the friend was added notify the user who sent the request if their notification options require it.
						if(!empty($friendAdded)) {
							// Get notification options for sender of the friend requset and who's friend request was accepted
							// 5 is the id for 'friend_accept' notificaiton
							$notification = $users->getNotificationOptionForUser($inboxMsg['sender_id'], 5);
							
							// Send system notification
							if($notification['by_system'] == 1) {
								$users->insertNotification($recipId, $_SESSION['username'] . ' has sent you a message.');
							}
							
							// Send an email notification
							if($notification['by_email'] == 1) {
								
							}
							
							// Delete the inbox message since it's no longer required.
							$appReq->msgDeleted = $users->deleteInboxMessage($inboxMsg['id'], $_SESSION['userId']);
						}
					}
				}
			} elseif($appReq->action === 'decline-friend-request') {
				if(!empty($appReq->messageId)) {
					$inboxMsg = $users->getInboxMsg($appReq->messageId);
					// Delete the friend request since it's no longer required.
					$appReq->msgDeleted = $users->deleteInboxMessage($inboxMsg['id'], $_SESSION['userId']);
				}
			} elseif($appReq->action === 'mark-message-as-read') {
				if(!empty($appReq->messageId)) {
					$appReq->msgRead = $users->setInboxMsgAsRead($appReq->messageId);
				}
			} elseif($appReq->action === 'mark-notification-as-read') {
				if(!empty($_SESSION['userId'])) {
					$users->setNotificationsAsReadForUser($_SESSION['userId']);
				}
			}
		}
		
		echo json_encode($appReq);
		exit;
	} else {
		// If no POST parameters match redirect the user to their previous page.
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}

function getFriendUsernames($friends) {
	foreach($friends as $friend) {
		$friendUsernames[$friend['id']] = $friend['username'];
	}
	$friendUsernames = !empty($friendUsernames) ? $friendUsernames : array();
	return $friendUsernames;
}
