<?php

namespace EsTest\Property;

class FilePropertyRepository implements PropertyRepositoryInterface {

	private $directory;

	public function __construct($directory) {
		$this->directory = $directory;
	}

	public function save($name, $value) {
		file_put_contents($this->getFile($name), $value, LOCK_EX);
	}

	public function load($name) {
		$file = $this->getFile($name);
		return file_exists($file) ? file_get_contents($file) : null;
	}

	private function getFile($name) {
		return $this->directory . '/' . sha1($name);
	}
}