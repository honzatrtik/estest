<?php

namespace EsTest\Player;

class PlayerToken {

	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function isEqual(PlayerToken $token) {
		return $this->getValue() === $token->getValue();
	}
}