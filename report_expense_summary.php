<?php
require_once("inc_all_reports.php");
validateAccountantRole();

if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
} else {
    $year = date('Y');
}
$sb = "expense_date";
$o = "DESC";

$url_query_strings_sb = http_build_query(array_merge($_GET, array('sb' => $sb, 'o' => $o)));
$sql_expense_years = mysqli_query($mysqli, "SELECT DISTINCT YEAR(expense_date) AS expense_year FROM expenses WHERE expense_category_id > 0 ORDER BY expense_year DESC");

$sql_categories = mysqli_query($mysqli, "SELECT * FROM categories WHERE category_type = 'Expense' ORDER BY category_name ASC");
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

$date_start = $year."-".$tax_year_start_month."-".$tax_year_start_day;
$next_year = $year + 1;
$date_end = $next_year."-".$tax_year_end_month."-".$tax_year_end_day;


?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" integrity="sha512-XMVd28F1oH/O71fzwBnV7HucLxVwtxf26XV8P4wPk26EDxuGZ91N8bsOttmnomcCD3CS5ZMRL50H0GgOHvegtg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.js"></script>

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
        <h3 class="card-title mt-2"><i class="fas fa-fw fa-coins mr-2"></i>Expense Summary</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
        </div>
    </div>
    <div class="card-body">
        <form class="mb-3">
            <select onchange="this.form.submit()" class="form-control" name="year">
                <?php

                while ($row = mysqli_fetch_array($sql_expense_years)) {
                    $expense_year = $row['expense_year'];
                    ?>
                    <option <?php if ($year == $expense_year) { ?> selected <?php } ?> > <?php echo $expense_year; ?></option>

                <?php } ?>

            </select>
        </form>

        <canvas id="cashFlow" width="100%" height="20"></canvas>

        <div class="table-responsive-sm">
            <table class="table table-striped">
                <thead class="text-dark">
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

                        $total_expense_for_all_months = 0;
                        for ($month = 4; $month<=15; $month++) {
                        if($month == 12)
			{
		    	    $month_correct = 12;
			}
			else
			{
			    $month_correct = $month % 12;
			}
                            $sql_expenses = mysqli_query($mysqli, "SELECT SUM(expense_amount) AS expense_amount_for_month FROM expenses WHERE expense_category_id = $category_id AND MONTH(expense_date) = $month_correct AND expense_date BETWEEN '$date_start' AND '$date_end'");
                            $row = mysqli_fetch_array($sql_expenses);
                            $expense_amount_for_month = floatval($row['expense_amount_for_month']);
                            $total_expense_for_all_months = $expense_amount_for_month + $total_expense_for_all_months;


                            ?>
                            <td class="text-right"><a class="text-dark" href="expenses.php?q=<?php echo $category_name; ?>&dtf=<?php echo "$year-$month"; ?>-01&dtt=<?php echo "$year-$month"; ?>-31"><?php echo numfmt_format_currency($currency_format, $expense_amount_for_month, $session_company_currency); ?></a></td>

                        <?php } ?>

                        <th class="text-right"><a class="text-dark" href="expenses.php?q=<?php echo $category_name; ?>&dtf=<?php echo $year; ?>-01-01&dtt=<?php echo $year; ?>-12-31"><?php echo numfmt_format_currency($currency_format, $total_expense_for_all_months, $session_company_currency); ?></a></th>
                    </tr>

                <?php } ?>

                <tr>
                    <th>Total</th>
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
                        $sql_expenses = mysqli_query($mysqli, "SELECT SUM(expense_amount) AS expense_total_amount_for_month FROM expenses WHERE MONTH(expense_date) = $month_correct AND expense_vendor_id > 0 AND expense_date BETWEEN '$date_start' AND '$date_end'");
                        $row = mysqli_fetch_array($sql_expenses);
                        $expense_total_amount_for_month = floatval($row['expense_total_amount_for_month']);
                        $total_expense_for_all_months = $expense_total_amount_for_month + $total_expense_for_all_months;


                        ?>

                        <th class="text-right"><a class="text-dark" href="expenses.php?dtf=<?php echo "$year-$month"; ?>-01&dtt=<?php echo "$year-$month"; ?>-31"><?php echo numfmt_format_currency($currency_format, $expense_total_amount_for_month, $session_company_currency); ?></a></th>

                    <?php } ?>

                    <th class="text-right"><a class="text-dark" href="expenses.php?dtf=<?php echo $year; ?>-01-01&dtt=<?php echo $year; ?>-12-31"><?php echo numfmt_format_currency($currency_format, $total_expense_for_all_months, $session_company_currency); ?></th>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card card-dark">
    <div class="card-header py-2">
        <h3 class="card-title mt-2"><i class="fas fa-fw fa-coins mr-2"></i>Itemized Expense Summary</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-fw fa-print mr-2"></i>Print</button>
        </div>   
    </div>
    
    <div class="card-body">
       <form class="mb-4" autocomplete="off" method = "GET" action="report_expense_summary.php">
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
		    <button type="button" class="btn btn-default btn-lg" id="exportBtn"><i class="fas fa-fw fa-download mr-2"></i>Export</button>
		 </div>
	      </div>
	   </div>
	   
	   <hr>
	   
	   <div class="collapse mt-3" id="advancedFilter">
	      <div class="row">
		 <div class="col-md-2">
		    <div class="form-group">
		       <label>Vendor</label>
		       
		    </div>
		 </div>
		 <div id="vendorFilter" class="dropdown-check-list">
		     <span class="anchor" id="selectVendor">Select</span>
		     <ul class="items">
		     </ul>
		 </div>
	      </div>
	  
	      <div class="row">
	       	  <div class="col-md-2">
		  <div class="form-group">
		    <label>Taxes</label>
		    
		  </div>
		</div>
		<div id="taxFilter" class="dropdown-check-list">
		  <span class="anchor" id="selectTax">Select</span>
		  <ul class="items">
		  </ul>
		</div>
	      </div>
	      
	      <div class="row">
	       	  <div class="col-md-2">
		  <div class="form-group">
		    <label>Category</label>
		    
		  </div>
		</div>
		<div id="categoryFilter" class="dropdown-check-list">
		  <span class="anchor" id="selectCategory">Select</spand>
		  <ul class="items">
		  </ul>
		</div>
	      </div>
		    
	      <!--
	      <div class="row">
	      	<div class="col-md-2">
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" class="form-control" name="dtf" max="2999-12-31" >
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" class="form-control" name="dtt" max="2999-12-31" >
                    </div>
                </div>
	      </div>
	      -->
	   </div>  
        </form>
	   
         <div class="table-responsive-sm">
            <table class="table table-striped table-borderless table-hover" id="expenses_report_table">
            <?php 
		$sorted_method = $_GET['sorted_method'];
		$sorted_order = $_GET['sorted_order'];
		
            	if(is_null($sorted_method))
            	{
            	    $sorted_method = 'expense_amount'; //default option	    	              	    
            	}
            	
            	if(is_null($sorted_order))
            	{
            	    $sorted_order = 'DESC';
            	}
            	
            	$dtf = "2000-01-01";
            	$dtt = "2999-12-31";
            	
            	
            	if(isset($_GET['dtf'])  && !empty($_GET['dtf']))
            	{
            	    $dtf = $_GET['dtf'];
            	    
		}
			
            	if(isset($_GET['dtt'])  && !empty($_GET['dtt']))
            	{
            	    $dtt = $_GET['dtt'];
		}
		
		$min = 0;
		$max = 99999999;
		
		if(isset($_GET['min'])  && !empty($_GET['min']))
            	{
            	    $min = $_GET['min'];
            	    
		}
			
            	if(isset($_GET['max'])  && !empty($_GET['max']))
            	{
            	    $max = $_GET['max'];
		}
            	   
		$tax_id_choosing_min = 0;
            	$tax_id_choosing_max = 9999;
            	//!emtpty needed here because when you clear the filter, $_GET['something'] is "" which is still considered as set variable.
		if(isset($_GET['tax_choosing_filter']) && !empty($_GET['tax_choosing_filter']))
		{
		    $tax_id_choosing_min = intval($_GET['tax_choosing_filter']);  
            	    $tax_id_choosing_max = intval($_GET['tax_choosing_filter']);
		}
		
		$vendor_id_choosing_min = 0;
		$vendor_id_choosing_max = 9999;
            	if(isset($_GET['vendor_choosing_filter']) && !empty($_GET['vendor_choosing_filter']))
            	{
            	    $vendor_id_choosing_min = $_GET['vendor_choosing_filter'];
            	    $vendor_id_choosing_max = $_GET['vendor_choosing_filter'];
		}
		
		$category_id_choosing_min = 0;
		$category_id_choosing_max = 9999;
		if(isset($_GET['category_choosing_filter']) && !empty($_GET['category_choosing_filter']))
		{
		    $category_id_choosing_min = $_GET['category_choosing_filter'];
		    $category_id_choosing_max = $_GET['category_choosing_filter'];
		}
		$sql_itemized_expense_query_string = "SELECT SQL_CALC_FOUND_ROWS * FROM expenses 
		    LEFT JOIN vendors ON expense_vendor_id = vendor_id
		    LEFT JOIN taxes ON expense_tax_id = tax_id
		    LEFT JOIN categories ON expense_category_id = category_id
		    LEFT JOIN accounts ON expense_account_id = account_id
		    WHERE expense_vendor_id > 0 
		    AND DATE(expense_date) BETWEEN '$dtf' AND '$dtt'
		    AND expense_amount BETWEEN '$min' AND '$max'
		    AND expense_tax_id BETWEEN '$tax_id_choosing_min' AND '$tax_id_choosing_max'
		    AND expense_vendor_id BETWEEN '$vendor_id_choosing_min' AND '$vendor_id_choosing_max'
		    AND expense_category_id BETWEEN '$category_id_choosing_min' AND '$category_id_choosing_max'
		    ORDER BY $sb $o";
            	$sql_itemized_expense_query = mysqli_query($mysqli, $sql_itemized_expense_query_string);
		//$num_rows = mysqli_fetch_row($sql_itemized_expense_query);
		?>
		<thead>
		<tr>    
		    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_id&o=<?php echo $disp; ?>">ID</a></th>
            	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_date&o=<?php echo $disp; ?>">Date</a></th>
            	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=vendor_name&o=<?php echo $disp; ?>">Vendor</a></th>
            	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=category_name&o=<?php echo $disp; ?>">Category</a></th>              
            	    <th><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_reference&o=<?php echo $disp; ?>">Reference</a></th>
            	    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_description&o=<?php echo $disp; ?>">Description</a></th>
            	    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_amount&o=<?php echo $disp; ?>">Amount (inc Tax) </a></th>
            	    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=expense_tax&o=<?php echo $disp; ?>">Tax Paid</a></th>
                    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=tax_name&o=<?php echo $disp; ?>">Tax Name</a></th>
                    <th class="text-dark"><a class="text-dark" href="?<?php echo $url_query_strings_sb; ?>&sb=tax_percent&o=<?php echo $disp; ?>">Tax Percent</a></th>
                    <th>Receipt</th>
            	</tr>
            	</thead>
            	<tbody>
            	
		<?php
		$herfArray = array();
		while($row = mysqli_fetch_array($sql_itemized_expense_query))
		{
		     $expense_id = intval($row['expense_id']);
                     $expense_date = htmlentities($row['expense_date']);
                     $expense_amount = floatval($row['expense_amount']);
                     $expense_currency_code = htmlentities($row['expense_currency_code']);
                     $expense_description = htmlentities($row['expense_description']);
                     $expense_receipt = htmlentities($row['expense_receipt']);
                     $expense_reference = htmlentities($row['expense_reference']);
                     $expense_created_at = htmlentities($row['expense_created_at']);
                     $expense_vendor_id = intval($row['expense_vendor_id']);
                     $expense_tax = $row['expense_tax'];
                     $expense_tax_id = intval($row['expense_tax_id']);
                     $tax_name = htmlentities($row['tax_name']);
                     $tax_percent = intval($row['tax_percent']);
                     $vendor_name = htmlentities($row['vendor_name']); 
                     $expense_category = htmlentities($row['category_name']);
                     $herf = "uploads/expenses/".$expense_receipt;    
                     array_push($herfArray, $herf);
                     if (empty($expense_receipt)) {
                     $receipt_attached = "";
                     } else {
                     $receipt_attached = "<a id='receipt' class='text-secondary mr-2' target='_blank' href='uploads/expenses/$expense_receipt' download='$expense_date-$vendor_name-$category_name-$expense_id.png'><i class='fa fa-file-pdf'></i></a>";}   
                     $date = new DateTime($expense_date);
                     $non_american_format_date = $date->format('d-m-Y');?>
                     <tr>
		    	<td><?php echo $expense_id;?></td>
		    	<td><?php echo $non_american_format_date;?></td>
		    	<td><?php echo $vendor_name;?></td>
		    	<td><?php echo $expense_category; ?></td>
		    	<td><?php echo $expense_reference; ?></td>
		    	<td><?php echo $expense_description; ?></td>
		    	<td class="text-bold"><?php echo numfmt_format_currency($currency_format, $expense_amount, $expense_currency_code); ?></td>
		    	<td class="text-bold"><?php echo numfmt_format_currency($currency_format, $expense_tax, $expense_currency_code); ?></td>
		        <td class="text-bold"><?php echo $tax_name;?></td>     
		        <td class="text-bold"><?php echo $tax_percent . '%';?></td>    
		        <td><?php echo $receipt_attached;?></td>    
		    </tr>      
		<?php
		}
		$jsArray = json_encode($herfArray);
            ?>
                </tbody>
            </table>
            
        </div>
         
    </div>
</div>


<?php require_once("footer.php"); 
require_once("expense_add_modal.php");
require_once("category_quick_add_modal.php");
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" integrity="sha512-XMVd28F1oH/O71fzwBnV7HucLxVwtxf26XV8P4wPk26EDxuGZ91N8bsOttmnomcCD3CS5ZMRL50H0GgOHvegtg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>

	$(document).ready(function(){
	    function filterTable()
	    {
	    	var selectedValue = [];
	    	// Show all
	    	$('#expenses_report_table tbody tr').show();
	    	
	    	// Filter by vendor
	    	if ($('.vendor-filter:checked').length > 0) {
		  $('#expenses_report_table tbody tr').filter(function() {
		    var vendor = $(this).find("td:eq(2)").text();
		    return !$('.vendor-filter[vendor="' + vendor + '"]').prop('checked');
		  }).hide();
		}

		// Filter rows by category
		if ($('.category-filter:checked').length > 0) {
		  $('#expenses_report_table tbody tr').filter(function() {
		    var category = $(this).find("td:eq(3)").text();
		    return !$('.category-filter[category="' + category + '"]').prop('checked');
		  }).hide();
		}
		
		// Filter by tax
		if ($('.tax-filter:checked').length > 0) {
		  $('#expenses_report_table tbody tr').filter(function() {
		    var tax = $(this).find("td:eq(8)").text();
		    return !$('.tax-filter[tax="' + tax + '"]').prop('checked');
		  }).hide();
		}
	    }
	    
	    function exportToCSV() {
		  var csvContent = "data:text/csv;charset=utf-8";
		  var rows = [];

		  // Add the header row to the CSV
		  var headerRow = [];
		  headerRow.push('Date');
		  $('#expenses_report_table thead th').each(function(index) {
		    //the if is for removing the receipt column which is the last one
		    if (index < $('#expenses_report_table thead th').length - 1) {
		    headerRow.push('"' + $(this).text() + '"');
		    }
		  });
		  //somehow if we use semicolumn here, it lose data
		  rows.push(headerRow.join(','));

		  // Get the filtered rows and convert them to CSV format
		  $('#expenses_report_table tbody tr:visible').each(function() {
		    var rowData = [];
		    
		    $(this).find('td').each(function(index) {
		    //the if is for removing the receipt column which is the last one
		      if (index < $('#expenses_report_table thead th').length - 1) {
			var cellData = $(this).text();
			// Wrap numbers with commas in double quotes
			cellData = '"' + cellData + '"';
			rowData.push(cellData);
		      }
		    });
		    rows.push(rowData.join(','));
		  });

		  // Combine the rows and add them to the CSV content
		  csvContent += rows.join('\n');

		  // Create a temporary link element to initiate the download
		  var link = document.createElement('a');
		  link.href = encodeURI(csvContent);
		  link.download = "expenses.csv";
		  link.click();
		  
		  downloadReceipts();
		}

	    //download zip file for receipt function
	    async function downloadReceipts() {
		
	    	var selectedID = [];
	    	$('#expenses_report_table tbody tr:visible').each(function() {
	    	    var ID = $(this).find("td:eq(0)").text();
	    	    selectedID.push("ID=" + ID);
	    	});

		var jsVariable = <?php echo $jsArray; ?>;
		
		//get image url in an array
		let urls = [];
		jsVariable.forEach(function(receiptImage){
		    
		    var parts = receiptImage.split('/', 3);
		    for(var i = 0; i < jsVariable.length ; i++)
		    {
		    	//if the id in the image file name match selectedID
		    	var idString = selectedID[i] + "_";
		    	if(parts[2].includes(idString))
		    	{
		    	    urls.push(receiptImage);
		    	    break;
		    	}
		    }
		});

		//Get image data
		const promises = urls.map(async (url) => {
		    const res = await fetch(url);
		    const blob = await res.blob();
		    return blob;
		});
		const res = await Promise.all(promises);
		
		//Create a zip of images
		const zip = new JSZip();
		res.forEach(function(blob, index) {
		  var fileNameFromURL= urls[index].split('/')[2];
		  // Determine the file name based on the Blob's type
		  var fileName = blob.name || fileNameFromURL;

		  // Add each Blob as a file in the zip archive
		  zip.file(fileName, blob);
		});
		//Download result
		
		zip.generateAsync({ type: 'blob' }).then(function(content) {
		  // 'content' is the Blob object representing the zip file

		  // Save the zip file
		  saveAs(content, 'archive.zip');
		});
	    }

	    // Call the exportToCSV function when the export button is clicked
	    $('#exportBtn').on('click', exportToCSV);
	    
	    // Generate checkboxes for names dynamically
	    var vendors = [];
	    $('#expenses_report_table tbody tr').each(function() {
	        var vendor = $(this).find("td:eq(2)").text();
		if (!vendors.includes(vendor)) {
		  vendors.push(vendor);
		  var checkbox = $('<input type="checkbox" class="vendor-filter">').attr('vendor', vendor);
		  var label = $('<label>').append(checkbox, vendor);
		  $('#vendorFilter .items').append(label);
		  $('#vendorFilter .items').append($('<br>'));
		  
		}
	      });
	     
	    var categories = [];
	    $('#expenses_report_table tbody tr').each(function() {
	        var category = $(this).find("td:eq(3)").text();
		if (!categories.includes(category)) {
		  categories.push(category);
		  var checkbox = $('<input type="checkbox" class="category-filter">').attr('category', category);
		  var label2 = $('<label>').append(checkbox, category);
		  $('#categoryFilter .items').append(label2);
		  $('#categoryFilter .items').append($('<br>'));
		}
	      });
	      
	    var taxes = [];
	    $('#expenses_report_table tbody tr').each(function() {
	        var tax = $(this).find("td:eq(8)").text();
		if (!taxes.includes(tax)) {
		  taxes.push(tax);
		  var checkbox = $('<input type="checkbox" class="tax-filter">').attr('tax', tax);
		  
		  var label3 = $('<label>').append(checkbox, tax);
		  $('#taxFilter .items').append(label3);
		  $('#taxFilter .items').append($('<br>'));
		}
	    });
	    
	    //dropdown
	    function showCheckBoxes(id)
	    {
	    	var dropdown = document.getElementById(id);
	    	var checkboxes = dropdown.querySelector(".items");
	    	checkboxes.style.display = checkboxes.style.display === "block" ? "none" : "block";
	    }
	    
	    document.getElementById('selectVendor').addEventListener("click", function(){showCheckBoxes("vendorFilter")}); 
	    document.getElementById('selectTax').addEventListener("click", function(){showCheckBoxes("taxFilter")});
	    document.getElementById('selectCategory').addEventListener("click", function(){showCheckBoxes("categoryFilter")});
	    
	    // Call the filterTable function whenever a name filter checkbox changes
	    $('#vendorFilter').on('change', '.vendor-filter', filterTable);
	    $('#categoryFilter').on('change', '.category-filter', filterTable);
	    $('#taxFilter').on('change', '.tax-filter', filterTable);
	    
	});
</script> 
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    var ctx = document.getElementById("cashFlow");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan", "Feb", "Mar"],
            datasets: [{
                label: "Expense",
                lineTension: 0.3,
                fill: false,
                borderColor: "#dc3545",
                pointBackgroundColor: "#dc3545",
                pointBorderColor: "#dc3545",
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "#dc3545",
                pointHitRadius: 50,
                pointBorderWidth: 2,
                data: [
                    <?php

                    $largest_expense_month = 0;
			//start from 4 to match the tax year
                    for ($month = 4; $month<=15; $month++) {
                    if($month == 12)
                    {
                    	$month_correct = 12;
                    }
                    else
                    {
                    	$month_correct = $month % 12;
                    }
                    $sql_expenses = mysqli_query($mysqli, "SELECT SUM(expense_amount) AS expense_amount_for_month FROM expenses WHERE MONTH(expense_date) = $month_correct AND expense_vendor_id > 0 AND expense_date BETWEEN '$date_start' AND '$date_end'");
                    $row = mysqli_fetch_array($sql_expenses);
                    $expenses_for_month = floatval($row['expense_amount_for_month']);

                    if ($expenses_for_month > 0 && $expenses_for_month > $largest_expense_month) {
                        $largest_expense_month = $expenses_for_month;
                    }

                    echo "$expenses_for_month,";

                    } ?>

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
                        max: <?php $max = max(1000, $largest_expense_month, $largest_income_month, $largest_invoice_month); echo roundUpToNearestMultiple($max); ?>,
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


<?php require("expense_export_modal.php");?>
