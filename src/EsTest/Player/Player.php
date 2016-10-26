<?php

namespace EsTest\Player;

use EsTest\Player\PlayerType;

class Player {

	private $type;
	private $token;

	public function __construct(PlayerType $type, $token) {
		$this->type = $type;
		$this->token = $token;
	}

	public function getType() {
		return $this->type;
	}

	public function getToken() {
		return $this->token;
	}
}