<?php

interface API_Tax
{
	public function getTaxLiability(fMoney $revenue, fMoney $deductions);
}
