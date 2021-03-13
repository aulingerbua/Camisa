<?php

/** Includes a javascript that resides in /engine/js
 * @param string $script name of the script without suffix
 * @param string $path can be a path in the file system, the keyword 'template' or NULL. If NULL, 
 * the script is searched in /engine/js
 */
function include_javascript(string $script, string $path = NULL)
{
    if (! $path)
        $path = "/engine/js";
    if ($path == "template")
        $path = "templates/" . Template;
    echo "<script src='$path/$script.js'></script>";
}

/**
 * Includes the classes (and functions) of the named module.
 *
 * The module name must be exactly as the filename without the suffixes.
 *
 * @param string $module
 */
function include_module_classes($module)
{
    include_once IncPath . "$module.inc.php";
}

/**
 * Displays Pages that can be editet.
 * The page name is displayed as link wrapped in a list element.
 */
function list_editable_pages()
{
    $pg = new Pages();
    $pages = $pg->editablePages();
    foreach ($pages as $page) {
        echo '<li';
        if ($_GET['page'] == $page) {
            echo ' id="current"';
        }
        echo "><a href='?tool=page&page=$page'>$page</a></li>\n";
    }
}

/**
 * Returns the currently logged-in user name.
 *
 * @return string
 */
function current_user()
{
    return $_SESSION['user'];
}

/**
 * Returns the level of the currently logged-in user as integer value.
 *
 * @return integer
 */
function current_user_level()
{
    return intval($_SESSION['level']);
}

/**
 * Creates a dropdown menue to select members.
 *
 * @param unknown $selected
 *            the user id of the selected item.
 * @param unknown $exclude
 *            string or array of user ids to exclude.
 * @param string $showFullNames
 *            whether to show full member names or user ids.
 * @return NULL
 */
function members_dropdown($selected = NULL, $exclude = NULL, $showFullNames = FALSE)
{
    $qry = "SELECT uid,name FROM members";
    if ($exclude) {
        $qry .= " WHERE uid NOT IN ('";
        $qry .= implode("','", $exclude) . "')";
    }
    $qry .= ";";
    if (! $data = execute_query($qry))
        return NULL;
    echo "<select id='memberlist' name='uid'>\n";
    foreach ($data as $member) {
        $uid = $member['uid'];
        $name = $withUid ? $member['uid'] : $member['name'];
        $select = ($selected && $selected == $uid) ? "selected" : NULL;
        echo "<option value='$uid' $select>$name</option>;\n";
    }
    echo "<select>\n";
}

/**
 * Checks if a current user can edit an entry.
 *
 * For an adminitrator this function returns always true. For the other possibilities the argument $options must be a json object as string. It must contain an entry
 * edit, specifying, who can edit the entry. Possible values are owner, team or all. If $options is not present, cannot be decoded or does not contain an entry edit,
 * false is returned.
 *
 * @param string $options
 *            the options as string.
 * @param string $owner
 *            the owner of the entry.
 * @param string $team
 *            the team that is allowed to edit the entry.
 * @return boolean
 */
function may_edit(string $options, string $owner = NULL, object $team = NULL)
{
    if (current_user_level() === - 1)
        return TRUE;
    $opt = make_options_object(json_decode($options));
    if (! ($edit = $opt->edit))
        return FALSE;
    if ($edit == "all")
        return TRUE;
    if ($edit == "owner" && current_user() === $owner)
        return TRUE;
    if (class_exists("TeamWork"))
        if ($edit == "team" && (new TeamWork())->isMemberOfTeam($opt->team, current_user()))
            return TRUE;
    return FALSE;
}

/**
 * Make an object (or array) of options.
 *
 * This is used to convert a json string used to store options in a data base field to an accessible object.
 *
 * @param string $optionsString
 *            the string to decompose that contains the options
 * @param boolean $asArray
 *            if TRUE returns an associated array
 * @return NULL|mixed the options as object or array
 */
function make_options_object($optionsString, $asArray = FALSE)
{
    $log = start_system_log("make_options_array");
    if (! $optionsString)
        return NULL;
    try {
        $options = json_decode($optionsString, $asArray);
    } catch (Exception $e) {
        $log->write($e->getMessage(), LOG_WARNING);
        return NULL;
    }
    return $options;
}

/**
 * create a selection bar,
 * a form containing selection fields
 */
function selection($current = NULL, $labels = NULL, ...$fields)
{
    $form[] = '<form class="selection-form" action="" method=get>';
    $form[] = '<ul>';
    // $form [] = '<li><input type="hidden" id="link" name="page" value="' . $link .'"></li>';
    for ($i = 0; $i < count($fields); $i ++) {
        $field = $fields[$i];
        $name = key($field);
        $l = $labels[$i] ? $labels[$i] : $name;
        $form[] = '<li><label for="' . $name . '">' . $l . '</label>';
        $form[] = '<select id="' . $name . '" name="' . $name . '">';
        $form[] = '<option value="' . NULL . '">Alle</option>';
        foreach ($field[$name] as $val) {
            $sel = $current[$name] === $val ? "selected" : NULL;
            $form[] = '<option value="' . $val . '" ' . $sel . '>' . $val . '</option>';
        }
        $form[] = '</select></li>';
    }
    $form[] = '<li><input type="submit" id="choose" value="Auswahl"></li>';
    $form[] = '</ul>';
    $form[] = '</form>';
    echo implode("\n", $form);
}

/**
 * create a selection bar,
 * a list containing selection fields without the form element
 */
function selection_ul($current = NULL, $labels = NULL, ...$fields)
{
    $form[] = '<ul>';
    // $form [] = '<li><input type="hidden" id="link" name="page" value="' . $link .'"></li>';
    for ($i = 0; $i < count($fields); $i ++) {
        $field = $fields[$i];
        $name = key($field);
        $l = $labels[$i] ? $labels[$i] : $name;
        $form[] = '<li><label for="' . $name . '">' . $l . '</label>';
        $form[] = '<select id="' . $name . '" name="' . $name . '">';
        $form[] = '<option value="' . NULL . '">Alle</option>';
        foreach ($field[$name] as $val) {
            $sel = $current[$name] === $val ? "selected" : NULL;
            $form[] = '<option value="' . $val . '" ' . $sel . '>' . $val . '</option>';
        }
        $form[] = '</select></li>';
    }
    $form[] = '<li><input type="submit" id="choose" value="Auswahl"></li>';
    $form[] = '</ul>';
    echo implode("\n", $form);
}

/**
 * Makes and echoes a selection form element.
 *
 * @param string $name
 *            name attribute of the element
 * @param array $options
 *            associated array, keys are the values of the options elements and the values are the displayed text.
 * @param string $select
 *            name of the option to show as selected.
 * @return boolean returns FALSE if name or options is missing.
 */
function make_selection_element(string $name, array $options, string $select = NULL)
{
    if (! $name | ! is_array($options))
        return FALSE;
    $sel = "<select name='$name'>\n";
    foreach ($options as $value => $text) {
        $selected = $value === $select ? " selected" : NULL;
        $sel .= "<option value='$value'$selected>$text</option>\n";
    }
    $sel .= "</select>\n";
    echo $sel;
    return TRUE;
}

/**
 * Filters $inparray where $field meets $crit.
 * The contents of the data field can have
 * one or more comma separated values.
 */
function res_filter($inparray, $field, $crit)
{
    if (count($inparray[$field]) == 1) {
        if (count($test = explode(',', str_replace(", ", ",", $inparray[$field]))) > 1) {
            if (in_array($crit, $test)) {
                $found[] = $inparray;
            }
        } else {
            if ($inparray[$field] == $crit) {
                $found[] = $inparray;
            }
        }
    } else {
        for ($i = 0; $i < count($inparray); $i ++) {
            if (count($test = explode(',', str_replace(", ", ",", $inparray[$i][$field]))) > 1) {
                if (in_array($crit, $test)) {
                    $found[] = $inparray[$i];
                }
            } else {
                if ($inparray[$i][$field] == $crit) {
                    $found[] = $inparray[$i];
                }
            }
        }
    }
    return $found;
}

/**
 * Splits an array into two,
 * one where the keys match pattern
 * and the rest.
 */
function split_array_key(array $input, $pattern)
{
    foreach ($input as $k => $val) {
        if (preg_match($pattern, $k)) {
            $split["match"][$k] = $val;
        } else {
            $split["alt"][$k] = $val;
        }
    }
    return array_reverse($split);
}

/**
 * Creates a log object for writing installation Messages.
 *
 * @return log
 */
function start_install_log($caller = NULL)
{
    $log = new Log("install.log", $caller, "Reports Messages about the installation and updates of the base system and modules.", "install log");
    return $log;
}

/**
 * Creates a log object for writing system Messages, i.e.
 * Messages of the software.
 *
 * @return log
 */
function start_system_log($caller = NULL)
{
    $log = new Log("system.log", $caller, "Reports errors, warnings and other Messages of the software", "system log");
    return $log;
}

/**
 * Retrieves the ssl key from the system table.
 *
 * @return mixed
 */
function sslK()
{
    $db = db_init();
    $log = start_system_log("sslK");
    $qry = "SELECT value FROM system WHERE entry = 'sslkey'";
    if (! ($result = $db->query($qry))) {
        echo "Severe error!";
        $log->write("Data base error! " . $db->error, LOG_ERROR);
        die();
    }
    $K = $result->fetch_row();
    $db->close();
    return $K[0];
}

/**
 * Catches the $_POST variable and handles the inputs, updates or deletes into the data base.
 *
 * @param Registry $inputs
 *            the class that is connected to a data base table
 * @see Registry
 */
function handle_db_input(Registry $inputs)
{
    $chkdel = $inputs->dataBaseIo();

    if (is_string($chkdel)) {
        echo $chkdel;
    }
}

/**
 * retrieves the info of registered Templates
 *
 * @return an array containing the name and module of the template
 */
function get_registered_templates()
{
    $tpl = new Templates();
    return $tpl->retrieve(NULL);
}

/**
 * Display the header of the page.
 * If no argument is provided, the default header.php is displayed.
 *
 * @param string $header
 */
function get_header($header = NULL)
{
    $header = $header ? PagesPath . $header . ".php" : PagesPath . "header.php";
    require_once $header;
}

/**
 * Display the footer of the page.
 * If no argument is provided, the default footer.php is displayed.
 *
 * @param string $footer
 */
function get_footer($footer = NULL)
{
    $footer = $footer ? PagesPath . $footer . ".php" : PagesPath . "footer.php";
    require_once $footer;
}

/**
 * Set the id of a navigation link to "current".
 * How "current" looks like can be defined via css.
 * On success echoes "id='current'".
 *
 * @param string $query
 *            the query variable
 * @param string $value
 *            the value the query variable is tested against.
 * @param boolean $default
 *            if true, this link is the default current link.
 */
function make_current($query, $value, $default = false)
{
    if ($_GET[$query] === $value)
        echo "id='current'";
    elseif (! $_GET[$query] && $default)
        echo "id='current'";
}

/**
 * Returns the string id='current'.
 *
 * @param string $query
 *            the query variable
 * @param string $value
 *            the value the query variable is tested against.
 * @param boolean $default
 *            if true, this link is the default current link.
 * @return string
 *
 * @see make_current()
 */
function set_current($query, $value, $default = false)
{
    if ($default)
        return "id='current'";
    if (! $_GET[$query])
        return NULL;
    if ($_GET[$query] === $value)
        return "id='current'";
    return NULL;
}

/**
 * Loads the module style sheets.
 * To be used in the html header.
 *
 * Writes a warning to the system log if styles could not be loaded.
 */
function load_module_styles()
{
    global $db;
    $log = start_system_log('load_module_styles');
    $qry = "SELECT value FROM system where entry = 'styles'";
    if (! $res = $db->query($qry)) {
        $log->write("Module styles could not be loaded", LOG_WARNING);
        return NULL;
    }
    while ($row = $res->fetch_row())
        echo '<link rel="stylesheet" type="text/css" href="' . HOME . 'styles/' . $row[0] . '">' . "\n";
}

/**
 * Init the data base for read-write access and return a data base object.
 *
 * @return Psqlo|mysqli data base object.
 */
function db_init()
{
    $log = start_system_log('db_init');
    $dbAccess = DBaccess ? DBaccess : [
        'host' => DBhost,
        'user' => DBuser,
        'port' => DBport,
        'password' => DBport,
        'database' => DBname
    ];
    if (DBtype === "psql")
        $db = new Psqlo($dbAccess['host'], $dbAccess['user'], $dbAccess['port'], $dbAccess['password'], $dbAccess['database']);
    else
        $db = new mysqli($dbAccess['host'], $dbAccess['user'], $dbAccess['password'], $dbAccess['database']);

    if ($db->connect_errno) {
        $log->write("Data base connection error! " . $db->connect_error, LOG_ERROR);
        echo "severe error";
        die();
    }
    return $db;
}

/**
 * Initializes the PHPmailer.
 *
 * Settings (server, SMTP, password, ...) are read from the system table.
 * Then a phpMailer object is created, options set, and the phpMailer object returned.
 * If no settings are found in the system table an error message is written to the system log.
 *
 * @param
 *            bool html whether to allow html in the message body
 *            
 * @return boolean|PHPMailer the PHPmailer object with settings or false if no settings are found.
 */
function phpMailer_init(bool $html = false)
{
    $log = start_system_log('phpMailer_init');
    $settings = (new System())->getSendmailSettings();
    if (! $settings) {
        $log->write("no sendmail settings found", LOG_ERROR);
        return FALSE;
    }

    require IncPath . 'phpMailer/class.phpmailer.php';

    // Create a new PHPMailer instance
    $mailer = new PHPMailer();
    // Set who the message is to be sent from
    $mailer->setFrom($settings['fromemail'], $settings['fromname']);
    // character set
    $mailer->CharSet = "utf-8";
    // Set the body
    $mailer->isHTML($html);

    if ($settings['smtpauth'] == 1) {
        require IncPath . 'phpMailer/class.smtp.php';
        // Tell PHPMailer to use SMTP
        $mailer->isSMTP();
        // Username to use for SMTP authentication
        $mailer->Username = $settings['username'];
        // Password to use for SMTP authentication
        $mailer->Password = $settings['pwd'];
        // Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mailer->SMTPDebug = 0;
        // Ask for HTML-friendly debug output
        $mailer->Debugoutput = 'html';
        // Set the hostname of the mail server
        $mailer->Host = $settings['smtp'];
        // Set the SMTP port number - likely to be 25, 465 or 587
        $mailer->Port = $settings['port'];
        if ($settings['port'] == 465)
            $mailer->SMTPSecure = 'ssl';
        elseif (in_array($settings['port'], [
            25,
            587
        ]))
            $mailer->SMTPSecure = 'starttls';
        else {
            $log->write("Invalid smtp port " . $settings['port'], LOG_WARN);
            return false;
        }
        // Whether to use SMTP authentication
        $mailer->SMTPAuth = true;
    }
    return $mailer;
}

/**
 * Sends an email messgage to the specified member ID(s)
 *
 * @param mixed $memberID
 * @param string $message
 * @param string $subject
 * @return boolean
 */
function notifyMember($memberID, string $message, $subject = "notification")
{
    $log = start_system_log("notifyMember");
    $postie = phpMailer_init();
    $us = new Users();
    if (is_array($memberID)) {
        foreach ($memberID as $id) {
            $maddr = $us->getEmail($id);
            $postie->addAddress($maddr);
        }
    } else {
        $maddr = $us->getEmail($memberID);
        $postie->addAddress($maddr);
    }

    $postie->Subject = "[" . SITE . "] $subject";
    if (! $message) {
        $log->write("No message body. Mail not sent.");
        return FALSE;
    }
    $postie->Body = $message;
    if (! $postie->send()) {
        $log->write($postie->ErrorInfo . " Mail not sent.");
        return FALSE;
    }
    return TRUE;
}

/**
 * Sends an email messgage to members having an Admin role
 *
 * @param string $message
 * @param string $subject
 * @return boolean
 */
function notifyAdmins(string $message, string $subject = "notification")
{
    $admins = (new Users())->retrieve([
        'role' => 'administrator'
    ], NULL, "uid");
    for ($i = 1; $i < count($admins); $i ++) {
        $ids[] = $admins[$i]["uid"];
    }
    return notifyMember($ids, $message, $subject);
}

/**
 * Takes a query string and executes the query.
 *
 * This is a stand alone function doing the same job as @link{Registry::executeQuery()}. If the query produces an error, an exception is thrown. If the query returns
 * data,
 * this data is returned as an associated array. Otherwise, the number of affected rows is returned.
 *
 * @param string $qry
 *            the query as string.
 * @param boolean $multi
 *            whether it is a multi query.
 * @param boolean $closeDB
 *            whether the database connection should explicitly be closed.
 * @param boolean $closeRes
 *            whether the result should be closed (freed)
 * @throws Exception
 * @return boolean|integer|array false on error, the number of affected rows or the recieved data.
 */
function execute_query($qry, $multi = FALSE, $closeDB = FALSE, $closeRes = FALSE)
{
    // echo "<p>$qry</p>";
    $data = NULL;
    $db = db_init();
    if ($multi)
        $result = $db->multi_query($qry);
    else
        $result = $db->query($qry);

    if (! $result) {
        throw new Exception("Error executing query: " . $db->error);
        return false;
    }

    if ($result === true)
        $data = $db->affected_rows;
    else {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        if ($data and $closeRes)
            $result->free();
    }
    if ($closeDB)
        $db->close();
    return $data;
}
?>
