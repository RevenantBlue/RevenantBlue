<?php
namespace RevenantBlue\Site;

class CommentHierarchy {

	private static $comments;

	// Build the tree structure.
	public static function buildComments($limit, $offset, $articleId = NULL) {
		self::$comments = new Comments;
		// Get all of the root nodes.
		if(isset($articleId)) {
			$rootNodes = self::$comments->getRootNodesForArticle($limit, $offset, $articleId, 1);
		} else {
			$rootNodes = self::$comments->getRootNodesWithLimit($limit, $offset);
		}
		$tree = array();
		if(!empty($rootNodes)) {
			foreach($rootNodes as $comment) {
				$tree[] = array(
					'id'           => $comment['com_id'],
					'articleId'    => $comment['article_id'],
					'ancestor'     => $comment['ancestor'],
					'root'         => $comment['com_id'],
					'rootDistance' => $comment['root_distance'],
					'pathLength'   => $comment['path_length'],
					'name'         => $comment['com_author'],
					'published'    => $comment['com_published'],
					'content'      => $comment['com_content'],
					'ip'           => $comment['com_ip'],
					'date'         => $comment['com_date'],
					'likes'        => $comment['com_likes'],
					'dislikes'     => $comment['com_dislikes'],
					'flags'        => $comment['com_flags'],
					'children'     => self::getTree($comment['ancestor'], $comment['com_id'])
				);
			}
		}
		return $tree;
	}

	public static function getTree($rootid, $root) {
		self::$comments = new Comments;
		$arr = array();
		$result = self::$comments->getAllChildNodes($rootid);
		// Regressively find all children of each root.
		foreach($result as $row) {
			$arr[] = array(
				'id'           => $row['com_id'],
				'articleId'    => $row['article_id'],
				'ancestor'     => $row['ancestor'],
				'root'         => $root,
				'rootDistance' => $row['root_distance'],
				'pathLength'   => $row['path_length'],
				'name'         => $row['com_author'],
				'published'    => $row['com_published'],
				'content'      => $row['com_content'],
				'ip'           => $row['com_ip'],
				'date'         => $row['com_date'],
				'likes'        => $row['com_likes'],
				'dislikes'     => $row['com_dislikes'],
				'flags'        => $row['com_flags'],
				'children'     => self::getTree($row["descendant"], $root)
			);
		}
	   return $arr;
	}

	public static function displayComments($inarray, &$toarray = array()) {
		foreach($inarray as $inkey => $inval) {
			$toarray[$inval['id']]['root_distance'] = $inval['rootDistance'];
			$toarray[$inval['id']]['com_author'] = $inval['name'];
			$toarray[$inval['id']]['com_id'] = $inval['id'];
			$toarray[$inval['id']]['article_id'] = $inval['articleId'];
			$toarray[$inval['id']]['com_published'] = $inval['published'];
			$toarray[$inval['id']]['com_content'] = $inval['content'];
			$toarray[$inval['id']]['com_ip'] = $inval['ip'];
			$toarray[$inval['id']]['com_date'] = $inval['date'];
			$toarray[$inval['id']]['com_likes'] = $inval['likes'];
			$toarray[$inval['id']]['com_dislikes'] = $inval['dislikes'];
			$toarray[$inval['id']]['com_flags'] = $inval['flags'];
			$toarray[$inval['id']]['ancestor'] = $inval['ancestor'];
			$toarray[$inval['id']]['root'] = $inval['root'];
			$toarray[$inval['id']]['path_length'] = $inval['pathLength'];
			if(is_array($inval['children'])) {
				self::displayComments($inval['children'], $toarray);
			}
		}
		if(count($inarray == 1)) return $toarray;
	}
}
