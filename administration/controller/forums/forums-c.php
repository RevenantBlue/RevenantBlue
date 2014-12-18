<?php
namespace RevenantBlue\Admin;

require_once DIR_ADMIN . 'controller/forums/forum-validation.php';
require_once DIR_ADMIN . 'controller/forums/forum-hierarchy.php';
require_once DIR_ADMIN . 'controller/admin/admin-c.php';
require_once DIR_ADMIN . 'model/forums/forums-main.php';

$forums = new Forums;
// Get the list of roles.
$roles = $acl->loadRoles();

if($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(isset($_GET['controller'])) {
		if($_GET['controller'] === 'forums') {
			// Page options
			$optionsForPage = $users->getOptionsByGroup('forum');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'forum');
			
			$totalRootNodes = $forums->countForums();
			$totalForums = $forums->countAllForums();
			$rootNodes = $forums->getRootNodeIds();
			$forumList = ForumHierarchy::buildForums($totalRootNodes, 0);
			$forumList = json_encode($forumList);
			
			
			//echo $totalRootNodes . "<br />";
			//echo $totalForums . "<br />";
			
			// Get all forum ids, remove root ids and get the moderators for the remaining forums.
			$allForumIds = $forums->getAllForumIds();

			$forumIds = array_diff($allForumIds, $rootNodes);
			
			//print_r2($forumIds);
			// Get the moderator ids
			foreach($forumIds as $forumId) {
					$forumUserModeratorIds[$forumId] =  $forums->getUserModeratorsForForum($forumId);
					$forumRoleModeratorIds[$forumId] = $forums->getRoleModeratorsForForum($forumId);
			}
			//print_r2($forumUserModeratorIds);
			//print_r2($forumRoleModeratorIds);
			// Iterate through the moderator ids and change the array too look like 'id' => 'moderator name'
			if(!empty($forumUserModeratorIds)) {
				foreach($forumUserModeratorIds as $forumId => $forumUserModeratorId) {
					//print_r2($forumId);
					foreach($forumUserModeratorId as $userId) {
						//print_r2($userId);
						$id = $forums->getModeratorId($forumId, $userId, 'user');
						$forumMods['users'][$forumId][$id] = array($userId => $users->getUsernameById($userId));
					}
				}
				foreach($forumRoleModeratorIds as $forumId => $forumRoleModeratorId) {
					foreach($forumRoleModeratorId as $roleId) {
						$id = $forums->getModeratorId($forumId, $roleId, 'role');
						$role = $acl->getRoleById($roleId);
						$forumMods['roles'][$forumId][$id] = array($roleId => $role['name']);
					}
				}
			}
			//print_r2($forumMods);
			// Store as a JSON string for use with the frontend.
			if(!empty($forumMods)) {
				$forumMods = json_encode($forumMods);
			}
		} elseif($_GET['controller'] === 'forum-profile') {
			if(isset($_GET['id'])) {
				$forum = $forums->getForumById((int)$_GET['id']);
				$parentId = $forums->getParentId($forum['id']);
				$rolePermissions = $forums->getForumRolePermissions($forum['id']);
			}
			$totalRootNodes = $forums->countForums();
			$totalForums = $forums->countAllForums();
			$rootNodes = $forums->getRootNodeIds();
			$forumList = ForumHierarchy::buildForums($totalRootNodes, 0);
			$forumList = ForumHierarchy::displayForums($forumList);
			
			$forumPermissions = $forums->getForumPermissions();
			
			// Page options
			$optionsForPage = $users->getOptionsByGroup('forum-profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'forum-profile');
		} elseif($_GET['controller'] === 'forum-section-profile') {
			if(isset($_GET['id'])) {
				$forumSection = $forums->getForumById((int)$_GET['id']);
				$rolePermissions = $forums->getForumRolePermissions($_GET['id']);
			}
			$viewPerm = $forums->getForumPermissionByName('view forum');
			
			// Page options
			$optionsForPage = $users->getOptionsByGroup('forum-section-profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'forum-section-profile');
		} elseif($_GET['controller'] === 'forum-moderator-profile') {
			if(isset($_GET['id'])) {
				$moderator = $forums->getModerator((int)$_GET['id']);
				$role = $acl->getRoleById($moderator['role_id']);
				$moderatorName = isset($moderator['user_id']) ? $users->getUsernameById($moderator['user_id']) : $role['name'];
			} elseif(isset($_GET['userId'])) {
				$userModerator = $users->getUsernameById((int)$_GET['userId']);
			} elseif(isset($_GET['roleId'])) {
				$role = $acl->getRoleById((int)$_GET['roleId']);
				$roleModerator = $role['name'];
			}
			$totalRootNodes = $forums->countForums();
			$totalForums = $forums->countAllForums();
			$rootNodes = $forums->getRootNodeIds();
			$forumList = ForumHierarchy::buildForums($totalRootNodes, 0);
			$forumList = ForumHierarchy::displayForums($forumList);
			// Page options
			$optionsForPage = $users->getOptionsByGroup('forum-moderator-profile');
			$userOptions = $users->loadUserOptionsByGroup($_SESSION['userId'], 'forum-moderator-profile');
		} elseif($_GET['controller'] === 'forum-reported-posts') {
			$numOfReportedPosts = $forums->countReportedPosts();
			if(isset($_GET['type'])) {
				if($_GET['type'] === 'condensed') {
					$reportedPosts = $forum->loadReportedPostsCondensed($numOfReportedPosts, 0);
				}
			} else {
				$reportedPosts = $forums->loadReportedPosts($numOfReportedPosts, 0);
			}
		}
	}
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	if(isset($_POST['forumAction'])) {
		if($_POST['forumAction'] === 'edit' && is_array($_POST['forumCheck'])) {
			// If the selected forum is a root node redirect to the section editor else redirect to the forum editor
			$rootNodeIds = $forums->getRootNodeIds();
			if(in_array((int)$_POST['forumCheck'][0], $rootNodeIds)) { 
				header('Location: ' . HTTP_ADMIN . 'forums/section/' . (int)$_POST['forumCheck'][0], TRUE, 302);
				exit;
			} else {
				header('Location: ' . HTTP_ADMIN . 'forums/' . (int)$_POST['forumCheck'][0], TRUE, 302);
				exit;
			}
		} elseif($_POST['forumAction'] === 'save-section') {
			$forumArr['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$forumArr['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$forumArr['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$forumArr['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$forumArr['userId'] = $_SESSION['userId'];   
			$forumArr['rolePerms'] = isset($_POST['rolePerms']) ? $_POST['rolePerms'] : '';
			$forumArr['archived'] = isset($_POST['archived']) ? $_POST['archived'] : '';
			// Validate forum POST parameters.
			$forumValidation = new ForumValidation($forumArr, TRUE);
			if(empty($forumValidation->errors)) {
				if(isset($_POST['id'])) {
					$forumId = (int)$_POST['id'];
					// Update the forum section
					$sectionUpdated = $forums->updateForumSection($forumValidation->forum);
					// Update the role permissions.
					foreach($_POST['rolePerms'] as $roleId => $rolePerms) {
						foreach($rolePerms as $permAlias => $status) {
							$perm = $forums->getForumPermissionByAlias($permAlias);
							if($status === 'on') {
								// Only insert if it doesn't exist to avoid duplicate insert MySQL errors.
								$forumRolePermExists = $forums->getForumRolePermission($forumId, $roleId, $perm['id']);
								if(!$forumRolePermExists) {
									$forums->insertForumRolePermission($forumId, $roleId, $perm['id']);
								}
							} elseif($status === 'off') {
								$forums->deleteForumRolePermission($forumId, $roleId, $perm['id']);
							}
						}
					}
					if(!empty($_POST['id']) && !empty($sectionUpdated)) {
						$_SESSION['success'] = 'Forum section updated successfully.';
						// Set the section Id for URL redirect
						$sectionId = $forumId;
						// Clear the forum role permission cache entry
						if(REDIS === TRUE) {
							$redis->del(PREFIX . 'forum:rolePermissions', TRUE);
						}
					} else {
						$_SESSION['errors'] = 'An error occurred while updating the forum section.';
						$_SESSION['section'] = $forumValidation->forum;
					}
				} else {
					$newSectionId = $forums->insertForum($forumValidation->forum);
					foreach($_POST['rolePerms'] as $roleId => $rolePerms) {
						foreach($rolePerms as $permAlias => $status) {
							$perm = $forums->getForumPermissionByAlias($permAlias);
							if($status === 'on') {
								$forums->insertForumRolePermission($newSectionId, $roleId, $perm['id']);
							}
						}
					}
					if(!empty($newSectionId)) {
						$_SESSION['success'] = 'Forum section created successfully.';
						// Set the section id for URL redirecting
						$sectionId = $newSectionId;
						// Clear the forum role permission cache entry
						if(REDIS === TRUE) {
							$redis->del(PREFIX . 'forum:rolePermissions', TRUE);
						}
					} else {
						$_SESSION['errors'] = 'An error occurred while creating the forum section.';
						$_SESSION['section'] = $forumValidation->forum;
					}
				}
				switch($_POST['forumAction']) {
					case 'save-section':
						header('Location: ' . HTTP_ADMIN . 'forums/section/' . $sectionId, TRUE, 302);
						exit;
						break;
					case 'save-close-section':
						header('Location: ' . HTTP_ADMIN . 'forums', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_METHOD'], TRUE, 302);
						exit;
						break;
				}
			} else {
				$_SESSION['errors'] = $forumValidation->errors;
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		} elseif($_POST['forumAction'] === 'save-forum' || $_POST['forumAction'] === 'save-close-forum') {
			$forumArr['id'] = isset($_POST['id']) ? $_POST['id'] : '';
			$forumArr['title'] = isset($_POST['title']) ? $_POST['title'] : '';
			$forumArr['alias'] = isset($_POST['alias']) ? $_POST['alias'] : '';
			$forumArr['description'] = isset($_POST['description']) ? $_POST['description'] : '';
			$forumArr['userId'] = $_SESSION['userId'];
			$forumArr['username'] = $_SESSION['username'];
			$forumArr['parentId'] = isset($_POST['parentId']) ? $_POST['parentId'] : '';
			$forumArr['perms'] = isset($_POST['perms']) ? $_POST['perms'] : '';
			$forumArr['rolePerms'] = isset($_POST['rolePerms']) ? $_POST['rolePerms'] : '';
			$forumArr['weight'] = isset($_POST['weight']) ? $_POST['weight'] : '';
			$forumArr['archived'] = isset($_POST['archived']) ? $_POST['archived'] : '';
			
			// Send the array for validation.
			$forumValidation = new ForumValidation($forumArr);
			if(empty($forumValidation->errors)) {
				if(isset($_POST['id'])) {
					$forumId = (int)$_POST['id'];
					$currentForum = $forums->getForumById((int)$_POST['id']);
					$currentParentId = $forums->getParentId((int)$_POST['id']);
					
					$forums->updateForum($forumValidation->forum);
					
					// Set the forum type and password
					$forums->updateForumPermission($forumId, 'forum_type', $_POST['type']);
					$forums->updateForumPermission($forumId, 'password', $_POST['password']);
					
					// Set the forum specific permissions
					foreach($_POST['perms'] as $permName => $value) {
						if(in_array($permName, $forums->whiteList)) {
							if($value === 'on') {
								$forums->updateForumPermission($forumId, $permName, 1);
							} elseif($value === 'off') {
								$forums->updateForumPermission($forumId, $permName, 0);
							} else {
								$forums->updateForumPermission($forumId, $permName, $value);
							}
						}
					}
					
					foreach($_POST['rolePerms'] as $roleId => $rolePerms) {
						// Get the current role perms, check for differences to prevent duplicate entry even though the database won't allow it.
						$currentRolePerms = $forums->getForumRolePermissionsByRole($_POST['id'], $roleId);
						foreach($rolePerms as $permAlias => $status) {
							$perm = $forums->getForumPermissionByAlias($permAlias);
							if($status === 'on') {
								$forumRolePermExists = $forums->getForumRolePermission($forumId, $roleId, $perm['id']);
								// Only insert if it doesn't exist to avoid duplicate insert MySQL errors.
								if(!$forumRolePermExists) {
									$forums->insertForumRolePermission($forumId, $roleId, $perm['id']);
								}
							} elseif($status === 'off') {
								$forums->deleteForumRolePermission($forumId, $roleId, $perm['id']);
							}
						}
					}
				} else {
					$forumId = $forums->insertForum($forumValidation->forum, $_POST['parentId']);
					// Set the forum type and password
					$forums->updateForumPermission($forumId, 'forum_type', $_POST['type']);
					$forums->updateForumPermission($forumId, 'password', $_POST['password']);
					
					// Set the forum specific permissions
					foreach($_POST['perms'] as $permName => $value) {
						if(in_array($permName, $forums->whiteList)) {
							if($value === 'on') {
								$forums->updateForumPermission($forumId, $permName, 1);
							} elseif($value === 'off') {
								$forums->updateForumPermission($forumId, $permName, 0);
							} else {
								$forums->updateForumPermission($forumId, $permName, $value);
							}
						}
					}
					// Assign the appropriate role permissions.
					foreach($_POST['rolePerms'] as $roleId => $rolePerms) {
						foreach($rolePerms as $permAlias => $status) {
							if($status === 'on') {
								$perm = $forums->getForumPermissionByAlias($permAlias);
								$rolePermExists = $forums->getForumRolePermission($forumId, $roleId, $perm['id']);
								if(empty($rolePermExists)) {
									$forums->insertForumRolePermission($forumId, $roleId, $perm['id']);
								}
							}
						}
					}
				}
				if(empty($forumValidation->errors)) {
					if(!empty($_POST['id'])) {
						$_SESSION['success'] = 'Forum updated successfully.';
					} else {
						$_SESSION['success'] = 'Forum created successfully.';
					}
					
					if(REDIS === TRUE) {
						$redis->del(PREFIX . 'forum:rolePermissions', TRUE);
					}
				}
				switch($_POST['forumAction']) {
					case 'save-forum':
						header('Location: ' . HTTP_ADMIN . 'forums/' . $forumId, TRUE, 302);
						exit;
						break;
					case 'save-close-forum':
						header('Location: ' . HTTP_ADMIN . 'forums', TRUE, 302);
						exit;
						break;
					default:
						header('Location: ' . $_SERVER['REQUEST_METHOD'], TRUE, 302);
						exit;
						break;
				}
			} else {
				$_SESSION['errors'] = $forumValidation->errors;
				$_SESSION['forum'] = $forumValidation;
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		} elseif($_POST['forumAction'] === 'save-moderator' && !empty($_POST['forums'])) {
			// If the id isn't set for the moderator then consider the entry a new moderator.
			if(!isset($_POST['id'])) {
				$type = !empty($_GET['userId']) ? 'user' : 'role';
				$moderatorId = !empty($_GET['userId']) ? $_GET['userId'] : $_GET['roleId']; 
				foreach($_POST['forums'] as $forumId) {
					$moderatorExists = $type === 'user' ? $forums->getForumByUserModerator($forumId, $moderatorId) : $forums->getForumByRoleModerator($forumId, $moderatorId);
					// If the moderator and forum combination do not exist insert it into the database.
					if(empty($moderatorExists)) {
						$insertSuccess[] = $forums->insertModeratorPermission($forumId, $moderatorId, $type);
					}
					foreach($_POST['modPerms'] as $permission) {
						// Get the key stored in the first element of the permission array.
						$permName = key($permission);
						$updateSuccess[] = $forums->updateForumModerator($forumId, $moderatorId, $type, $permName, (int)$permission[$permName]);
					}
				}
				if(isset($insertSuccess) && isset($updateSuccess) && (in_array('', $insertSuccess) || in_array('', $updateSuccess))) {
					$_SESSION['errors'] = 'A database error occurred while inserting the moderator into the database.';
				} else {
					if(REDIS === TRUE) {
						resetModeratorCache($redis);
					}
					$_SESSION['success'] = 'Moderator saved successfully.';
				}
				header('Location: ' . HTTP_ADMIN . 'forums', TRUE, 302);
				exit;

			} else {
				foreach($_POST['modPerms'] as $permission) {
					$permName = key($permission);
					$updateSuccess[] = $forums->updateModerator((int)$_POST['id'], $permName, $permission[$permName]);
				}
				if(isset($updateSuccess) && in_array('', $updateSuccess)) {
					$_SESSION['errors'] = 'A database error occurred while updating the moderator';
				} else {
					if(REDIS === TRUE) {
						resetModeratorCache($redis);
					}
					$_SESSION['success'] = 'Moderator updated successfully.';
				}
				
				header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
				exit;
			}
		} else if($_POST['forumAction'] === 'delete-forum') {
			// Iterate through the checked forums and delete their content.
			foreach($_POST['forumCheck'] as $forumId) {
				$forums->deleteForumTree($forumId);
				$forums->deleteForum($forumId);
				$forums->deleteAllRolePermissionsForForum($forumId);
				$forums->deleteForumTopics($forumId);
				$forums->deleteForumPosts($forumId);
				// Iterate through each forum's descendants and delete their content as well.
				$descendants = $forums->getDescendantIds($forumId);
				foreach($descendants as $descendant) {
					$forums->deleteForumTree($descendant);
					$forums->deleteForum($descendant);
					$forums->deleteAllRolePermissionsForForum($descendant);
					$forums->deleteForumTopics($descendant);
					$forums->deleteForumPosts($descendant);
				}
			}
			$_SESSION['success'] = 'Forum(s) deleted successfully.';
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else if($_POST['forumAction'] === 'delete-post-and-report') {
			// Iterate through each report and delete the assigned post as well as the report itself.
			foreach($_POST['forumCheck'] as $reportId) {
				$postReport = $forums->getReportedPost((int)$reportId);
				$post = $forums->getPost($postReport['post_id']);
				if(!empty($post)) {
					$success[] = $forums->deletePost($postReport['post_id'], $post['topic_id']);
				}
			}
			
			if(!empty($success)) {
				if(in_array(0, $success)) {
					$_SESSION['errors'] = 'One or more posts and reports were not deleted correctly due to a database error.';
				} else {
					$_SESSION['success'] = 'The post(s) and report(s) were successfully deleted.';
				}
			} else {
				$_SESSION['success'] = 'No posts or reports were deleted';
			}
			
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else if($_POST['forumAction'] === 'delete-report-only') {
			foreach($_POST['forumCheck'] as $reportId) {
				$postReport = $forums->getReportedPost((int)$reportId);
				if(!empty($postReport)) {
					$success[] = $forums->deleteReportedPost($postReport['post_id'], $postReport['user_id']);
				}
			}
			
			if(!empty($success)) {
				if(in_array(0, $success)) {
					$_SESSION['errors'] = 'Report(s) were not deleted correctly due to a database error.';
				} else {
					$_SESSION['success'] = 'The report(s) were successfully deleted.';
				}
			} else {
				$_SESSION['success'] = 'No reports were deleted';
			}
			header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
			exit;
		} else {
			// If no action matches redirect the user to their current page.
			header('Location: ' . HTTP_ADMIN . 'forums', TRUE, 302);
			exit;
		}
	} elseif(isset($_POST['adminRequest'])) {
		$adminReq = json_decode($_POST['adminRequest']);
		if(is_object($adminReq) && isset($adminReq->type) && $adminReq->type == 'forum') {
			if($adminReq->action === 'publish' || $adminReq->action === 'unpublish' || $adminReq->action === 'feature' || $adminReq->action === 'disregard') {
				for($x = 0; $x < count($adminReq->ids); $x++) {
					if(is_numeric($adminReq->ids[$x])) {
						if($adminReq->action === "publish") {
							$forums->setPublished($adminReq->ids[$x], 1);
							$adminReq->published[$x] = $forums->getPublished($adminReq->ids[$x]);
						} elseif($adminReq->action === "unpublish") {
							$forums->setPublished($adminReq->ids[$x], 0);
							$adminReq->published[$x] = $forums->getPublished($adminReq->ids[$x]);
						} elseif($adminReq->action === "feature") {
							$forums->setFeatured($adminReq->ids[$x], 1);
							$adminReq->featured[$x] = $forums->getFeatured($adminReq->ids[$x]);
						} elseif($adminReq->action === "disregard") {
							$forums->setFeatured($adminReq->ids[$x], 0);
							$adminReq->featured[$x] = $forums->getFeatured($adminReq->ids[$x]);
						}
					}
				}
			} elseif($adminReq->action === 'user-autocomplete') {
				$adminReq->users = $users->getUsernameSearch(10, 0, $adminReq->search, 'username', 'DESC');
			} elseif($adminReq->action === 'get-user-id') {
				$adminReq->userId = $users->getUserId($adminReq->username);
			} elseif($adminReq->action === 'reorder') {
				$sectionOrder = 0;
				$forumOrder = 0;
				$subForumOrder = 0;
				// Organize the sections
				foreach($adminReq->forumHierarchy as $section) {
					$sectionOrder += 1;
					$forums->setWeight($section->id, $sectionOrder);
					if(!empty($section->children)) {
						// Organize the forums
						foreach($section->children as $forum) {
							$forumOrder += 1;
							$currentParent = $forums->getParentId($forum->id);
							if($currentParent !== $section->id) {
								$forums->moveSubTreeToParent($forum->id, $section->id);
							}
							$forums->setWeight($forum->id, $forumOrder);
							if(!empty($forum->children)) {
								// Organize the subforums
								foreach($forum->children as $subForum) {
									$subForumOrder += 1;
									$currentParent = $forums->getParentId($subForum->id);
									if($currentParent !== $forum->id) {
										$forums->moveSubTreeToParent($subForum->id, $forum->id); 
									}
									$forums->setWeight($subForum->id, $subForumOrder); 
								}
							}
						}
					}
				}
			} elseif($adminReq->action === 'delete-user-moderator') {
				$forums->deleteModerator($adminReq->id);
				if(REDIS === TRUE) {
					resetModeratorCache($redis);
				}
			} elseif($adminReq->action === 'delete-role-moderator') {
				$forums->deleteModerator($adminReq->id);
				if(REDIS === TRUE) {
					resetModeratorCache($redis);
				}
			} elseif($adminReq->action === 'forumglobals') {
				if(aclVerify('administer site config') && !empty($adminReq->numOfTopicsToShow) && !empty($adminReq->numOfPostsToShow)) {
					$updated[] = $config->updateConfiguration($adminReq->numOfTopicsToShow, 'num_of_topics_to_show');
					$updated[] = $config->updateConfiguration($adminReq->numOfPostsToShow, 'num_of_posts_to_show');
				}
				
				if(!in_array('', $updated)) {
					clearCache();
					$adminReq->success = true;
				}
			}
		}
		echo json_encode($adminReq);
		exit;
	}
}
function resetModeratorCache($redis) {
	$redis->del(PREFIX . 'moderatorPerms');
	$redis->del(PREFIX . 'forumsToModerate');
}
function resetForumRolePermCache($redis) {
	$redis->del(PREFIX . 'forum:rolePermissions');
}
function forumCleanUp() {
	if(isset($_SESSION['forum'])) {
		unset($_SESSION['forum']);
	}
}
