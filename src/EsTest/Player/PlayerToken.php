<?php

namespace EsTest\Player;

class PlayerToken {

	private $token;

	public function __construct($token) {
		$this->token = $token;
	}

	public function getToken() {
		return $this->token;
	}

	public function isEqual(PlayerToken $token) {
		return $this->getToken() === $token->getToken();
	}
}