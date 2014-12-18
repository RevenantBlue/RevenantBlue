<?php

namespace RevenantBlue;

class SimplePaginator {

	public  $page;
	public  $totalRecords;
	public  $currentPage;
	public  $queryString;
	public  $menu;
	public  $prevLink;
	public  $nextLink;
	public  $navClass = 'nav-direction';
	public  $previousTitle = "Previous Photo";
	public  $nextTitle = "Next Photo";

	public function __construct() {
		$this->page = htmlspecialchars($_SERVER['PHP_SELF']);
	}
	
	public function trimPage($needle, $replaceWith = '') {
		$this->page = str_replace($needle, $replaceWith, $this->page);
	}

	public function paginate() {
		
		// Check the value of the GET page request.
		if(!empty($_GET['page']) && is_numeric($_GET['page'])) {
			$this->currentPage = (int)htmlspecialchars($_GET['page']);
		}
		if(empty($_GET['page']) && empty($this->currentPage)) {
			$this->currentPage = 1;
		}
		
		
		// Ensure that the current page is within the appropriate boundries.
		if($this->currentPage > $this->totalRecords) {
			$this->currentPage = 1;
		}
		if($this->currentPage <= 0) {
			$this->currentPage = $this->totalRecords;
		}
		
		// Handle get requests
		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$numberOfArgs = substr_count($_SERVER['QUERY_STRING'], '=');
			if($numberOfArgs >= 1) {
				$args = explode('&', $_SERVER['QUERY_STRING']);
				foreach($args as $arg) {
					$values = explode('=', $arg);
					if($values[0] === 'controller') {
						continue;
					}
					if(!isset($this->queryString) && $values[0] != 'page' && $values[0] != 'limit') {
						$this->queryString = '&amp;' . $arg;
					} else {
						if(isset($this->queryString)) $this->queryString .= '&amp;' . $arg;
					}
				}
				if($this->queryString === "&amp;") {
					$this->queryString = '';
				}
			}
		}

		// Build the menu.
		if(!isset($this->menu)) { 
			$this->menu = "<ul>\n";
		}
		
		
		if(!isset($prevPage)) {
			$prevPage = (int)$this->currentPage - 1;
		}
		
		//print_r2($this->currentPage);
		//print_r2(var_dump($prevPage));
		//print_r2($prevPage);
		
		if($prevPage <= 0) {
			$prevPage = $this->totalRecords;
		}
		
		//print_r2($prevPage);
		
		$this->menu .= "\t<li class=\"$this->navClass\"><a title=\"$this->previousTitle\" href=\"$this->page?page=$prevPage" . $this->queryString ."\">Previous</a></li>\n";

		if(!isset($nextPage)) {
			$nextPage = (int)$this->currentPage + 1;
		}
		
		if($nextPage > $this->totalRecords) {
			$nextPage = 1;
		}
		
		$this->menu .= "\t<li class=\"$this->navClass\"><a title=\"$this->nextTitle\" href=\"$this->page?page=$nextPage" . $this->queryString . "\">Next</a></li>\n";
		$this->menu .= "</ul>";
		$this->prevLink = 'title="' . $this->previousTitle . '" href="' . $this->page . '?page=' . $prevPage . $this->queryString . '"';
		$this->nextLink = 'title="' . $this->nextTitle . '" href="' . $this->page . '?page=' . $nextPage . $this->queryString . '"';
	}
}
