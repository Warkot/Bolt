<?php

abstract class Utilities {

	public static function splitReleaseTag($releaseTag) {
		$split = preg_split('/[.]/', $releaseTag);
		return [
			"release" => $split[0],
			"version" => $split[1]
		];
	}
}
