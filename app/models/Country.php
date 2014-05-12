<?php

class Country extends Eloquent {

	protected $table = 'countries';
	public $timestamps = false;
	protected $softDelete = false;

	function getCountryAttribute($value) {
		return trim($value);
	}

}