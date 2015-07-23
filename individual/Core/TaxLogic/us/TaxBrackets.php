<?php

class US_TaxBrackets {
  public static function getIndividualBrackets($year) {
    $brackets = null;
  	if ($year == 2012)
  	{
  		$brackets = array(
  			array('start' =>      0, 'stop' =>   8700, 'rate' => 0.10),
  			array('start' =>   8701, 'stop' =>  35350, 'rate' => 0.15),
  			array('start' =>  35351, 'stop' =>  85650, 'rate' => 0.25),
  			array('start' =>  85651, 'stop' => 178650, 'rate' => 0.28),
  			array('start' => 178651, 'stop' => 388350, 'rate' => 0.33),
  			array('start' => 388351, 'stop' =>   null, 'rate' => 0.35),
  		);
  	}
  	else if ($year == 2013)
  	{
  		$brackets = array(
  			array('start' =>      0, 'stop' =>   8950, 'rate' => 0.10),
  			array('start' =>   8951, 'stop' =>  36250, 'rate' => 0.15),
  			array('start' =>  36251, 'stop' =>  87850, 'rate' => 0.25),
  			array('start' =>  87851, 'stop' => 183250, 'rate' => 0.28),
  			array('start' => 183251, 'stop' => 398350, 'rate' => 0.33),
  			array('start' => 398351, 'stop' => 400000, 'rate' => 0.35),
  			array('start' => 400001, 'stop' =>   null, 'rate' => 0.396),
  		);
  	}
  	else
  	{
  		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
  	}

  	return $brackets;
  }

  public static function getJointBrackets($year) {
    $brackets = null;
  	if ($year == 2012)
  	{
  		$brackets = array(
  			array('start' =>      0, 'stop' =>  17400, 'rate' => 0.10),
  			array('start' =>  17401, 'stop' =>  70700, 'rate' => 0.15),
  			array('start' =>  70701, 'stop' => 142700, 'rate' => 0.25),
  			array('start' => 142701, 'stop' => 217450, 'rate' => 0.28),
  			array('start' => 217451, 'stop' => 388350, 'rate' => 0.33),
  			array('start' => 388351, 'stop' =>   null, 'rate' => 0.35),
  		);
  	}
  	else if ($year == 2013)
  	{
  		$brackets = array(
  			array('start' =>      0, 'stop' =>  17900, 'rate' => 0.10),
  			array('start' =>  17901, 'stop' =>  72500, 'rate' => 0.15),
  			array('start' =>  72501, 'stop' => 146400, 'rate' => 0.25),
  			array('start' => 146401, 'stop' => 223050, 'rate' => 0.28),
  			array('start' => 223051, 'stop' => 398350, 'rate' => 0.33),
  			array('start' => 398351, 'stop' => 450000, 'rate' => 0.35),
  			array('start' => 450001, 'stop' =>   null, 'rate' => 0.396),
  		);
  	}
  	else
  	{
  		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
  	}

  	return $brackets;
  }
}
