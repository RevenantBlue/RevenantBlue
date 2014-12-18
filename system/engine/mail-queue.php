<?php
namespace RevenantBlue;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_Mailer;
use \PDO;

class MailHandler extends Db {
	
	private $mailTable;
	
	public function __construct() {
		$this->mailTable = PREFIX . 'mail_queue';
	}
	
	public function loadMailToSend() {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("SELECT * FROM $this->mailTable");
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function insertMailToSend($mail) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare(
				"INSERT INTO $this->mailTable
				   (mail_subject, mail_body, mail_to, mail_from, mail_to_first_name, mail_to_last_name, mail_sender, mail_to_user_id)
				 VALUES
				   (:subject, :body, :to, :from, :toFirst, :toLast, :sender, :toId)"
			);
			$stmt->bindParam(':subject', $mail['subject'], PDO::PARAM_STR);
			$stmt->bindParam(':body', $mail['body'], PDO::PARAM_STR);
			$stmt->bindParam(':to', $mail['to'], PDO::PARAM_STR);
			$stmt->bindParam(':from', $mail['from'], PDO::PARAM_STR);
			$stmt->bindParam(':toFirst', $mail['toFirst'], PDO::PARAM_STR);
			$stmt->bindParam(':toLast', $mail['toLast'], PDO::PARAM_STR);
			$stmt->bindParam(':sender', $mail['fromName'], PDO::PARAM_STR);
			$stmt->bindParam(':toId', $mail['toId'], PDO::PARAM_INT);
			$stmt->execute();
			return self::$dbh->lastInsertId();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function deleteSentMail($mailId) {
		try {
			if(!self::$dbh) $this->connect();
			$stmt = self::$dbh->prepare("DELETE FROM $this->mailTable WHERE id = :id");
			$stmt->bindParam(':id', $mailId, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->rowCount();
		} catch(PDOException $e) {
			$this->errorLog($e);
		}
	}
	
	public function sendMail($email, $ssl = FALSE) {
		global $globalSettings;
		
		// Create a new instance of swift mailer, fill in the appropriate values, and send the email.
		$message = Swift_Message::newInstance();
		$message->setSubject($email['subject']);
		$message->setBody($email['body'], 'text/html');
		$message->setTo($email['to'], $email['toFirst'] . ' ' . $email['toLast']);
		$message->setFrom($email['from'], $email['fromName']);
		$message->setSender($email['from']);
		
		if($ssl === TRUE) {
			$transport = Swift_SmtpTransport::newInstance($globalSettings['smtp_server']['value'], $globalSettings['smtp_port']['value'], "ssl");
		} else {
			$transport = Swift_SmtpTransport::newInstance($globalSettings['smtp_server']['value'], $globalSettings['smtp_port']['value']);
		}
		
		$transport->setUsername($globalSettings['email_username']['value']);
		$transport->setPassword($globalSettings['email_password']['secure_value']);
		$mailer = Swift_Mailer::newInstance($transport);
		$mailSent = $mailer->send($message);
	}
}
