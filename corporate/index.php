<?php
// Iteration 3: Create Tax Drivers.

// This uses my Thrive framework, which extends
// the Flourish framework.
// NOTE: Using require, or require_once has performance penalties that aren't
//       necessary.  If the file isn't loaded successfully, the app will crash
//       anyway.
include_once '../libs/thrive/Autoloader.php';
new Thrive_Autoloader;

// Main execution path.
function getTaxLiability()
{
	if (!empty($_GET['gross_income']))
	{
		// I prefer filter_var($_GET) over filter_input(INPUT_GET) because it
		// is **impossible** to simulate form data in unit tests with filter_input().
		if (($grossIncome = fRequest::get('gross_income', 'float')) === false)
		{

			throw new InvalidArgumentException("Invalid input for gross income, only numbers are accepted.");
		}
		echo "Gross income: " . $grossIncome;
		if (($expenses = fRequest::get('expenses', 'float' )) === false)
		{
			throw new InvalidArgumentException("Invalid input for expenses, only numbers are accepted.");
		}
 		if (($wages =  fRequest::get('wages', 'float')) === false)
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

// Load the template file.
include 'views/home.tpl.php';
