<?php
/**
This page will create the FFN object and then show you how to call the different methods 
to return the information that you want. The first thing you need to do is register for an 
API Key at FantasyFootballNerd.com.  When you register for one, enter into the API_KEY 
variable below.  You won't be able to get data without an API key.
**/

define('API_KEY', '0000'); // //-- Replace 0000 with your API key 
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
	echo '<p>Season: ', $schedule->Season, '</p>';
	
	foreach($schedule->Games AS $game) {
		echo '<p>';
		echo 'Week: ', $game->Week, ' ', $game->AwayTeam, ' at ', $game->HomeTeam, ' on ', date("M j, Y", strtotime($game->GameDate)), ' at ', $game->GameTime, ' ', $schedule->Timezone;
		echo '</p>';
	}
}

/**** Player Listing **********************************************/ 
if ($display == "players") {

	$players = $ffn->getPlayers();
	
	echo '<h4>All NFL Players</h4>';
	foreach($players->Players AS $player) {
		echo '<p>';
		echo playerLink($player->playerId, $player->Name), ' : ', $player->Position, ' : ', $player->Team;
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
		
		echo '<h4>Player Details</h4>';
		echo '<p>First Name: ', $playerDetails->FirstName, '</p>';
		echo '<p>Last Name: ',  $playerDetails->LastName,  '</p>';
		echo '<p>Team: ', $playerDetails->Team, '</p>';
		echo '<p>Position: ', $playerDetails->Position, '</p>';
		echo '<p><strong>Articles</strong></p>';
		
		foreach($playerDetails->News AS $story) {
			echo '<p><a href="', $story->Source, '">', $story->Title, '</a> Published: ', $story->Published, '</p>';
		}
	}
}

/**** Draft Rankings **********************************************/
if ($display == "draftRankings") {
	
	if (empty($_GET['position'])) {
	?>
		<form action="<?php echo PHP_SELF; ?>" method="get">
		<input type="hidden" name="display" value="draftRankings" />
		<p>Position: 
			<select name="position" size="1">
				<option value="ALL" selected>ALL</option>
				<option value="QB">QB</option>
				<option value="RB">RB</option>
				<option value="WR">WR</option>
				<option value="TE">TE</option>
				<option value="DEF">DEF</option>
				<option value="K">K</option>
			</select>
		</p>
		<p># of Results: <input type="text" name="limit" size="4" value="10" /> (1 - 1000)</p>
		<p>Include Strength of Schedule? 
			<select name="sos" size="1">
				<option value="1" selected>Yes</option>
				<option value="0">No</option>
			</select>
		</p>
		<p><input type="submit" name="submit" value="submit"/></p>
		</form>
	<?php
	} else {

		$draft = $ffn->getDraftRankings($_GET['position'], $_GET['limit'], $_GET['sos']);
		
		echo '<h4>Preseason Draft Rankings</h4>';
		echo '<p>The last number is the defensive rank, where a lower number means a tougher defense.</p>';
		foreach($draft->Players AS $player) {
			echo '<p>';
			echo playerLink($player->playerId, $player->PlayerName), ' ', $player->Team, ' ', $player->Position;
			echo ' Overall Rank: ', $player->OverallRank, ' Rank among ', $player->Position, "'s: ", $player->PositionRank, ' Bye Week: ', $player->ByeWeek;
			echo '</p>';
			
			if (count($player->StrengthOfSchedule) > 0) {
				echo '<p>';
				foreach ($player->StrengthOfSchedule AS $sched) {
					echo 'Week ', $sched->WeekNumber, ' vs ', $sched->Opponent, ' : ', $sched->DefensiveRank, '<br />';
				}
			}
			echo '</p>';
		}
	}
}

/**** Player Injuries **********************************************/
if ($display == "injuries") {

	//-- Get the injuries for a specific week (replace "1" with a week number) --
	// @todo handle week number
	$injuries = $ffn->getInjuries("1");
	
	echo '<h4>Injuries</h4>';

	foreach($injuries->Injuries AS $inj) {
		echo '<p>';
		echo 'Week: ', $inj->Week, ' ', $inj->Player, ' ', $inj->Team, ' ', $inj->Position;
		echo ' Injury: ', $inj->Injury, ' Practice Status: ', $inj->PracticeStatus, ' Game Status: ', $inj->GameStatus, ' Updated on ', date("M j, Y", strtotime($inj->Updated));
		echo '</p>';
	}
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
			<p><input type="submit" /></p>
		</form>
	<?php 
	} else {

		$sitStart = $ffn->getWeeklyRankings($_GET['position'], $_GET['week']);
		
		echo '<h4>Weekly Sit/Start Rankings</h4>';
		foreach($sitStart->Players AS $player) {
			echo '<p>';
			echo playerLink($player->playerId, $player->PlayerName), ' ', $player->Team, ' ', $player->Position, ' Rank: ', $player->Rank;
			echo ' Standard Scoring: Low ', $player->StandardLow, ' Nerd Proj ', $player->StandardPoints, ' High ', $player->StandardHigh;
			echo ' PPR: Low ', $player->PPRLow, ' Nerd Proj ', $player->PPR, ' High ', $player->PPRHigh;
			echo '</p>';
		}
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
