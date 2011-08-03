<?php
include_once('./includes/config.php');

$message = '';
$mysql_connection = mysql_connect('localhost', $mysql_username, $mysql_password);
if (!$mysql_connection) {
    die(mysql_error());
}
$db_selected = mysql_select_db('uren', $mysql_connection);
if (!$db_selected) {
    die (mysql_error());
}

//Take care of the template
$template = new Template();
$template->set_custom_template('templates', 'default');
