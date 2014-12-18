<?php
namespace RevenantBlue\Site;

class MenuHierarchy {

	private static $menus;
	
	// Build the tree structure.
	public static function buildMenus($limit, $offset) {	
		self::$menus = new Menus;
		$rootNodes = self::$menus->getRootNodesWithLimit($limit, $offset);
		$tree = array();
		if(!empty($rootNodes)) {
			foreach($rootNodes as $menu) {
				$tree[] = array(
					'id'           => $menu['id'],
					'ancestor'     => $menu['ancestor'],
					'root'         => $menu['id'],
					'rootDistance' => $menu['root_distance'],
					'pathLength'   => $menu['path_length'],	
					'name'         => $menu['menu_name'],
					'alias'        => $menu['menu_alias'],
					'url'          => $menu['menu_url'],
					'target'       => $menu['menu_target'],
					'published'    => $menu['published'],
					'orderOfItem'  => $menu['order_of_item'],
					'image'        => $menu['image'],
					'imageAlt'     => $menu['image_alt'],
					'imagePath'    => $menu['image_path'],
					'children'     => self::getTree($menu['ancestor'], $menu['id'])
				);
			}
		}
		return $tree;
	}
	
	public static function buildMenu($menuAlias) {
		self::$menus = new Menus;
		$menu = self::$menus->getMenuByAlias($menuAlias);
		$tree = array();
		if(!empty($menuAlias)) {
			$tree = array(
				'id'           => $menu['id'],
				'ancestor'     => $menu['ancestor'],
				'root'         => $menu['id'],
				'rootDistance' => $menu['root_distance'],
				'pathLength'   => $menu['path_length'],	
				'name'         => $menu['menu_name'],
				'alias'        => $menu['menu_alias'],
				'url'          => $menu['menu_url'],
				'target'       => $menu['menu_target'],
				'published'    => $menu['published'],
				'orderOfItem'  => $menu['order_of_item'],
				'image'        => $menu['image'],
				'imageAlt'     => $menu['image_alt'],
				'imagePath'    => $menu['image_path'],
				'children'     => self::getTree($menu['ancestor'], $menu['id'])
			);
		}
		return $tree;
	}

	public static function getTree($rootId, $root) {
		self::$menus = new Menus;
		$arr = array();
		$result = self::$menus->getAllChildNodes($rootId);
		if(!empty($result) && is_array($result)) {
			foreach($result as $row) { 
				$arr[] = array(
					'id'           => $row['id'],
					'ancestor'     => $row['ancestor'],
					'root'         => $root,
					'rootDistance' => $row['root_distance'],
					'pathLength'   => $row['path_length'],
					'name'         => $row['menu_name'],
					'alias'        => $row['menu_alias'],
					'url'          => $row['menu_url'],
					'target'       => $row['menu_target'],
					'published'    => $row['published'],
					'orderOfItem'  => $row['order_of_item'],
					'image'        => $row['image'],
					'imageAlt'     => $row['image_alt'],
					'imagePath'    => $row['image_path'],
					'children'     => self::getTree($row['descendant'], $root)
				);
			}
		}
	   return $arr;
	}

	public static function displayMenus($inarray, &$toarray = array()) {
		foreach($inarray as $inkey => $inval) { 
			$toarray[$inval['id']]['root_distance'] = $inval['rootDistance'];
			$toarray[$inval['id']]['name'] = $inval['name'];
			$toarray[$inval['id']]['alias'] = $inval['alias'];
			$toarray[$inval['id']]['url'] = $inval['url'];
			$toarray[$inval['id']]['id'] = $inval['id'];
			$toarray[$inval['id']]['published'] = $inval['published'];
			$toarray[$inval['id']]['order_of_item'] = $inval['orderOfItem'];
			$toarray[$inval['id']]['ancestor'] = $inval['ancestor'];
			$toarray[$inval['id']]['root'] = $inval['root'];
			$toarray[$inval['id']]['path_length'] = $inval['pathLength'];
			$toarray[$inval['id']]['target'] = $inval['target'];
			$toarray[$inval['id']]['image'] = $inval['image'];
			$toarray[$inval['id']]['imageAlt'] = $inval['imageAlt'];
			$toarray[$inval['id']]['imagePath'] = $inval['imagePath'];
			if(is_array($inval['children'])) {
				self::displayMenus($inval['children'], $toarray);
			}
		}
		if(count($inarray == 1)) return $toarray;
	}
}
