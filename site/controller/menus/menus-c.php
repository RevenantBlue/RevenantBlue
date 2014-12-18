<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'controller/menus/menu-hierarchy.php';
require_once DIR_APPLICATION . 'model/menus/menus-main.php';

$menus = new Menus;
// Menu page requirements

class MenuBuilder {
	
	public static $id;
	public static $className;
	public static $title;
	public static $style;
	
	public static function setProperties($id = FALSE, $classname = FALSE, $title = FALSE, $style = FALSE) {
		if(!empty($id)) {
			self::$id = $id;
			self::$className = $className;
			self::$title = $title;
			self::$style = $style;
		}
	}
	
	public static function displayMenu($menuAlias, $id, $className, $finalMenu = FALSE, $currentNode = FALSE) {
		// This method returns a hierarchical menu structure using unorderd lists.
		if(empty($finalMenu)) {
			$menu = MenuHierarchy::buildMenu($menuAlias);
			
			// Set properties
			self::$id = $id;
			self::$className = $className;
			
			if(!empty($menu) && is_array($menu)) {
				//print_r2($menu);
				//Build the Menu
				$finalMenu .= '<ul';
				// Check if the id has been set and include it in the initial ul for the menu.
				if(!empty(self::$id)) {
					 $finalMenu .= ' id="' . self::$id . '"';
				}
				// Check if the class has been set and include it in the initial ul for the menu.
				if(!empty(self::$classname)) {
					$finalMenu .= ' class="' . self::$className . '"';
				}
				
				if(!empty(self::$title)) {
					$finalMenu .= ' title="' . self::$title . '"';
				}
				
				if(!empty(self::$style)) {
					$finalMenu .= ' style="' . self::$style . '"';
				}
				
				$finalMenu .= '>';
				
				// Iterate through the children of the menu, the first array in $menu is the root node which will not be included.
				foreach($menu['children'] as $node) {
					//print_r2($node);
					$finalMenu .= '<li class="menu-depth-' . hsc($node['rootDistance']) . '">';
					
					if(!empty($node['image'])) {
						$finalMenu .= '<a href="' . hsc($node['url']) . '">' .
										'<img src="' . HTTP_IMAGE . 'articles/' . $node['image'] . '" alt="' . $node['imageAlt'] . '" target="' . $node['target'] . '" />' .
									 '</a>';
					} else {
						$finalMenu .= '<a href="' . hsc($node['url']) . '" target="' . $node['target'] . '">' . hsc($node['name']) . '</a>';
					}
									
								  '</li>';
					// Recursively build the rest of the menu to include all descendants.
					if(!empty($node['children'])) {
						$finalMenu .= '<ul class="menu-grp-' . $node['rootDistance'] . '">';
						foreach($node['children'] as $childNode) {
							$finalMenu .= '<li class="menu-depth-' . hsc($childNode['rootDistance']) . '">'. 
												'<a href="' . hsc($childNode['url']) . '">' . hsc($childNode['name']) . '</a>' .  
										  '</li>';
							if(!empty($childNode['children'])) {
								$finalMenu = self::displayMenu('', '', '', $finalMenu, $childNode);
							}
						}
						$finalMenu .= '</ul>';
					}
				}
				$finalMenu .= '</ul>';
				
			}
		} elseif(!empty($finalMenu) && !empty($currentNode)) {
			// This section only executes during recursion to build submenus.
			if(is_array($currentNode)) {
				$finalMenu .= '<ul class="menu-grp-' . $currentNode['rootDistance'] . '">';
				foreach($currentNode['children'] as $node) {
					//print_r2($node);
					$finalMenu .= '<li class="menu-depth-' . hsc($node['rootDistance']) . '">' . 
										'<a href="' . hsc($node['url']) . '">' . hsc($node['name']) . '</a>' . 
								  '</li>';
					if(!empty($node['children'])) {
						$finalMenu .= '<ul>';
						foreach($node['children'] as $childNode) {
							$finalMenu .= '<li class="menu-depth-' . hsc($childNode['rootDistance']) . '">' . 
												'<a href="' . hsc($childNode['url']) . '">' . hsc($childNode['name']) . '</a>' .
										  '</li>';
						}
						$finalMenu .= '</ul>';
					}
				}
				$finalMenu .= '</ul>';
			} 
		}
		
		return $finalMenu;
	}
}
