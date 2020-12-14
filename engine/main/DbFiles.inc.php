<?php
class DbFiles {
	private $dbFile;
	private $dataTypes = [ 
			'INTEGER' => SQLITE3_INTEGER,
			'REAL' => SQLITE3_FLOAT,
			'TEXT' => SQLITE3_TEXT,
			'BLOB' => SQLITE3_BLOB,
			'NULL' => SQLITE3_NULL 
	];
	function __construct(string $file) {
		$this->dbFile = dataPath . $file . ".db";
	}
	/**
	 * Creates a table in a SQLite3 file.
	 *
	 * @param string $table
	 *        	name of the table.
	 * @param array $fields
	 *        	field names and types.
	 * @param mixed $pk
	 *        	primary key(s), column name(s)
	 * @param mixed $uk
	 *        	unique key(s), column name(s)
	 * @param string $onConflict
	 *        	string of the ON CONFLICT clause
	 * @param $foreign string
	 *        	of the FOREIGN KEY clause
	 */
	public function createTable(string $table, array $fields, $pk = NULL, $uk = NULL, string $onConflict = NULL, string $foreign = NULL) {
		$log = start_install_log ( "DbFiles::createTable" );
		$qry = "CREATE TABLE IF NOT EXISTS $table";
		foreach ( $fields as $field ) {
			$null = $field->null === false ? "NOT NULL" : NULL;
			$primKey = $field->primaryKey !== null ? "PRIMARY KEY" : null;
			$default = ($field->default !== null & ! $primKey) ? "DEFAULT " . $field->default : null;
			$unique = ($field->unique !== null & ! $primKey) ? "UNIQUE " : NULL;
			$fd [] = "'" . $field->name . "' " . $field->type . " $null $default $primKey $unique";
		}
		$qry .= " (" . implode ( ",", $fd );
		if (is_array ( $pk ))
			$pk = $pk ? ", PRIMARY KEY (" . implode ( ",", $pk ) . ")" : NULL;
		else
			$pk = $pk ? ", PRIMARY KEY (" . $pk . ")" : NULL;
		
		if (is_array ( $uk ))
			$uk = $uk ? ", UNIQUE (" . implode ( ",", $uk ) . ")" : NULL;
		else
			$uk = $uk ? ", UNIQUE (" . $uk . ")" : NULL;
		$onConflict = $onConflict ? " ON CONFLICT " . $onConflict : NULL;
		$foreign = $foreign ? " FOREIGN KEY " . $foreign : NULL;
		$qry .= "$pk$uk$onConflict$foreign);";
		$db = new SQLite3 ( $this->dbFile );
		if (! $db->exec ( $qry ))
			$log->write ( $db->lastErrorMsg (), LOG_WARN );
		$db->close ();
	}
	/**
	 * Saves data to the data base table.
	 *
	 * @param string $table
	 *        	name of the table.
	 * @param array $data
	 *        	array of data to insert.
	 * @param bool $autoid
	 *        	if true an id is created from the table name and the rowid. works only if a column named id is in the table.
	 * @return number
	 */
	public function save(string $table, array $data, bool $autoid = FALSE) {
		$log = start_system_log ( "DbFiles::save" );
		$db = new SQLite3 ( $this->dbFile );
		$fields = self::getFields ( $table );
		if ($data ['rowid']) {
			$fields ['name'] [] = "rowid";
			$fields ['type'] [] = "INTEGER";
		}
		if ($autoid) {
			$res = $db->query ( "SELECT max(rowid) FROM $table;" );
			$nextRow = $res->fetchArray ( SQLITE3_NUM ) [0];
			$id = "$table_$nextrow,";
			$data ['id'] = $id;
			if (! in_array ( $fields ['name'], "id" )) {
				$log->write ( "autoid cannot be created. No column named 'id' in $table.", LOG_ERROR );
			}
		}
		$numberOfFields = count ( $fields ['name'] );
		$qry = "REPLACE INTO $table (" . implode ( ",", $fields ['name'] ) . ") VALUES (" . implode ( ",", array_fill ( 0, $numberOfFields, "?" ) ) . ")";
		$stm = $db->prepare ( $qry );
		for($i = 0; $i < count ( $fields ['name'] ); $i ++) {
			if (($t = $this->dataTypes [$fields ['type'] [$i]]) == SQLITE3_INTEGER)
				$v = intval ( $data [$fields ['name'] [$i]] );
			elseif (($t = $this->dataTypes [$fields ['type'] [$i]]) == SQLITE3_FLOAT)
				$v = floatval ( $data [$fields ['name'] [$i]] );
			else
				$v = $data [$fields ['name'] [$i]];
			$stm->bindValue ( $i + 1, $v, $t );
		}
		$success = $stm->execute ();
		if (! $success)
			$log->write ( $db->lastErrorMsg (), LOG_ERROR );
		$stm->close ();
		$db->close ();
		return $success ? 1 : 0;
	}
	/**
	 * @param string $table
	 * @param int $rowid
	 * @return number
	 */
	function delete(string $table, int $rowid) {
		$log = start_system_log ( "DbFiles::delete" );
		$db = new SQLite3 ( $this->dbFile );
		$qry = "DELETE FROM $table WHERE rowid=$rowid";
		$success = $db->exec ( $qry );
		if (! success)
			$log->write ( $db->lastErrorMsg (), LOG_ERROR );
		$db->close ();
		return $success ? 1 : 0;
	}
	/**
	 * Runs a SELECT query against athe data base and returns the result a associated array.
	 *
	 * @param string $table
	 *        	name of the table
	 * @param array $subset
	 *        	key-value pairs of the features to select
	 * @param array $options
	 *        	additional search options (not yet used)
	 * @param mixed ...$columns
	 *        	zero or more columns to select
	 * @return boolean|array results or false on error.
	 */
	public function get($table, $subset = NULL, $options = NULL, $withRowid = FALSE, ...$columns) {
		$data = NULL;
		$fields = self::getFields ( $table );
		$log = start_system_log ( "DbFiles::get" );
		$db = new SQLite3 ( $this->dbFile );
		$distinct = $options['distinct'] ? " DISTINCT " : NULL;
		$orderby = ($ob = $options['orderby']) ? " ORDER BY $ob ": NULL;
		$qry = "SELECT$distinct";
		//return FALSE;
		/*
		 * if ($options ['distinct']) {
		 * $qry .= " DISTINCT ";
		 * }
		 */
		$comp = "=";
		if ($columns) {
			$columns = implode ( ",", $columns );
		} else {
			$columns = "*";
		}
		if ($withRowid)
			$columns .= ",rowid";
		
		$qry .= " $columns FROM $table";
		
		if (is_array ( $subset )) {
			$qry .= ' WHERE ';
			foreach ( $subset as $key => $value ) {
				if ($valArr = is_array ( $value )) {
					$sel [] = "$key IN (" . implode ( ",", array_fill ( 0, count ( $value ), "?" ) ) . ")";
				} else {
					$sel [] = "$key $comp ?";
				}
			}
			$qry .= implode ( " AND ", $sel );
			$qry .= $orderby;
			
			//echo "<p>$qry</p>";
			$subs = 0;
			$stm = $db->prepare ( $qry );
			foreach ( $subset as $key => $value ) {
				$subs += 1;
				$nOfVals = count ( $value );
				$i = array_search ( $key, $fields ['name'] );
				if (($t = $this->dataTypes [$fields ['type'] [$i]]) == SQLITE3_INTEGER) {
					if ($valArr) {
						for($s = 0; $s < $nOfVals; $s ++) {
							$v = intval ( $value [$s] );
							$subs += $s;
							$stm->bindValue ( $subs, $v, $t );
						}
					} else {
						$v = intval ( $value );
						$stm->bindValue ( $subs, $v, $t );
						$subs += 1;
					}
				} elseif (($t = $this->dataTypes [$fields ['type'] [$i]]) == SQLITE3_FLOAT) {
					if ($valArr) {
						for($s = 0; $s < $nOfVals; $s ++) {
							$v = floatval ( $value [$s] );
							$subs += $s;
							$stm->bindValue ( $subs, $v, $t );
						}
					} else {
						$v = floatval ( $value );
						$stm->bindValue ( $subs, $v, $t );
						$subs += 1;
					}
				} else {
					if ($valArr) {
						for($s = 0; $s < $nOfVals; $s ++) {
							$v = $value [$s];
							$subs += $s;
							$stm->bindValue ( $subs, $v, $t );
						}
					} else {
						$v = $value;
						$stm->bindValue ( $subs, $v, $t );
						$subs += 1;
					}
				}
			}
			$res = $stm->execute ();
		} else
			$res = $db->query ( $qry . "$orderby;" );
		if (! $res) {
			$log->write ( $db->lastErrorMsg (), LOG_ERROR );
			return FALSE;
		}
		while ( $row = $res->fetchArray ( SQLITE3_ASSOC ) ) {
			$rows [] = $row;
		}
		$db->close ();
		return $rows;
	}
	/**
	 * Returns the fields (columns) of the specified table.
	 *
	 * @param string $table
	 * @return mixed an array of strings containing the field names
	 */
	public function getFields(string $table) {
		$log = start_system_log ( "DbFiles::getFields" );
		$db = new SQLite3 ( $this->dbFile );
		$res = $db->query ( "PRAGMA table_info($table);" );
		while ( $set = $res->fetchArray ( SQLITE3_ASSOC ) ) {
			$fields ['name'] [] = $set ['name'];
			$fields ['type'] [] = $set ['type'];
		}
		$res->finalize ();
		if (! $fields) {
			$log->write ( "No fields in table $table in " . $this->dbFile, LOG_ERROR );
			$db->close ();
			die ( "No table found or no fields in table. The module is not properly installed." );
		}
		$db->close ();
		return $fields;
	}
}