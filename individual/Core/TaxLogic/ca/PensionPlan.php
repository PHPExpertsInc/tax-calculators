<?php

// http://www.cra-arc.gc.ca/tx/bsnss/tpcs/pyrll/clcltng/cpp-rpc/cnt-chrt-pf-eng.html
class CA_PensionPlan_TaxLogic extends Scaffold_CATaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$taxableIncome = $this->grossIncome;
		echo "<div>[pension] Contribution rate: " . ($this->fetchTaxRate() * 100) . "%.</div>\n";

		$maxPensionable = $this->fetchMaxPensionable();

		if ($this->grossIncome->gt($maxPensionable)) {
			$pensionable = new fMoney($maxPensionable);
		}
		else
		{
			$pensionable = $this->grossIncome;
		}

		$exemption = $this->fetchExemption();
		if ($pensionable->gte($exemption))
		{
			$taxLiability = $pensionable->sub($exemption)->mul($this->fetchTaxRate());
		}
		else
		{
			$taxLiability = new fMoney(0);
		}

		echo "<div>[pension] Contribution liability: " . $taxLiability->format() . "</div>\n";

		return $taxLiability;
	}

	protected function fetchTaxRate()
	{
		if ($this->year >= 2003)
		{
			return .0495;
		}

		throw new InvalidArgumentException("Pension Plan contribution data for the year '{$this->year}' is currently unavailable.");
	}

	protected function fetchMaxPensionable()
	{
		if ($this->year == 2014)
		{
			return 52500;
		}
		else if ($this->year === 2015)
		{
			return 53600;
		}

		throw new InvalidArgumentException("Pension Plan contribution data for the year '{$this->year}' is currently unavailable.");
	}

	protected function fetchExemption()
	{
		if ($this->year >= 1997)
		{
			return 3500;
		}

		throw new InvalidArgumentException("Pension Plan contribution data for the year '{$this->year}' is currently unavailable.");
	}
}
