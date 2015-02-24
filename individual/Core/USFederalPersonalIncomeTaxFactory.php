<?php

class USFederalPersonalIncomeTaxesFactory
{
	/**
	 * @static
	 * @param $taxMode
	 * @param $year
	 * @param fMoney $grossIncome
	 * @param fMoney $deductions
	 * @param $employmentType
	 * @return US_FederalIncomeTaxCalculator
	 */
	public static function build($taxMode, $year, fMoney $grossIncome, fMoney $deductions, $employmentType)
	{
		//($mode = null, $year = null, fMoney $grossIncome = null, fMoney $deductions = null);
		$incomeLogic = new US_FederalIncome_TaxLogic($taxMode, $year, $grossIncome, $deductions);
		$ssiLogic = new US_SocialSecurity_TaxLogic($taxMode, $year, $grossIncome, $deductions);
		$ssiLogic->setEmploymentType($employmentType);
		$medicareLogic = new US_Medicare_TaxLogic($taxMode, $year, $grossIncome, $deductions);
		$medicareLogic->setEmploymentType($employmentType);

		// $taxMode, API_TaxLogic $incomeTaxLogic, $year, fMoney $grossIncome, fMoney $deductions)
		$taxCalc = new US_FederalIncomeTaxCalculator($taxMode, $incomeLogic, $year, $grossIncome, $deductions);
		$taxCalc->addTaxLogic('ssi', $ssiLogic);
		$taxCalc->addTaxLogic('medicare', $medicareLogic);

		// Add ObamaCare's *stupid* Add. Medicare Tax ;-//
		// Boy, am I glad I used the Decorator pattern, or this would be a mess ;o.
		// FIXME: Bugged, this only should apply to high wage earners.
		if ($year >= 2013)
		{
			$addMedicareLogic = new US_AdditionalMedicare_TaxLogic($taxMode, $year, $grossIncome, $deductions);
			$addMedicareLogic->setEmploymentType($employmentType);
			$taxCalc->addTaxLogic('addMedicare', $addMedicareLogic);
		}

		return $taxCalc;
	}
}

