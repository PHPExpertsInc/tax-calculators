<?php

class TaxBracketManager
{
	public function fetchAll()
	{
		$bracketsInfo = array(
			array('min' => 0,        'max' => 50000,    'rate' => 0.15),
			array('min' => 50001,    'max' => 75000,    'rate' => 0.25),
			array('min' => 75001,    'max' => 100000,   'rate' => 0.34),
			array('min' => 100001,   'max' => 335000,   'rate' => 0.39),
			array('min' => 335001,   'max' => 10000000, 'rate' => 0.34),
			array('min' => 10000001, 'max' => 15000000, 'rate' => 0.35),
			array('min' => 15000001, 'max' => 18333333, 'rate' => 0.38),
			array('min' => 18333334, 'max' => null,     'rate' => 0.35),
		);

		$brackets = array();
		foreach ($bracketsInfo as $i)
		{
			$bracket = new TaxBracket;
			$bracket->min = $i['min'];
			$bracket->max = $i['max'];
			$bracket->rate = $i['rate'];

			$brackets[] = $bracket;
		}

		return $brackets;
	}
}
