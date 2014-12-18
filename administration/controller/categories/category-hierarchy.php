<?php
namespace RevenantBlue\Admin;

class CategoryHierarchy {

	private static $categories;
	
	// Build the tree structure.
	public static function buildCategories($limit, $offset) {	
		self::$categories = new Categories;
		$rootNodes = self::$categories->getRootNodesWithLimit($limit, $offset);
		$tree = array();
		if(!empty($rootNodes)) {
			foreach($rootNodes as $category) {
				$tree[] = array(
					"id"           => $category['cat_id'],
					"ancestor"     => $category['ancestor'],
					"root"         => $category['cat_id'],
					"rootDistance" => $category['root_distance'],
					"pathLength"   => $category['path_length'],	
					"name"         => $category['cat_name'],
					"alias"        => $category['cat_alias'],
					"published"    => $category['cat_published'],
					"orderOfItem"  => $category['cat_order_of_item'],
					"children"     => self::getTree($category['ancestor'], $category['cat_id']));
			}
		}
		return $tree;
	}

	public static function getTree($rootid, $root) {
		self::$categories = new Categories;
		$arr = array();
		$result = self::$categories->getAllChildNodes($rootid);
		foreach($result as $row) { 
			$arr[] = array(
				"id"           => $row['cat_id'],
				"ancestor"     => $row['ancestor'],
				"root"         => $root,
				"rootDistance" => $row['root_distance'],
				"pathLength"   => $row['path_length'],
				"name"         => $row['cat_name'],
				"alias"        => $row['cat_alias'],
				"published"    => $row["cat_published"],
				"orderOfItem"  => $row['cat_order_of_item'],
				"children"     => self::getTree($row["descendant"], $root)
			);
		}
	   return $arr;
	}

	public static function displayCategories($inarray, &$toarray = array()) {
		foreach($inarray as $inkey => $inval) { 
			$toarray[$inval['id']]['root_distance'] = $inval['rootDistance'];
			$toarray[$inval['id']]['cat_name'] = $inval['name'];
			$toarray[$inval['id']]['cat_alias'] = $inval['alias'];
			$toarray[$inval['id']]['cat_id'] = $inval['id'];
			$toarray[$inval['id']]['cat_published'] = $inval['published'];
			$toarray[$inval['id']]['cat_order_of_item'] = $inval['orderOfItem'];
			$toarray[$inval['id']]['ancestor'] = $inval['ancestor'];
			$toarray[$inval['id']]['root'] = $inval['root'];
			$toarray[$inval['id']]['path_length'] = $inval['pathLength'];
			if(is_array($inval['children'])) {
				self::displayCategories($inval['children'], $toarray);
			}
		}
		if(count($inarray == 1)) return $toarray;
	}
}
