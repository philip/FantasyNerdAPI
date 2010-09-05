<?php
/**
 * A simple PHP5 class to retrieve data from FantasyFootballNerd.com (FFN)
 * Class is available free of charge, however you will need an API Key to return results.
 * The class is intended to retrieve data from FFN, but please store the data locally to remain
 * bandwidth-friendly.
 *
 * @author J. Joseph Dyken <nerd@fantasyfootballnerd.com>
 * @copyright Copyright (c) 2010, TayTech, LLC
 * @version 1.1 2010-08-21
**/
class FFN {

	/**
	 * Your API Key from FantasyFootballNerd.com
	 * @var int
	**/
	private $apiKey;

	/**
	 * Error Message - a holder for any error that we may encounter
	 * @var string
	**/
	public $errorMsg;

	/**
	 * Constructor
	 *
	 * @param int $apiKey Your API Key given to you by registering at FantasyFootballNerd.com
	**/
	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}//-----

	/**
	 * Get the season schedule
	 * This will return a stdClass object with the season schedule
	 *
	 * @return object
	**/
	public function getSchedule() {

		$endpoint = "http://api.fantasyfootballnerd.com/ffnScheduleXML.php?apiKey=" . $this->apiKey;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);
		$obj->Season   = $doc->getElementsByTagName("Schedule")->item(0)->getAttribute("Season");
		$obj->Timezone = $doc->getElementsByTagName("Schedule")->item(0)->getAttribute("Timezone");
		$obj->Games    = array();

		foreach ($doc->getElementsByTagName("Game") AS $game) {
			$obj->Games[] = (object) array(
				"Week"		=> $game->getAttribute("Week"),
				"GameDate"	=> $game->getAttribute("GameDate"),
				"HomeTeam"	=> $game->getAttribute("HomeTeam"),
				"AwayTeam"	=> $game->getAttribute("AwayTeam"),
				"GameTime"	=> $game->getAttribute("GameTime"),
				);
		}
		return $obj;
	}//-----

	/**
	 * Get the list of players from FFN
	 * This will return a stdClass object with all the NFL players. This does not need to be called
	 * more than once per week as it doesn't change with much frequency.
	 *
	 * @return object
	**/
	public function getPlayers() {

		$endpoint = "http://api.fantasyfootballnerd.com/ffnPlayersXML.php?apiKey=" . $this->apiKey;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);

		if ($doc->getElementsByTagName("Error")->length > 0) {
			$this->errorMsg = $doc->getElementsByTagName("Error")->item(0)->nodeValue;
		}

		$obj->Players = array();
		foreach ($doc->getElementsByTagName("Player") AS $player) {
			$obj->Players[] = (object) array(
				"playerId"	=> $player->getAttribute("playerId"),
				"Name"		=> $player->getAttribute("Name"),
				"Position"	=> $player->getAttribute("Position"),
				"Team"		=> $player->getAttribute("Team")
			);
		}
		return $obj;
	}//-----

	/**
	 * Get player details
	 * This will return a stdClass object with the details for the player requested.
	 *
	 * @param int $playerId The FFN playerId to retrieve
	 * @return object
	**/
	public function getPlayerDetails($playerId) {

		$endpoint = "http://api.fantasyfootballnerd.com/ffnPlayerDetailsXML.php?apiKey=" . $this->apiKey . "&playerId=" . $playerId;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);

		if ($doc->getElementsByTagName("Error")->length > 0) {
			$this->errorMsg = $doc->getElementsByTagName("Error")->item(0)->nodeValue;
		}

		$obj->FirstName	= $doc->getElementsByTagName("FirstName")->item(0)->nodeValue;
		$obj->LastName	= $doc->getElementsByTagName("LastName")->item(0)->nodeValue;
		$obj->Team		= $doc->getElementsByTagName("Team")->item(0)->nodeValue;
		$obj->Position	= $doc->getElementsByTagName("Position")->item(0)->nodeValue;
		$obj->News		= array();

		foreach ($doc->getElementsByTagName("Article") AS $article) {
			$obj->News[] = (object) array(
				"Title"		=> $article->getElementsByTagName("Title")->item(0)->nodeValue,
				"Source"	=> $article->getElementsByTagName("Source")->item(0)->nodeValue,
				"Published"	=> $article->getElementsByTagName("Published")->item(0)->nodeValue,
			);
		}
		return $obj;
	}//-----

	/**
	 * Get Draft Rankings
	 * This will return a stdClass object with the current preseason draft rankings
	 *
	 * @param string $position The position to retrieve. Options: ALL, QB, RB, WR, TE, DEF, K
	 * @param int $limit How many results to return. Pass an integer between 1 and 1000
	 * @param int $sos Return the Strength of Schedule for every player? Pass a 1 for yes, 0 for no
	 * @return object
	**/
	public function getDraftRankings($position, $limit, $sos) {

		$endpoint = "http://api.fantasyfootballnerd.com/ffnRankingsXML.php?apiKey=" . $this->apiKey . "&position=" . $position . "&limit=" . $limit . "&sos=" . $sos;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);

		if ($doc->getElementsByTagName("Error")->length > 0) {
			$this->errorMsg = $doc->getElementsByTagName("Error")->item(0)->nodeValue;
		}

		$obj->Players = array();
		foreach ($doc->getElementsByTagName("Player") AS $player) {
			$p = array(
				"playerId"		=> $player->getAttribute("playerId"),
				"PlayerName"	=> $player->getAttribute("Name"),
				"Position"		=> $player->getAttribute("Position"),
				"Team"			=> $player->getAttribute("Team"),
				"OverallRank"	=> $player->getAttribute("OverallRank"),
				"PositionRank"	=> $player->getAttribute("PositionRank"),
				"ByeWeek"		=> $player->getAttribute("ByeWeek"),
			);

			if ($player->getElementsByTagName("Week")->length > 0) {
				$weeks = array();
				foreach ($player->getElementsByTagName("Week") AS $week) {
					$weeks[] = (object) array(
						"WeekNumber"	=> $week->getAttribute("Number"),
						"Opponent"		=> $week->getAttribute("Opponent"),
						"DefensiveRank"	=> $week->getAttribute("DefensiveRank"),
					);
				}
			}
			$p['StrengthOfSchedule'] = $weeks;
			$obj->Players[] = (object)$p;
		}
		return $obj;
	}//-----

	/**
	 * Get the current injury list
	 * This will return an array of stdClass objects with a list of injured players by team
	 *
	 * @param int $week The week number to retrieve injuries for (1-17)
	 * @return object
	**/
	public function getInjuries($week) {

		$week = (int) $week;
		$endpoint = "http://api.fantasyfootballnerd.com/ffnInjuriesXML.php?apiKey=" . $this->apiKey . "&week=" . $week;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);

		if ($doc->getElementsByTagName("Error")->length > 0) {
			$this->errorMsg = $doc->getElementsByTagName("Error")->item(0)->nodeValue;
		}

		$obj->Injuries = array();
		if ($doc->getElementsByTagName("Team")->length > 0) {
			$inj = array();
			foreach ($doc->getElementsByTagName("Injuries")->item(0)->getElementsByTagName("Team") AS $team) {
				$teamId = $team->getAttribute("Code");
				foreach ($team->getElementsByTagName("Injury") AS $inj) {
					$obj->Injuries[] = (object) array(
						"Week"			=> $inj->getElementsByTagName("Week")->item(0)->nodeValue,
						"playerId"		=> $inj->getElementsByTagName("playerId")->item(0)->nodeValue,
						"Player"		=> $inj->getElementsByTagName("PlayerName")->item(0)->nodeValue,
						"Team"			=> $inj->getElementsByTagName("Team")->item(0)->nodeValue,
						"Position"		=> $inj->getElementsByTagName("Position")->item(0)->nodeValue,
						"Injury"		=> $inj->getElementsByTagName("InjuryDesc")->item(0)->nodeValue,
						"PracticeStatus"=> $inj->getElementsByTagName("PracticeStatusDesc")->item(0)->nodeValue,
						"GameStatus"	=> $inj->getElementsByTagName("GameStatusDesc")->item(0)->nodeValue,
						"Updated"		=> $inj->getElementsByTagName("LastUpdate")->item(0)->nodeValue,
					);
				}
			}
		}
		return $obj;
	}//-----

	/**
	 * Get Weekly Projections
	 * This will return an array of stdClass objects with the requested week's projections
	 *
	 * @param string $position The position to retrieve. Options: QB, RB, WR, TE, DEF, K
	 * @param int $week The week to return results for (1-17)
	 * @return object
	**/
	public function getWeeklyRankings($position, $week) {

		$week = (int) $week;
		$endpoint = "http://api.fantasyfootballnerd.com/ffnSitStartXML.php?apiKey=" . $this->apiKey . "&week=" . $week . "&position=" . $position;

		$data = $this->call($endpoint);

		$obj = new stdClass;
		$doc = new DOMDocument();
		$doc->loadXML($data);

		if ($doc->getElementsByTagName("Error")->length > 0) {
			$this->errorMsg = $doc->getElementsByTagName("Error")->item(0)->nodeValue;
		}

		$obj->Players = array();
		foreach ($doc->getElementsByTagName("Player") AS $player) {
			$p = array(
				"playerId"			=> $player->getAttribute("playerId"),
				"PlayerName"		=> $player->getAttribute("Name"),
				"Position"			=> $player->getAttribute("Position"),
				"Team"				=> $player->getAttribute("Team"),
				"Week"				=> $player->getAttribute("Week"),
				"Rank"				=> $player->getAttribute("Rank"),
				"ProjectedPoints"	=> $player->getAttribute("ProjectedPoints"),
				"StandardPoints"	=> $player->getElementsByTagName("Standard")->item(0)->nodeValue,
				"StandardLow"		=> $player->getElementsByTagName("StandardLow")->item(0)->nodeValue,
				"StandardHigh"		=> $player->getElementsByTagName("StandardHigh")->item(0)->nodeValue,
				"PPR"				=> $player->getElementsByTagName("PPR")->item(0)->nodeValue,
				"PPRLow"			=> $player->getElementsByTagName("PPRLow")->item(0)->nodeValue,
				"PPRHigh"			=> $player->getElementsByTagName("PPRHigh")->item(0)->nodeValue
			);
			$obj->Players[] = (object) $p;
		}
		return $obj;
	}//-----

	/**
	 * Utility to call the FFN endpoint and return the data result
	 * The url with the appropriate vars attached to it.
	 *
	 * @param string $url The endpoint url that we're calling
	**/
	private function call($url) {

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);

		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}//-----------------------------
}
?>
