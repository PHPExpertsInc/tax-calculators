<?php

abstract class Scaffold_GenericTaxLogic
{
	protected $year;
	protected $taxMode = API_Types_TaxMode::SINGLE;

	/** @var fMoney */
	protected $grossIncome;
	/** @var fMoney */
	protected $deductions;

	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions)
	{
		$this->setTaxMode($taxMode);
		$this->setYear($year);
		$this->setGrossIncome($grossIncome);
		$this->setDeductions($deductions);
	}

	public function setYear($year)
	{
		if (!is_numeric($year))
		{
			throw new InvalidArgumentException("Year must be a whole number.");
		}

		$this->year = $year;
	}

	public function setGrossIncome(fMoney $grossIncome)
	{
		$this->grossIncome = $grossIncome;
	}

	public function setDeductions(fMoney $deductions)
	{
		$this->deductions = $deductions;
	}
}
