<?php

	// This class uses phpmailer to send out emails, one at a time from the app

	class Mail {
		private $mailer;
		private $application_name;
		private $application_email;

		public function __construct() {
			// Create an instance of the PHPMailer
			$this->mailer = new PHPMailer();
			$this->setApplicationName(APPLICATION_NAME);
			$this->setApplicationEmail(APPLICATION_EMAIL);
		}

		public function setApplicationName($app_name) {
			// Define an applicatin name in /app/settings/config.php
			$this->application_name = $app_name;
			return $this;
		}

		public function getApplicationName() {
			return $this->application_name;
		}

		public function setApplicationEmail($app_email) {
			// Define an application email in /app/settings/config.php
			$this->application_email = $app_email;
			return $this;
		}

		public function getApplicationEmail() {
			return $this->application_email;
		}

		// Sendmail function, use this to send outgoing emails
		public function sendmail($to_name, $to_email, $from_name = '', $from_email = '', $subject, $body, $altbody = '') {
			if($from_name == '') $from_name = APPLICATION_NAME;
			if($from_email == '') $from_email = APPLICATION_EMAIL;

			if(!$subject) {
				display_system("The subject has not been set");
			} else if(!$body) {
				display_system("The body has not been set");
			} else {
				$this->mailer->From = $from_email;
				$this->mailer->FromName = $from_name;
				$this->mailer->AddAddress($to_email, $to_name);
				$this->mailer->AddBCC($from_email, $from_name);
				$this->mailer->IsHTML(true);
				if($body == '')
					$this->mailer->IsHTML(false);

				$this->mailer->Subject = $subject;
				$this->mailer->Body = $body;
				$this->mailer->AltBody = $altbody;

				if(!$this->mailer->Send()) {
					display_error("The email from " . $from_name. " (" . $from_email. ") to " . $to_name. " (" . $to_email. ") could not be sent. The mailer replied with the following error :: " . $this->mailer->ErrorInfo . ".<br />The contents of the email were as follows :<br /><b>" . $subject . "</b><br />" . $body . "");
					return false;
				}

				// Need to do this, otherwise recipients will keep adding up
				$this->mailer->ClearAllRecipients();
				return true;
			}
		}
	}

?>
