<?php
/** This class contains methods for displaying, uploading and downloading files on the server.
 * @author Armin Aulinger
 *
 */
class FileHandling {
	function __construct($base) {
		$this->basePath = $base;
	}
	private $mimeTypes = [ 
			'text' => [ 
					'text/plain',
					'text/html',
					'text/css',
					'text/richtext',
					'text/csv',
					'text/vnd.rn-realtext',
					'application/rtf',
					'application/x-rtf',
					'application/pdf',
					'application/x-tex' 
			],
			'image' => [ 
					'image/png',
					'image/jpeg',
					'image/pjpeg',
					'image/tiff',
					'image/x-tiff',
					'image/gif',
					'image/svg+xml' 
			],
			'audio' => [ 
					'audio/aiff',
					'audio/x-aiff',
					'audio/mpeg3',
					'audio/mpeg',
					'audio/x-mpeg-3',
					'audio/x-mpeg',
					'audio/ogg',
					'application/ogg',
					'audio/wav',
					'audio/x-wav',
					'application/x-ms-wmp',
					'application/x-midi',
					'audio/midi',
					'audio/x-mid',
					'audio/x-midi',
					'music/crescendo',
					'x-music/x-midi' 
			],
			'video' => [ 
					'application/x-troff-msvideo',
					'video/avi',
					'video/msvideo',
					'video/x-msvideo',
					'video/x-dv',
					'video/mpeg',
					'video/x-mpeg',
					'video/quicktime',
					'video/x-sgi-movie' 
			],
			'archive' => [ 
					'application/x-compress',
					'application/x-compressed',
					'application/x-zip-compressed',
					'application/zip',
					'multipart/x-zip',
					'application/gnutar',
					'application/x-tar' 
			],
			'office' => [ 
					'application/excel',
					'application/vnd.ms-excel',
					'application/x-excel',
					'application/x-msexcel',
					'application/msword',
					'application/mspowerpoint',
					'application/powerpoint',
					'application/vnd.ms-powerpoint',
					'application/x-mspowerpoint',
					'application/vnd.oasis.opendocument.text',
					'application/vnd.oasis.opendocument.presentation',
					'application/vnd.oasis.opendocument.spreadsheet' 
			] 
	];
	private $basePath;
	private $mime_allowed = [ 
			"text/plain" 
	];
	//!< @var integer 1MB by default
	private $max_size = 1048576;
	public $buttons = [ 
			upload => 'hochladen',
			delete => 'löschen',
			quit => 'abbrechen' 
	];
	/**
	 * This function defines the mime types of files that can be uploaded.
	 *
	 * By default, only plain text can be uploaded. You can further define an array of mime types or an array of key words: text (includes pdf), image, audio, videa,
	 * office or archive.
	 *
	 * @param array $mime_types
	 *        	an array of mime types to enable upload for.
	 */
	public function setMime(array $mime_types) {
		$mimes = [];
		if (is_array ( $mime_types )) {
			if (in_array("all", $mime_types))
				$mime_types = array_keys($this->mimeTypes);
			foreach ($mime_types as $tpe)
				$mimes = array_merge($mimes,$this->mimeTypes[$tpe]);
			$this->mime_allowed = $mimes;
		} else
			$this->mime_allowed = $mime_types;
	}
	/**
	 * Sets the maximum file size.
	 *
	 * @param integer $size size in bytes.
	 */
	public function setSize($size) {
		$this->max_size = $size;
	}
	public $err_msg;
	/**
	 * Fetch the contents of the a directory and return an array of files and directories.
	 *
	 * @param string $path
	 * @return string[]
	 */
	public function fetchDirectoryContents($path) {
		$di = new DirectoryIterator ( $this->basePath . $path );
		$di->rewind ();
		foreach ( $di as $item ) {
			if ($item->isDot ()) {
				$return_path [] = $item->getPathname ();
				continue;
			}
			if ($item->isDir ()) {
				$directory ['date'] = $item->getMtime ();
				$directory ['name'] = $item->getBasename ();
				$directory ['path'] = $item->getRealPath ();
				$directories [] = $directory;
			}
			if ($item->isFile ()) {
				$file ['date'] = $item->getMtime ();
				$file ['name'] = $item->getBasename ();
				$file ['path'] = $item->getPathname ();
				$file ['type'] = $item->getExtension ();
				$files [] = $file;
			}
		}
		$contents = [ 
				"directories" => $directories,
				"files" => $files 
		];
		return $contents;
	}
	/**
	 * Deletes a file
	 *
	 * @param string $file
	 * @return boolean true on success
	 */
	public function deleteFile($file) {
		$file = $this->basePath . $file;
		if (is_dir ( $file )) {
			if (! rmdir ( $file )) {
				$this->err_msg = "Directory cannot be deleted. Only empty directories can be deleted.";
				return false;
			}
		} elseif (! unlink ( $file )) {
			$this->err_msg = "could not delete $file";
			return false;
		}
		return TRUE;
	}
	/**
	 * Creates a new directory
	 *
	 * @param string $file
	 * @return boolean true on success
	 */
	public function mkDir($file) {
		$dir = $this->basePath . $file;
		if (! mkdir ( $dir, 0764 )) {
			$this->err_msg = "could not create directory $file";
			return false;
		}
		return TRUE;
	}
	/**
	 * Uploads a single file or an array of files.
	 * Performs also error treatment and echoes messages.
	 *
	 * @param string $path
	 *        	the path where the file(s) should be stored ion the server
	 * @param string $file
	 *        	the (array) of files to upload, i.e. its name in $_FILES
	 * @return boolean|string true or a message
	 */
	public function upload(string $path, string $file) {
		//var_dump ( $_FILES );
		if (! $file) {
			$this->err_msg = "Kein File angegeben";
			return false;
		}
		
		if (! $path) {
			$this->err_msg = "Kein Pfad angegeben";
			return FALSE;
		}
		
		$dir = $this->basePath . $path . "/";
		if (! is_writable ( $dir )) {
			$this->err_msg = "the destination directory is not writable.";
			return false;
		}
		if (is_array ( $_FILES [$file] ['error'] )) {
			for($k = 0; $k < count ( $_FILES [$file] ['error'] ); $k ++) {
				$destination = $dir . basename ( $_FILES [$file] ['name'] [$k] );
				if (empty ( $_FILES [$file] ['tmp_name'] [$k] )) {
					$this->err_msg = "Keine Datei gewählt";
					return FALSE;
				} elseif ($_FILES [$file] ['size'] [$k] > $this->max_size || $_FILES [$file] ['error'] [$k] == 2) {
					$this->err_msg = "Die Datei ist zu groß";
					return FALSE;
				} elseif (! in_array ( $_FILES [$file] ['type'] [$k], $this->mime_allowed )) {
					$this->err_msg = "Das Hochladen des Dateiformats " . $_FILES [$file] ['type']. " ist nicht erlaubt.";
					return FALSE;
				} elseif (! move_uploaded_file ( $_FILES [$file] ['tmp_name'] [$k], $destination )) {
					$this->err_msg = "Upload von " . $_FILES [$file] ['tmp_name'] [$k] . " in $destination aus unbekannten Gründen fehlgeschlagen: " . $_FILES [$file] ['error'];
					return FALSE;
				}
				$this->err_msg = "Files successfully uploaded.";
			}
		} else {
			$destination = $dir . basename ( $_FILES [$file] ['name'] );
			if (empty ( $_FILES [$file] ['tmp_name'] )) {
				$this->err_msg = "Keine Datei gewählt";
				return FALSE;
			} elseif ($_FILES [$file] ['size'] > $this->max_size || $_FILES [$file] ['error'] == 2) {
				$this->err_msg = "Die Datei ist zu groß";
				return FALSE;
			} elseif (! in_array ( $_FILES [$file] ['type'], $this->mime_allowed )) {
				$this->err_msg = "Das Hochladen des Dateiformats " . $_FILES [$file] ['type']. " ist nicht erlaubt.";
				return FALSE;
			} elseif (! move_uploaded_file ( $_FILES [$file] ['tmp_name'], $destination )) {
				$this->err_msg = "Upload von " . $_FILES [$file] ['tmp_name'] . " in $destination aus unbekannten Gründen fehlgeschlagen: " . $_FILES [$file] ['error'];
				return FALSE;
			} else {
				$this->err_msg = "File successfully uploaded.";
			}
		}
		return true;
	}
	/**
	 * reads the files found in $path and returns the html code of a selection element.
	 *
	 * @param string $path
	 *        	path of the directory
	 * @param string $pattern
	 *        	pattern in the file names to be matched
	 * @param string $name
	 *        	name and id of the selection element
	 * @return void|string
	 */
	public static function file_dropdown(string $path, string $pattern, string $name) {
		if (! $path)
			return;
		$dropdown [] = "<select id='$name' name='$name'>";
		$dropdown [] = "<option value=''>Wählen Sie ein Template</option>";
		$dir = new DirectoryIterator ( $path );
		foreach ( $dir as $file ) {
			if ($file->isFile () && preg_match ( "/$pattern/", $file->getFilename () )) {
				$name = $file->getBasename ( ".tpl.php" );
				$dropdown [] = "<option value='$name'>$name</option>";
			}
		}
		$dropdown [] = "</select>";
		return implode ( "\n", $dropdown );
	}
}
?>
