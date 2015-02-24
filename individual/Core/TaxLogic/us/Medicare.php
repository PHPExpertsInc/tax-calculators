<?php

class US_Medicare_TaxLogic extends FICA_TaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$taxableIncome = $this->grossIncome;
		echo "<div>[medicare] Tax rate: " . ($this->fetchTaxRate() * 100) . "%.</div>\n";
		$taxLiability = $taxableIncome->mul($this->fetchTaxRate());
		echo "<div>[medicare] Tax liability: " . $taxLiability->format() . "</div>\n";


		return $taxLiability;
	}

	protected function fetchTaxRate()
	{
		if ($this->year >= 2012)
		{
			if ($this->employmentType == API_Types_Employment::SELF)
			{
				return 0.029;
			}
			else
			{
				return 0.0145;
			}
		}
		else
		{
			throw new InvalidArgumentException("Medicare tax data for the year '{$this->year}' is currently unavailable.");
		}
	}

}

