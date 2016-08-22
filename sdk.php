<?php
/**
This page will create the FFN object and then show you how to call the different methods 
to return the information that you want. The first thing you need to do is register for an 
API Key at FantasyFootballNerd.com.  When you register for one, enter into the API_KEY 
constant below.  You won't be able to get data without an API key. This is the only
edit you are required to make, here.
**/

// Just to be sure. This error is annoying.
date_default_timezone_set('UTC');

define('API_KEY', ''); // //-- Insert your API key
define('PHP_SELF', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));

require_once("FFN.class.php");

$ffn = new FFN(API_KEY);

if (!API_KEY) {
	echo 'You did not set the API_KEY for your application. This is required.';
	exit;
}

$display = isset($_GET['display']) ? $_GET['display'] : FALSE;
?>

<html>
<head>
<title>FantasyFootballNerd.com API Test for PHP</title>
</head>
<body>

<h2>FantasyFootballNerd.com API Test</h2>
<ul>
<li><a href="<?php echo PHP_SELF; ?>?display=schedule">Get Season Schedule</a></li>
<li><a href="<?php echo PHP_SELF; ?>?display=players">Get All NFL Players</a></li>
<li><a href="<?php echo PHP_SELF; ?>?display=playerDetails">Get Player Details</a></li>
<li><a href="<?php echo PHP_SELF; ?>?display=draftRankings">Get Draft Rankings</a></li>
<li><a href="<?php echo PHP_SELF; ?>?display=injuries">Get Injuries</a></li>
<li><a href="<?php echo PHP_SELF; ?>?display=weeklyRankings">Get Weekly Rankings</a></li>
</ul>

<div style="height:10px;border-top:1px solid #3366CC;"></div>
<?php
/**** League Schedule **********************************************/
if ($display == "schedule") {

	$schedule = $ffn->getSchedule();

	echo '<h4>Season Schedule</h4>';
	echo '<p>Current Week: ', $schedule['currentWeek'], '</p>';
	
	foreach($schedule['Schedule'] AS $game) {
		echo '<p>';
		echo 'Week: ', $game['gameWeek'], ' ', $game['awayTeam'], ' at ', $game['homeTeam'], ' on ', $game['gameDate'], ' at ', $game['gameTimeET'], ' ET', ' playing on ', $game['tvStation'];
		echo '</p>';
	}
}

/**** Player Listing **********************************************/ 
if ($display == "players") {
	$players = $ffn->getPlayers();
	echo '<h4>All NFL Players</h4>';
	foreach($players AS $player) {
		echo '<p>';
		echo playerLink($player['playerId'], $player['displayName']), ' : ', $player['position'], ' : ', $player['team'];
		echo '</p>';
	}
}

/**** Player Details **********************************************/
if ($display == "playerDetails") {
	
	if (empty($_GET['playerId'])) {
	?>
		<form action="<?php echo PHP_SELF; ?>" method="get">
		<input type="hidden" name="display" value="playerDetails" />
		<p>FFN playerId: <input type="text" name="playerId" size="4" /></p>
		<p><input type="submit" /></p>
		</form>
	<?php 
	} else {
	
		$playerDetails = $ffn->getPlayerDetails($_GET['playerId']);
		if ($playerDetails['Error']) {
			echo $playerDetails['Error'];
			exit;
		}
		// @todo Implement this properly
		print "<pre>";
		print_r($playerDetails);
		print "</pre>";
	}
}

/**** Draft Rankings **********************************************/
if ($display == "draftRankings") {
	$ppr = empty($_GET['ppr']) ? 0 : 1;
	$rankings = $ffn->getDraftRankings($ppr);
	
	echo '<h3>Rankings. Is PPR enabled? ' . (empty($rankings['PPR']) ? 'No' : 'Yes') . '</h3>';
	echo "<pre>";
	foreach ($rankings['DraftRankings'] as $rank) {
		print_r($rank);
	}
	echo "</pre>";
}

/**** Player Injuries **********************************************/
if ($display == "injuries") {
	$week = empty($_GET['week']) ? 1 : (int) $_GET['week'];

	$injuries = $ffn->getInjuries($week);
	
	echo '<h4>Injuries</h4>';
	if ($injuries['Error']) {
		echo $injuries['Error'];
		exit;
	}

	// @todo Implement this properly
	echo "<pre>";
	print_r($injuries);
	echo "</pre>";
}

/**** Weekly Rankings **********************************************/
if ($display == "weeklyRankings") {
	
	if (empty($_GET['position']) && empty($_GET['week'])) {
	?>
		<form action="<?php echo PHP_SELF; ?>" method="get">
			<input type="hidden" name="display" value="weeklyRankings" />
			<p>Position: 
			<select name="position" size="1">
				<option value="QB" selected>QB</option>
				<option value="RB">RB</option>
				<option value="WR">WR</option>
				<option value="TE">TE</option>
				<option value="DEF">DEF</option>
				<option value="K">K</option>
			</select>
			</p>
			<p>Week: 
			<select name="week" size="1">
				<?php 
				for ($i = 1; $i < 18; $i++) {
					echo "<option value='$i'>$i</option>";
				} 
				?>
			</select>
			</p>
			<p>PPR: 
			<select name="ppr" size="1">
				<option value="1" selected>Yes</option>
				<option value="0">No</option>
			</select>
			</p>
			<p><input type="submit" /></p>
		</form>
	<?php 
	} else {

		$sitStart = $ffn->getWeeklyRankings($_GET['position'], $_GET['week'], $_GET['ppr']);
		
		print_r($sitStart);
		exit;
	}
}

if (!$display) {
?>

	<p>Here's the basic way to communicate with the FFN API. Don't forget to store the data locally in your own database to be kind to the FFN server.</p>
	<p>First instantiate the FFN object</p>
	<code>
	define('API_KEY', '0000');	//-- Replace the 0000 with your API key<br />
	require_once("FFN.class.php");<br />
	$ffn = new FFN(API_KEY);
	</code>
	<p>The following are the various methods that you can call with the ffn object.  Each will return an array of objects for that particular service.</p>
	<ul>
	<li>getSchedule</li>
	<li>getPlayers</li>
	<li>getPlayerDetails</li>
	<li>getDraftRankings</li>
	<li>getInjuries</li>
	<li>getWeeklyRankings</li>
	</ul>
	<p>View the samples on this page to retrieve data for each of the various services above.</p>
	<p>This is a work in progress, so please email <a href="mailto:nerd@fantasyfootballnerd.com">nerd@fantasyfootballnerd.com</a> with questions, bug reports, etc.</p>
	<p>Note: this is an incomplete list of features. For a complete list of API calls available to you, see <a href="http://www.fantasyfootballnerd.com/fantasy-football-api">http://www.fantasyfootballnerd.com/fantasy-football-api</a></p>

<?php 
}

if ($ffn->errorMsg) {
	echo "<p style='font-weight:bold;color:red;'>" . $ffn->errorMsg . "</p>";
}

function playerLink($player_id, $player_name) {
	echo ' <a href="', PHP_SELF, '?display=playerDetails&playerId=', $player_id, '">', $player_name, '</a> ';
}
?>

</body>
</html>
