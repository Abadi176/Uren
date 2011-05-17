<?php
$message = '';
//MySQL connect
$mysql_connection = mysql_connect('localhost', 'root', 'root');
if (!$mysql_connection) {
    die('Could not connect: ' . mysql_error());
}
$db_selected = mysql_select_db('uren', $mysql_connection);
if (!$db_selected) {
    die ('Can\'t use uren : ' . mysql_error());
}

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


//Handle submission
$selecteduser = (isset($_POST['user_id'])) ? $_POST['user_id'] : '';
$task = (isset($_POST['task'])) ? $_POST['task'] : '';
$selecteddate = (isset($_POST['datefield'])) ? $_POST['datefield'] : '';
$aantaluren = (isset($_POST['aantaluren'])) ? $_POST['aantaluren'] : '';
	
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
	
		$message = 'logged!';
	}
}

//Get hourlist
$sql = "SELECT * 
	FROM uren
	ORDER BY datum ASC";
$result = mysql_query($sql);
if (!$result) {
    die('Could not get result: ' . mysql_error());
}
while ($row = mysql_fetch_assoc($result)) {
    $hourlist[] = $row;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Sexy Uren Registratie</title>
<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.12.custom.css" rel="stylesheet" />
<link type="text/css" href="css/styles.css" rel="stylesheet" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.12.custom.min.js"></script>
		
<script type="text/javascript">
	$(function() {
		$( "#datepicker" ).datepicker();
	});
</script>
</head>
<body>
    <div id="header">
      <a href="index.php" id="logo">Sexy <strong>Uren Registratie</strong> </a>
    </div>
    <div id="content">
		<h3>Toevoegen</h3>
		<form action="./index.php" method="post">
			<div>User :
			<select name="user_id">
				<option value="0">Selecteer...</option>
				<?php
				foreach($userlist as $userdata) {
				?>
						<option value="<?=$userdata['user_id']?>"><?=$userdata['user_name']?></option>
				<?php
				}
				?>	
			</select>

			Datum :
			<input type="text" id="datepicker" name="datefield" value="<?=date("m/d/y",time())?>"/>
			
			Aantal uren:
			<input type="text" name="aantaluren" value="0"/>
			Taak:
			<input type="text" name="task" value="fappen"/>
			
			<input type="submit" name="submit" value="Submit"/>
      		</div>
		</form>
	<p><?=$message?></p>
	<hr />
<?php
//Link the userlist and entry list
for ($i=0, $size=sizeof($userlist); $i < $size; $i++)
{
?>
    <h3><?=htmlspecialchars($userlist[$i]['user_name'])?></h3>
	<table>
		<thead>
		<tr>
			<td class="aantalurentabel">Aantal uren</td>
			<td class="datum">Datum</td>
			<td class="taak">Taak</td>
		</tr>
		</thead>
<?
	for($j=0, $sizeuren = sizeof($hourlist); $j < $sizeuren; $j++)
	{
		
		if($hourlist[$j]['user'] == $userlist[$i]['user_id'])
		{
			$userlist[$i]['urenentries'] = $hourlist[$j];
			$userlist[$i]['uren'] += $hourlist[$j]['aantal_uren'];
?>
		<tr>
			<td><?=$hourlist[$j]['aantal_uren'] ?></td>
			<td><?=date('d-m-y', $hourlist[$j]['datum'])?></td>
			<td><?=htmlspecialchars($hourlist[$j]['taak'])?></td>
		</tr>	
<?			
			$totaal_listed = true;
		}
	}
?>
		<tr style="background-color: lightgrey;">
			<td>Totaal: <?=($userlist[$i]['uren'] > 0) ? $userlist[$i]['uren']:0 ?></td>
			<td class="disabled"></td>
			<td class="disabled"></td>
		</tr>	
	</table>
	<br />
<?
	$totaal_listed = false;
}

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
?>
      <hr />
      <h3>Stats</h3>
      <p><strong>Top teamlid:</strong> <?=$topper['user_name'] ?> met <?=$topper['uren'] ?> uur</p>
      <p><strong>Grootste slacker:</strong> <?=$slacker['user_name'] ?> met <?=($slacker['uren'] != 0)? $slacker['uren'] : 0 ?> uur</p>
      <p><strong>Gemiddeld aantal uren:</strong> <?=$average ?></p>
	</div>
	
	<p id="copy"><strong>Sexy Uren Registratie</strong> by Hidde "<a href="http://www.hiddejansen.com"><strong>Ganonmaster</strong></a>" Jansen &copy; All Rights Reserved</p>
</body>
</html>
<?php
mysql_close($mysql_connection);