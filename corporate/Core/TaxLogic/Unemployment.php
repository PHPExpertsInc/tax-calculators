<?php

// Unemployment Insurance tax does not allow for deductions. It is off of the entire gross wage.
// Source: http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp
class UnemploymentTax implements API_Tax
{
	protected $numOfEmployees;

	public function __construct($numOfEmployees)
	{
		$this->setNumberOfEmployees($numOfEmployees);
	}

	public function setNumberOfEmployees($numOfEmployees)
	{
		$this->numOfEmployees = $numOfEmployees;
	}

	public function getTaxLiability(fMoney $revenue, fMoney $deductions)
	{
		if (empty($this->numOfEmployees))
		{
			throw new LogicException("Cannot calculate Unemployment tax without specifying the number of employees.");
		}

		$taxLiability = new fMoney(0, 'USD');
		for ($a = 0; $a < $this->numOfEmployees; ++$a)
		{
			$taxLiability = $taxLiability->add($this->calculateTaxLiability($revenue));
		}

		return $taxLiability;
	}

	protected function calculateTaxLiability(fMoney $taxableRevenue)
	{
		if ($taxableRevenue->lte($this->fetchMinimumAmountToTax()))
		{
			return new fMoney(0, 'USD');
		}

		$maxTaxLiability = $this->fetchMaxTaxLiability();
		$taxRate = $this->fetchTaxRate();

		$taxLiability = $taxableRevenue->mul($taxRate);

		if ($taxLiability->gte($maxTaxLiability))
		{
			$fedTaxLiability = $maxTaxLiability;
		}

		$taxLiability = $fedTaxLiability->add($taxableRevenue->mul(0.027));

		if (isset($_GET['debug']))
		{
			echo "<pre>";
			echo "[unemployment] Taxable Revenue: $taxableRevenue\n";
			echo "[unemployment] Tax Rate: $taxRate\n";
			echo "[unemployment] Total Tax Liability: $taxLiability\n";
			echo "</pre>";
		}

		return $taxLiability;
	}

	protected function fetchMinimumAmountToTax()
	{
		return new fMoney(1500, 'USD');
	}

	protected function fetchMaxTaxLiability()
	{
		return new fMoney(56.00, 'USD');
	}

	protected function fetchTaxRate()
	{
		return 0.062;
	}
}
