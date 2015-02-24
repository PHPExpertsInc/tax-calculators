<?php

interface API_TaxLogic {
	// Setters
	public function __construct($taxMode, $year, fMoney $grossIncome, fMoney $deductions);
	public function setYear($year);
	public function setGrossIncome(fMoney $grossIncome);
	public function setDeductions(fMoney $deductions);
	public function setTaxMode($mode);
	public function calculateTaxLiability();
}
