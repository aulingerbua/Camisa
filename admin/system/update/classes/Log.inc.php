<?php
define ( "LOG_INFO", "info" ); // !< string for the log level "info"
define ( "LOG_WARNING", "warning" ); // !< string for the log level "warning"
define ( "LOG_ERROR", "error" ); // !< string for the log level "error"
/**
 * This class contains methods for creating logs, writing and displaying log entries.
 *
 * A log file consists of JSON Objects separated by semicolons. The firs JSON object contains the log header.
 * The log header contains the creation date of the log, its name, the permission level requirde to view the log
 * and a short description. The subsequent JSON objects are the log entries containing the timestamp, user who caused the entry,
 * the message, the log level and the caller function.
 *
 * @author Armin Aulinger
 *        
 */
class Log {
	private $header = [ 
			"creation_date" => NULL,
			"log_name" => NULL,
			"permission" => - 1,
			"description" => NULL 
	];
	private $entry = [ 
			"timestamp" => NULL,
			"user" => NULL,
			"message" => NULL,
			"level" => "info",
			"caller" => NULL 
	];
	private $file;
	private $sep = ";;";
	/**
	 *
	 * @param string $file
	 *        	name and path of the logfile
	 * @param string $caller
	 *        	caller function
	 * @param string $description
	 *        	log description
	 * @param string $name
	 *        	log name
	 */
	function __construct($file, $caller = NULL, $description = NULL, $name = NULL) {
		$this->file = LogPath . $file;
		$this->entry ['caller'] = $caller;
		if (! file_exists ( $this->file ))
			self::writeHeader ( $description, $name );
	}
	/**
	 * Writes the log header when the log file is created.
	 *
	 * @param string $description
	 *        	log description
	 * @param string $name
	 *        	log name
	 */
	private function writeHeader($description, $name) {
		$this->header ['creation_date'] = date ( "c" );
		$this->header ['description'] = $description;
		$this->header ['log_name'] = $name;
		file_put_contents ( $this->file, json_encode ( $this->header ) );
	}
	/**
	 * Writes a log entry
	 *
	 * @param strin $message
	 *        	the message
	 * @param string $level
	 *        	the log level
	 */
	public function write($message, $level = NULL) {
		$this->entry ['user'] = $_SESSION ['user'];
		$this->entry ['timestamp'] = date ( "c" );
		if (! $level)
			$this->entry ['level'] = $level;
		$this->entry ['message'] = $message;
		
		file_put_contents ( $this->file, "$this->sep\n" . json_encode ( $this->entry ), FILE_APPEND );
	}
	/**
	 * Displays the log in a table.
	 *
	 * It is actually two table, one showing the log header and a second one with the log entries.
	 */
	public function display() {
		if ($log_content = file_get_contents ( $this->file )) {
			$log_entries = explode ( "$this->sep", $log_content );
			$header = json_decode ( $log_entries [0], true );
			unset ( $log_entries [0] );
			$log_obj = json_decode ( $logfile, true );
			// var_dump ( $log_obj );
			$head [] = "<table class='log-head'>";
			$head [] = "<thead>";
			$head [] = "<tr>";
			$head [] = "<th>log name</th>";
			$head [] = "<th>log created</th>";
			$head [] = "<th>description</th>";
			$head [] = "</tr>";
			$head [] = "</thead>";
			$head [] = "<tbody>";
			$head [] = "<tr>";
			$head [] = "<td>" . $header ['log_name'] . "</td>";
			$head [] = "<td>" . $header ['creation_date'] . "</td>";
			$head [] = "<td>" . $header ['description'] . "</td>";
			$head [] = "</tr>";
			$head [] = "</tbody>";
			$head [] = "</table>";
			$body [] = "<table class='log-body'>";
			$body [] = "<thead>";
			$body [] = "<tr>";
			$body [] = "<th>time</th>";
			$body [] = "<th>user</th>";
			$body [] = "<th>calling function</th>";
			$body [] = "<th>level</th>";
			$body [] = "<th>message</th>";
			$body [] = "</tr>";
			$body [] = "</thead>";
			$body [] = "<tbody>";
			foreach ( $log_entries as $entry ) {
				$en = json_decode ( $entry, true );
				$body [] = "<tr>";
				$body [] = "<td>" . $en ['timestamp'] . "</td>";
				$body [] = "<td>" . $en ['user'] . "</td>";
				$body [] = "<td>" . $en ['caller'] . "</td>";
				$body [] = "<td>" . $en ['level'] . "</td>";
				$body [] = "<td>" . $en ['message'] . "</td>";
				$body [] = "</tr>";
			}
			$body [] = "</tbody>";
			$body [] = "</table>";
			echo implode ( "\n", $head ) . "\n" . implode ( "\n", $body );
		} else
			echo "logfile could not be read";
	}
}