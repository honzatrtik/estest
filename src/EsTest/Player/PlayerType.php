<?php

namespace EsTest\Player;

use MyCLabs\Enum\Enum;

class PlayerType extends Enum {
	const X = 'X';
	const O = 'O';

	public static function X() {
		return new static(static::X);
	}

	public static function O() {
		return new static(static::O);
	}
}