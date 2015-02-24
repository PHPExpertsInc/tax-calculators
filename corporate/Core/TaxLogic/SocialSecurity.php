<?php

// Social security tax does not allow for deductions. It is off of the entire gross wage.
class SocialSecurityTax implements API_Tax
{
	public function getTaxLiability(fMoney $revenue, fMoney $deductions)
	{
		$taxLiability = $this->calculateTaxLiability($revenue);

		return $taxLiability;
	}

	protected function calculateTaxLiability(fMoney $taxableRevenue)
	{
		$maxTaxable = $this->fetchMaxTaxableAmount();

		$taxableRevenue = min($maxTaxable, $taxableRevenue);
		$taxRate = $this->fetchTaxRate();
		$taxLiability = $taxableRevenue->mul($taxRate);

		if (isset($_GET['debug']))
		{
			echo "<pre>";
			echo "[ssi] Taxable Revenue: $taxableRevenue\n";
			echo "[ssi] Tax Rate: $taxRate\n";
			echo "[ssi] Total Tax Liability: $taxLiability\n";
			echo "</pre>";
		}

		return $taxLiability;
	}

	protected function fetchMaxTaxableAmount()
	{
		return new fMoney(110100, 'USD');
	}

	protected function fetchTaxRate()
	{
		return 0.062;
	}
}

