<?php
class System extends Registry {
	function __construct() {
		self::setTable ( "system" );
		self::setUniqueField ( "id" );
	}
	/**
	 * Form for inserting or updating sendmail settings.
	 *
	 * @param array $iniVals
	 *        	initial values for the form fields. Contains the values already stored in the system table.
	 */
	public function sendmailSettings($iniVals) {
		$html_checked = $iniVals ['html'] ? " checked" : "";
		$smtpa_checked = $iniVals ['smtpauth'] ? " checked" : "";
		echo "<form id='sendmail-settings' class='editor' action='' method='post'>\n";
		?>
<div class="box-inside-form">
	<label for="fromemail">sender email address</label><input type="email"
		name="fromemail" value="<?=$iniVals['fromemail']?>"> <label
		for="fromname">sender name</label><input type="text" name="fromname"
		value="<?=$iniVals['fromname']?>"> <label for="smtp">SMTP server</label><input
		type="text" name="smtp" value="<?=$iniVals['smtp']?>"> <label
		for="username">port</label> <input type="text" name="port"
		value="<?=$iniVals['port']?>"> <label for="pwd">user name</label> <input
		type="text" name="username" value="<?=$iniVals['username']?>"> <label
		for="pwd">password</label> <input type="password" name="pwd">
</div>
<div class="box-inside-form">
	<label for="html">html mail</label><input type="checkbox" name="html"
		<?=$html_checked?>><br> <label for="smtpauth">smtp authentification</label><input
		type="checkbox" name="smtpauth" <?=$smtpa_checked?>>
</div>
<div style="clear: both"></div>
<?php
		if ($iniVals) {
			echo self::makeUpdateButton ();
			echo '<input type="hidden" name="id" value="' . $iniVals ['id'] . '">';
		} else
			echo self::makeInsertButton ();
		echo "</form>";
	}
	/**
	 * Formats data in the $_POST variable filled with the sendmail settings form
	 * to insert or update the system table.
	 *
	 * @param array $data
	 *        	contains data of the sendmail settings
	 * @return NULL|string[]|number[]|unknown[] the sendmail settings formatted for a multy query (insert or update)
	 */
	public function putSendmailSettings(array $data) {
		if (! $data)
			return NULL;
		if ($data ['insert'])
			$settings ['insert'] = $data ['insert'];
		if ($data ['update']) {
			$settings ['update'] = $data ['update'];
			$settings ['id'] = $data ['id'];
		}
		
		$date = date ( 'Y-m-d H:i:s' );
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'html',
				'version' => 1,
				'value' => $data ['html'] == 'on' ? 1 : 0,
				'grp' => 'sendmail',
				'info' => 'enabling html mails' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'smtpauth',
				'version' => 1,
				'value' => $data ['smtpauth'] == 'on' ? 1 : 0,
				'grp' => 'sendmail',
				'info' => 'using smtp authentification' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'fromemail',
				'version' => 1,
				'value' => $data ['fromemail'],
				'grp' => 'sendmail',
				'info' => 'email address used for the from-header' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'fromname',
				'version' => 1,
				'value' => $data ['fromname'],
				'grp' => 'sendmail',
				'info' => 'name used for the from-header' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'smtp',
				'version' => 1,
				'value' => $data ['smtp'],
				'grp' => 'sendmail',
				'info' => 'the smtp server name' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'port',
				'version' => 1,
				'value' => $data ['port'],
				'grp' => 'sendmail',
				'info' => 'the smtp port to use' 
		];
		$settings ['data'] [] = [ 
				'date' => $date,
				'entry' => 'username',
				'version' => 1,
				'value' => $data ['username'],
				'grp' => 'sendmail',
				'info' => 'mail user name' 
		];
		if ($data ['pwd']) {
			$settings ['data'] [] = [ 
					'date' => $date,
					'entry' => 'pwd',
					'version' => 1,
					'value' => $data ['pwd'],
					'grp' => 'sendmail',
					'info' => 'mail user password' 
			];
		}
		return $settings;
	}
	/**
	 * Retrieves and reformats the sendmail settings.
	 *
	 * @return array containing the sendmail settings
	 */
	public function getSendmailSettings() {
		$data = self::retrieve ( [ 
				'grp' => 'sendmail' 
		], NULL, 'id', 'entry', 'value' );
		if (! $data)
			return NULL;
		for($i = 0; $i < count ( $data ); $i ++) {
			$settings [$data [$i] ['entry']] = $data [$i] ['value'];
			$ids [] = $data [$i] ['id'];
		}
		$settings ['id'] = implode ( ",", $ids );
		return $settings;
	}
	/**
	 *
	 * @param string $module        	
	 * @return boolean
	 */
	public static function hasModule($module) {
		$db = db_init ();
		$result = $db->query ( "SELECT 1 FROM system WHERE entry='module' AND value='$module';" );
		if (! $result)
			return FALSE;
		$found = $result->fetch_row ();
		return $found [0];
	}
}
?>