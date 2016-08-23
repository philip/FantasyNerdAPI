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
 *
 * This is a fork of the above. Author information for the fork:
 * @author Philip Olson <philip@roshambo.org>
 * Version is not being tracked at this time. See https://github.com/philip/FantasyNerdAPI for commit history.
 *
**/
class FFN {

	/**
	 * Your API Key from FantasyFootballNerd.com
	 * @var int
	**/
	private $apiKey;

	private $baseurl = "http://www.fantasyfootballnerd.com/service/";

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
		$url  = $this->baseurl . "schedule/json/" . $this->apiKey . "/";
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data;
	}//-----

	/**
	 * Get the list of players from FFN
	 * This will return a stdClass object with all the NFL players. This does not need to be called
	 * more than once per week as it doesn't change with much frequency.
	 *
	 * @return array
	**/
	public function getPlayers() {
		$url  = $this->baseurl . "players/json/" . $this->apiKey . "/";
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data['Players'];
	}

	/**
	 * Get player details
	 * This will return a stdClass object with the details for the player requested.
	 *
	 * @param int $playerId The FFN playerId to retrieve
	 * @return object
	**/
	public function getPlayerDetails($playerId) {
		$url  = $this->baseurl . "player/json/" . $this->apiKey . "/" . $playerId;
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data;
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
	public function getDraftRankings($ppr = 0) {
		$url  = $this->baseurl . "draft-rankings/json/" . $this->apiKey . "/" . $ppr;
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data;
	}//-----

	/**
	 * Get the current injury list
	 * This will return an array of stdClass objects with a list of injured players by team
	 *
	 * @param int $week The week number to retrieve injuries for (1-17)
	 * @return object
	**/
	public function getInjuries($week = 1) {
		$url  = $this->baseurl . "injuries/json/" . $this->apiKey . "/" . $week;
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data;
	}//-----

	/**
	 * Get Weekly Projections
	 * This will return an array of stdClass objects with the requested week's projections
	 *
	 * @param string $position The position to retrieve. Options: QB, RB, WR, TE, DEF, K
	 * @param int $week The week to return results for (1-17)
	 * @return object
	**/
	public function getWeeklyRankings($position = 'QB', $week = 1, $ppr = 1) {
		$url  = $this->baseurl . "weekly-rankings/json/" . $this->apiKey . "/" . $position . '/' . $week . '/' . $ppr;
		if (!$data = $this->fetch($url)) {
			return false;
		}
		return $data;
	}//-----

	/**
	 * Utility to call the FFN endpoint and return the data result
	 * The url with the appropriate vars attached to it.
	 *
	 * @param string $url The endpoint url that we're calling
	**/
	private function fetch($url) {
		$json = file_get_contents($url);

		if (empty($json)) {
			return false;
		}
		$data = json_decode($json, true);
		if (empty($data) || !is_array($data)) {
			return false;
		}
		return $data;
	}//-----------------------------
}
?>
