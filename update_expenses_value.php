<?php
require_once("config.php");

$expense_query = mysqli_query($mysqli, "SELECT * FROM expenses");
$currentDate = date('YYYY-mm-dd');
$currentYear = intval(date('Y'));
$currentMonth = intval(date('m'));
$currentDay = intval(date('d'));
$total_current_expense_value = 0;

while($row = mysqli_fetch_array($expense_query))
{
    $expense_id = $row['expense_id'];
    $expense_date = $row['expense_date'];
    $expense_date_parts = explode('-', $expense_date);
    $expense_date_year = intval($expense_date_parts[0]);
    $expense_date_month = intval($expense_date_parts[1]);
    $expense_date_day = intval($expense_date_parts[2]);
    $expense_current_value = intval($row['expense_current_value']);
    $expense_lifetime = intval($row['expense_lifetime']);  
    $expense_amount = intval($row['expense_amount']);
    $expense_current_value = intval($row['expense_current_value']);
    
    //update when new tax year
    if($expense_lifetime > 0)
    {
    	if ($expense_date_month <= 3)
    	{
    	    $no_passing_years = $currentYear - $expense_date_year + 1;
    	}
    	
    	else
    	{
    	    $no_passing_years = $currentYear - $expense_date_year;
    	}
    	
    	$depreciation_total = $expense_amount / $expense_lifetime * $no_passing_years;
    	
    	if($expense_current_value >= $depreciation_total)
    	{
    	    $updated_expense_current_value = $expense_amount - $depreciation_total;    	
    	}
    	else
    	{
    	    $updated_expense_current_value = 0;
    	    $expense_depreciation_anually = $depreciation_total - $expense_current_value;
    	    mysqli_query($mysqli,"UPDATE expenses SET expense_depreciation_anually = $expense_depreciation_anually WHERE expense_id = $expense_id");
    	}
    	
    	mysqli_query($mysqli,"UPDATE expenses SET expense_current_value = $updated_expense_current_value WHERE expense_id = $expense_id");
    }
}

?>
