<?
/**
This page will create the FFN object and then show you how to call the different methods 
to return the information that you want. The first thing you need to do is register for an 
API Key at FantasyFootballNerd.com.  When you register for one, enter into the $apiKey 
variable below.  You won't be able to get data without an API key.
**/

$apiKey = "2010090440152326";	//-- Replace 0000 with your API key 
require_once("FFN.class.php");
$ffn = new FFN($apiKey);

if (!$apiKey){exit("You didn't set the apiKey variable");}
?>

<html>
<head>
<title>FantasyFootballNerd.com API Test for PHP</title>
</head>
<body>

<h2>FantasyFootballNerd.com API Test</h2>
<ul>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=schedule">Get Season Schedule</a></li>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=players">Get All NFL Players</a></li>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=playerDetails">Get Player Details</a></li>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=draftRankings">Get Draft Rankings</a></li>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=injuries">Get Injuries</a></li>
<li><a href="<?= $_SERVER['PHP_SELF']; ?>?display=weeklyRankings">Get Weekly Rankings</a></li>
</ul>

<div style="height:10px;border-top:1px solid #3366CC;"></div>




<? /**************************************************/ ?>
<? if ($_GET['display'] == "schedule") {

//-- Get this year's schedule --
$schedule = $ffn->getSchedule();

//-- Loop through the schedule --
?>
	<h4>Season Schedule</h4>
	<p>Season: <?= $schedule->Season; ?></p>
	<? foreach($schedule->Games AS $game) { ?>
	<p>Week: <?= $game->Week; ?> <?= $game->AwayTeam; ?> at <?= $game->HomeTeam; ?> on <?= date("M j, Y", strtotime($game->GameDate)); ?> at <?= $game->GameTime; ?> <?= $schedule->Timezone; ?></p>
	<? } ?>

<? } ?>





<? /**************************************************/ ?>
<? if ($_GET['display'] == "players") {

//-- Get the players --
$players = $ffn->getPlayers();

//-- Loop through the players --
?>
	<h4>All NFL Players</h4>
	<? foreach($players->Players AS $player) { ?>
	<p><?= $player->Name; ?> <?= $player->Position; ?> : <?= $player->Team; ?> (FFN PlayerID: <?= $player->playerId; ?>)</p>
	<? } ?>

<? } ?>





<? /**************************************************/ ?>
<? if ($_GET['display'] == "playerDetails" && !$_GET['playerId']) { ?>
<form action="<?= $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="display" value="playerDetails" />
<p>FFN playerId: <input type="text" name="playerId" size="4" /></p>
<p><input type="submit" /></p>
</form>
<? } ?>

<? if ($_GET['display'] == "playerDetails" && $_GET['playerId']) { 
$playerDetails = $ffn->getPlayerDetails($_GET['playerId']);
?>
	<h4>Player Details</h4>
	<p>First Name: <?= $playerDetails->FirstName; ?></p>
	<p>Last Name: <?= $playerDetails->LastName; ?></p>
	<p>Team: <?= $playerDetails->Team; ?></p>
	<p>Position: <?= $playerDetails->Position; ?></p>
	<p><strong>Articles</strong></p>
	<? foreach($playerDetails->News AS $story) { ?>
	<p><a href="<?= $story->Source; ?>"><?= $story->Title; ?></a> Published: <?= $story->Published; ?></p>
	<? } ?>
<? } ?>









<? /**************************************************/ ?>
<? if ($_GET['display'] == "draftRankings" && !$_GET['position']) { ?>
<form action="<?= $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="display" value="draftRankings" />
<p>Position: <select name="position" size="1"><option value="ALL" selected>ALL</option><option value="QB">QB</option><option value="RB">RB</option><option value="WR">WR</option><option value="TE">TE</option><option value="DEF">DEF</option><option value="K">K</option></select></p>
<p># of Results: <input type="text" name="limit" size="4" value="10" /> (1 - 1000)</p>
<p>Include Strength of Schedule? <select name="sos" size="1"><option value="1" selected>Yes</option><option value="0">No</option></select></p>
<p><input type="submit" /></p>
</form>
<? } ?>

<? if ($_GET['display'] == "draftRankings" && $_GET['position']) { 
$draft = $ffn->getDraftRankings($_GET['position'], $_GET['limit'], $_GET['sos']);
?>
	<h4>Preseason Draft Rankings</h4>
	<? foreach($draft->Players AS $player) { ?>
	<p><?= $player->PlayerName; ?> (FFN playerId: <?= $player->playerId; ?>) <?= $player->Team; ?> <?= $player->Position; ?> Overall Rank: <?= $player->OverallRank; ?> Rank among <?= $player->Position; ?>'s: <?= $player->PositionRank; ?> Bye Week: <?= $player->ByeWeek; ?></p>
		<? if (count($player->StrengthOfSchedule) > 0) { ?>
			<p>
			<? foreach ($player->StrengthOfSchedule AS $sched) { ?>
			Week <?= $sched->WeekNumber; ?> vs <?= $sched->Opponent; ?> : <?= $sched->DefensiveRank; ?> (the lower the number, the tougher the defense)<br />
			<? } ?>
			</p>
		<? } ?>
	<? } ?>
<? } ?>








<? /**************************************************/ ?>
<? if ($_GET['display'] == "injuries") {

//-- Get the injuries for a specific week (replace "1" with a week number) --
$injuries = $ffn->getInjuries("1");
?>
	<h4>Injuries</h4>

	<? foreach($injuries->Injuries AS $inj) { ?>
	<p>Week: <?= $inj->Week; ?> <?= $inj->Player; ?> (playerId: <?= $inj->playerId; ?>) <?= $inj->Team; ?> <?= $inj->Position; ?> Injury: <?= $inj->Injury; ?> Practice Status: <?= $inj->PracticeStatus; ?> Game Status: <?= $inj->GameStatus; ?> Updated on <?= date("M j, Y", strtotime($inj->Updated)); ?></p>
	<? } ?>

<? } ?>







<? /**************************************************/ ?>
<? if ($_GET['display'] == "weeklyRankings" && !$_GET['position'] && !$_GET['week']) { ?>
<form action="<?= $_SERVER['PHP_SELF']; ?>" method="get">
<input type="hidden" name="display" value="weeklyRankings" />
<p>Position: <select name="position" size="1"><option value="QB" selected>QB</option><option value="RB">RB</option><option value="WR">WR</option><option value="TE">TE</option><option value="DEF">DEF</option><option value="K">K</option></select></p>
<p>Week: <select name="week" size="1"><? for ($i = 1; $i < 18; $i++){echo "<option value='$i'>$i</option>";} ?></select></p>
<p><input type="submit" /></p>
</form>
<? } ?>

<? if ($_GET['display'] == "weeklyRankings" && $_GET['position'] && $_GET['week']) { 
$sitStart = $ffn->getWeeklyRankings($_GET['position'], $_GET['week']);
?>
	<h4>Weekly Sit/Start Rankings</h4>
	<? foreach($sitStart->Players AS $player) { ?>
	<p><?= $player->PlayerName; ?> (FFN playerId: <?= $player->playerId; ?>) <?= $player->Team; ?> <?= $player->Position; ?> Rank: <?= $player->Rank; ?> Standard Scoring: Low <?= $player->StandardLow; ?> Nerd Proj <?= $player->StandardPoints; ?> High <?= $player->StandardHigh; ?> PPR: Low <?= $player->PPRLow; ?> Nerd Proj <?= $player->PPR; ?> High <?= $player->PPRHigh; ?></p>
	<? } ?>
<? } ?>








<? if (!$_GET['display']) { ?>

	<p>Here's the basic way to communicate with the FFN API. Don't forget to store the data locally in your own database to be kind to the FFN server.</p>
	<p>First instantiate the FFN object</p>
	<code>
	$apiKey = "0000";	//-- Replace the 0000 with your API key<br />
	require_once("FFN.class.php");<br />
	$ffn = new FFN($apiKey);
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

<? } ?>





<? if ($ffn->errorMsg) {echo "<p style='font-weight:bold;color:red;'>" . $ffn->errorMsg . "</p>";} ?>



</body>
</html>