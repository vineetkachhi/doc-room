<?php

namespace App\Helpers;

use DateTime;

class DateTimeFormatter
{
	public static function getTimeDifference($updatedAt){
		if(!$updatedAt)
		{
			$updatedAt = date("Y-m-d H:i:s");
		}
		$updatedDateTime = new DateTime(date("Y-m-d H:i:s", strtotime($updatedAt)));
		$roomTimerData = $updatedDateTime->diff(new DateTime(date("Y-m-d H:i:s")));
		$timerMinutes = ($roomTimerData->days * 24 * 60) +
		($roomTimerData->h * 60) + $roomTimerData->i;
		$timerSeconds = $roomTimerData->s;
		$roomTimerDetails['minutes'] = sprintf("%02d", $timerMinutes);
		$roomTimerDetails['seconds'] = sprintf("%02d", $timerSeconds);
		return $roomTimerDetails;
	}
}