<?php

class US_FederalIncome_TaxLogic extends Scaffold_GenericTaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$brackets = $this->fetchTaxBracketInfo();

		$taxableIncome = $this->grossIncome;
		$deductions = $this->determineDeductionAmount();
		if (!empty($_GET['debug'])) {
			echo "<div>[income] Deductions: $deductions</div>\n";
		}
		$taxableIncome = $taxableIncome->sub($deductions);

		//header('Content-Type: text/plain');

		$taxLiability = new fMoney(0, 'USD');
		foreach ($brackets as $bracket)
		{
			if ($taxableIncome->lte(0))
			{
				break;
			}

			if ($bracket->stop !== null)
			{
				$moneyInBracket = new fMoney($bracket->stop - $bracket->start);
			}
			else
			{
				$moneyInBracket = $taxableIncome;
			}
			if ($taxableIncome->lte($moneyInBracket))
			{
				$moneyInBracket = $taxableIncome;
			}
			if (!empty($_GET['debug'])) {
				$bracketRate = $bracket->rate * 100;
				echo "<div>[income] money in bracket: $moneyInBracket @ {$bracketRate}%</div>\n";
			}

			$bracketTax = $moneyInBracket->mul($bracket->rate);
			$taxLiability = $taxLiability->add($bracketTax);
			if (!empty($_GET['debug'])) {
				echo "<div>Bracket liability: $bracketTax</div>\n";
				echo "<div>Total Tax liability: $taxLiability</div>\n";
			}

			$taxableIncome = $taxableIncome->sub($moneyInBracket);
		}

		return $taxLiability;
	}

	/**
	 * @return fMoney
	 */
	protected function determineDeductionAmount()
	{
		// The IRS says to use the GREATER of individual deductions or the Standard deduction.
		$standardDeduction = $this->fetchStandardDeductionAmount();
		if (!empty($_GET['debug'])) {
			echo "<div>[income] Standard deduction: $standardDeduction</div>\n";
		}
		if ($this->deductions->gte($standardDeduction))
		{
			return $this->deductions;
		}
		else
		{
			return new fMoney($standardDeduction);
		}
	}

	/**
	 * @return int
	 * @throws LogicException
	 */
	protected function fetchStandardDeductionAmount()
	{
		if ($this->year == 2012)
		{
			if ($this->taxMode == API_Types_TaxMode::SINGLE)
			{
				return 5950;
			}
			else if ($this->taxMode == API_Types_TaxMode::JOINT)
			{
				return 11900;
			}
			else
			{
				throw new LogicException("Standard deducation data for the year '{$this->year}' and mode '{$this->taxMode}' is not currently available.");
			}
		}
		else if ($this->year == 2013)
		{
			if ($this->taxMode == API_Types_TaxMode::SINGLE)
			{
				return 6100;
			}
			else if ($this->taxMode == API_Types_TaxMode::JOINT)
			{
				return 12200;
			}
			else
			{
				throw new LogicException("Standard deducation data for the year '{$this->year}' and mode '{$this->taxMode}' is not currently available.");
			}
		}
		else
		{
			throw new LogicException("Standard deduction data for the year '{$this->year}' is not currently available.");
		}
	}

	/**
	 * @return Model_FederalIncomeTaxBracket[]|null
	 */
	protected function fetchTaxBracketInfo()
	{
		// Simulate loading from a data store. Use a data provider function for now.
		if ($this->taxMode == API_Types_TaxMode::SINGLE)
		{
			if (!empty($_GET['debug'])) {
				echo "<div>Year: {$this->year}</div>\n";
			}
			$bracketsInfo = US_TaxBrackets::getIndividualBrackets($this->year);
		}
		else if ($this->taxMode == API_Types_TaxMode::JOINT)
		{
			$bracketsInfo = US_TaxBrackets::getJointBrackets($this->year);
		}
		else
		{
			throw new LogicException("Federal Income: No bracket info for tax mode '{$this->taxMode}'");
		}

		// If we were using PDO, i'd just call $stmt->fetchObject('FederalIncomeTaxBracket');
		$brackets = array();
		foreach ($bracketsInfo as $info)
		{
			$b = new API_Model_TaxBracket;
			$b->start = $info['start'];
			$b->stop = $info['stop'];
			$b->rate = $info['rate'];
			$brackets[] = $b;
		}

		return $brackets;
	}
}

