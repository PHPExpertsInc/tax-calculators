<?php

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

