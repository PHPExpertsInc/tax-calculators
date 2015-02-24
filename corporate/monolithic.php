<?php
// Iteration 3: Create Tax Drivers.
set_include_path(get_include_path() . PATH_SEPARATOR .
 realpath('../'));

// This uses my Thrive framework, which extends
// the Flourish framework.
// NOTE: Using require, or require_once has performance penalties that aren't
//       necessary.  If the file isn't loaded successfully, the app will crash
//       anyway.
include_once 'thrive/Autoloader.php';
new Thrive_Autoloader;

// Iteration 2: Create the Data Models.

// TIP: By always building out class datatypes instead of relying on hashmaps (array keys0, you
//      make it much easier to create what's called an API that people can easily use later.
class TaxBracket
{
	public $min;
	public $max;
	public $rate;
}

// Let's create an interface for our new taxing system.
interface API_Tax
{
	public function getTaxLiability(fMoney $revenue, fMoney $deductions);
}

// It makes more sense to use a Factory for this.
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

class TaxBracketManager
{
	public function fetchAll()
	{
		$bracketsInfo = array(
			array('min' => 0,        'max' => 50000,    'rate' => 0.15),
			array('min' => 50001,    'max' => 75000,    'rate' => 0.25),
			array('min' => 75001,    'max' => 100000,   'rate' => 0.34),
			array('min' => 100001,   'max' => 335000,   'rate' => 0.39),
			array('min' => 335001,   'max' => 10000000, 'rate' => 0.34),
			array('min' => 10000001, 'max' => 15000000, 'rate' => 0.35),
			array('min' => 15000001, 'max' => 18333333, 'rate' => 0.38),
			array('min' => 18333334, 'max' => null,     'rate' => 0.35),
		);

		$brackets = array();
		foreach ($bracketsInfo as $i)
		{
			$bracket = new TaxBracket;
			$bracket->min = $i['min'];
			$bracket->max = $i['max'];
			$bracket->rate = $i['rate'];

			$brackets[] = $bracket;
		}

		return $brackets;
	}
}

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

// Unemployment Insurance tax does not allow for deductions. It is off of the entire gross wage.
// Source: http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp
class UnemploymentTax implements API_Tax
{
	protected $numOfEmployees;

	public function __construct($numOfEmployees)
	{
		$this->setNumberOfEmployees($numOfEmployees);
	}

	public function setNumberOfEmployees($numOfEmployees)
	{
		$this->numOfEmployees = $numOfEmployees;
	}

	public function getTaxLiability(fMoney $revenue, fMoney $deductions)
	{
		if (empty($this->numOfEmployees))
		{
			throw new LogicException("Cannot calculate Unemployment tax without specifying the number of employees.");
		}

		$taxLiability = new fMoney(0, 'USD');
		for ($a = 0; $a < $this->numOfEmployees; ++$a)
		{
			$taxLiability = $taxLiability->add($this->calculateTaxLiability($revenue));
		}

		return $taxLiability;
	}

	protected function calculateTaxLiability(fMoney $taxableRevenue)
	{
		if ($taxableRevenue->lte($this->fetchMinimumAmountToTax()))
		{
			return new fMoney(0, 'USD');
		}

		$maxTaxLiability = $this->fetchMaxTaxLiability();
		$taxRate = $this->fetchTaxRate();

		$taxLiability = $taxableRevenue->mul($taxRate);

		if ($taxLiability->gte($maxTaxLiability))
		{
			$taxLiability = $maxTaxLiability;
		}

		if (isset($_GET['debug']))
		{
			echo "<pre>";
			echo "[unemployment] Taxable Revenue: $taxableRevenue\n";
			echo "[unemployment] Tax Rate: $taxRate\n";
			echo "[unemployment] Total Tax Liability: $taxLiability\n";
			echo "</pre>";
		}

		return $taxLiability;
	}

	protected function fetchMinimumAmountToTax()
	{
		return new fMoney(1500, 'USD');
	}

	protected function fetchMaxTaxLiability()
	{
		return new fMoney(56.00, 'USD');
	}

	protected function fetchTaxRate()
	{
		return 0.062;
	}
}


class FedTaxes
{
	/** @var fMoney */
	public $income;
	/** @var fMoney */
	public $ssi;
	/** @var fMoney */
	public $medicare;
	/** @var fMoney */
	public $unemployment;
	/** @var fMoney */
	public $total;

	public function __construct(fMoney $income, fMoney $ssi, fMoney $medicare,
	                            fMoney $unemployment, fMoney $total)
	{
		$this->income = $income;
		$this->ssi = $ssi;
		$this->medicare = $medicare;
		$this->unemployment = $unemployment;
		$this->total = $total;
	}
}

// Main execution path.
function getTaxLiability()
{
	if (!empty($_GET['gross_income']))
	{
		// I prefer filter_var($_GET) over filter_input(INPUT_GET) because it
		// is **impossible** to simulate form data in unit tests with filter_input().
		if (($grossIncome = filter_var($_GET['gross_income'], FILTER_SANITIZE_NUMBER_FLOAT)) === false)
		{
			throw new InvalidArgumentException("Invalid input for gross income, only numbers are accepted.");
		}
		if (($expenses = filter_var($_GET['expenses'], FILTER_SANITIZE_NUMBER_FLOAT)) === false)
		{
			throw new InvalidArgumentException("Invalid input for expenses, only numbers are accepted.");
		}
		if (($wages = filter_var($_GET['wages'], FILTER_SANITIZE_NUMBER_FLOAT)) === false)
		{
			throw new InvalidArgumentException("Invalid input for wages, only numbers are accepted.");
		}
		if (($numOfEmployees = filter_var($_GET['num_of_employees'], FILTER_SANITIZE_NUMBER_INT)) === false)
		{
			throw new InvalidArgumentException("Invalid input for number of employees, only whole numbers are accepted.");
		}

		fMoney::setDefaultCurrency('USD');
		$taxManager = USFederalTaxesFactory::create(new fMoney($grossIncome), new fMoney($expenses), new fMoney($wages), $numOfEmployees);
		$amountOwed = $taxManager->getTaxLiability();

		$fedTaxes = new FedTaxes(new fMoney(0, 'USD'), new fMoney(0, 'USD'), new fMoney(0, 'USD'), new fMoney(0, 'USD'), new fMoney(0, 'USD'));
		$fedTaxes->income = $amountOwed->format();
		$fedTaxes->ssi = $taxManager->getLiabilityByTax('ssi')->format();
		$fedTaxes->medicare = $taxManager->getLiabilityByTax('medicare')->format();
		$fedTaxes->unemployment = $taxManager->getLiabilityByTax('unemployment')->format();

		// TODO: fMoney **really** should be able to add multiple values at once. I mean, come on!
		$total = new fMoney(0, 'USD');
		foreach (array('income', 'ssi', 'medicare', 'unemployment') as $tax)
		{
			$total = $total->add($fedTaxes->$tax);
		}
		$fedTaxes->total = $total->format();

		return $fedTaxes;
	}

	return null;
}

$fedTaxes = null;
try
{
	$fedTaxes = getTaxLiability();
}
catch(InvalidArgumentException $e)
{
	$errorMessage = "Oops! An error has occurred:<br/>\n";
	$errorMessage .= $e->getMessage();
}
catch(Exception $e)
{
	$errorMessage = "Oops: An error has occured.";
	$errorMessage .= $e->getMessage();

	error_log($e->getMessage());
}

?>
<?php
// Start of the view.
$e_grossIncome = isset($_GET['gross_income']) ? htmlspecialchars($_GET['gross_income']) : '';
$e_expenses = isset($_GET['expenses']) ? htmlspecialchars($_GET['expenses']) : '';
$e_wages = isset($_GET['wages']) ? htmlspecialchars($_GET['wages']) : '';
$e_numOfEmployees = isset($_GET['num_of_employees']) ? htmlspecialchars($_GET['num_of_employees']) : '';

if (isset($errorMessage))
{
	$e_errorMessage = htmlspecialchars($errorMessage);
}
// Iteration 1: Create the HTML.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Federal Corporate Income Tax Calculator</title>
<style type="text/css">
th { text-align: left; }
td { text-align: right; }
</style>
	</head>
	<body>
		<h1>Federal Corporate Income Tax Calculator</h1>
		<p>This app is designed to calculate the estimated income tax of a corporation.</p>
		<h4>Disclaimer</h4>
		<p>This app is for educational purposes only. It is by no means a substitute for proper tax accounting services!</p>
		<p><em>Note: Because of <a href="http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp">Congressional bungling</a>, the Unemployment Taxrate
			for 2011 is overstated (usually by just 0.2%).</em></p>
<?php
if (isset($e_errorMessage)):
?>
		<div id="error_messag">
			<?php echo $e_errorMessage; ?>
		</div>
<?php endif; ?>
		<div id="income_form">
			<form method="get">
				<table id="income_data_table">
					<tr>
						<th><label for="gross_income">Gross income:</label></th>
						<td><input type="text" name="gross_income" value="<?php echo $e_grossIncome; ?>"/></td>
					</tr>
					<tr>
						<th><label for="expenses">Expenses:</label></th>
						<td><input type="text" name="expenses" value="<?php echo $e_expenses; ?>"/></td>
					</tr>
					<tr>
						<th><label for="wages">Wages:</label></th>
						<td><input type="text" name="wages" value="<?php echo $e_wages; ?>"/></td>
					</tr>
					<tr>
						<th><label for="expenses">No. of Employees:</label></th>
						<td><input type="text" name="num_of_employees" value="<?php echo $e_numOfEmployees; ?>"/></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" value="Calculate"></td>
					</tr>
				</table>
			</form>
		</div>
<?php
if ($fedTaxes instanceof FedTaxes):
?>
		<div id="taxes_data">
			<h3>Tax Information</h3>
			<table id="tax_data_table">
				<tr>
					<th>Income Tax: </th>
					<td><?php echo $fedTaxes->income; ?></td>
				</tr>
				<tr>
					<th>Social Security Tax:</th>
					<td><?php echo $fedTaxes->ssi; ?></td>
				</tr>
				<tr>
					<th>Medicare Tax:</th>
					<td><?php echo $fedTaxes->medicare; ?></td>
				</tr>
				<tr>
					<th><a href="http://workforcesecurity.doleta.gov/unemploy/uitaxtopic.asp">Unemployment Tax:</a></th>
					<td><?php echo $fedTaxes->unemployment; ?></td>
				</tr>
				<tr>
					<th>Total Liability:</th>
					<td><?php echo $fedTaxes->total; ?></td>
				</tr>
			</table>
		</div>
<?php endif; ?>
	</body>
</html>





