<?php

class US_SocialSecurity_TaxLogic extends FICA_TaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$maxTaxable = new fMoney($this->fetchMaxTaxableIncome());
		if ($this->grossIncome->gte($maxTaxable))
		{
			$taxableIncome = $maxTaxable;
		}
		else
		{
			$taxableIncome = $this->grossIncome;
		}

		$taxLiability = $taxableIncome->mul($this->fetchTaxRate());
		if (!empty($_GET['debug'])) {
			echo '<div>[ssi] Max taxable: ' . $taxableIncome->format() . ' @ ' . ($this->fetchTaxRate() * 100) . '%</div>';
			echo '<div>[ssi] Tax liability: ' . $taxLiability->format() . '</div>';
		}

		return $taxLiability;
	}

	// http://rubinontax.blogspot.com/2012/05/social-security-taxes-to-rise-in-2013.html
	protected function fetchMaxTaxableIncome()
	{
		if ($this->year == 2012)
		{
			return 110100;
		}
		else if ($this->year >= 2013)
		{
			return 113700;
		}
		else
		{
			throw new LogicException("Social Security: No tax data for year '{$this->year}'.");
		}
	}

	protected function fetchTaxRate()
	{
		if ($this->year == 2012)
		{
			if ($this->employmentType == API_Types_Employment::SELF)
			{
				return 0.104;
			}
			else
			{
				return 0.042;
			}
		}
		else if ($this->year >= 2013)
		{
			if ($this->employmentType == API_Types_Employment::SELF)
			{
				return 0.124;
			}
			else
			{
				return 0.062;
			}
		}
		else
		{
			throw new InvalidArgumentException("Social Security tax data for the year '{$this->year}' is currently unavailable.");
		}
	}
}

