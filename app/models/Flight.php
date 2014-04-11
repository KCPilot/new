<?php

class Flight extends Eloquent {

	protected $table = 'flights';
	public $timestamps = true;
	protected $softDelete = false;
	protected $dates = ['departure_time','arrival_time'];
	protected $appends = ['duration'];

	public function aircraft()
	{
		return $this->hasMany('Aircraft','code','aircraft_id');
	}

	public function departure()
	{
		return $this->belongsTo('Airport', 'departure_id');
	}

	public function arrival()
	{
		return $this->belongsTo('Airport', 'arrival_id');
	}

	public function pilot()
	{
		return $this->belongsTo('Pilot','vatsim_id','vatsim_id');
	}

/*	public function getDurationAttribute()
	{
		if(is_null($this->departure_time)) return 'Unknown';
		$time = ($this->state == 1 || $this->state == 3) ? Carbon::now() : $this->arrival_time;
		$hours = $this->departure_time->diffInHours($time);
		$minutes = $this->departure_time->diffInMinutes($time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . ':' . str_pad($minutes,2,'0',STR_PAD_LEFT);
	}*/

	public function departureCountry()
	{
		return $this->belongsTo('Country', 'departure_country_id');
	}

	public function arrivalCountry()
	{
		return $this->belongsTo('Country', 'arrival_country_id');
	}

	public function airline()
	{
		return $this->hasOne('Airline','icao','airline_id');
	}

	public function privateCountry()
	{
		return $this->belongsTo('Country','airline_id');
	}

	public function positions()
	{
		return $this->hasMany('Position')->orderBy('time','asc');
	}

	public function lastPosition()
	{
		return $this->hasOne('Position')->orderBy('time','desc');
	}

	public function getStatusAttribute() {
		switch($this->state) {
			case 0:
				return 'Departing...';
			case 1:
			case 3:
				return 'Airborne';
			case 2:
				return 'Arrived';
			case 4:
				return 'Preparing...';
		}
	}

	public function getStatusIconAttribute() {
		switch($this->state) {
			case 0:
			case 4:
				return 'departing';
			case 1:
			case 3:
				return 'airborne';
			case 2:
				return 'arrived';
		}
	}

	public function getAltitudeAttribute($value) {
		if(starts_with($value,'FL') || starts_with($value,'F')) {
			return filter_var($value, FILTER_SANITIZE_NUMBER_INT)*100;
		} elseif(strlen($value) <= 3) {
			return $value*100;
		} else {
			return $value;
		}
	}

	public function getTraveledTimeAttribute() {
		if(is_null($this->departure_time)) return null;
		$now = Carbon::now();
		$hours = $now->diffInHours($this->departure_time);
		$minutes = $now->diffInMinutes($this->departure_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	public function getTogoTimeAttribute() {
		if(is_null($this->arrival_time)) return null;
		$now = Carbon::now();
		$hours = $now->diffInHours($this->arrival_time);
		$minutes = $now->diffInMinutes($this->arrival_time);
		$minutes = $minutes - $hours * Carbon::MINUTES_PER_HOUR;
		return $hours . 'h ' . str_pad($minutes,2,'0',STR_PAD_LEFT) . 'm';
	}

	function setDeparture(Airport $airport) {
		$this->attributes['departure_id'] = $airport->id;
		$this->attributes['departure_country_id'] = $airport->country_id;
	}

	function setArrival(Airport $airport) {
		$this->attributes['arrival_id'] = $airport->id;
		$this->attributes['arrival_country_id'] = $airport->country_id;
	}

	function statePreparing() {
		$this->attributes['state'] = 4;
	}

	function isPreparing() {
		return ($this->state == 4);
	}

	function stateDeparting() {
		$this->attributes['state'] = 0;
	}

	function isDeparting() {
		return ($this->state == 0);
	}

	function stateAirborne() {
		$this->attributes['state'] = 1;
	}

	function isAirborne() {
		return ($this->state == 1);
	}

	function stateArriving() {
		$this->attributes['state'] = 3;
	}

	function isArriving() {
		return ($this->state == 3);
	}

	function stateArrived() {
		$this->attributes['state'] = 2;
	}

	function isArrived() {
		return ($this->state == 2);
	}

	function isAirline($airline) {
		$this->attributes['callsign_type'] = 1;
		$this->attributes['airline_id'] = $airline;
	}

	function isPrivate($registration) {
		$this->attributes['callsign_type'] = 2;
		$this->attributes['airline_id'] = $registration;
	}

	function getNmAttribute() {
		return $this->distance * 0.54;
	}

	function getHoursAttribute() {
		return floor($this->duration/60);
	}

	function getMinutesAttribute() {
		return str_pad(($this->duration - ($this->hours * 60)),2,'0',STR_PAD_LEFT);
	}

}