<?php
error_reporting(E_ALL); 

include_once('includes/template.php');
include_once('includes/page_header.php');

//Set the current template
$template->set_filenames(array(
    'body' => 'index_body.html'
));

/*
/* Now the actual page
/*/

$message = '';
$error = false;
$begin_limit = 0;
$end_limit = time();

//Get userlist
$sql = "SELECT * 
	FROM users
	ORDER BY user_name ASC";
$result = mysql_query($sql);
if (!$result) {
    die('Could not get result: ' . mysql_error());
}
while ($row = mysql_fetch_assoc($result)) {
    $userlist[] = $row;
}

//Handle deletion
$delete_id = (isset($_GET['delete'])) ? intval($_GET['delete']) : '';

if($delete_id > 0)
{
	$sql = "DELETE 
		FROM uren 
		WHERE entry_id = '" . mysql_real_escape_string($delete_id) . "'";
	$result = mysql_query($sql);
}

//Handle sorting
$sort_week = (isset($_GET['week_sort'])) ? intval($_GET['week_sort']) : '';
$sort_year = (isset($_GET['year_sort'])) ? intval($_GET['year_sort']) : '';
if(!empty($sort_year) && !empty($sort_year))
{
	$begin_limit = getFirstDayOfWeek($sort_year, $sort_week);
	$end_limit = getLastDayOfWeek($sort_year, $sort_week);
}

//Handle submission
$selecteduser = (isset($_POST['user_id'])) ? $_POST['user_id'] : '';
$task = (isset($_POST['task'])) ? $_POST['task'] : '';
$selecteddate = (isset($_POST['datefield'])) ? $_POST['datefield'] : '';
$aantaluren = (isset($_POST['aantaluren'])) ? intval($_POST['aantaluren']) : '';

if(isset($_POST['submit']))	
{	
	if(empty($selecteduser) || empty($selecteddate) || empty($aantaluren) || empty($task))
	{
		$message = 'Fields empty';
		$error = true;
	}
	
	if($error == false)
	{
		$error == true;
		foreach($userlist as $user)
		{
			if($user['user_id'] == $selecteduser)
			{
				//Found user in array
				$error = false;
				continue;
			}
		}
	}
	
	$selecteddate = explode('/', $selecteddate);
	$selecteddate = mktime(0, 0, 0, intval($selecteddate[0]), intval($selecteddate[1]), intval($selecteddate[2]));
	
	
	if($error == false)
	{
		$sql = "INSERT INTO uren (datum, aantal_uren, user, taak) VALUES ('" . mysql_real_escape_string($selecteddate) . "', '" . mysql_real_escape_string($aantaluren) . "', '"  . mysql_real_escape_string($selecteduser) . "', '" . mysql_real_escape_string($task) . "')";
		$result = mysql_query($sql);
		if (!$result) {
			die('Could not get result: ' . mysql_error());
		}
	
		$message = '1';
	}
}

//Generate user list
foreach($userlist as $userdata) {
	$template->assign_block_vars('user_loop', array(
		"USER_ID"	=> $userdata['user_id'],
		"USER_NAME"	=> $userdata['user_name'],
	));
}

//Get hourlist
$sql = "SELECT * 
	FROM uren
	WHERE datum >= '" . mysql_real_escape_string($begin_limit) . "'
		AND datum <= '" . mysql_real_escape_string($end_limit) . "'
	ORDER BY datum ASC";
$result = mysql_query($sql);
if (!$result) {
    die('Could not get result: ' . mysql_error());
}
while ($row = mysql_fetch_assoc($result)) {
    $hourlist[] = $row;
}
//Generate entry list for each user
for ($i=0, $size=sizeof($userlist); $i < $size; $i++)
{	
	$template->assign_block_vars('entry_list_loop', array(
		"USER_NAME"		=> $userlist[$i]['user_name'],
	));

    $userlist[$i]['uren'] = 0;
	for($j=0, $sizeuren = sizeof($hourlist); $j < $sizeuren; $j++)
	{
		if($hourlist[$j]['user'] == $userlist[$i]['user_id'])
		{
			$template->assign_block_vars('entry_list_loop.single_entry', array(
				"HOURS"		=> $hourlist[$j]['aantal_uren'],
				"DATE"		=> date('d-m-y', $hourlist[$j]['datum']),
				"TASK"		=> htmlspecialchars($hourlist[$j]['taak']),
				"ENTRY_ID"	=> $hourlist[$j]['entry_id'],
			));
						
			$userlist[$i]['urenentries'] = $hourlist[$j];
			$userlist[$i]['uren'] += $hourlist[$j]['aantal_uren'];
		}
	}
	
	$template->assign_block_vars('entry_list_loop.totals', array(
		"TOTAL_HOURS"	=> $userlist[$i]['uren'],
	));

	$totaal_listed = false;
}

//Stats & graphs
$topper = false;
$slacker = false;
$average = 0;
$total = 0;
foreach($userlist as $user)
{
	if($user['uren'] > $topper['uren'])
	{
		$topper = $user;
	}
	if($slacker == false)
	{
		$slacker = $user;
	}
	if(($user['uren'] < $slacker['uren']) && ($slacker != false))
	{
		$slacker = $user;
	}
	$total += $user['uren'];
}
$average = $total / sizeof($userlist);

foreach($userlist as $user)
{
	//Graphs
	$template->assign_block_vars('graphs', array(
		"USER_NAME"		=> $user['user_name'],
		"PERCENTAGE"	=> percent($user['uren'], $topper['uren']),
		"COLOR"			=> random_color(),
	));
}

//Sorting features

//Generate years sorting list
for($i=0; $i < 50; $i++)
{
	$selected = ((2010 + $i) == intval(date('Y'))) ? 1 : 0;
	if(!empty($sort_year))
	{
		$selected = ($i+2010 == $sort_year) ? 1 : 0;
	}

	$template->assign_block_vars('years_sorting', array(
		"YEAR"		=> 2010 + $i,
		"SELECTED"	=> $selected,
	));
}

//Generate week sorting list
for($i=0; $i < 52; $i++)
{
	$selected = ($i+1 == intval(date('W'))) ? 1 : 0;
	if(!empty($sort_week))
	{
		$selected = ($i+1 == $sort_week) ? 1 : 0;
	}

	$template->assign_block_vars('week_sorting', array(
		"WEEK"		=> $i+1,
		"SELECTED"	=> $selected,
	));
}


$template->assign_vars(array(
	"CURRENT_DATE"	=> date("m/d/y",time()),
	"MESSAGE"		=> $message,
	"TOP_USER_NAME"		=> $topper['user_name'],
	"TOP_USER_HOURS"	=> $topper['uren'],
	"SLACKER_NAME"		=> $slacker['user_name'],
	"SLACKER_HOURS"		=> ($slacker['uren'] != 0)? $slacker['uren'] : 0,
	"AVERAGE"		=> round($average),
	
));

//OUTPUT FUCKING EVERYTHING
$template->display('body');


//Useful functions
function percent($num_amount, $num_total) {
	$count1 = $num_amount / $num_total;
	$count2 = $count1 * 100;
	return round($count2);
}

function getLastDayOfWeek($year, $weeknr){
 
    $offset = date('w', mktime(0,0,0,1,1,$year));
    $offset = ($offset < 5) ? 1-$offset : 8-$offset;
    $sunday = mktime(0,0,0,1,7+$offset,$year);
    return strtotime('+' . ($weeknr - 1) . ' weeks', $sunday);
}

function getFirstDayOfWeek($year, $weeknr)
{
	$offset = date('w', mktime(0,0,0,1,1,$year));
	$offset = ($offset < 5) ? 1-$offset : 8-$offset;
	$monday = mktime(0,0,0,1,1+$offset,$year);

	return strtotime('+' . ($weeknr - 1) . ' weeks', $monday);
}

function random_color(){
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}