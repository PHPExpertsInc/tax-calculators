<?php

class US_AdditionalMedicare_TaxLogic extends FICA_TaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$minTaxable = new fMoney($this->fetchMinTaxIncomeThreshold());
		if ($this->grossIncome->gte($minTaxable))
		{
			$taxableIncome = $this->grossIncome;
		}
		else
		{
			return new fMoney(0, 'USD');
		}

		$taxLiability = $taxableIncome->mul($this->fetchTaxRate());

		return $taxLiability;
	}

	protected function fetchMinTaxIncomeThreshold()
	{
		if ($this->taxMode == API_Types_TaxMode::SINGLE)
		{
			return 200000;
		}
		else if ($this->taxMode == API_Types_TaxMode::JOINT)
		{
			return 250000;
		}
		else if ($this->taxMode == API_Types_TaxMode::SEPARATE)
		{
			return 125000;
		}
		else if ($this->taxMode == API_Types_TaxMode::HOUSEHEAD)
		{
			return 200000;
		}
		else if ($this->taxMode == API_Types_TaxMode::WIDOWER)
		{
			return 200000;
		}
		else
		{
			throw new LogicException("Additional Medicare Tax: Mode '{$this->taxMode}' data is not currently available.");
		}
	}

	protected function fetchTaxRate()
	{
		if ($this->year == 2013)
		{
			return 0.009;
		}
		else
		{
			throw new InvalidArgumentException("Social Security tax data for the year '{$this->year}' is currently unavailable.");
		}
	}
}

