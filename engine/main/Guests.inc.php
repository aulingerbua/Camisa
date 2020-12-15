<?php

class Guests extends Registry
{

    // class to invite and register guests
    function __construct()
    {
        parent::setTable('members');
        parent::setUniqueField('uid');
        Registry::setButtons([
            'insert' => "senden",
            'update' => "registrieren"
        ]);
    }

    private function randUserName()
    {
        /*
         * create a random user name
         * can be changed by the guest
         * during the registration
         */
        $keys = [
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "x",
            "y",
            "z"
        ];
        $name = "";
        for ($z = 0; $z < 7; $z ++) {
            $name .= mt_rand(0, 1) ? $keys[mt_rand(0, count($keys) - 1)] : strtoupper($keys[mt_rand(0, count($keys) - 1)]);
        }
        return $name;
    }

    /**
     * Sends the invitation email and registers email and random user name
     *
     * @param array $details
     * @return boolean
     */
    public function invite(array $details)
    {
        $success = FALSE;
        if (! filter_var($details['email'], FILTER_VALIDATE_EMAIL)) {
            $this->err_msg = "Ungültige Emailadresse.";
        } elseif (empty($details['uid'])) {
            $this->err_msg = "Kein Username angegeben";
        } else {
            $link = URI . "/admin/receptiondesk.php?uid=" . $details['uid'];

            if (self::exist([
                email => $details['email']
            ])) {
                $this->warn_msg = "Diese Emailadresse ist bereits registriert.";
                $details['quit'] = $this->buttons['quit'];
            } else {
                $subject = "[" . SITE . "] Einladung";
                $message = wordwrap("Sie wurden eingeladen, sich als Gast auf der Seite " . SITE . " zu registrieren. Bitte klicken Sie zur Bestätigung auf den folgenden Link: ");
                $message .= "<br> <a href=$link>$link</a>.";
                $message .= "<br> Bitte antworten Sie nicht auf diese Email.";
                $mail = phpMailer_init();
                // Set who the message is to be sent to
                $mail->addAddress($details['email']);
                // Set the subject line
                $mail->Subject = $subject;

                $mail->Body = $message;

                if ($mail->send()) {
                    $details['pwd'] = $details['uid'] . "2" . $details['uid'];
                    self::dataBaseIo($details);
                    $confirmation = "Folgende Einladung wurde an " . $details['email'] . " verschickt:<br>";
                    $this->conf_msg = $confirmation . $message;
                    $success = TRUE;
                } else {
                    $this->err_msg = "Einladungsmail konnte nicht gesendet werden. " . $mail->ErrorInfo;
                }
            }
        }
        return $success;
    }

    public function showForm($iniValues = NULL)
    {
        $name = self::randUserName();

        $form[] = '<form action="" class="editor" method="post">';
        $form[] = '<div class="box-inside-form">';
        $form[] = '<label for="email">Email';
        $form[] = '<input type="text" id="email" name="email" value=""></label>';
        $form[] = '</div>';
        $form[] = self::makeInsertButton();
        $form[] = '<input type="hidden" id="uid" name="uid" value="' . $name . '">';
        $form[] = '<input type="hidden" id="role" name="role" value="guest">';
        $form[] = '</form>';

        echo implode("\n", $form);
    }

    /**
     * Displays the form to complete guest registration
     */
    public function registerForm()
    {
        ?>

<form id="login-form" action="" method="POST">
	<div class="camisa-logo">
		<img alt="CaMiSa banner" src="<?=ImagesPath?>Camisa_logo.svg">
	</div>
	<p>Bitte einen Benutzernamen und ein Passwort wählen.</p>
	<label for="uid">Benutzername</label>
	<p>
		<input type="text" id="uid" name="uid" autofocus>
	</p>
	<label for="pwd">Passwort</label>
	<p>
		<input type="password" id="pwd" name="pwd">
	</p>
		<?php
        echo $message;
        ?>
		<p>
		<input type="submit" id="update" name="update"
			value="<?=$this->buttons ['update']?>">
	</p>
	<p>
		<a href="<?php echo HOME?>">Zurück zu <?=SITE?></a>
	</p>
</form>
<?php
    }

    /**
     * Registers the invited member.
     *
     * @param array $data
     * @param string $uid
     * @return boolean
     */
    public function register($data, $uid)
    {
        // register invitated user
        unset($data['update']);
        if (self::exist($data['uid'])) {
            $this->err_msg = "Der Benutzername existiert bereits.";
            return FALSE;
        } elseif (! $data['pwd'] = password_hash($data['pwd'], PASSWORD_DEFAULT)) {
            $this->err_msg = "Passwort hash schiefgegangen.";
            return FALSE;
        } else {
            $data['since'] = date("Y-m-d H:i:s");
            self::update($data, $uid);
            $this->conf_msg = "<h2>Glückwunsch</h2>\n<p>Sie haben sich erfolgreich registriert.</p>";
            $this->conf_msg .= "<a href=" . HOME . ">Zurück zu " . SITE . "</a>";
            notifyAdmins("A new guest " . $data['uid'] . "registered at your site.");
            return TRUE;
        }
    }

    /**
     * Checks if the invitation is valid.
     *
     * @param unknown $uid
     * @return boolean
     */
    public function checkInvitation($uid)
    {
        $pwd = self::retrieve([
            'uid' => $uid
        ], NULL, 'pwd');
        // var_dump ( $pwd [0] );
        if ($pwd[0]['pwd'] === $uid . "2" . $uid)
            return true;
        else
            return false;
    }
}