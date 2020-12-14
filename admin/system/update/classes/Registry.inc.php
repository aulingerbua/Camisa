<?php
/** This is the basic class for writing, reading and updating data base tables.
 * 
 * @author Armin Aulinger
 * 
 * This is an abstract class. That means you must create a child class to use its methods for writing,
 * reading and updating data base tables. In the constructor of the child class you must define the table
 * name that should be manipulated and the unique field (usually the primary key) of the table, which I will call class
 * table in the following.
 *
 */
abstract class Registry {
	use Messages;
	protected $table; // !< @param $table the class table name
	protected $uniquefield; // !< @param $uniquefield the unique field to identify a data set
	protected $orderby = NULL; // !< @param $orderby the default column for ordering (ASC). Can be set to an array containing "field" and "method".
	private $options = [ ]; // !< @param array $options the options stored in the options field of the class table.
	protected $buttons = [ 
			'insert' => 'einfügen',
			'update' => 'ändern',
			'delete' => 'löschen',
			'quit' => 'abbrechen' 
	]; // !< names of the submit, update, delete and restet buttons
	/**
	 * Sets the class table name to the argument.
	 *
	 * @param string $tbl        	
	 */
	protected function setTable($tbl) {
		$this->table = $tbl;
	}
	/**
	 * Sets the unique field of the table to the argument.
	 *
	 * @param string $fname        	
	 */
	protected function setUniqueField($fname) {
		$this->uniquefield = $fname;
	}
	/**
	 * Sets the default order column to the argument.
	 *
	 * @param unknown $order        	
	 */
	public function setOrderBy($order) {
		$this->orderby = $order;
	}
	/**
	 * Defines which options can be stored in the options field of the class table.
	 *
	 * The options' names must be different from the field names.
	 *
	 * @param array $options
	 *        	name of the options.
	 * @throws Exception
	 */
	public function defineOptions(array $options) {
		$notAllowed = self::getTableFields ();
		$notAllowed [] = $this->uniquefield;
		foreach ( $options as $op )
			if (in_array ( $op, $notAllowed )) {
				throw new Exception ( "The option names must not contain one of the table fields" );
				exit ( 1 );
			}
		
		$this->options = $options;
	}
	/**
	 * Get the values of the submit, update, delete and restet buttons.
	 *
	 * @param unknown ...$names
	 *        	which names to recieve.
	 * @return string[]|string an array of the button values or a single one
	 */
	public function getButtonValues(...$names) {
		while ( $names ) {
			$v = array_shift ( $names );
			$butt [$v] = $this->buttons [$v];
		}
		$butt = $butt ? $butt : $this->buttons;
		if (count ( $butt ) > 1) {
			return $butt;
		} else {
			return $butt [$v];
		}
	}
	
	/**
	 * For setting the values of the submit, update, delete and restet buttons
	 *
	 * The keys of the array are used as
	 * button names and the values of the array
	 * become the button values.
	 *
	 * @example ["update" => "change", "delete" => "remove"]
	 *         
	 * @param
	 *        	$butt
	 */
	protected function setButtons(array $butt) {
		foreach ( $butt as $b => $v ) {
			if (key_exists ( $b, $this->buttons ))
				$this->buttons [$b] = $v;
		}
	}
	/**
	 * Takes a query string and executes the query.
	 *
	 * If the query produces an error, an exception is thrown. If the query returns data,
	 * this data is returned as an associated array. Otherwise, the number of affected rows is returned.
	 *
	 * @param string $qry
	 *        	the query as string.
	 * @param boolean $multi
	 *        	whether it is a multi query.
	 * @param boolean $closeDB
	 *        	whether the database connection should explicitly be closed.
	 * @param boolean $closeRes
	 *        	whether the reult should be closed (freed)
	 * @throws Exception
	 * @return boolean|integer|array false on error, the number of affected rows or the recieved data.
	 */
	public function executeQuery($qry, $multi = FALSE, $closeDB = FALSE, $closeRes = FALSE) {
		//echo "<p>$qry</p>";
		$data = NULL;
		$db = db_init ();
		if ($multi)
			$result = $db->multi_query ( $qry );
		else
			$result = $db->query ( $qry );
		
		if (! $result) {
			throw new Exception ( "Error executing query: " . $db->error );
			return false;
		}
		
		if ($result === true)
			$data = $db->affected_rows;
		else {
			while ( $row = $result->fetch_assoc () ) {
				$data [] = $row;
			}
			if ($data and $closeRes)
				$result->free ();
		}
		if ($close)
			$db->close ();
		return $data;
	}
	/**
	 * Check if a table exists in the data base.
	 *
	 * If the parameter is NULL, the data base is checked for the main table of a data manipulating class.
	 *
	 * @param
	 *        	$table
	 * @return boolean
	 */
	public function tableExists($table = NULL) {
		$db = db_init ();
		if ($table)
			$qry = "SELECT 1 FROM $table LIMIT 1;";
		else
			$qry = "SELECT 1 FROM $this->table LIMIT 1;";
		$result = $db->query ( $qry );
		if (! $result)
			return FALSE;
		
		return TRUE;
	}
	/**
	 * Returns the fields of the class table.
	 *
	 * @return array an array of the table fields.
	 */
	protected function getTableFields() {
		$db = db_init ();
		if (DBtype == "psql")
			return ($db->get_fieldnames ( $this->table ));
		
		$qry = "show columns from $this->table;";
		$result = $db->query ( $qry );
		while ( $row = $result->fetch_assoc () ) {
			if ($row ['Extra'] != "auto_increment")
				$fields [] = $row ['Field'];
		}
		return ($fields);
	}
	/**
	 * Returns the entries in the unique identifier field as an array.
	 *
	 * @return array $nl
	 */
	public function nameList() {
		$db = db_init ();
		$log = start_system_log ( "nameList" );
		
		$qry = 'SELECT ' . $this->uniquefield . ' FROM ' . $this->table;
		
		if ($this->orderby) {
			$qry .= " ORDER BY $this->orderby";
		}
		// echo $qry;
		$result = $db->query ( $qry );
		
		if (! $result) {
			$log->write ( "Data base error! " . $db->error, LOG_ERROR );
		}
		
		while ( $row = $result->fetch_assoc () ) {
			$nl [] = $row [$this->uniquefield];
		}
		
		return $nl;
	}
	/**
	 * Shows an unordered list with links that can be used in the backend forms to edit entries which are identified in the table by the value of the $field.
	 * If $field is omitted or NULL, the unique field of the class table or the table set in the argument $table is assumed.
	 *
	 * @param unknown $tool
	 *        	the tool whose table entries should be manipulated.
	 * @param unknown $field
	 *        	field to look for entries.
	 * @param unknown $table
	 *        	table to search fields in
	 * @throws Exception
	 * @return NULL
	 */
	public function listEditLinks($tool, $field = NULL, $table = NULL) {
		if (! $tool) {
			throw new Exception ( "You must provide the argument tool to the function editLinks" );
			return NULL;
		}
		$field = $field ?: $this->uniquefield;
		$table = $table ?: $this->table;
		$qry = "SELECT DISTINCT $field FROM $table";
		if ($order = self::expandOrderBy ())
			$qry .= $order;
		$links = self::executeQuery ( $qry );
		echo "<ul>";
		foreach ( $links as $link ) {
			$lnk = $link [$field];
			$current = set_current ( $tool, $lnk );
			echo "<li><a href='?tool=$tool&$tool=$lnk' $current>$lnk</a></li>";
		}
		echo "</ul>";
	}
	/**
	 * Converts option entries to a single entry.
	 *
	 * Takes the entries of $input that define entries in the options field and converts them to a json object.
	 * Then unneeded entries are deleted and the options entry created instead. If an array with options, different than those in $input are provided, their values are
	 * kept. This makes it possible to define different options in different sets without overwriting them.
	 *
	 * @param unknown $input
	 *        	$input array containing an options field.
	 * @param array $current
	 *        	the current options before update.
	 */
	private function makeOptionsField(&$input, $current = NULL) {
		foreach ( $this->options as $opt ) {
			$options [$opt] = $input [$opt] ?: $current [$opt];
			unset ( $input [$opt] );
		}
		if ($check = make_options_object ( $current, TRUE )) {
			foreach ( $check as $ck => $cv ) {
				foreach ( $options as $ok => $ov ) {
					if ($ck != $ok)
						$options [$ck] = $cv;
				}
			}
		}
		$input ['options'] = json_encode ( $options );
	}
	/**
	 * Returns the options from a data set found by $which.
	 * If more than one data set is found, the options only from the first set are returned.
	 *
	 * @param string|array $which
	 *        	can be a value from the unique field or an associated array.
	 * @param boolean $asArray
	 *        	if TRUE returns an associated array
	 * @return NULL|mixed the options as object or array
	 * @see self::retrieve.
	 */
	public function getOptions($which, $asArray = FALSE) {
		$opString = self::retrieve ( $which, NULL, 'options' ) [0] ['options'];
		return self::translateOptionsString ( $opString, $asArray );
	}
	/**
	 * Make an object (or array) of options.
	 *
	 * This is used to convert a json string used to store options in a data base field to an accessible object.
	 *
	 * @param string $optionsString
	 *        	the string to decompose that contains the options
	 * @param boolean $asArray
	 *        	if TRUE returns an associated array
	 * @return NULL|mixed the options as object or array
	 */
	public function translateOptionsString($optionsString, $asArray = FALSE) {
		$log = start_system_log ( "translateOptionsString" );
		if (! $optionsString)
			return NULL;
		try {
			$options = json_decode ( $optionsString, $asArray );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage (), LOG_WARNING );
			return NULL;
		}
		return $options;
	}
	/**
	 * Inserts data into the class table.
	 *
	 * The data must be provided in an associated array, where the keys correspond to the field names.
	 * The values will then be inserted into these fields. This method can also handle multiple inserts
	 * if these are provided in an array called data.
	 *
	 * @param array $input
	 *        	an associated array
	 * @return true on success false on failure
	 */
	protected function insert(array $input) {
		$db = db_init ();
		$log = start_system_log ( "insert" );
		
		/* check fieldnames (auto_increment fields are excluded!!) */
		$fieldnames = self::getTableFields ();
		
		// insert multiple rows
		if ($data = $input ['data']) {
			$qry = "";
			foreach ( $data as $set ) {
				if (! empty ( $this->options ))
					self::makeOptionsField ( $set );
				$qry .= "INSERT INTO $this->table (";
				foreach ( $set as $key => $column ) {
					if (in_array ( $key, $fieldnames )) {
						$fields [] = $key;
						$vals [] = "'" . $db->real_escape_string ( $column ) . "' ";
					}
				}
				$qry .= implode ( ",", $fields ) . ") VALUES ( " . implode ( ",", $vals ) . ");";
				unset ( $key, $column, $fields, $vals );
			}
			// echo "<p>$qry</p>";
			// an extra multy query method exists only for the mysql data base.
			/*
			 * if (DBtype != "psql") {
			 * if ($db->multi_query ( $qry ))
			 * return TRUE;
			 * else
			 * echo "Fehler in insert! " . $db->error;
			 * return FALSE;
			 * }
			 */
			try {
				self::executeQuery ( $qry, true );
			} catch ( Exception $e ) {
				$log->write ( $e->getMessage (), LOG_ERROR );
				return FALSE;
			}
		} else {
			if (! empty ( $this->options ))
				self::makeOptionsField ( $input );
				// insert only one row
			$qry = "INSERT INTO $this->table (";
			foreach ( $input as $key => $column ) {
				if (in_array ( $key, $fieldnames )) {
					$fields [] = $key;
					$vals [] = "'" . $db->real_escape_string ( $column ) . "' ";
				}
			}
			//unset ( $column );
			$qry .= implode ( ",", $fields ) . ") VALUES ( " . implode ( ",", $vals ) . ");";
		}
		
		/*
		 * if (! $db->query ( $qry ))
		 * $log->write ( "Data base error! " . $db->error, LOG_ERROR );
		 * $db->close ();
		 */
		
		try {
			self::executeQuery ( $qry );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage (), LOG_ERROR );
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * Updates the fields in the class table.
	 *
	 * The $input is an associated array like in the @link insert(array $input) function.
	 * In addition, the unique identifier of the row to be updated must be either found in the input array
	 * or can be submitted as function argument.
	 *
	 * @param array $input
	 *        	an associated array with the updete values
	 * @param unknown $which
	 *        	the unique identifier of the updated row.
	 * @return boolean true on success, false if the update failed or nothing has to be updated.
	 */
	protected function update(array $input, $which) {
		
		$db = db_init ();
		$log = start_system_log ( "update" );
		$which = $which ?: $input [$this->uniquefield];
		if (! $which) {
			$log->write ( "The field for identifying the row that should be updated must not be NULL.", LOG_ERROR );
			return FALSE;
		}
		
		// check fieldnames (auto_increment fields are excluded!!)
		$fieldnames = self::getTableFields ();
		
		// update multiple rows
		if ($data = $input ['data']) {
			$which = explode ( ",", $input [$this->uniquefield] );
			//var_dump($data);
			//var_dump($which);
			$current = self::retrieve ( [ 
					$this->uniquefield => $which 
			] );
			$qry = "";
			for($i = 0; $i < count ( $data ); $i ++) {
				
				// get the correct data set
				$s = 0;
				do {
					//$set = $data [$i];
					$w = $which [$s];
					$c = $current [$i];
					$test = $w === $c [$this->uniquefield];
					$s ++;
				} while ( ! $test and $s <= count ( $which ) - 1 );
				if (! empty ( $this->options ))
					self::makeOptionsField ( $data [$i], $current [$i] ['options'] );
				$qry .= "UPDATE $this->table SET ";
				
				// get columns to be updated
				foreach ( $data [$i] as $key => $column ) {
					if (in_array ( $key, $fieldnames )) {
						if ($c [$key] != $column) {
							$update [] = "$key='" . $db->real_escape_string ( $column ) . "'";
						}
					}
				}
				if (! $update) {
					unset ( $key, $column, $update );
					continue;
				}
				$qry .= implode ( ",", $update );
				$qry .= " WHERE $this->uniquefield = '$w';";
				unset ( $key, $column, $update );
			}
			//echo "<p>$qry</p>";
			// an extra multy query method exists only for the mysql data base.
			/*
			 * if (DBtype != "psql") {
			 * if ($db->multi_query ( $qry ))
			 * return TRUE;
			 * else
			 * $log->write ( "Data base error! " . $db->error, LOG_ERROR );
			 * return FALSE;
			 * }
			 */
			
			try {
				self::executeQuery ( $qry, true );
			} catch ( Exception $e ) {
				$log->write ( $e->getMessage () );
			}
		} else {
			
			// update only one row
			$current = self::retrieve ( $which ) [0];
			if (! empty ( $this->options ))
				self::makeOptionsField ( $input, $current ['options'] );
			
			$qry = "UPDATE $this->table SET ";
			
			// get columns to be updated
			foreach ( $input as $key => $column ) {
				if (in_array ( $key, $fieldnames )) {
					if ($current [$key] != $column) {
						$update [] = "$key='" . $db->real_escape_string ( $column ) . "'";
					}
				}
			}
			unset ( $column );
			if (! $update)
				return false;
			
			$qry .= implode ( ",", $update );
			$qry .= " WHERE $this->uniquefield = '$which';";
			// echo "<p>$qry</p>";
			

			try {
				self::executeQuery ( $qry );
			} catch ( Exception $e ) {
				$log->write ( $e->getMessage () );
				return FALSE;
			}
			/*
			 * if (! $db->query ( $qry ))
			 * echo "Fehler in update! " . $db->error;
			 *
			 * $db->close ();
			 * return TRUE;
			 */
		}
		return TRUE;
	}
	/**
	 * Checks whether a value in a field exists.
	 *
	 * If $name is an associated array, the field corresponding to the key is searched for the value of the array.
	 * If $name is a string the unique field is searched for this value.
	 *
	 * @param array|string $name
	 *        	the field and value to be searched
	 * @return true if the value exists in the specified field.
	 */
	public function exist($name) {
		$rr = $name ? self::retrieve ( $name ) [0] : NULL;
		
		$found = $rr ? TRUE : FALSE;
		
		return $found;
	}
	/**
	 * Returns the distinct values in thesbmitted field.
	 *
	 * @param $column name
	 *        	of the field to be searched
	 * @return an array of distinct values
	 */
	public function distinct($column) {
		$db = db_init ();
		$log = start_system_log ( "distinct" );
		
		$qry = "SELECT DISTINCT $column FROM $this->table WHERE $column IS NOT NULL";
		
		if ($result = $db->query ( $qry )) {
			while ( $row = $result->fetch_assoc () ) {
				$data [] = $row [$column];
			}
		} 

		else {
			$log->write ( "Data base error! " . $db->error, LOG_ERROR );
		}
		$db->close ();
		return $data;
	}
	/**
	 * Makes a dropdown menue and returns it as a string.
	 *
	 * The function reads the distinct values from a field in the class table
	 * and puts them as values in the option element.
	 *
	 * @param string $fromField
	 *        	the field in the class table from which the values are taken.
	 * @param string $name
	 *        	the name of the dropdown menue. By default the $fromField.
	 * @param string $selected
	 *        	the value if matched will be selected.
	 * @param string $nulloption
	 *        	string for an empty option field.
	 * @param string $required
	 *        	determines whether any option must be selected.
	 * @throws Exception.
	 * @return string the select element to display.
	 */
	protected function makeDropdown($fromField, $name = NULL, $selected = NULL, $nulloption = FALSE, $required = FALSE) {
		$name = $name ?: $fromField;
		$values = self::retrieve ( NULL, NULL, $fromField );
		if (! $values or ! $name)
			throw new Exception ( "Error in makeDropdown: no values found or no name provided." );
		$required = $required ? "required" : NULL;
		$dropdown = "<select name='$name' $required>";
		if ($nulloption !== FALSE)
			$dropdown .= "<option value=''>$nulloption</option>";
		foreach ( $values as $option ) {
			$opt = $option [$fromField];
			$select = $selected == $opt ? "selected" : NULL;
			$dropdown .= "<option value='$opt' $select>$opt</option>";
		}
		$dropdown .= "</select>";
		return $dropdown;
	}
	/**
	 * Expands the orderBy variable to a string that can be appended to a data base query string.
	 *
	 * @throws Exception
	 * @return string
	 */
	private function expandOrderBy() {
		if (! $this->orderby)
			return NULL;
		if (is_array ( $this->orderby )) {
			if ($orderby = $this->orderby ['field']) {
				$method = $this->orderby ['method'] ?: "ASC";
				$order = " ORDER BY $orderby " . $method;
			} else {
				throw new Exception ( 'If the class variable orderby is an array, it must contain the field "field".' );
			}
		} elseif ($orderby = $this->orderby)
			$order = " ORDER BY $orderby";
		return $order;
	}
	/**
	 * Query the data base.
	 *
	 * This is the general function to send a query to the class table and retrieve values from this table.
	 * If $subset is an associated array, the keys are the field names and its values will be searched in
	 * these fields. That values can themselves be an array. Then the field values are searched in this array.
	 * If $subset is a string, the unique field of the class table is searched.
	 * If $subset is NULL, all datasets are returned. If $columns (strings separated by commas) are provided,
	 * only these fields are returned. $options is an associated array of additional arguments. It can be distinct,
	 * restrict, or orderby. If restrict is set, also operator (<, >, != or ==), field and value must be set. If
	 * orderby is set to a field name, also the orderbymethod can be set (desc, asc or a random function).
	 *
	 * @param string $subset        	
	 * @param string $options        	
	 * @param string ...$columns        	
	 * @return an associated array with the results of the data base query
	 */
	public function retrieve($subset = NULL, $options = NULL, ...$columns) {
		$db = db_init ();
		$log = start_system_log ( "retrieve" );
		
		If (empty ( $subset )) {
			$subset = NULL;
		}
		
		$qry = "SELECT";
		
		if ($options ['distinct']) {
			$qry .= " DISTINCT ";
		}
		
		if ($options ['compare']) {
			$comp = $options ['compare'];
		} else {
			$comp = "=";
		}
		
		if ($columns) {
			$columns = implode ( ",", $columns );
		} else {
			$columns = "*";
		}
		
		$qry .= " $columns FROM $this->table";
		
		if (is_array ( $subset )) {
			$qry .= ' WHERE ';
			foreach ( $subset as $key => $value ) {
				if (is_array ( $value )) {
					$sel [] = "$key IN ('" . implode ( "','", $db->real_escape_string ( $value ) ) . "')";
				} else {
					$sel [] = "$key $comp '" . $db->real_escape_string ( $value ) . "'";
				}
			}
			$qry .= implode ( " AND ", $sel );
		} else if ($subset) {
			$qry .= " WHERE $this->uniquefield = '" . $db->real_escape_string ( $subset ) . "'";
		}
		
		if ($restrict = $options ['restrict']) {
			
			if ($subset)
				$append = " AND ";
			else
				$append = " WHERE ";
			
			$operator = $restrict ['operator'];
			$field = $restrict ['field'];
			$value = $restrict ['value'];
			
			if ($restrict ['datatype'] === "time")
				$value = date ( 'Y-m-d G:i:s', $value );
			
			$qry .= $append . $field . " $operator " . "'" . $db->real_escape_string ( $value ) . "'";
		}
		
		if ($order = self::expandOrderBy ())
			$qry .= $order;
		
		if ($limit = $options ['limit']) {
			$qry .= " LIMIT $limit";
		}
		
		if ($offset = $options ['offset']) {
			$qry .= " OFFSET $offset";
		}
		
		try {
			$data = self::executeQuery ( $qry, false, true, true );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage (), LOG_ERROR );
		}
		return $data;
	}
	/**
	 * Deletes the row corresponding to the unique identifier.
	 *
	 * @param $which the
	 *        	unique identifier of the data set.
	 * @param $constraints option
	 *        	to set constraints about the entry to delete.
	 */
	protected function delete($which, $constraints = NULL) {
		$log = start_system_log ( "delete" );
		// Datensatz löschen.
		$qry = "DELETE FROM $this->table WHERE $this->uniquefield = '$which'";
		// echo "<p>$qry</p>";
		try {
			self::executeQuery ( $qry );
		} catch ( Exception $e ) {
			$log->write ( $e->getMessage () );
		}
	}
	protected function checkDelete($which) {
		$del [] = '<form class="delete-entry" action="" method="post">';
		$del [] = '<p>Eintrag ' . $which . ' löschen?</p>';
		$del [] = '<input type="submit" name="delete" value="OK" />';
		$del [] = '<input type="submit" name="quit" value="' . $this->buttons ['quit'] . '" />';
		$del [] = '<input type="hidden" name="del" value="' . $which . '" />';
		$del [] = '</form>';
		
		return implode ( "\n", $del );
	}
	/**
	 * Creates the insert button for the data input form.
	 *
	 * using this function ensures that the insert value in $_POST is recocnized by the data manipulation block.
	 *
	 * @param
	 *        	class can be used to set a class to the input element.
	 *        	
	 * @return string the html of an input type submit with the name "insert"
	 */
	protected function makeInsertButton($class = null) {
		$cl = $class ? "class='$class' " : null;
		return '<input type="submit" id="insert" name="insert" ' . $cl . 'value="' . $this->buttons ['insert'] . '">';
	}
	/**
	 * Creates the delete button for the data input form.
	 *
	 * using this function ensures that the delete value in $_POST is recocnized by the data manipulation block.
	 *
	 * @param
	 *        	class can be used to set a class to the input element.
	 * @return string the html of an input type submit with the name "delete"
	 */
	protected function makeDeleteButton($class = null) {
		$cl = $class ? "class='$class' " : null;
		return '<input type="submit" id="delete" name="delete" ' . $cl . ' value="' . $this->buttons ['delete'] . '">';
	}
	/**
	 * Creates the update button for the data input form.
	 *
	 * @param
	 *        	class can be used to set a class to the input element.
	 *        	using this function ensures that the insert value in $_POST is recocnized by the data manipulation block.
	 *        	
	 * @return string the html of an input type submit with the name "update"
	 */
	protected function makeUpdateButton($class = null) {
		$cl = $class ? "class='$class' " : null;
		return '<input type="submit" id="update" name="update" ' . $cl . ' value="' . $this->buttons ['update'] . '">';
	}
	/**
	 * Creates the reset button for the data input form.
	 *
	 * @param
	 *        	class can be used to set a class to the input element.
	 *        	using this function ensures that the reset value in $_POST is recocnized by the data manipulation block.
	 *        	
	 * @return string the html of an input type submit with the name "reset"
	 */
	protected function makeQuitButton($class = null) {
		$cl = $class ? "class='$class' " : null;
		return '<input type="reset" id="quit" name="quit" ' . $cl . ' value="' . $this->buttons ['quit'] . '">';
	}
	/**
	 * Creates a hidden element to submit the value of the unique field of a class table, used to update or delete an entry.
	 *
	 * @param unknown $class
	 *        	class can be used to set a class to the input element.
	 *        	using this function ensures that the reset value in $_POST is recocnized by the data manipulation block.
	 * @return string the html of an input type hidden with the name of the unique field defined for the class table.
	 */
	protected function makeUfHiddenElement($value, $class = null) {
		$cl = $class ? "class='$class' " : null;
		return '<input type="hidden" id="uf" name="' . $this->uniquefield . '" ' . $cl . ' value="' . $value . '">';
	}
	/**
	 * Creates the standard buttons of a form to interact with the class table.
	 * This is usually used in a backend form to insert, update or delete data sets.
	 *
	 * @param unknown $uniqueField
	 *        	the value of the unique field of the class table.
	 */
	protected function standardFormButtons($iniValues = NULL) {
		if ($uniqueField = $iniValues [$this->uniquefield]) {
			echo self::makeUpdateButton ();
			echo self::makeDeleteButton ();
			echo self::makeQuitButton ();
			echo self::makeUfHiddenElement ( $uniqueField );
		} else {
			echo self::makeInsertButton ();
			echo self::makeQuitButton ();
		}
	}
	/**
	 * Handles the input, update and delete actions for the class table.
	 *
	 * This function evaluates the $_POST variable that was filled by submitting a form for inserting, updating
	 * or deleting a data set and calls the required function for this operation, i.e. SQL query. It must be
	 * placed before the form, if the form reloads the same page, which is usually the case. If data from the class table is also shown
	 * on the same page, it makes sense to place the function call befor the function that retrieves the data to display. Then, the updated
	 * are displayed immediately.
	 *
	 * If the action called is delete, the function returns a string with another form to ask for verification. Only if
	 * this is verified and dataBaseIo called again, the data set is deleted. For all other actions, the function
	 * returns NULL.
	 *
	 * @param array $values
	 *        	optionally if only specific values should be inserted. Make sure it contains a button value
	 *        	to carry out a db action.
	 * @return string|NULL|boolean a html string to show the delete verification, true if an operation was succesfull or NULL if nothing is to do.
	 */
	public function dataBaseIo($values = NULL) {
		// the change-update-delete block
		$values = $values ? $values : $_POST;
		if (! count ( $values ))
			return NULL;
			
			// quit
		if ($values ['quit'] == $this->buttons ['quit']) {
			unset ( $values );
		}
		
		// finaly delete entry
		if (in_array ( $values ['delete'], [ 
				'OK',
				'Nur diesen',
				'Alle' 
		] )) {
			$del = $values ['id'] ?: $values ['del'];
			static::delete ( $del, $values ['delete'] );
			$this->message = $del . ' gelöscht.';
		}
		
		// insert, delete or update
		if ($values ['insert'] == $this->buttons ['insert']) {
			unset ( $values ['insert'] );
			static::insert ( $values );
		} elseif ($values ['update'] == $this->buttons ['update']) {
			unset ( $values ['update'] );
			return static::update ( $values, $values [$this->uniquefield] );
		} elseif ($values ['delete'] == $this->buttons ['delete']) {
			return static::checkDelete ( $values [$this->uniquefield] );
		}
		return NULL;
	}
}
?>
