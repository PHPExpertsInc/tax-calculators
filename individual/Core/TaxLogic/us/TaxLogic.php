<?php

abstract class Scaffold_USTaxLogic extends Scaffold_GenericTaxLogic
{
	public function setTaxMode($mode)
	{
		if ($mode != API_Types_TaxMode::SINGLE && $mode != API_Types_TaxMode::JOINT)
		{
			throw new InvalidArgumentException("Invalid tax mode. Only Individual or Joint modes are valid.");
		}

		$this->taxMode = $mode;
	}
}
