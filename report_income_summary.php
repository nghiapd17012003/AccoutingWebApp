<?php

require_once("inc_all_reports.php");
validateAccountantRole();

if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
} else {
    $year = date('Y');
}

$sb = "revenue_date";
$o = "DESC";

$url_query_strings_sb = http_build_query(array_merge($_GET, array('sb' => $sb, 'o' => $o)));

$total_of_total = 0;

$sql_payment_years = mysqli_query($mysqli, "SELECT DISTINCT YEAR(payment_date) AS payment_year FROM payments
    UNION SELECT DISTINCT YEAR(revenue_date) AS payment_year FROM revenues 
    UNION SELECT(SELECT MIN(EXTRACT(YEAR FROM payment_date)) FROM payments) - 1 as payment_year
    UNION SELECT(SELECT MIN(EXTRACT(YEAR FROM revenue_date)) FROM revenues) - 1 as payment_year
    ORDER BY payment_year DESC");
 

$sql_categories = mysqli_query($mysqli, "SELECT * FROM categories WHERE category_type = 'Income' ORDER BY category_name ASC");

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

<style>
.dropdown-check-list .anchor{
    position: relative;
    cursor: pointer;
    display: inline-block;
    padding: 5px 50px 5px 10px;
    border: 1px solid #ccc;
}

.dropdown-check-list .anchor:after {
    position: absolute;
    content: "";
    border-left: 2px solid black;
    padding: 5px;
    right: 10px;
    top: 20%;
    -moz-transform: rotate(-135deg);
    -ms-transform: rotate(-135deg);
    -o-transform: rotate(-135deg);
    -webkit-transform: rotate(-135deg);
    transform: rotate(-135deg);
}

.dropdown-check-list .anchor:active:after{
   right: 8px;
   top: 21%
}

.dropdown-check-list ul.items{
    padding: 2px;
    display: none;
    margin: 0;
    border: 1px solid #ccc;
    border-top: none;
}

.dropdown-check-list ul.items li {
    list-style: none;
}

.dropdown-check-list.visible .anchor{
    color: #0094ff;
}

.dropdown-check-list.visible .items{
    display: block;
}
</style>
<div class="card card-dark">
    <div class="card-header py-2">
        <h3 class="card-title mt-2"><i class="fas fa-fw fa-coins mr-2"></i>Income Summary</h3>
        
        <div class="card-tools">
            <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
        </div>
    </div>
    <div class="card-body p-0">
    <form class="mb-4" autocomplete="off">
        <div class="row">
            <div class="col-sm-4">
                <div class="input-group">
                    
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#advancedFilter"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="collapse mt-3 <?php if (!empty($_GET['dtf'])) { echo "show"; } ?>" id="advancedFilter">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" class="form-control" name="dtf" max="2999-12-31" value="<?php echo htmlentities($dtf); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" class="form-control" name="dtt" max="2999-12-31" value="<?php echo htmlentities($dtt); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group-append">
                        <button class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        if(!empty($_GET['dtf'])&&!empty($_GET['dtt'])) 
        {
            $date_start = $_GET['dtf']; $date_end = $_GET['dtt'];
            $flag = true;
        }
        else
        {
            $date_start = $year."-".$tax_year_start_month."-".$tax_year_start_day;
	    $next_year = $year + 1;
	    $date_end = $next_year."-".$tax_year_end_month."-".$tax_year_end_day;
        }
        
        ?>
        </form>
        <form class="p-3">
            <select onchange="this.form.submit()" class="form-control" name="year">
            
            	<option>-</option>
                <?php

                while ($row = mysqli_fetch_array($sql_payment_years)) {
                    $payment_year = intval($row['payment_year']);
                    ?>
                    
                    <option <?php if ($year == $payment_year) { ?> selected<?php } ?> > <?php echo $payment_year; ?></option>
                    <?php
                }
                ?>

            </select>
        </form>

        <canvas id="cashFlow" width="100%" height="20"></canvas>

        <div class="table-responsive-sm">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Category</th>              
                    <th class="text-right">April</th>
                    <th class="text-right">May</th>
                    <th class="text-right">June</th>
                    <th class="text-right">July</th>
                    <th class="text-right">August</th>
                    <th class="text-right">September</th>
                    <th class="text-right">October</th>
                    <th class="text-right">November</th>
                    <th class="text-right">December</th>
                    <th class="text-right">January</th>
                    <th class="text-right">February</th>
                    <th class="text-right">March</th>
                    <th class="text-right">Total</th>
                </tr>
                </thead>
                <tbody>
                <?php
                while ($row = mysqli_fetch_array($sql_categories)) {
                    $category_id = intval($row['category_id']);
                    $category_name = htmlentities($row['category_name']);

                    ?>

                    <tr>
                        <td><?php echo $category_name; ?></td>

                        <?php

                        $total_payment_for_all_months = 0;

                        for($month = 4; $month<=15; $month++) {
                            //Payments to Invoices
                            	if($month == 12)
				{
			    	    $month_correct = 12;
				}
				else
				{
				    $month_correct = $month % 12;
				}
                            $sql_payments = mysqli_query($mysqli, "SELECT SUM(payment_amount) AS payment_amount_for_month FROM payments, invoices WHERE payment_invoice_id = invoice_id AND invoice_category_id = $category_id AND MONTH(payment_date) = $month_correct AND payment_date BETWEEN '$date_start' AND '$date_end'");
                            $row = mysqli_fetch_array($sql_payments);
                            $payment_amount_for_month = floatval($row['payment_amount_for_month']);

                            //Revenues
                            $sql_revenues = mysqli_query($mysqli, "SELECT SUM(revenue_amount) AS revenue_amount_for_month FROM revenues WHERE revenue_category_id = $category_id AND MONTH(revenue_date) = $month_correct AND revenue_date BETWEEN '$date_start' AND '$date_end'");
                            $row = mysqli_fetch_array($sql_revenues);
                            $revenues_amount_for_month = floatval($row['revenue_amount_for_month']);

                            $payment_amount_for_month = $payment_amount_for_month + $revenues_amount_for_month;
                            $total_payment_for_all_months = $payment_amount_for_month + $total_payment_for_all_months;


                            ?>
                            <td class="text-right"><?php echo numfmt_format_currency($currency_format, $payment_amount_for_month, $session_company_currency); ?></td>

                            <?php

                        }

                        ?>

                        <td class="text-right text-bold"><?php echo numfmt_format_currency($currency_format, $total_payment_for_all_months, $session_company_currency); ?></td>
                    </tr>

                    <?php
                    $total_of_total += $total_payment_for_all_months;

                }

                ?>

                <tr>
                    <th>Total</th>
                    <?php

                    for($month = 4; $month<=15; $month++) {
                    	if($month == 12)
			{
		    	    $month_correct = 12;
			}
			else
			{
			    $month_correct = $month % 12;
			}
                        $sql_payments = mysqli_query($mysqli, "SELECT SUM(payment_amount) AS payment_total_amount_for_month FROM payments, invoices WHERE payment_invoice_id = invoice_id AND MONTH(payment_date) = $month_correct AND payment_date BETWEEN '$date_start' AND '$date_end'");
                        $row = mysqli_fetch_array($sql_payments);
                        $payment_total_amount_for_month = floatval($row['payment_total_amount_for_month']);

                        $sql_revenues = mysqli_query($mysqli, "SELECT SUM(revenue_amount) AS revenue_amount_for_month FROM revenues WHERE revenue_category_id > 0 AND MONTH(revenue_date) = $month_correct AND revenue_date BETWEEN '$date_start' AND '$date_end'");
                        $row = mysqli_fetch_array($sql_revenues);
                        $revenues_total_amount_for_month = floatval($row['revenue_amount_for_month']);

                        $payment_total_amount_for_month = $payment_total_amount_for_month + $revenues_total_amount_for_month;


                        $total_payment_for_all_months = $payment_total_amount_for_month + $total_payment_for_all_months;

                        ?>

                        <th class="text-right"><?php echo numfmt_format_currency($currency_format, $payment_total_amount_for_month, $session_company_currency); ?></th>
                        <?php

                    }

                    ?>

                    <th class="text-right"><?php echo numfmt_format_currency($currency_format, $total_of_total, $session_company_currency); ?></th>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



<div class="card card-dark">
    <div class="card-header py-2">
        <h3 class="card-title mt-2"><i class="fas fa-fw fa-coins mr-2"></i>Itemized Revenue Summary</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
        </div>
    </div>
    
    <div class="card-body">
    <form class="mb-4" autocomplete="off" method = "GET" action="report_income_summary.php">
        <div class="row">
            <div class="col-sm-4">
                <div class="input-group">
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#advancedFilter"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="float-right">
                    <button type="button" class="btn btn-default btn-lg" data-toggle="modal" data-target="#exportIncomeReportModal"><i class="fas fa-fw fa-download mr-2"></i>Export</button>
                </div>
            </div>
        </div>
        
        <div class="collapse mt-3" id="advancedFilter">
	      <div class="row">
		 <div class="col-md-2">
		    <div class="form-group">
		       <label>Category</label>
		       
		    </div>
		 </div>
		 <div id="categoryFilter" class="dropdown-check-list">
		     <span class="anchor" id="selectCategory">select</span>
		     <ul class="items">
		     </ul>
		 </div>
	      </div>
        </div>        
    </form>
        <div class="table-responsive-sm">
            <table class="table table-striped table-borderless table-hover" id="incomes_report_table">
            <?php 
		$sorted_method = $_GET['sorted_method'];
		$sorted_order = $_GET['sorted_order'];
            	if(is_null($sorted_method))
            	{
            	    $sorted_method = 'revenue_amount'; //default option	    	              	    
            	}
            	
            	if(is_null($sorted_order))
            	{
            	    $sorted_order = 'DESC';
            	}
            	$sql_itemized_revenue_query = mysqli_query($mysqli, "SELECT SQL_CALC_FOUND_ROWS * FROM revenues		    
		    LEFT JOIN taxes ON revenue_tax_id = tax_id
		    LEFT JOIN categories ON revenue_category_id = category_id
		    WHERE revenue_category_id > 0
		    ORDER BY $sb $o");
		    
		$sql_itemized_payment_query = mysqli_query($mysqli, "SELECT SQL_CALC_FOUND_ROWS * FROM payments");
		
	    ?>
	    	<tr>	    
	    	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=revenue_date&o=<?php echo $disp; ?>">Date</a></th>
	    	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=category_name&o=<?php echo $disp; ?>">Category</a></th>              
	    	    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=revenue_amount&o=<?php echo $disp; ?>">Amount (inc Tax) </a></th>
		    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=revenue_net_profit&o=<?php echo $disp; ?>">Net profit</a></th>
	    	    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=revenue_tax&o=<?php echo $disp; ?>">Tax Paid</a></th>
		    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=tax_name&o=<?php echo $disp; ?>">Tax Name</a></th>
		    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=tax_percent&o=<?php echo $disp; ?>">Tax Percent</a></th>
	    	</tr>
	    <?php
	    	 while($row =  mysqli_fetch_array($sql_itemized_revenue_query))
	    	 {
	    	     $revenue_id = $row['revenue_id'];
	    	     $revenue_date = $row['revenue_date'];
	    	     $category_name = $row['category_name'];
	    	     $revenue_amount = $row['revenue_amount'];
	    	     $revenue_net_profit = $row['revenue_net_profit'];
	    	     $revenue_tax = $row['revenue_tax'];
	    	     $tax_name = $row['tax_name'];
	    	     $tax_percent = $row['tax_percent'];
	    	     $revenue_currency_code = $row['revenue_currency_code'];
	    	     $date = new DateTime($revenue_date);
                     $non_american_format_date = $date->format('d-m-Y');?>
                     <tr>
		    	<td><?php echo $non_american_format_date;?></td>
		    	<td><?php echo $category_name;?></td>
		    	<td class="text-bold"><?php echo numfmt_format_currency($currency_format, $revenue_amount, $revenue_currency_code); ?></td>
		    	<td class="text-bold"><?php echo numfmt_format_currency($currency_format, $revenue_net_profit, $revenue_currency_code); ?></td>
		    	<td class="text-bold"><?php echo numfmt_format_currency($currency_format, $revenue_tax, $revenue_currency_code); ?></td>
		        <td class="text-bold"><?php echo $tax_name;?></td>     
		        <td class="text-bold"><?php echo $tax_percent . '%';?></td>  
                     </tr>
                     <?php
	    	 }
	    	 
	    ?>
            </table>
            
    	</div>
    </div>
</div>


<?php require_once("footer.php"); 
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
	$(document).ready(function(){
	    function filterTable()
	    {
	    	var selectedValue = [];
	    	// Show all
	    	$('#incomes_report_table tbody tr').show();
	    	
	    	// Filter by category
	    	if ($('.category-filter:checked').length > 0) {
		  $('#incomes_report_table tbody tr:not(:first-child)').filter(function() {
		    var category = $(this).find("td:eq(1)").text();
		    return !$('.category-filter[category="' + category + '"]').prop('checked');
		  }).hide();
		}
	    }
	    
	    var categories = [];
	    $('#incomes_report_table tbody tr').each(function() {
	        var category = $(this).find("td:eq(1)").text();
		if (!categories.includes(category)) {
		  categories.push(category);
		  var checkbox = $('<input type="checkbox" class="category-filter">').attr('category', category);
		  var label = $('<label>').append(checkbox, category);
		  $('#categoryFilter .items').append(label);
		  $('#categoryFilter .items').append($('<br>'));
		  
		}
	      });
	      
	    function showCheckBoxes(id)
	    {
	    	var dropdown = document.getElementById(id);
	    	var checkboxes = dropdown.querySelector(".items");
	    	checkboxes.style.display = checkboxes.style.display === "block" ? "none" : "block";
	    }
	    document.getElementById('selectCategory').addEventListener("click", function(){showCheckBoxes("categoryFilter")}); 
	    
	    $('#categoryFilter').on('change', '.category-filter', filterTable);
	});
</script>
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    // Area Chart Example
    var ctx = document.getElementById("cashFlow");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar"],
            datasets: [{
                label: "Income",
                fill: false,
                borderColor: "#007bff",
                pointBackgroundColor: "#007bff",
                pointBorderColor: "#007bff",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "#007bff",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: [
                    <?php

                    for ($month = 4; $month<=15; $month++) {
                    
                    if($month == 12)
                    {
                    	$month_correct = 12;
                    }
                    else
                    {
                    	$month_correct = $month % 12;
                    }
                    $sql_payments = mysqli_query($mysqli, "SELECT SUM(payment_amount) AS payment_amount_for_month FROM payments, invoices WHERE payment_invoice_id = invoice_id AND MONTH(payment_date) = $month_correct AND payment_date BETWEEN '$date_start' AND '$date_end'");
                    $row = mysqli_fetch_array($sql_payments);
                    $payments_for_month = floatval($row['payment_amount_for_month']);

                    $sql_revenues = mysqli_query($mysqli, "SELECT SUM(revenue_amount) AS revenue_amount_for_month FROM revenues WHERE revenue_category_id > 0 AND MONTH(revenue_date) = $month_correct AND revenue_date BETWEEN '$date_start' AND '$date_end'");
                    $row = mysqli_fetch_array($sql_revenues);
                    $revenues_for_month = floatval($row['revenue_amount_for_month']);

                    $income_for_month = $payments_for_month + $revenues_for_month;

                    if ($income_for_month > 0 && $income_for_month > $largest_income_month) {
                        $largest_income_month = $income_for_month;
                    }


                    ?>
                    <?php echo "$income_for_month,"; ?>

                    <?php

                    }

                    ?>

                ],
            }],
        },
        options: {
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 12
                    }
                }],
                yAxes: [{
                    ticks: {
                        min: 0,
                        max: <?php $max = max(1000, $largest_income_month, $largest_invoice_month); echo roundUpToNearestMultiple($max); ?>,
                        maxTicksLimit: 5
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, .125)",
                    }
                }],
            },
            legend: {
                display: false
            }
        }
    });

</script>
<?php require_once("report_income_export_modal.php");?>

