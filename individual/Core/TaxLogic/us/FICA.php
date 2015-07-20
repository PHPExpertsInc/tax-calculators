<?php

abstract class FICA_TaxLogic extends Scaffold_USTaxLogic
{
	protected $employmentType;

	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions)
	{
		$this->setEmploymentType(API_Types_Employment::EMPLOYEE);
		parent::__construct($taxMode, $year, $grossIncome, $deductions);
	}

	public function setEmploymentType($employmentType)
	{
		if ($employmentType != API_Types_Employment::EMPLOYEE && $employmentType != API_Types_Employment::SELF)
		{
			throw new InvalidArgumentException("FICA: Invalid employment type.");
		}

		$this->employmentType = $employmentType;
	}
}

