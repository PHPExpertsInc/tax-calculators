<?php

class CA_TaxBrackets {
  public static function getBrackets($year) {
    $brackets = null;
    if ($year === 2014 || $year === 2015)
  	{
  		$brackets = array(
  			array('start' =>      0, 'stop' =>  44701, 'rate' => 0.15),
  			array('start' =>  44702, 'stop' =>  89401, 'rate' => 0.22),
  			array('start' =>  89402, 'stop' => 138586, 'rate' => 0.26),
  			array('start' => 138587, 'stop' =>   null, 'rate' => 0.29),
  		);
  	}
  	else
  	{
  		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
  	}

  	return $brackets;
  }
}
