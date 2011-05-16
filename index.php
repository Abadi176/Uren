<?php
$message = '';
//MySQL connect
$mysql_connection = mysql_connect('localhost', 'root', '');
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
	ORDER BY user ASC";
$result = mysql_query($sql);
if (!$result) {
    die('Could not get result: ' . mysql_error());
}
while ($row = mysql_fetch_assoc($result)) {
    $hourlist[] = $row;
}

?>
<html>
<head>
<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.12.custom.css" rel="stylesheet" />	
<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.12.custom.min.js"></script>
		
<script>
	$(function() {
		$( "#datepicker" ).datepicker();
	});
</script>
</head>
<body>
	<div>
		<strong>Toevoegen</strong><br />
		<form action="./index.php" method="POST">
			User :
			<select name="user_id">
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
		</form>
	<p><?=$message?></p>
	</div>
	<hr />
	<table border="1" width="100%">
		<tr>
			<td>Naam</td>
			<td>Aantal uren</td>
			<td>Datum</td>
			<td width="50%">Taak</td>
			<td>Totaal</td>
		</tr>
<?php
//Link the userlist and entry list
for ($i=0, $size=sizeof($userlist); $i < $size; $i++)
{
	for($j=0, $sizeuren = sizeof($hourlist); $j < $sizeuren; $j++)
	{
		
		if($hourlist[$j]['user'] == $userlist[$i]['user_id'])
		{
			$userlist[$i]['urenentries'] = $hourlist[$j];
			$userlist[$i]['uren'] += $hourlist[$j]['aantal_uren'];
?>
		<tr>
			<td><?=($totaal_listed == false) ? $userlist[$i]['user_name'] : '';?></td>
			<td><?=$hourlist[$j]['aantal_uren'] ?></td>
			<td><?=date('d-m-y', $hourlist[$j]['datum'])?></td>
			<td><?=$hourlist[$j]['taak']?></td>
			<td></td>
		</tr>	
<?			
			$totaal_listed = true;
		}
	}
?>
		<tr style="background-color: lightgrey;">
			<td></td>
			<td></td>
			<td></td>
			<td style="text-align: right;"><?=$userlist[$i]['user_name'] ?></td>
			<td><?=$userlist[$i]['uren'] ?></td>
		</tr>	
<?
	$totaal_listed = false;
}
?>
	</table>
	
	
</body>
</html>
<?php
mysql_close($mysql_connection);