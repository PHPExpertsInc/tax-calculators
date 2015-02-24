<?php

// FIXME: Figure out a way to save the API_Tax interface ;-/
class USFederalIncomeTax /* implements API_Tax*/
{
	// AVOID MAGIC CONSTANTS.
	// FIXME: These need to be moved to their own classes. It's a serious weakness right now!!!!
	const INCOME_TAX_KEY = 'income';
	const SSI_TAX_KEY = 'ssi';
	const MEDCARE_TAX_KEY = 'medicare';
	const UNEMPLOYMENT_TAX_KEY = 'unemployment';

	/** @var TaxBracketManager */
	protected $bracketManager;

	/** @var TaxBracket[] */
	protected $brackets;

	/** @var fMoney */
	protected $revenue;

	/** @var fMoney */
	protected $deductions;

	/** @var fMoney[] */
	protected $taxLiabilities;

	public function __construct(fMoney $revenue, fMoney $deductions, fMoney $wages, array $otherTaxLiabilities, $bracketManager = null)
	{
		$this->amountOwed = new fMoney('0', 'USD');

		$this->revenue = $revenue;
		$deductions = $deductions->add($wages);
		$this->deductions = $deductions;
		$this->taxLiabilities = $otherTaxLiabilities;

		if ($bracketManager === null)
		{
			$bracketManager = new TaxBracketManager();
		}
		$this->bracketManager = $bracketManager;
	}

	/**
	 * @param $tax
	 * @return fMoney
	 * @throws LogicException*/
	public function getLiabilityByTax($tax)
	{
		if (!isset($this->taxLiabilities[$tax]))
		{
			throw new LogicException("No tax liabilities named '$tax'");
		}

		return $this->taxLiabilities[$tax];
	}

	public function getTaxLiability()
	{
		$brackets = $this->fetchTaxBracketRates();

		// Federal income tax algorithm works like this:
		// Total Revenue - Qualified Deductions - Other Fed Taxes -> Tax Brackets -> Rate.
		// Minimum possible taxes owed: $0.

		$otherTaxes = new fMoney(0, 'USD');
		// TODO: It'd be a nice-to-have to be able to dynamically figure out which
		// taxes are non-income and just do a foreach() here...
		$otherTaxes = $otherTaxes->add($this->getLiabilityByTax(self::SSI_TAX_KEY));
		$otherTaxes = $otherTaxes->add($this->getLiabilityByTax(self::MEDCARE_TAX_KEY));
		$otherTaxes = $otherTaxes->add($this->getLiabilityByTax(self::UNEMPLOYMENT_TAX_KEY));

		$amountOwed = $this->calculateTaxLiability($this->revenue, $this->deductions, $otherTaxes);
		$this->taxLiabilities[self::INCOME_TAX_KEY] = $amountOwed;
		return $amountOwed;
	}

	protected function calculateTaxLiability(fMoney $taxableRevenue, fMoney $deductions, fMoney $otherTaxes)
	{
		if (isset($_GET['debug']))
		{
			echo "<pre>";
			echo "[income] Total revenue: $taxableRevenue\n";
			echo "[income] Qualified deductions: " . $deductions . "\n";
			echo "[income] Other Federal tax liabilities: " . $otherTaxes . "\n";
		}

		$taxableRevenue = $taxableRevenue->sub($deductions);
		$taxableRevenue = $taxableRevenue->sub($otherTaxes);
		if ($taxableRevenue->lte(0)) { return new fMoney(0, 'USD'); }

		if (isset($_GET['debug']))
		{
			echo "[income] Taxable revenue: $taxableRevenue\n";
			echo "----------\n";
		}

		$totalTaxLiability = new fMoney(0, 'USD');
		foreach ($this->brackets as /** @var TaxBracket */ $bracket)
		{
			// Assume 500 0000
			// Algorithm: Get subvalue -> get liability -> add liability -> subtract subvalue -> continue
			if ($bracket->max !== null)
			{
				$amountTaxedInBracket = $bracket->max - $bracket->min;
			}
			else
			{
				$amountTaxedInBracket = $taxableRevenue;
			}

			if ($taxableRevenue->lte($amountTaxedInBracket))
			{
				$amountTaxedInBracket = $taxableRevenue;
			}

			$taxLiability = new fMoney($amountTaxedInBracket, 'USD');
			$taxLiability = $taxLiability->mul($bracket->rate);

			$totalTaxLiability = $totalTaxLiability->add($taxLiability);

			$taxableRevenue = $taxableRevenue->sub($amountTaxedInBracket);

			if (isset($_GET['debug']))
			{
				echo "[income] Bracket tax rate: {$bracket->rate}\n";
				echo "[income] Income taxed in bracket: {$bracket->min} ... {$bracket->max}\n";
				echo "[income] Amount in bracket: $amountTaxedInBracket\n";
				echo "[income] Tax Lability in bracket: $taxLiability\n";
				echo "[income] Taxable revenue: $taxableRevenue\n";
				echo "[income] Total Taxable liability: $totalTaxLiability\n";
				echo "------------\n";
			}

			if ($taxableRevenue->lte(0))
			{
				break;
			}
		}

		if (isset($_GET['debug']))
		{
			echo "</pre>";
		}

		return $totalTaxLiability;
	}

	protected function fetchTaxBracketRates()
	{
		if ($this->brackets !== null)
		{
			return $this->brackets;
		}

		// TODO: Load this from a database table.
		// FIXME: Add more brackets.
		// TIP: By doing a little extra work now, all i have to do later is do $pdo->fetchArray() later ;-)
		// I **LOVE** our complicated corporate income tax system!! WOOO!!!!!

		$brackets = $this->bracketManager->fetchAll();
		$this->brackets = $brackets;

		return $brackets;
	}
}
