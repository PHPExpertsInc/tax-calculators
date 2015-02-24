<?php

// Uses the Factory Design Pattern.
class USFederalTaxesFactory
{
	public static function create(fMoney $revenue, fMoney $deductions, fMoney $wages, $numOfEmployees)
	{
		// TODO: Rearchitect out the magic Constants.
		// TODO: Add a proper Service Locator Pattern here...
		$taxes = array(
			'ssi' => 'SocialSecurityTax',
			'medicare' => 'MedicareTax',
			//'unemployment' => 'UnemploymentTax',
		);
		foreach ($taxes as $taxName => $taxClass)
		{
			$tax = new $taxClass();
			/** @var $tax API_Tax */
			$liabilities[$taxName] = $tax->getTaxLiability($wages, $deductions);
		}

		$tax = new UnemploymentTax($numOfEmployees);
		$liabilities['unemployment'] = $tax->getTaxLiability($wages, $deductions);

		$incomeTax = new USFederalIncomeTax($revenue, $deductions, $wages, $liabilities);

		return $incomeTax;
	}
}

