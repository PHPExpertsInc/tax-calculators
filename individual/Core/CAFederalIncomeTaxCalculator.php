<?php

class CA_FederalIncomeTaxCalculator extends Scaffold_CATaxLogic
{
	/** @var API_TaxLogic[] */
	protected $taxLogicArray;
	protected $taxLiabilities;

	/** @var fMoney */
	protected $totalTaxes;

	public function __construct($taxTaxMode, API_TaxLogic $incomeTaxLogic, $year, fMoney $grossIncome, fMoney $deductions)
	{
		parent::__construct($taxTaxMode, $year, $grossIncome, $deductions);

		$this->addTaxLogic('income', $incomeTaxLogic);
	}

	public function addTaxLogic($name, API_TaxLogic $incomeTaxLogic)
	{
		$this->taxLogicArray[$name] = $incomeTaxLogic;
	}

	public function calculateTaxLiabilities()
	{
		$totalTaxes = new fMoney(0);
		foreach ($this->taxLogicArray as $name => /** @var API_TaxLogic */ $logic)
		{
			$this->taxLiabilities[$name] = $logic->calculateTaxLiability();
			$totalTaxes = $totalTaxes->add($this->taxLiabilities[$name]);
		}

		$this->totalTaxes = $totalTaxes;

		return $this->taxLiabilities;
	}

	public function getTaxLiability($name)
	{
		if (!isset($this->taxLiabilities[$name]))
		{
			throw new InvalidArgumentException("No tax liability information for '$name");
		}
		return $this->taxLiabilities[$name];
	}

	public function getTaxLiabilityReport()
	{
		if ($this->totalTaxes === null)
		{
			throw new LogicException("Called " . __METHOD__ . " before calculating taxes.");
		}

		$taxLiabilities = new API_Model_TaxLiabilities;
		$taxLiabilities->grossIncome = $this->grossIncome;
		$taxLiabilities->federalIncomeTax = $this->taxLiabilities['income']->format();
		$taxLiabilities->cppContribution = $this->taxLiabilities['cpp']->format();
		$taxLiabilities->employmentIns = $this->taxLiabilities['ei']->format();

		$taxLiabilities->totalTaxes = $this->totalTaxes->format();
		$taxLiabilities->netIncome = $this->grossIncome->sub($this->totalTaxes)->format();

		return $taxLiabilities;
	}
}
