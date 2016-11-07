<?php

namespace EsTest\Player;

class Player {

	private $type;
	private $token;

	public function __construct(PlayerType $type, PlayerToken $token, $name) {
		$this->type = $type;
		$this->token = $token;
		$this->name = $name;
	}

	public static function create($type, $token, $name) {
		return new static(new PlayerType($type), new PlayerToken($token), $name);
	}

	public function getType() {
		return $this->type;
	}

	public function getToken() {
		return $this->token;
	}

	public function getName() {
		return $this->name;
	}


}