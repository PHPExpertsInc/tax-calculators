<?php

abstract class Scaffold_CATaxLogic extends Scaffold_GenericTaxLogic
{
	public function setTaxMode($mode)
	{
		if ($mode != API_Types_TaxMode::SINGLE && $mode != API_Types_TaxMode::SPOUSE_AMOUNT)
		{
			throw new InvalidArgumentException("Invalid tax mode. Only Individual or Spouse Amount modes are valid.");
		}

		$this->taxMode = $mode;
	}
}
