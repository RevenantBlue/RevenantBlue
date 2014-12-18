<?php
namespace RevenantBlue\Admin;

class ForumHierarchy {

	private static $forums;

	// Build the tree structure.
	public static function buildForums($limit, $offset) {
		self::$forums = new Forums;
		$rootNodes = self::$forums->getRootNodesWithLimit($limit, $offset);
		$tree = array();
		if(!empty($rootNodes)) {
			foreach($rootNodes as $forum) {
				$tree[] = array("id"          => $forum['id'],
								"ancestor"     => $forum['ancestor'],
								"root"         => $forum['id'],
								"rootDistance" => $forum['root_distance'],
								"pathLength"   => $forum['path_length'],
								"title"        => $forum['forum_title'],
								"alias"        => $forum['forum_alias'],
								"published"    => $forum['published'],
								"featured"     => $forum['featured'],
								"weight"       => $forum['weight'],
								"archived"     => $forum['archived'],
								"children"     => self::getTree($forum['ancestor'], $forum['id']));
			}
		}
		return $tree;
	}

	public static function getTree($rootId, $root) {
		self::$forums = new Forums;
		$arr = array();
		$result = self::$forums->getAllChildNodes($rootId);
		foreach($result as $row) {
			$arr[] = array(
				"id"           => $row['id'],
				"ancestor"     => $row['ancestor'],
				"root"         => $root,
				"rootDistance" => $row['root_distance'],
				"pathLength"   => $row['path_length'],
				"title"        => $row['forum_title'],
				"alias"        => $row['forum_alias'],
				"published"    => $row['published'],
				"featured"     => $row['featured'],
				"weight"       => $row['weight'],
				"archived"     => $row['archived'],
				"children"     => self::getTree($row['descendant'], $root)
			);
		}
	   return $arr;
	}

	public static function displayForums($inarray, &$toarray = array()) {
		foreach($inarray as $inkey => $inval) {
			$toarray[$inval['id']]['root_distance'] = $inval['rootDistance'];
			$toarray[$inval['id']]['forum_title'] = $inval['title'];
			$toarray[$inval['id']]['forum_alias'] = $inval['alias'];
			$toarray[$inval['id']]['id'] = $inval['id'];
			$toarray[$inval['id']]['archived'] = $inval['archived'];
			$toarray[$inval['id']]['weight'] = $inval['weight'];
			$toarray[$inval['id']]['ancestor'] = $inval['ancestor'];
			$toarray[$inval['id']]['root'] = $inval['root'];
			$toarray[$inval['id']]['path_length'] = $inval['pathLength'];
			if(is_array($inval['children'])) {
				self::displayForums($inval['children'], $toarray);
			}
		}
		if(count($inarray == 1)) return $toarray;
	}
}
