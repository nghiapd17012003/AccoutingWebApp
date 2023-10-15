<?php
$date = sanitizeInput($_POST['date']);
$amount = floatval($_POST['amount']);
$expense_tax_paid = floatval($_POST['expense_tax_paid']);
$expense_tax_id = intval($_POST['expense_tax_id']);
$account = intval($_POST['account']);
$vendor = intval($_POST['vendor']);
$client = intval($_POST['client']);
$category = intval($_POST['category']);
$description = sanitizeInput($_POST['description']);
$reference = sanitizeInput($_POST['reference']);
$expense_lifetime = intval($_POST['expense_lifetime']);
$expense_depreciation_account = intval($_POST['depreciation_account']);

if($expense_lifetime > 0)
{
    $expense_depreciation_annually = $amount/$expense_lifetime;
}
else
{
    $expense_depreciation_annually = 0;
}
?>
