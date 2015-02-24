<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../');
include_once 'thrive/Autoloader.php';

new Thrive_Autoloader();


abstract class API_Types_TaxMode
{
	const SINGLE    = 'Single';
	const JOINT     = 'Married: Joint';
	const SEPARATE  = 'Married: Separate';
	const HOUSEHEAD = 'Head of Household';
	const WIDOWER   = 'Qualified Widower';
}

abstract class API_Types_Employment
{
	const EMPLOYEE = 'Employee';
	const SELF     = 'Self-Employeed';
}


class API_Model_TaxBracket
{
	public $start;
	public $stop;
	public $rate;
}

class API_Model_TaxLiabilities
{
	public $grossIncome;
	public $federalIncomeTax;
	public $ssiTax;
	public $medicareTax;
	public $addMedicareTax;
	public $totalTaxes;
	public $netIncome;
}


interface API_TaxLogic {
	// Setters
	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions);
	public function setYear($year);
	public function setGrossIncome(fMoney $grossIncome);
	public function setDeductions(fMoney $deductions);
	public function setTaxMode($mode);
	public function calculateTaxLiability();
}

abstract class Scaffold_GenericTaxLogic
{
	protected $year;
	protected $taxMode = API_Types_TaxMode::SINGLE;

	/** @var fMoney */
	protected $grossIncome;
	/** @var fMoney */
	protected $deductions;

	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions)
	{
		$this->setTaxMode($taxMode);
		$this->setYear($year);
		$this->setGrossIncome($grossIncome);
		$this->setDeductions($deductions);
	}

	public function setYear($year)
	{
		if (!is_numeric($year))
		{
			throw new InvalidArgumentException("Year must be a whole number.");
		}

		$this->year = $year;
	}

	public function setGrossIncome(fMoney $grossIncome)
	{
		$this->grossIncome = $grossIncome;
	}

	public function setDeductions(fMoney $deductions)
	{
		$this->deductions = $deductions;
	}

	public function setTaxMode($mode)
	{
		if ($mode != API_Types_TaxMode::SINGLE && $mode != API_Types_TaxMode::JOINT)
		{
			throw new InvalidArgumentException("Invalid tax mode. Only Individual or Joint modes are valid.");
		}

		$this->taxMode = $mode;
	}
}

/**
 * @param $year
 * @return array|null
 * @throws InvalidArgumentException
 */
function m_fetchUSFederalIndividualIncomeTaxBrackets($year)
{
	$brackets = null;
	if ($year == 2012)
	{
		$brackets = array(
			array('start' =>      0, 'stop' =>   8700, 'rate' => 0.10),
			array('start' =>   8701, 'stop' =>  35350, 'rate' => 0.15),
			array('start' =>  35351, 'stop' =>  85650, 'rate' => 0.25),
			array('start' =>  85651, 'stop' => 178650, 'rate' => 0.28),
			array('start' => 178651, 'stop' => 388350, 'rate' => 0.33),
			array('start' => 388351, 'stop' =>   null, 'rate' => 0.35),
		);
	}
	else if ($year == 2013)
	{
		$brackets = array(
			array('start' =>      0, 'stop' =>   8700, 'rate' => 0.15),
			array('start' =>   8701, 'stop' =>  35350, 'rate' => 0.15),
			array('start' =>  35351, 'stop' =>  85650, 'rate' => 0.28),
			array('start' =>  85651, 'stop' => 178650, 'rate' => 0.31),
			array('start' => 178651, 'stop' => 388350, 'rate' => 0.36),
			array('start' => 388351, 'stop' =>   null, 'rate' => 0.396),
		);
	}
	else
	{
		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
	}

	return $brackets;
}

function m_fetchUSFederalJointIncomeTaxBrackets($year)
{
	$brackets = null;
	if ($year == 2012)
	{
		$brackets = array(
			array('start' =>      0, 'stop' =>  17400, 'rate' => 0.10),
			array('start' =>  17401, 'stop' =>  70700, 'rate' => 0.15),
			array('start' =>  70701, 'stop' => 142700, 'rate' => 0.25),
			array('start' => 142701, 'stop' => 217450, 'rate' => 0.28),
			array('start' => 217451, 'stop' => 388350, 'rate' => 0.33),
			array('start' => 388351, 'stop' =>   null, 'rate' => 0.35),
		);
	}
	else if ($year == 2013)
	{
		$brackets = array(
			array('start' =>      0, 'stop' =>   8700, 'rate' => 0.15),
			array('start' =>   8701, 'stop' =>  35350, 'rate' => 0.15),
			array('start' =>  35351, 'stop' =>  85650, 'rate' => 0.28),
			array('start' =>  85651, 'stop' => 178650, 'rate' => 0.31),
			array('start' => 178651, 'stop' => 388350, 'rate' => 0.36),
			array('start' => 388351, 'stop' =>   null, 'rate' => 0.396),
		);
	}
	else
	{
		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
	}

	return $brackets;
}


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
		//echo "[income] Deductions: $deductions\n";
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
			//echo "[income] money in bracket: $moneyInBracket\n";

			$taxLiability = $taxLiability->add($moneyInBracket->mul($bracket->rate));

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
		//echo "[income] Standard deduction: $standardDeduction\n";
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
				return 5950;
			}
			else if ($this->taxMode == API_Types_TaxMode::JOINT)
			{
				return 9900;
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
			$bracketsInfo = m_fetchUSFederalIndividualIncomeTaxBrackets($this->year);
		}
		else if ($this->taxMode == API_Types_TaxMode::JOINT)
		{
			$bracketsInfo = m_fetchUSFederalJointIncomeTaxBrackets($this->year);
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

abstract class FICA_TaxLogic extends Scaffold_GenericTaxLogic
{
	protected $employmentType;

	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions)
	{
		$this->setEmploymentType(API_Types_Employment::EMPLOYEE);
		parent::__construct($taxMode, $year, $grossIncome, $deductions);
	}

	public function setEmploymentType($employmentType)
	{
		if ($employmentType != API_Types_Employment::EMPLOYEE && $employmentType != API_Types_Employment::SELF)
		{
			throw new InvalidArgumentException("FICA: Invalid employment type.");
		}

		$this->employmentType = $employmentType;
	}
}

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

class US_Medicare_TaxLogic extends FICA_TaxLogic implements API_TaxLogic
{
	/**
	 * @return fMoney
	 */
	public function calculateTaxLiability()
	{
		$taxableIncome = $this->grossIncome;
		//echo "[medicare] Tax rate: " . $this->fetchTaxRate() . "\n";
		$taxLiability = $taxableIncome->mul($this->fetchTaxRate());

		return $taxLiability;
	}

	protected function fetchTaxRate()
	{
		if ($this->year >= 2012)
		{
			if ($this->employmentType == API_Types_Employment::SELF)
			{
				return 0.029;
			}
			else
			{
				return 0.0145;
			}
		}
		else
		{
			throw new InvalidArgumentException("Medicare tax data for the year '{$this->year}' is currently unavailable.");
		}
	}

}

class US_AdditionalMedicare_TaxLogic extends FICA_TaxLogic implements API_TaxLogic
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

		return $taxLiability;
	}

	protected function fetchMaxTaxableIncome()
	{
		if ($this->taxMode == API_Types_TaxMode::SINGLE)
		{
			return 200000;
		}
		else if ($this->taxMode == API_Types_TaxMode::JOINT)
		{
			return 2500000;
		}
		else if ($this->taxMode == API_Types_TaxMode::SEPARATE)
		{
			return 125000;
		}
		else if ($this->taxMode == API_Types_TaxMode::HOUSEHEAD)
		{
			return 200000;
		}
		else if ($this->taxMode == API_Types_TaxMode::WIDOWER)
		{
			return 200000;
		}
		else
		{
			throw new LogicException("Additional Medicare Tax: Mode '{$this->taxMode}' data is not currently available.");
		}
	}

	protected function fetchTaxRate()
	{
		if ($this->year == 2013)
		{
			return 0.009;
		}
		else
		{
			throw new InvalidArgumentException("Social Security tax data for the year '{$this->year}' is currently unavailable.");
		}
	}
}


class US_FederalIncomeTaxCalculator extends Scaffold_GenericTaxLogic
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
		$taxLiabilities->grossIncome = $this->grossIncome->format();
		$taxLiabilities->federalIncomeTax = $this->taxLiabilities['income']->format();
		$taxLiabilities->ssiTax = $this->taxLiabilities['ssi']->format();
		$taxLiabilities->medicareTax = $this->taxLiabilities['medicare']->format();

		// Don't forget Obama's *stupid* Additional Medicare Tax ;-/
		if ($this->year >= 2013)
		{
			$taxLiabilities->addMedicareTax = $this->taxLiabilities['addMedicare']->format();
		}

		$taxLiabilities->totalTaxes = $this->totalTaxes->format();
		$taxLiabilities->netIncome = $this->grossIncome->sub($this->totalTaxes)->format();

		return $taxLiabilities;
	}
}

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
		if ($year >= 2013)
		{
			$addMedicareLogic = new US_AdditionalMedicare_TaxLogic($taxMode, $year, $grossIncome, $deductions);
			$addMedicareLogic->setEmploymentType($employmentType);
			$taxCalc->addTaxLogic('addMedicare', $addMedicareLogic);
		}

		return $taxCalc;
	}
}

// Front-ctrl
if (!empty($_GET['income']))
{
	if (($income = filter_var($_GET['income'], FILTER_SANITIZE_NUMBER_FLOAT)) === false)
	{
		$errorMessage = "Invalid input: The Income value must be a number.";
	}
	if (($deductions = filter_var($_GET['deductions'], FILTER_SANITIZE_NUMBER_FLOAT)) === false)
	{
		$errorMessage = "Invalid input: The Deductions value must be a number.";
	}
/*
	if (($year = filter_var($_GET['year'], FILTER_SANITIZE_NUMBER_INT)) === false)
	{
		$errorMessage = "Invalid input: The Year value must be a whole number2." . $_GET['year'];
	}
*/
	if (($taxMode = filter_var($_GET['mode'], FILTER_SANITIZE_STRING)) === false)
	{
		$errorMessage = "Invalid input: The tax mode is invalid.";
	}
	if (($employmentType = filter_var($_GET['employment_type'], FILTER_SANITIZE_STRING)) === false)
	{
		$errorMessage = "Invalid input: The employment type is invalid.";
	}

	fMoney::setDefaultCurrency('USD');

	$years = array(2012, 2013);
	$taxReport = array();
	foreach ($years as $year)
	{
		$taxCalc = USFederalPersonalIncomeTaxesFactory::build($taxMode, $year, new fMoney($income), new fMoney($deductions), $employmentType);
		$taxCalc->calculateTaxLiabilities();
		$taxReport[$year] = $taxCalc->getTaxLiabilityReport();
	}
}

$e_income = (!empty($income)) ? htmlspecialchars($income) : '0';
$e_deductions = (!empty($deductions)) ? htmlspecialchars($deductions) : '0';
$e_mode = (!empty($taxMode)) ? htmlspecialchars($taxMode) : '';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Federal Income Tax Calculator</title>
		<!-- TODO: Split into separate file -->
		<style type="text/css">
table th { text-align: left; }
table td input, table.report td { text-align: right; }
		</style>
	</head>
	<body>
		<h1>Federal Income Tax Calculator</h1>
		<div>
<?php
	if (!empty($errorMessage))
	{
?>
			<div class="errorMessage">
				<h3>ERROR:</h3>
				<div><?php echo $errorMessage; ?></div>
			</div>
<?php
	}
?>
			<p>Enter tax information...</p>
			<form method="get">
				<table>
					<tr>
						<th><label for="income">Gross income: </label></th>
						<td><input type="text" name="income" value="<?php echo $e_income; ?>"/></td>
					</tr>
					<tr>
						<th><label for="deductions">Deductions: </label></th>
						<td><input type="text" name="deductions" value="<?php echo $e_deductions; ?>"/></td>
					</tr>
<!--
					<tr>
						<th><label for="year">Tax Year: </label></th>
						<td>
							<select name="year">
								<option>2012</option>
								<option>2013</option>
							</select>
						</td>
					</tr>
-->
					<tr>
						<th><label for="mode">Mode:</label></th>
						<td>
							<select name="mode">
								<option value="<?php echo API_Types_TaxMode::SINGLE; ?>"<?php echo (!empty($taxMode) && $taxMode == API_Types_TaxMode::SINGLE) ? ' selected="selected"' : ''; ?>>Single</option>
								<option value="<?php echo API_Types_TaxMode::JOINT; ?>"<?php echo (!empty($taxMode) && $taxMode == API_Types_TaxMode::JOINT) ? ' selected="selected"' : ''; ?>>Married: Joint</option>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="employment_type">Employment type: </label></th>
						<td>
							<select name="employment_type">
								<option value="<?php echo API_Types_Employment::EMPLOYEE; ?>"
									<?php echo (!empty($employmentType) && $employmentType == API_Types_Employment::EMPLOYEE) ? ' selected="selected"' : ''; ?>>Employee</option>
								<option value="<?php echo API_Types_Employment::SELF; ?>"
									<?php echo (!empty($employmentType) && $employmentType == API_Types_Employment::SELF) ? ' selected="selected"' : ''; ?>>Self-Employeed</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2">
							<input type="submit" value="calculate"/>
						</td>
					</tr>
				</table>
			</form>
<?php
	if (!empty($taxReport))
	{
?>
			<div id="tax_liability">
				<h3>Tax Liability</h3>
				<table class="report">
					<tr>
						<td>&nbsp;</td>
						<th style="text-align: center">2012</th>
						<th style="text-align: center">2013</th>
					</tr>
					<tr>
						<th>Mode: </th>
						<td colspan="2"><?php echo $e_mode; ?></td>
					</tr>
					<tr>
						<th>Gross Income:</th>
						<td><?php echo $taxReport[2012]->grossIncome; ?></td>
						<td><?php echo $taxReport[2013]->grossIncome; ?></td>
					</tr>
					<tr>
						<th>Federal Income Tax:</th>
						<td><?php echo $taxReport[2012]->federalIncomeTax; ?></td>
						<td><?php echo $taxReport[2013]->federalIncomeTax; ?></td>
					</tr>
					<tr>
						<th>Social Security Tax:</th>
						<td><?php echo $taxReport[2012]->ssiTax; ?></td>
						<td><?php echo $taxReport[2013]->ssiTax; ?></td>
					</tr>
					<tr>
						<th>Medicare Tax:</th>
						<td><?php echo $taxReport[2012]->medicareTax; ?></td>
						<td><?php echo $taxReport[2013]->medicareTax; ?></td>
					</tr>
					<tr>
						<th>Additional Medicare Tax:</th>
						<td><?php echo $taxReport[2012]->addMedicareTax; ?></td>
						<td><?php echo $taxReport[2013]->addMedicareTax; ?></td>
					</tr>
					<tr>
						<th>Total Tax Liability: </th>
						<td><?php echo $taxReport[2012]->totalTaxes; ?></td>
						<td><?php echo $taxReport[2013]->totalTaxes; ?></td>
					</tr>
					<tr>
						<th>Net Income:</th>
						<td><?php echo $taxReport[2012]->netIncome; ?></td>
						<td><?php echo $taxReport[2013]->netIncome; ?></td>
					</tr>
				</table>
			</div>
<?php
	}
?>
		</div>
	</body>
</html>
