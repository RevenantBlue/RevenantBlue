<?php

namespace RevenantBlue;

class Pager extends Db {

	// This page is made specifically to be used on the PDO Database abstraction layer.

	// To use: set $totalRecords and run the paginate method.

	public   $menu;                                // The pagination links that will display on the screen.
	public   $limit;                               // Number of items to show per page.
	private  $defaultLimit = 10;                   // The defaul number of items to show per page.
	public   $offset = 0;                          // The offset for displaying the correct results from the database.
	public   $totalRecords;                        // The total number of items to paginate.
	private  $numOfPages;                          // The number of pages that will be shown.
	public   $page;                                // Default location of the page, is set to $_SERVER['PHP_SELF'] in the construct.
	private  $queryString;                         // The query string that should be added the page GET argument.
	private  $currentPage;                         // The current page that is dispalyed to the browser.
	public   $delta = 7;                           // The range of pages to show, should be odd so an even number of pages appear on both sides of the selected page.
	private  $startRange;                          // The starting number in the range of the range of pages to show.
	private  $endRage;                             // The ending number in the range of pages to show.
	private  $ranges;                              // Contains the ranges of numbers to show in the pagination menu.
	public   $alwaysShowPrev = false;              // If you want to always show the previous option on the nav menu, set to true.
	public   $alwaysShowNext = false;              // If you want to always show the next option on the nav menu, set it to true.
	public   $previousName = "Previous";           // The name to show for the previous button.  Default is "Prev".
	public   $nextName = "Next";                   // The name to show for the next button.  Default is "Next".
	public   $firstAndLast = TRUE;                 // Controls whether or not to show the first and last page control on the pagination menu.  Default is false.
	public   $limitMenu;
	private  $unselectedClass = "paginate";       // Class name for the CSS class that controls the style for the unselected links in the pagination menu.
	private  $selectedClass = "selected";         // Class name for the CSS class that controls the style for the selected links in the pagination menu.
	private  $navClass = "paginationNav";         // Class name for the CSS class that controls the style for the navigation in the pagination menu.
	private  $previousInactive = "prevInactive";  // Class name that styles the previous li property when it is inactive.
	private  $nextInactive = "nextInactive";      // Class name that styles the next li property when it is inactive.

	public function __construct() {
		$this->page = htmlspecialchars($_SERVER['PHP_SELF']);
	}
	
	public function trimPage($needle, $replaceWith = '') {
		$this->page = str_replace($needle, $replaceWith, $this->page);
	}

	public function paginate() {
		// If the limit (records to show per page) is empty set it to the default limit.
		if(empty($this->limit)) $this->limit = $this->defaultLimit;

		// If the limit GET variable has been set assign the limit property to its value.
		if(isset($_GET['limit']) && $_GET['limit'] !== 'All') {
			$this->limit = (int)$_GET['limit'];
		} elseif((isset($_GET['limit']) && $_GET['limit'] === 'All') || isset($this->limit) && $this->limit === 'All') {
			$this->limit = (int)$this->totalRecords;
		}
		// Since we do not want to divide by zero if the limit is zero assign a zero limit to the default limit.
		if($this->limit === 0) $this->limit = $this->defaultLimit;
		// Ensure that limit is an integer at this point
		if(is_string($this->limit)) $this->limit = (int)$this->limit;
		// Set the number of pages - we get this by dividing the totalRecords sessions variable by the limit or records to show perpage.
		if(!isset($this->numOfPages)) $this->numOfPages = ceil($this->totalRecords / $this->limit);

		// Check the GET variable, if it isn't empty assign the value of it to the currentPage property.
		if(!empty($_GET['page']) && is_numeric($_GET['page'])) $this->currentPage = (int)htmlspecialchars($_GET['page']);
		if(empty($_GET['page'])) $this->currentPage = 1;

		// Make sure the currentpage is valid.
		if($this->currentPage > $this->numOfPages) $this->currentPage = $this->numOfPages;
		if($this->currentPage < 1) $this->currentPage = 1;

		// Set the offset
		$this->offset = ($this->currentPage - 1) * $this->limit;

		// Set the range of pages to show
		$this->startRange = $this->currentPage - floor($this->delta/2);
		$this->endRange = $this->currentPage + floor($this->delta/2);

		if($this->startRange <= 0) {
			$this->endRange += abs($this->startRange)+1;
			$this->startRange = 1;
		}

		if($this->endRange > $this->numOfPages) {
			$this->startRange -= $this->endRange - $this->numOfPages;
			$this->endRange = $this->numOfPages;
		}

		// The ranges property contains an array that will ensure the pages we see are within the visible range.
		$this->ranges = range($this->startRange, $this->endRange);

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

		// Build the Menu
		if(!isset($this->menu)) $this->menu = "<ul>\n";

		//Display &laquo; to skip to the first result if firstAndLast is set to true
		if($this->firstAndLast == true  && $this->currentPage > 1) {
			if(isset($_GET['limit'])) {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"First page\" href=\"$this->page?page=1&limit=$this->limit$this->queryString\">&laquo; </a></li>\n";
			} else {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"First page\" href=\"$this->page?page=1$this->queryString\">&laquo; </a></li>\n";
			}
		}

		// If the previous page variable has not been set set it to the current page minus one
		if(!isset($prevPage)) $prevPage = $this->currentPage - 1;

		// Setup the previous nav option functionality and allow it to be shown all the time or on every page but page one depending on the value of alwaysShowPrev.
		if($this->currentPage > 1 && $this->alwaysShowPrev == false) {
			if(isset($_GET['limit'])) {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Previous Page\" href=\"$this->page?page=$prevPage&limit=$this->limit$this->queryString\">$this->previousName</a></li>\n";
			} else {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Previous Page\" href=\"$this->page?page=$prevPage$this->queryString\">$this->previousName</a></li>\n";
			}
		} elseif($this->alwaysShowPrev == true) {
			if($this->currentPage == 1) {
				$this->menu .= "<li class=\"$this->previousInactive\">$this->previousName</li>\n";
			} else {
				if(isset($_GET['limit'])) {
					$this->menu .= "<li class=\"$this->navClass\"><a title=\"Previous Page\" href=\"$this->page?page=$prevPage&limit=$this->limit$this->queryString\">$this->previousName</a></li>\n";
				} else {
					$this->menu .= "<li class=\"$this->navClass\"><a title=\"Previous Page\" href=\"$this->page?page=$prevPage$this->queryString\">$this->previousName</a></li>\n";
				}
			}
		}

		// Assign intro dots to the first three pages if the first three pages are not in range.
		if(!in_array(1, $this->ranges) && !in_array(2, $this->ranges) && !in_array(3, $this->ranges)) {
			for($x = 1; $x <= 3; $x++) {
				if(isset($_GET['limit'])) {
					$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x&limit=$this->limit$this->queryString\">$x</a></li>\n";
				} else {
					$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x$this->queryString\">$x</a></li>\n";
				}
			}
			$this->menu .= "<li>...</li>\n";
		}
		// Loop through the page numbers to display and remove unwanted page numbers.
		for($x = 1; $x <= $this->numOfPages; $x++) {
			if($x > 0 && $x <= $this->numOfPages ) {
				// Check to see if the page number is within our visible range array.
				if(in_array($x, $this->ranges)) {
					if($x == $this->currentPage) {
						if(isset($_GET['limit'])) {
							$this->menu .= "<li class=\"$this->selectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$this->currentPage&limit=$this->limit$this->queryString\">$x</a></li>\n";
						} else {
							$this->menu .= "<li class=\"$this->selectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$this->currentPage$this->queryString\">$x</a></li>\n";
						}
					} else {
						if(isset($_GET['limit'])) {
							$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x&limit=$this->limit$this->queryString\">$x</a></li>\n";
						} else {
							$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x$this->queryString\">$x</a></li>\n";
						}
					}
				}
			}
		}
		// Assign intro dots to the first three pages if the first three pages are not in range.
		if(!in_array($this->numOfPages, $this->ranges) && !in_array(($this->numOfPages - 1), $this->ranges) && !in_array(($this->numOfPages - 2), $this->ranges)) {
			$this->menu .= "<li>...</li>\n";
			for($x = ($this->numOfPages - 2); $x <= $this->numOfPages; $x++) {
				if(isset($_GET['limit'])) {
					$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x&limit=$this->limit$this->queryString\">$x</a></li>\n";
				} else {
					$this->menu .= "<li class=\"$this->unselectedClass\"><a title=\"Page $x\" href=\"$this->page?page=$x$this->queryString\">$x</a></li>\n";
				}
			}
		}

		//Display $raquo; when the page is less than the total number of pages.
		if(!isset($nextPage)) $nextPage = $this->currentPage + 1;
		if($this->currentPage < $this->numOfPages && $this->alwaysShowNext == false) {
			if(isset($_GET['limit'])) {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Next Page\" href=\"$this->page?page=$nextPage&limit=$this->limit$this->queryString\">$this->nextName</a></li>\n";
			} else {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Next Page\" href=\"$this->page?page=$nextPage$this->queryString\">$this->nextName</a></li>\n";
			}
		} elseif($this->alwaysShowNext == true) {
			if($this->currentPage >= $this->numOfPages) {
				$this->menu .= "<li class=\"$this->nextInactive\">$this->nextName</li>\n";
			} else {
				if(isset($_GET['limit'])) {
					$this->menu .= "<li class=\"$this->navClass\"><a title=\"Next Page\" href=\"$this->page?page=$nextPage&limit=$this->limit$this->queryString\">$this->nextName</a></li>\n";
				} else {
					$this->menu .= "<li class=\"$this->navClass\"><a title=\"Next Page\" href=\"$this->page?page=$nextPage$this->queryString\">$this->nextName</a></li>\n";
				}
			}
		}

		// Test to to if the first and last option is set to true, if it is show the option to click to the end of the page results.
		if(isset($this->menu) && $this->firstAndLast == true) {
			if(isset($_GET['limit'])) {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Last Page\" href=\"$this->page?page=$this->numOfPages&limit=$this->limit$this->queryString\"> &raquo;</a></li>\n";
			} else {
				$this->menu .= "<li class=\"$this->navClass\"><a title=\"Last Page\" href=\"$this->page?page=$this->numOfPages$this->queryString\"> &raquo;</a></li>\n";
			}
		}
		// End the Menu
		if(isset($this->menu)) $this->menu .= "</ul>\n";

		// If the number of records is less than the limit, disable the menu.
		if($this->limit >= $this->totalRecords) $this->menu = FALSE;
	}

	public function displayItemsPerPage() {
		$items = '';
		$ipp_array = array(10,25,50,100,500,1000);
		// Sort the array for correct display.
		sort($ipp_array);
		// Add the all value to the end, saved for last as to not screw up the sorting.
		$ipp_array[] = 'All';
		foreach($ipp_array as $ipp_opt) {
			if($ipp_opt == $this->limit && $this->limit !== (int)$this->totalRecords) {
				$items .= "<option selected value=\"$ipp_opt\">$ipp_opt</option>\n";
			} elseif($ipp_opt === 'All' && $this->limit === (int)$this->totalRecords) {
				$items .= "<option selected value=\"$ipp_opt\">$ipp_opt</option>\n";
			} else {
				$items .= "<option value=\"$ipp_opt\">$ipp_opt</option>\n";
			}
		}
		$this->limitMenu =  "<ul class=\"items-per-page\">\n";
		$this->limitMenu .= "\t<li>\n\t\t<span>Results to Show:</span>\n\t</li>\n";
		$this->limitMenu .= 
			"<li>\n\t
				<select onchange=\"window.location='$this->page?page=1&amp;limit=' + this[this.selectedIndex].value + '$this->queryString';return false;\">
					$items
				</select>\n\t
			</li>\n
		</ul>\n";

		if($this->totalRecords === 0 || $this->totalRecords <= 10) $this->limitMenu = FALSE;
	}
}
?>
