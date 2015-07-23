<?php

// http://www.cra-arc.gc.ca/tx/bsnss/tpcs/pyrll/clcltng/ei/cnt-chrt-pf-eng.html
class CA_EmploymentIns_TaxLogic extends Scaffold_CATaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$taxableIncome = $this->grossIncome;
		echo "<div>[empins] Insurance rate: " . ($this->fetchTaxRate() * 100) . "%.</div>\n";

		$maxInsurable = $this->fetchMaxInsurable();

		if ($this->grossIncome->gt($maxInsurable)) {
			$pensionable = new fMoney($maxInsurable);
		}
		else
		{
			$pensionable = $this->grossIncome;
		}

		$taxLiability = $pensionable->mul($this->fetchTaxRate());

		echo "<div>[empins] Insurance liability: " . $taxLiability->format() . "</div>\n";

		return $taxLiability;
	}

	protected function fetchTaxRate()
	{
		if ($this->year >= 2012)
		{
			return .0188;
		}

		throw new InvalidArgumentException("Employment Insurance data for the year '{$this->year}' is currently unavailable.");
	}

	protected function fetchMaxInsurable()
	{
		if ($this->year == 2014)
		{
			return 48600;
		}
		else if ($this->year === 2015)
		{
			return 49500;
		}

		throw new InvalidArgumentException("Employment Insurance data for the year '{$this->year}' is currently unavailable.");
	}
}
