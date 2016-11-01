<?php

namespace EsTest\Property;

interface PropertyRepositoryInterface {
	public function save($name, $value);
	public function load($name);
}