<?php

// Medicare tax does not allow for deductions. It is off of the entire gross wage.
class MedicareTax implements API_Tax
{
	public function getTaxLiability(fMoney $revenue, fMoney $deductions)
	{
		$taxLiability = $this->calculateTaxLiability($revenue);

		return $taxLiability;
	}

	protected function calculateTaxLiability(fMoney $taxableRevenue)
	{
		$taxRate = $this->fetchTaxRate();
		$taxLiability = $taxableRevenue->mul($taxRate);

		if (isset($_GET['debug']))
		{
			echo "<pre>";
			echo "[medicare] Taxable Revenue: $taxableRevenue\n";
			echo "[medicare] Tax Rate: $taxRate\n";
			echo "[medicare] Total Tax Liability: $taxLiability\n";
			echo "</pre>";
		}

		return $taxLiability;
	}

	protected function fetchTaxRate()
	{
		return 0.0145;
	}
}

