<?php

class CAFederalPersonalIncomeTaxesFactory
{
	/**
	 * @static
	 * @param $taxMode
	 * @param $year
	 * @param fMoney $grossIncome
	 * @param fMoney $deductions
	 * @param $employmentType
	 * @return Scaffold_GenericTaxLogic
	 */
	public static function build($taxMode, $year, fMoney $grossIncome, fMoney $deductions, $employmentType)
	{
		$incomeLogic = new CA_FederalIncome_TaxLogic($taxMode, $year, $grossIncome, $deductions);
		$cppLogic = new CA_PensionPlan_TaxLogic($taxMode, $year, $grossIncome, $deductions);
		$eiLogic = new CA_EmploymentIns_TaxLogic($taxMode, $year, $grossIncome, $deductions);

		$taxCalc = new CA_FederalIncomeTaxCalculator($taxMode, $incomeLogic, $year, $grossIncome, $deductions);
		$taxCalc->addTaxLogic('cpp', $cppLogic);
		$taxCalc->addTaxLogic('ei', $eiLogic);

		return $taxCalc;
	}
}
