<?php
/**
 * This is a wrapper for pg_connect and pg_query (or pg_query_param) to mimic the functions of mysqli in object mode.
 *
 * @author aulinger
 *        
 */
class Psqlo {
	private $connection;
	public $connect_errno = NULL;
	public $error;
	function __construct($host, $user, $port, $password, $database) {
		$conStr = "host=$host port=$port user=$user password=$password dbname=$database";
		// echo $conStr;
		if (! $this->connection = pg_connect ( $conStr ))
			$this->connect_errno = 1;
	}
	/**
	 * A wrapper for pg_query.
	 *
	 * Returns a PsqlResult object on success and false if an error occurs. The error, returned by pg_last_error is accessible via Psqlo->error.
	 *
	 * @param string $qry        	
	 * @return boolean|PsqlResult
	 */
	public function query($qry) {
		if (! $result = pg_query ( $this->connection, $qry )) {
			$this->error = pg_last_error ( $this->connection );
			return FALSE;
		}
		return (new PsqlResult ( $result ));
	}
	/**
	 * A wrapper for pg_escape_literal.
	 *
	 * pg_escape_literal surrounds the escaped string by quotes. These are trimmed in order to be consistent with mysql_real_escape_string.
	 *
	 * @param string $string        	
	 * @return string
	 */
	public function real_escape_string($string) {
		$str = pg_escape_literal ( $this->connection, $string );
		$str = trim ( $str, "'" );
		return $str;
	}
	/**
	 * Returns the field names of a table as an array.
	 *
	 * @param string $table        	
	 * @return boolean|array
	 */
	public function get_fieldnames($table, $exclude_autoinc = TRUE) {
		$qry = "SELECT column_name FROM information_schema.columns WHERE table_name ='$table'";
		if ($exclude_autoinc) {
			$exclude = self::get_autoinc_fields ( $table );
		}
		if (! $result = pg_query ( $this->connection, $qry )) {
			$this->error = pg_last_error ( $this->connection );
			return FALSE;
		}
		while ( $f = pg_fetch_row ( $result ) ) {
			if ($exclude) {
				if (! in_array ( $f [0], $exclude ))
					$fields [] = $f [0];
			} else
				$fields [] = $f [0];
		}
		return ($fields);
	}
	/**
	 * Returns the field names where the column default contains the nextval function and is hence auto incremented.
	 * 
	 * @param string $table        	
	 * @return boolean|mixed
	 */
	private function get_autoinc_fields($table) {
		$qry = "SELECT column_name FROM information_schema.columns WHERE table_name ='$table' AND column_default LIKE '%nextval%';";
		if (! $result = pg_query ( $this->connection, $qry )) {
			$this->error = pg_last_error ( $this->connection );
			return FALSE;
		}
		while ( $f = pg_fetch_row ( $result ) ) {
			$fields [] = $f [0];
		}
		return ($fields);
	}
}
?>