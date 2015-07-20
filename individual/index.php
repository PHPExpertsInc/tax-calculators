<?php
include_once '../libs//thrive/Autoloader.php';
ob_start();
new Thrive_Autoloader();

// Set DEBUG mode on always.
$_GET['debug'] = 1;

if (isset($_GET['country']) && in_array(trim($_GET['country']), array('US'))) {
	$country = trim($_GET['country']);
}
if (!isset($country)) {
	$country = 'US';
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
		$taxCalc = call_user_func($country.'FederalPersonalIncomeTaxesFactory::build', $taxMode, $year, new fMoney($income), new fMoney($deductions), $employmentType);
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
