<?php
namespace RevenantBlue;
use Swift_Validate;

// Since this script is called from the command line
// we need to set the directories so we can load our configuration and mail-queue object.

$webRoot = dirname(dirname(dirname(__FILE__)));
$cwd = dirname(dirname(__FILE__));

require_once $webRoot . '/config.php';
require_once DIR_DATABASE . 'db.php';
require_once DIR_DATABASE . 'redis.php';
require_once DIR_ADMIN . 'model/config/config-main.php';
require_once DIR_ADMIN . 'startup.php';
require_once DIR_SYSTEM . 'engine/mail-queue.php';
// Require the swiftmailer library.
require_once DIR_SYSTEM . 'library/swiftmailer/lib/swift_required.php';

$mh = new MailHandler;

$mailToSend = $mh->loadMailToSend();

// If there is mail to send - send it.
if(!empty($mailToSend) && is_array($mailToSend)) {
	
	foreach($mailToSend as $mail) {
		
		// Build the email to send
		$email['subject'] = $mail['mail_subject'];
		$email['body'] = $mail['mail_body'];
		$email['to'] = $mail['mail_to'];
		$email['toFirst'] = $mail['mail_to_first_name'];
		$email['toLast'] = $mail['mail_to_last_name'];
		$email['from'] = $mail['mail_from'];
		$email['fromName'] = $mail['mail_sender'];
		
		if(Swift_Validate::email($email['to']) ){
			// Send the email
			if((int)$globalSettings['smtp_port']['value'] === 465) {
				$mh->sendMail($email, TRUE);
			} else {
				$mh->sendMail($email);
			}
			
			// Delete the sent email.
			$mh->deleteSentMail((int)$mail['id']);
		}
	}
}
