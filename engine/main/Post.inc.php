<?php
class Post {
	use Messages;
	protected $n_circles;
	/*
	 * needed to get the contact email address
	 * from the data base
	 */
	protected $table;
	protected $field;
	protected $contact;
	protected function setTable($table) {
		$this->table = $table;
	}
	protected function set_field($field) {
		$this->field = $field;
	}
	protected function set_contact($contact) {
		$this->contact = $contact;
	}
	public function checkPost(array $values) {
		if (! $values) {
			$this->err_msg = "leere Eingabe.";
			return FALSE;
		} else if ($_SESSION ['ncirc'] != $values ['circles']) {
			$this->err_msg = "Anzahl der Kreise ist falsch.";
			return FALSE;
		} else if (! filter_var ( $values ['email'], FILTER_VALIDATE_EMAIL )) {
			$this->err_msg = "Ungültige Emailadresse.";
			return FALSE;
		} else {
			return TRUE;
		}
	}
	protected function fetchContactEmail($contact_name) {
		global $db;
		
		$qry = "select $this->field from $this->table where $this->contact = '$contact_name'";
		
		$result = $db->query ( $qry );
		if (! $result) {
			echo 'Fehler in fetchContactEmail! ' . $db->error;
		} else {
			if ($email = $result->fetch_row ()) {
				return $email [0];
			} else {
				return FALSE;
			}
		}
	}
	/**
	 * Sends a mail.
	 *
	 * @param array $email
	 *        	content and header of the email.
	 * @param string $confirm
	 *        	confirmation message to be displayed after the mail was successfully sent.
	 * @return boolean true on success, false on failure.
	 */
	protected function send(array $email, $confirm = NULL) {
		// send email and return a message
		$log = start_system_log ( "Post::send" );
		$mailer = phpMailer_init ();
		if (! $mailer) {
			$this->err_msg = "Leider gab es ein Problem mit der Email. \n";
			$log->write ( "phpMailer initiation failed", LOG_ERROR );
			return FALSE;
		}
		$mailer->addAddress ( $email ['to'] );
		$mailer->Body = $email ['message'];
		$mailer->Subject = $email ['subject'];
		$mailer->addReplyTo ( $email ['from'] );
		try {
			if ($mailer->send ())
				$this->conf_msg = $confirm;
		} catch ( phpmailerException $e ) {
			$this->err_msg = "Leider gab es ein Problem mit der Email. \n";
			$log->write ( $e->errorMessage (), LOG_WARN );
			return false;
		}
		
		return true;
	}
}
class Contact extends Post {
	private $member;
	function __construct(...$member) {
		parent::setTable ( "members" );
		parent::set_field ( "email" );
		parent::set_contact ( "name" );
		// if no members are defined get the list of all registered members
		$this->member = $member ? $member : (new Profiles ())->memberNames ();
	}
	public function showForm($iniValues = NULL) {
		// show contact form
		$form [] = '<form id="emailform" action="" method="POST">';
		$form [] = '<p>Felder mit * müssen ausgefüllt werden</p>';
		$form [] = '<p>Anfrage</p>';
		$form [] = '<label for="to">An</label>';
		/*
		 * show the selection element if more than one
		 * member can be contacted
		 */
		if (is_array ( $this->member )) {
			$to = '<select action="" id ="contact" name="contact">';
			foreach ( $this->member as $ind => $name ) {
				$to .= '<option value="' . $name . '"';
				if ($iniValues ['contact'] == $name) {
					$to .= ' selected';
				}
				$to .= '>' . $name . '</option>';
			}
			$to .= '</select>';
		} else {
			$to = '<input type="text" id ="contact" name="contact" disabled="disabled" value="' . $this->member . '">';
		}
		$form [] = $to;
		$form [] = '<label for="sender">Name*</label>';
		$form [] = '<input type="text" id="sender" name="sender" required value="' . $iniValues ['sender'] . '">';
		$form [] = '<label for="email">Email*</label>';
		$form [] = '<input type="text" id="email" name="email" required value="' . $iniValues ['email'] . '" />';
		$form [] = '<label for="subject">Betreff</label>';
		$form [] = '<input type="text" id="subject" name="subject" value="' . $iniValues ['subject'] . '">';
		$form [] = '<label for="message">Nachricht</label>';
		$form [] = '<textarea id="message" name="message" cols="50" rows="15">' . $iniValues ['message'] . '</textarea>';
		$form [] = '<img src="img/randomcircles.php" alt="circles" height="80" width="100">';
		$form [] = '<label for="circles">Wie viele Kreise sind zu sehen?</label>';
		$form [] = '<input type="text" name="circles" id="circles" size="3" autocomplete="off">';
		$form [] = '<input type="submit" name="check" value="senden">';
		$form [] = '</form>';
		
		echo implode ( "\n", $form );
	}
	public function send(array $email, $confirm = NULL) {
		if (! $email ['contact']) {
			$this->err_msg = "Kein Kontaktname.";
			return FALSE;
		} else if (! $send ['to'] = self::fetchContactEmail ( $email ['contact'] )) {
			$this->err_msg = "Keine Kontaktadresse gefunden.";
			return FALSE;
		} else {
			$send ['subject'] = "[achtsam: Kontakt] " . $email ['subject'];
			$send ['message'] = "Kontaktaufnahme von " . $email ['sender'] . "\n";
			$send ['message'] .= "email: " . $email ['email'] . "\n";
			$send ['message'] .= "Nachricht: \n" . $email ['message'];
			$send ['message'] .= wordwrap ( $message, 70 );
			
			$send ['from'] = $email ['email'];
			
			$confirm [] = "<h2>Vielen Dank</h2>";
			$confirm [] = "<p>Folgende Email wurde an " . $email ['contact'] . " gesendet:</p>";
			$confirm [] = nl2br ( $email ['message'] );
			
			return parent::send ( $send, implode ( "\n", $confirm ) );
		}
	}
}
class Application extends Post {
	function __construct() {
		parent::setTable ( "events" );
		parent::set_field ( "online" );
		parent::set_contact ( "eventID" );
	}
	public function showForm($iniValues = NULL) {
		// show contact form
		$form [] = '<form id="emailform" action="" method="POST">';
		$form [] = '<p>Felder mit * müssen ausgefüllt werden</p>';
		$form [] = '<p>Anmeldung</p>';
		$form [] = '<label for="sender">Name*</label>';
		$form [] = '<input type="text" id="sender" name="sender" required value="' . $iniValues ['sender'] . '">';
		$form [] = '<label for="email">Email*</label>';
		$form [] = '<input type="text" id="email" name="email" required value="' . $iniValues ['email'] . '" />';
		$form [] = '<label for="street">Strasse*</label>';
		$form [] = '<input type="text" id="street" name="street" required value="' . $iniValues ['street'] . '" />';
		$form [] = '<label for="city">PLZ Ort*</label>';
		$form [] = '<input type="text" id="city" name="city" required value="' . $iniValues ['city'] . '" />';
		$form [] = '<label for="subject">Betreff</label>';
		$form [] = '<input type="text" id="subject" name="subject" value="' . $iniValues ['subject'] . '">';
		$form [] = '<label for="message">Nachricht</label>';
		$form [] = '<textarea id="message" name="message" cols="50" rows="15">' . $iniValues ['message'] . '</textarea>';
		$form [] = '<img src="img/randomcircles.php" alt="circles" height="80" width="100">';
		$form [] = '<label for="circles">Wie viele Kreise sind zu sehen?</label>';
		$form [] = '<input type="text" name="circles" id="circles" size="3" autocomplete="off">';
		$form [] = '<input type="submit" name="check" value="senden">';
		$form [] = '<input type="hidden" name="eventID" value="' . $iniValues ['eventID'] . '">';
		$form [] = '<input type="hidden" name="application" value="' . $iniValues ['application'] . '">';
		$form [] = '</form>';
		
		echo implode ( "\n", $form );
	}
	public function send(array $email, $confirm = NULL) {
		// var_dump ( $email );
		// send email and return a message
		if (! $email ['eventID']) {
			$this->err_msg = "Keine eventID.";
			return FALSE;
		} else if (! $send ['to'] = self::fetchContactEmail ( $email ['eventID'] )) {
			$this->err_msg = "Keine Anmeldeemailadresse gefunden.";
			return FALSE;
		} else {
			
			$send ['subject'] = "[achtsam: Anmeldung] " . $email ['subject'];
			$send ['message'] = "Anmeldung von " . $email ['name'] . "\n";
			$send ['message'] .= "Email: " . $email ['email'] . "\n";
			$send ['message'] .= "Adresse: " . $email ['street'] . ", " . $email ['city'] . "\n";
			$send ['message'] .= "Nachricht: " . $email ['message'];
			$send ['message'] .= wordwrap ( $message, 70 );
			
			$send ['from'] = $email ['email'];
			
			$confirm [] = "<h2>Vielen Dank</h2>";
			$confirm [] = "<p>Die Anmeldung wurde gesendet</p>";
			$confirm [] = nl2br ( $email ['message'] );
			$confirm [] = '<p>Die Kursleitung wird sich mit ihnen in Verbindung setzen.</p>';
			
			return parent::send ( $send, implode ( "\n", $confirm ) );
		}
	}
}