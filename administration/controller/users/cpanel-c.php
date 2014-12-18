<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/users/users-validation.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'messages') {
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

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	if(isset($_POST['updateNotificationOpts'])) {
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
			header('Location: ' . HTTP_ADMIN . 'messages/notification-opts', TRUE, 302);
			exit;
		} else {
			header('Location: ' . HTTP_ADMIN . 'messages/notification-opts', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(isset($adminReq->type) && isset($adminReq->action) && $adminReq->type === 'cpanel') {
			if($adminReq->action === 'send-pm') {
				// Get the id of the recipient.
				$recipId = $users->getUserId($adminReq->recipient);
				// If there is no user id send an error
				if(!empty($recipId)) {
					$validation = new GlobalValidation;
					// Get highest ranked role, true means to return the role's id instead of its name.
					$highestRoleId = $acl->getHighestRankedRoleForUser($_SESSION['userId'], TRUE);
					// Get the content filter.
					$contentFormat = $config->getContentFilterForRole($highestRoleId);
					
					$adminReq->subject = $validation->validateString($adminReq->subject, 255, 'The subject cannot be more than 255 characters');
					$adminReq->content = $validation->validateContent($adminReq->content, $contentFormat['format_id']);
					$adminReq->recipientUsername = $users->getUsernameById($recipId);
					if(empty($validation->errors)) {
						
						$message = array(
							'senderId'          => $_SESSION['userId']
						  , 'recipientId'       => $recipId
						  , 'subject'           => $adminReq->subject
						  , 'content'           => $adminReq->content
						  , 'senderUsername'    => $_SESSION['username']
						  , 'recipientUsername' => $adminReq->recipientUsername 
						);
						
						// Insert the private message into the database and return the sent message id to send back to the user.
						$adminReq->sentMsgId = $users->insertPrivateMessage($message);
						
						// Check if the receiving user wants to be notified of a message.
						$option = $users->getNotificationOptionByName('pm_received');
						$notification = $users->getNotificationOptionForUser($recipId, $option['id']);
						// Make a system notification
						if($notification['by_system'] == 1) {
							$users->insertNotification($recipId, $_SESSION['username'] . ' has sent you a message.');
						}
						
						// Send an email notification
						if($notification['by_email'] == 1) {
							$recipientData = $users->getUserData($recipId);
							
							$userValidation = new UserValidation;
							
							$pMessage = array(
								'from'    => $_SESSION['username'],
								'subject' => $adminReq->subject,
								'message' => $adminReq->content
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
						
						$adminReq->messageSent = TRUE;
						$adminReq->subject = hsc($adminReq->subject);
						$adminReq->dateSent = nicetime($users->getMessageSentDate($adminReq->sentMsgId));
					} else {
						$adminReq->error = $validation->errors[0];
					}
				} else {
					$adminReq->error = 'The recipient you entered does not exist.';
				}
			} elseif($adminReq->action === 'check-if-user-exists') {
				$recipientUsername = $users->getUserByUsername($adminReq->recipient);
				
				if(!empty($recipientUsername)) {
					$adminReq->userExists = 1;
				}
			} elseif($adminReq->action === 'delete-messages') {
				if(!empty($adminReq->inboxMsgIds) && is_array($adminReq->inboxMsgIds)) {
					foreach($adminReq->inboxMsgIds as $inboxMsgId) {
						$adminReq->inboxMsgsDeleted[$inboxMsgId] = $users->deleteInboxMessage((int)$inboxMsgId, $_SESSION['userId']);
					}
				}
				if(!empty($adminReq->sentMsgIds) && is_array($adminReq->sentMsgIds)) {
					foreach($adminReq->sentMsgIds as $sentMsgId) {
						$adminReq->sentMsgsDeleted[$sentMsgId] = $users->deleteSentMessage((int)$sentMsgId, $_SESSION['userId']);
					}
				}
			} elseif($adminReq->action === 'delete-notifications') {
				if(!empty($adminReq->notificationIds) && is_array($adminReq->notificationIds)) {
					foreach($adminReq->notificationIds as $notificationId) {
						$adminReq->notificationsDeleted[$notificationId] = $users->deleteNotification($notificationId, $_SESSION['userId']);
					}
				}
			} elseif($adminReq->action === 'accept-friend-request') {
				if(!empty($adminReq->messageId)) {
					$inboxMsg = $users->getInboxMsg($adminReq->messageId);
					if(!empty($inboxMsg) && !empty($_SESSION['username']) && $inboxMsg->recipient_username === $_SESSION['username']) {
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
							$adminReq->msgDeleted = $users->deleteInboxMessage($inboxMsg['id'], $_SESSION['userId']);
						}
					}
				}
			} elseif($adminReq->action === 'decline-friend-request') {
				if(!empty($adminReq->messageId)) {
					$inboxMsg = $users->getInboxMsg($adminReq->messageId);
					// Delete the friend request since it's no longer required.
					$adminReq->msgDeleted = $users->deleteInboxMessage($inboxMsg['id'], $_SESSION['userId']);
				}
			} elseif($adminReq->action === 'mark-message-as-read') {
				if(!empty($adminReq->messageId)) {
					$adminReq->msgRead = $users->setInboxMsgAsRead($adminReq->messageId);
				}
			} elseif($adminReq->action === 'mark-notifications-as-read') {
				$users->setNotificationsAsReadForUser($_SESSION['userId']);
			}
		}
		
		echo json_encode($adminReq);
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
