<?php
include_once '../libs//thrive/Autoloader.php';
ob_start();
new Thrive_Autoloader();

// Set DEBUG mode on always.
$_GET['debug'] = 1;

/**
 * @param $year
 * @return array|null
 * @throws InvalidArgumentException
 */
function fetchUSFederalIndividualIncomeTaxBrackets($year)
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
			array('start' =>      0, 'stop' =>   8950, 'rate' => 0.10),
			array('start' =>   8951, 'stop' =>  36250, 'rate' => 0.15),
			array('start' =>  36251, 'stop' =>  87850, 'rate' => 0.25),
			array('start' =>  87851, 'stop' => 183250, 'rate' => 0.28),
			array('start' => 183251, 'stop' => 398350, 'rate' => 0.33),
			array('start' => 398351, 'stop' => 400000, 'rate' => 0.35),
			array('start' => 400001, 'stop' =>   null, 'rate' => 0.396),
		);
	}
	else
	{
		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
	}

	return $brackets;
}

function fetchUSFederalJointIncomeTaxBrackets($year)
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
			array('start' =>      0, 'stop' =>  17900, 'rate' => 0.10),
			array('start' =>  17901, 'stop' =>  72500, 'rate' => 0.15),
			array('start' =>  72501, 'stop' => 146400, 'rate' => 0.25),
			array('start' => 146401, 'stop' => 223050, 'rate' => 0.28),
			array('start' => 223051, 'stop' => 398350, 'rate' => 0.33),
			array('start' => 398351, 'stop' => 450000, 'rate' => 0.35),
			array('start' => 450001, 'stop' =>   null, 'rate' => 0.396),
		);
	}
	else
	{
		throw new InvalidArgumentException("The tax bracket information for the year $year is currently unavailable.");
	}

	return $brackets;
}

// Front-ctrl
if (!empty($_GET['income']))
{
	if (($income = fRequest::get('income', 'float')) === false)
	{
		$errorMessage = "Invalid input: The Income value must be a number.";
	}
	if (($deductions = fRequest::get('deductions', 'float')) === false)
	{
		$errorMessage = "Invalid input: The Deductions value must be a number.";
	}
/*
	if (($year = filter_var($_GET['year'], FILTER_SANITIZE_NUMBER_INT)) === false)
	{
		$errorMessage = "Invalid input: The Year value must be a whole number2." . $_GET['year'];
	}
*/
	if (($taxMode = urldecode(filter_var($_GET['mode'], FILTER_SANITIZE_STRING))) === false)
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

	$totalTaxes2013 = $diff = new fMoney($taxReport[2013]->totalTaxes);
	$totalTaxes2012 = new fMoney($taxReport[2012]->totalTaxes);
	$diff = $diff->sub($totalTaxes2012);
	$percent = round((($diff->__toString())/($totalTaxes2012->__toString())) * 100, 2);
	if ($percent > 0) { $percent = "+$percent% more"; }
	else { $percent = "-$percent% less"; }
	$diffStr = $diff->format();
	$taxReport[2013]->totalTaxes = $totalTaxes2013->format() . "<br/><strong>($diffStr; $percent)</string>";
}

$e_income = (!empty($income)) ? htmlspecialchars($income) : '0';
$e_deductions = (!empty($deductions)) ? htmlspecialchars($deductions) : '0';
$e_mode = (!empty($taxMode)) ? htmlspecialchars($taxMode) : '';

$debugInfo = ob_get_clean();

include 'views/home.tpl.php';
