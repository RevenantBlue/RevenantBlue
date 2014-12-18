<?php

namespace RevenantBlue;

class TableSorter {
	// Create the link for table sorting based off the current GET order and sort variables.
	public static function sortLink($url, $query, $order, $defaultSort = 'asc', $default = FALSE) {
		// Add the backend name to the url
		$url = BACKEND_NAME . $url;
		// Create the table links if the GET order and sort variable are set.
		if(isset($_GET['order']) && isset($_GET['sort'])) {
			if(isset($_GET[$query])) {
				$columnUrl = HTTP_SERVER . $url . "?" . $query . "=" . $_GET[$query] . "&amp;order=" . $order . "&amp;sort=";
			} else {
				$columnUrl = HTTP_SERVER . $url . "?order=" . $order . "&sort=";
			}
			if($_GET['order'] == $order && $_GET['sort'] == 'asc') {
				$columnUrl .= "desc";
			} elseif($_GET['order'] == $order && $_GET['sort'] == 'desc') {
				$columnUrl .= "asc";
			} else {
				$columnUrl .= $defaultSort;
			}
		// If a default columns to order and sort type have been selected display the appropriate link.
		} elseif(!empty($default) && !isset($_GET['order']) && !isset($_GET['sort'])) {
			if($defaultSort == 'asc') {
				$columnUrl = HTTP_SERVER . $url . "?order=" . $order . "&amp;sort=desc";
			} elseif($defaultSort == 'desc') {
				$columnUrl = HTTP_SERVER . $url . "?order=" . $order . "&amp;sort=asc";
			}
		// Use the default sort if set.
		} elseif(!isset($_GET['order']) && !isset($_GET['sort']) && isset($defaultSort)) {
			$columnUrl = HTTP_SERVER . $url . "?order=" . $order . "&amp;sort=" . $defaultSort;
		// If no default was selected for this column then make the default sort ascending.
		} else {
			$columnUrl = HTTP_SERVER . $url . "?order=" . $order . "&amp;sort=asc";
		}
		return $columnUrl;
	}
	public static function displaySortIcon($order, $defaultSort = 'asc', $default = FALSE) {
		// Display the ascending icon.
		if(isset($_GET['order']) && $_GET['order'] == $order && $_GET['sort'] == 'asc') {
			$sortIcon =  '<span class="ui-icon ui-icon-triangle-1-n"></span>';
		// Display the descending icon.
		} elseif(isset($_GET['order']) && $_GET['order'] == $order && $_GET['sort'] == 'desc') {
			$sortIcon = '<span class="ui-icon ui-icon-triangle-1-s"></span>';
		// Display the ascending/descending icon depending on the default sort setting.
		} elseif($default == TRUE && !isset($_GET['order']) && !isset($_GET['sort'])) {
			if($defaultSort = 'asc') {
				$sortIcon = '<span class="ui-icon ui-icon-triangle-1-n"></span>';
			} elseif($defaultSort = 'desc') {
				'<span class="ui-icon ui-icon-triangle-1-s"></span>';
			}
		}
		if(isset($sortIcon)) return $sortIcon;
	}
}
