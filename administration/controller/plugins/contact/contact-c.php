<?php 

require_once DIR_ADMIN . 'model/modules/contact/contact-main.php';

$contacts = new Contact;

if(isset($_GET['order']) && isset($_GET['sort']) && in_array($_GET['order'], $contacts->whiteList)) { 			
	$contactList = $contacts->loadMessages($_GET['order'], $_GET['sort']);
} else {
	$contactList = $contacts->loadMessages('create_date', 'desc');
}

if($_SERVER['REQUEST_METHOD'] === "POST") {
	if(isset($_POST['contactCheck']) && aclVerify('administer modules')) {
		foreach($_POST['contactCheck'] as $key => $contact) {
			$success[] = $contacts->deleteContact($contact);
		}
		if(!in_array(FALSE, $success)) {
			$_SESSION['success'] = "Contact message(s) successfully deleted.";
		} else {
			$_SESSION['errors'] = "An error occured while deleting one or more contact messages";
		}
		header('Location: ' . $_SERVER['REQUEST_URI'], TRUE, 302);
		exit;
	}
}
