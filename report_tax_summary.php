<?php

require_once("inc_all_reports.php");
validateAccountantRole();

if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
} else {
    $year = date('Y');
}

//GET unique years from expenses, payments and revenues
$sql_all_years = mysqli_query($mysqli, "SELECT DISTINCT(YEAR(item_created_at)) AS all_years FROM invoice_items
UNION
SELECT DISTINCT(YEAR(revenue_date)) AS all_years FROM revenues
UNION
SELECT (SELECT MIN(EXTRACT(YEAR FROM item_created_at)) FROM invoice_items) - 1 AS all_years
UNION
SELECT (SELECT MIN(EXTRACT(YEAR FROM revenue_date)) FROM revenues) - 1 AS all_years
ORDER BY all_years DESC");

$sql_tax = mysqli_query($mysqli, "SELECT * FROM taxes ORDER BY tax_name ASC");

$sql_tax_period = mysqli_query($mysqli, "SELECT * FROM date_configurations WHERE date_configuration_name = 'tax year'");
$row;
$tax_year_start_day;
$tax_year_start_month;
$tax_year_end_day;
$tax_year_end_month;
if($sql_tax_period->num_rows>0)
{
    $row = $sql_tax_period->fetch_assoc();
    $tax_year_start_day = intval($row['date_configuration_start_day']);
    $tax_year_start_month = intval($row['date_configuration_start_month']);
    $tax_year_end_day = intval($row['date_configuration_end_day']);
    $tax_year_end_month = intval($row['date_configuration_end_month']);
}

?>

    <div class="card card-dark">
        <div class="card-header py-2">
            <h3 class="card-title mt-2"><i class="fas fa-fw fa-balance-scale mr-2"></i>Income Tax Summary</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
            </div>
        </div>
        <div class="card-body p-0">
            <form class="p-3">
                <select onchange="this.form.submit()" class="form-control" name="year">
                    <?php

                    while ($row = mysqli_fetch_array($sql_all_years)) {
                        $all_years = intval($row['all_years']);
                        ?>
                        <option <?php if ($year == $all_years) { echo "selected"; } ?> > <?php echo $all_years; ?></option>

                        <?php
                    }
                    ?>

                </select>
            </form>
            <?php
            $date_start = $year."-".$tax_year_start_month."-".$tax_year_start_day;
	    $next_year = $year + 1;
	    $date_end = $next_year."-".$tax_year_end_month."-".$tax_year_end_day;
            ?>
            <div class="table-responsive-sm">
                <table class="table table-sm">
                    <thead class="text-dark">
                    <tr>
                        <th>Tax</th>
                        <th class="text-right">Jan-Mar(Next year)</th>
                        <th class="text-right">Apr-Jun</th>
                        <th class="text-right">Jul-Sep</th>
                        <th class="text-right">Oct-Dec</th>
                        <th class="text-right">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($row = mysqli_fetch_array($sql_tax)) {
                        $tax_id = intval($row['tax_id']);
                        $tax_name = htmlentities($row['tax_name']);
                        ?>

                        <tr>
                            <td><?php echo $tax_name; ?></td>

                            <?php

                            $tax_collected_quarter_one = 0;

                            for($month = 1; $month<=3; $month++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(item_tax) AS tax_collected_for_month 
                                    FROM invoices, invoice_items 
                                    WHERE item_invoice_id = invoice_id
                                    AND invoice_status LIKE 'Paid' 
                                    AND item_tax_id = $tax_id 
                                    AND MONTH(invoice_date) = $month and invoice_date BETWEEN $date_start AND $date_end"
                                );				
                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $sql_tax_collected_revenue = mysqli_query($mysqli, "SELECT SUM(revenue_tax) AS tax_collected_for_month_revenue FROM revenues WHERE revenue_tax_id = $tax_id AND revenue_date between '$date_start' AND '$date_end' AND MONTH(revenue_date) = $month");
                                $row2 = mysqli_fetch_array($sql_tax_collected_revenue);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']) + floatval($row2['tax_collected_for_month_revenue']);
					
                                $tax_collected_quarter_one = $tax_collected_quarter_one + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_one, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_two = 0;

                            for($month = 4; $month <= 6; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(item_tax) AS tax_collected_for_month 
                                    FROM invoices, invoice_items 
                                    WHERE item_invoice_id = invoice_id
                                    AND invoice_status LIKE 'Paid' 
                                    AND item_tax_id = $tax_id 
                                    AND MONTH(invoice_date) = $month and invoice_date BETWEEN $date_start AND $date_end"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $sql_tax_collected_revenue = mysqli_query($mysqli, "SELECT SUM(revenue_tax) AS tax_collected_for_month_revenue FROM revenues WHERE revenue_tax_id = $tax_id AND revenue_date between '$date_start' AND '$date_end' AND MONTH(revenue_date) = $month");
                                $row2 = mysqli_fetch_array($sql_tax_collected_revenue);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']) + floatval($row2['tax_collected_for_month_revenue']);

                                $tax_collected_quarter_two = $tax_collected_quarter_two + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_two, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_three = 0;

                            for($month = 7; $month <= 9; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(item_tax) AS tax_collected_for_month 
                                    FROM invoices, invoice_items 
                                    WHERE item_invoice_id = invoice_id
                                    AND invoice_status LIKE 'Paid' 
                                    AND item_tax_id = $tax_id 
                                    AND MONTH(invoice_date) = $month and invoice_date BETWEEN $date_start AND $date_end"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $sql_tax_collected_revenue = mysqli_query($mysqli, "SELECT SUM(revenue_tax) AS tax_collected_for_month_revenue FROM revenues WHERE revenue_tax_id = $tax_id AND revenue_date between '$date_start' AND '$date_end' AND MONTH(revenue_date) = $month");
                                $row2 = mysqli_fetch_array($sql_tax_collected_revenue);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']) + floatval($row2['tax_collected_for_month_revenue']);

                                $tax_collected_quarter_three = $tax_collected_quarter_three + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_three, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_four = 0;

                            for($month = 10; $month <= 12; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(item_tax) AS tax_collected_for_month 
                                    FROM invoices, invoice_items 
                                    WHERE item_invoice_id = invoice_id
                                    AND invoice_status LIKE 'Paid' 
                                    AND item_tax_id = $tax_id 
                                    AND MONTH(invoice_date) = $month and invoice_date BETWEEN $date_start AND $date_end"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $sql_tax_collected_revenue = mysqli_query($mysqli, "SELECT SUM(revenue_tax) AS tax_collected_for_month_revenue FROM revenues WHERE revenue_tax_id = $tax_id AND revenue_date BETWEEN '$date_start' AND '$date_end' AND MONTH(revenue_date) = $month");
                                $row2 = mysqli_fetch_array($sql_tax_collected_revenue);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']) + floatval($row2['tax_collected_for_month_revenue']);
				
				
                                $tax_collected_quarter_four = $tax_collected_quarter_four + $tax_collected_for_month;
                            }

                            $total_tax_collected_four_quarters = $tax_collected_quarter_one + $tax_collected_quarter_two + $tax_collected_quarter_three + $tax_collected_quarter_four;

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_four, $session_company_currency); ?></td>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $total_tax_collected_four_quarters, $session_company_currency); ?></td>
                        </tr>

                        <?php

                    }

                    ?>

                    <tr>
                        <th>Total Taxes<br><br><br></th>
                        <?php

                        $tax_collected_total_quarter_one = 0;

                        for($month = 1; $month <= 3; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(item_tax) AS tax_collected_for_month 
                                FROM invoices, invoice_items 
                                WHERE item_invoice_id = invoice_id
                                AND invoice_status LIKE 'Paid'  
                                AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_one = $tax_collected_total_quarter_one + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_one, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_two = 0;

                        for($month = 4; $month <= 6; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(item_tax) AS tax_collected_for_month 
                                FROM invoices, invoice_items 
                                WHERE item_invoice_id = invoice_id
                                AND invoice_status LIKE 'Paid'  
                                AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_two = $tax_collected_total_quarter_two + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_two, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_three = 0;

                        for($month = 7; $month <= 9; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(item_tax) AS tax_collected_for_month 
                                FROM invoices, invoice_items 
                                WHERE item_invoice_id = invoice_id
                                AND invoice_status LIKE 'Paid'  
                                AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_three = $tax_collected_total_quarter_three + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_three, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_four = 0;

                        for($month = 10; $month <= 12; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(item_tax) AS tax_collected_for_month
                                FROM invoices, invoice_items 
                                WHERE item_invoice_id = invoice_id
                                AND invoice_status LIKE 'Paid'  
                                AND YEAR(invoice_date) = $year AND MONTH(invoice_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_four = $tax_collected_total_quarter_four + $tax_collected_for_month;
                        }

                        $tax_collected_total_all_four_quarters = $tax_collected_total_quarter_one + $tax_collected_total_quarter_two + $tax_collected_total_quarter_three + $tax_collected_total_quarter_four;

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_four, $session_company_currency); ?></th>




                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_all_four_quarters, $session_company_currency); ?></th>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

       
    </div>

<?php
$sql_all_years = mysqli_query($mysqli, "SELECT DISTINCT(YEAR(item_created_at)) AS all_years FROM invoice_items
UNION
SELECT DISTINCT(YEAR(revenue_date)) AS all_years FROM revenues
UNION
SELECT (SELECT MIN(EXTRACT(YEAR FROM item_created_at)) FROM invoice_items) - 1 AS all_years
UNION
SELECT (SELECT MIN(EXTRACT(YEAR FROM revenue_date)) FROM revenues) - 1 AS all_years
ORDER BY all_years DESC");

$sql_tax = mysqli_query($mysqli, "SELECT * FROM taxes ORDER BY tax_name ASC");

$sql_tax_period = mysqli_query($mysqli, "SELECT * FROM date_configurations WHERE date_configuration_name = 'tax year'");
$row;
$tax_year_start_day;
$tax_year_start_month;
$tax_year_end_day;
$tax_year_end_month;
if($sql_tax_period->num_rows>0)
{
    $row = $sql_tax_period->fetch_assoc();
    $tax_year_start_day = intval($row['date_configuration_start_day']);
    $tax_year_start_month = intval($row['date_configuration_start_month']);
    $tax_year_end_day = intval($row['date_configuration_end_day']);
    $tax_year_end_month = intval($row['date_configuration_end_month']);
}
?>

    <div class="card card-dark">
        <div class="card-header py-2">
            <h3 class="card-title mt-2"><i class="fas fa-fw fa-balance-scale mr-2"></i>Expense Tax Summary</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
            </div>
        </div>
        <div class="card-body p-0">
            <form class="p-3">
                <select onchange="this.form.submit()" class="form-control" name="year">
                <?php 
                    while($row = mysqli_fetch_array($sql_all_years)){
                    	$all_years = intval($row['all_years']);
                    	?>
                    	<option <?php if($year == $all_years){echo "selected";} ?> > <?php echo $all_years; ?> </option>
                    }
                
                    <?php
                    }
                ?>
                </select>
            </form>
            <?php
            $date_start = $year."-".$tax_year_start_month."-".$tax_year_start_day;
	    $next_year = $year + 1;
	    $date_end = $next_year."-".$tax_year_end_month."-".$tax_year_end_day;
            ?>
            <div class="table-responsive-sm">
                <table class="table table-sm">
                    <thead class="text-dark">
                    <tr>
                        <th>Tax</th>
                        <th class="text-right">Jan-Mar(Next Year)</th>
                        <th class="text-right">Apr-Jun</th>
                        <th class="text-right">Jul-Sep</th>
                        <th class="text-right">Oct-Dec</th>
                        <th class="text-right">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($row = mysqli_fetch_array($sql_tax)) {
                        $tax_id = intval($row['tax_id']);
                        $tax_name = htmlentities($row['tax_name']);
                        ?>

                        <tr>
                            <td><?php echo $tax_name; ?></td>

                            <?php

                            $tax_collected_quarter_one = 0;

                            for($month = 1; $month<=3; $month++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                    FROM expenses
                                    WHERE expense_tax_id = $tax_id
                                    AND expense_date BETWEEN '$date_start' AND '$date_end'
                                    AND MONTH(expense_date) = $month"
                                );				
                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']);
					
                                $tax_collected_quarter_one = $tax_collected_quarter_one + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_one, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_two = 0;

                            for($month = 4; $month <= 6; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                    FROM expenses
                                    WHERE expense_tax_id = $tax_id
                                    AND expense_date BETWEEN '$date_start' AND '$date_end'
                                    AND MONTH(expense_date) = $month"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);

                                $tax_collected_for_month = floatval($row['tax_collected_for_month']);
                                $tax_collected_quarter_two = $tax_collected_quarter_two + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_two, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_three = 0;

                            for($month = 7; $month <= 9; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                    FROM expenses
                                    WHERE expense_tax_id = $tax_id
                                    AND expense_date BETWEEN '$date_start' AND '$date_end'
                                    AND MONTH(expense_date) = $month"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                                $tax_collected_quarter_three = $tax_collected_quarter_three + $tax_collected_for_month;
                            }

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_three, $session_company_currency); ?></td>

                            <?php

                            $tax_collected_quarter_four = 0;

                            for($month = 10; $month <= 12; $month ++) {

                                $sql_tax_collected = mysqli_query(
                                    $mysqli,
                                    "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                    FROM expenses
                                    WHERE expense_tax_id = $tax_id
                                    AND expense_date BETWEEN '$date_start' AND '$date_end'
                                    AND MONTH(expense_date) = $month"
                                );

                                $row = mysqli_fetch_array($sql_tax_collected);
                                
                                $tax_collected_for_month = floatval($row['tax_collected_for_month']);
				
				
                                $tax_collected_quarter_four = $tax_collected_quarter_four + $tax_collected_for_month;
                            }

                            $total_tax_collected_four_quarters = $tax_collected_quarter_one + $tax_collected_quarter_two + $tax_collected_quarter_three + $tax_collected_quarter_four;

                            ?>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_quarter_four, $session_company_currency); ?></td>

                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $total_tax_collected_four_quarters, $session_company_currency); ?></td>
                        </tr>

                        <?php

                    }

                    ?>

                    <tr>
                        <th>Total Taxes<br><br><br></th>
                        <?php

                        $tax_collected_total_quarter_one = 0;

                        for($month = 1; $month <= 3; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                FROM expenses
                                WHERE expense_date BETWEEN '$date_start' AND '$date_end'
                                AND MONTH(expense_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_one = $tax_collected_total_quarter_one + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_one, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_two = 0;

                        for($month = 4; $month <= 6; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                FROM expenses
                                WHERE expense_date BETWEEN '$date_start' AND '$date_end'
                                AND MONTH(expense_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_two = $tax_collected_total_quarter_two + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_two, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_three = 0;

                        for($month = 7; $month <= 9; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                FROM expenses
                                WHERE expense_date BETWEEN '$date_start' AND '$date_end'
                                AND MONTH(expense_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_three = $tax_collected_total_quarter_three + $tax_collected_for_month;
                        }

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_three, $session_company_currency); ?></th>

                        <?php

                        $tax_collected_total_quarter_four = 0;

                        for($month = 10; $month <= 12; $month ++) {

                            $sql_tax_collected = mysqli_query(
                                $mysqli,
                                "SELECT SUM(expense_tax) AS tax_collected_for_month 
                                FROM expenses
                                WHERE expense_date BETWEEN '$date_start' AND '$date_end'
                                AND MONTH(expense_date) = $month"
                            );

                            $row = mysqli_fetch_array($sql_tax_collected);
                            $tax_collected_for_month = floatval($row['tax_collected_for_month']);

                            $tax_collected_total_quarter_four = $tax_collected_total_quarter_four + $tax_collected_for_month;
                        }

                        $tax_collected_total_all_four_quarters = $tax_collected_total_quarter_one + $tax_collected_total_quarter_two + $tax_collected_total_quarter_three + $tax_collected_total_quarter_four;

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_quarter_four, $session_company_currency); ?></th>




                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $tax_collected_total_all_four_quarters, $session_company_currency); ?></th>
                    </tr>
                    </tbody>
                </table>
            </div>
        <div>
    </div>

<?php require_once("footer.php");
