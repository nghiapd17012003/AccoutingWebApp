
<style>
    .uploadReceipt{
    	top: 3%;
    	position: absolute;
    	left: 52%;
    	width: 45%;
   	height: 50%;
   	overflow: auto;
    }
    .modal-new-size{
    	position: relative;
    	width: 100%;
    	height: 100%;
    }
    .textScanResult{
    	position: absolute;
    	top: 55%;
    	left: 52%;
    	width: 45%;
   	height: 45%;
    	background: white;
    	white-space: nowrap;
  	overflow: auto;
    }
    .modal-dialog{
    	position: absolute;
    	left:0px !important;
    	width: 50%;
    }
    #imageDisplay {
  	width: 100%; /* Make the image fill the width of the container */
  	height: auto; /* Allow the height to adjust automatically to maintain aspect ratio */
    }
</style>
<div class="modal modal-new-size" id="addExpenseModal" tabindex="-1"> 
    <div class="textScanResult">
    	<h2>Scan Result</h2>
    	<p class="scanResult" id="scanResult"> here!<p>
    </div>
    
    <div class="uploadReceipt">
    	<img id="imageDisplay" src="#" alt="Selected Image">
    </div>
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-fw fa-cart-plus mr-2"></i>New Expense</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.js"></script>
            <script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>
            <form action="post.php" method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-body bg-white">

                    <div class="form-row">
                        <div class="form-group col-md">
                            <label>Date <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-calendar"></i></span>
                                </div>
                                <input type="date" class="form-control" name="date" max="2999-12-31" value="<?php echo date("Y-m-d"); ?>" id="date" required>
                            </div>
                        </div>

                        <div class="form-group col-md">
                            <label>Amount (inc Tax) <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control" step="0.01" name="amount" placeholder="Enter amount" id="amount" autofocus required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                    	<div class="form-group col-md">
                            <label>Tax Type<strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <select class="form-control select2" name="expense_tax_id" required>
                                    <option value="0">- No Tax -</option>
                                    <?php
                                        $taxes_sql = mysqli_query($mysqli, "SELECT * FROM taxes ORDER BY tax_name ASC");
                                        while ($row = mysqli_fetch_array($taxes_sql)) {
                                            $tax_id = intval($row['tax_id']);
                                            $tax_name = htmlentities($row['tax_name']);
                                            $tax_percent = floatval($row['tax_percent']);
                                            ?>
                                            <option value="<?php echo $tax_id; ?>"><?php echo "$tax_name $tax_percent%"; ?></option>

                                            <?php
                                        }
                                    ?>
          
                                </select>
                            </div>
                        </div>
                        
                    	<div class="form-group col-md">
                            <label>Tax Paid<strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control" step="0.01" name="expense_tax_paid" placeholder="Enter amount"  id ="tax_paid" autofocus required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md">
                            <label>Account <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-piggy-bank"></i></span>
                                </div>
                                <select class="form-control select2" name="account" required>
                                    <option value="">- Account -</option>
                                    <?php

                                    $sql = mysqli_query($mysqli, "SELECT account_id, account_name, opening_balance FROM accounts WHERE account_archived_at IS NULL ORDER BY account_name ASC");
                                    while ($row = mysqli_fetch_array($sql)) {
                                        $account_id = intval($row['account_id']);
                                        $account_name = htmlentities($row['account_name']);
                                        $opening_balance = floatval($row['opening_balance']);

                                        $sql_payments = mysqli_query($mysqli, "SELECT SUM(payment_amount) AS total_payments FROM payments WHERE payment_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_payments);
                                        $total_payments = floatval($row['total_payments']);

                                        $sql_revenues = mysqli_query($mysqli, "SELECT SUM(revenue_amount) AS total_revenues FROM revenues WHERE revenue_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_revenues);
                                        $total_revenues = floatval($row['total_revenues']);

                                        $sql_expenses = mysqli_query($mysqli, "SELECT SUM(expense_amount) AS total_expenses FROM expenses WHERE expense_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_expenses);
                                        $total_expenses = floatval($row['total_expenses']);

                                        $balance = $opening_balance + $total_payments + $total_revenues - $total_expenses;

                                        ?>
                                        <option <?php if ($config_default_expense_account == $account_id) { echo "selected"; } ?> value="<?php echo $account_id; ?>"><div class="float-left"><?php echo $account_name; ?></div><div class="float-right"> [$<?php echo number_format($balance, 2); ?>]</div></option>

                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md">
                            <label>Vendor <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-building"></i></span>
                                </div>
                                <select class="form-control select2" name="vendor" required>
                                    <option value="">- Vendor -</option>
                                    <?php

                                    $sql = mysqli_query($mysqli, "SELECT vendor_id, vendor_name FROM vendors WHERE vendor_client_id = 0 AND vendor_template = 0 AND vendor_archived_at IS NULL ORDER BY vendor_name ASC");
                                    while ($row = mysqli_fetch_array($sql)) {
                                        $vendor_id = intval($row['vendor_id']);
                                        $vendor_name = htmlentities($row['vendor_name']);
                                        ?>
                                        <option value="<?php echo $vendor_id; ?>"><?php echo $vendor_name; ?></option>

                                        <?php
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#addQuickVendorModal"><i class="fas fa-fw fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description <strong class="text-danger">*</strong></label>
                        <textarea class="form-control" rows="6" name="description" placeholder="Enter a description" required></textarea>
                    </div>
                    
                    <div class="form-row">
	                <div class="form-group col-md">
	                    <label>Reference</label>
	                    <div class="input-group">
	                        <div class="input-group-prepend">
	                            <span class="input-group-text"><i class="fa fa-fw fa-file-alt"></i></span>
	                        </div>
	                        <input type="text" class="form-control" name="reference" placeholder="Enter a reference">
	                    </div>
		        </div>
                    </div>
                    
                    <div class="form-row">
	                <div class="form-group col-md">
                            <label>Depreciation <strong class="text-danger"></strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-piggy-bank"></i></span>
                                </div>
                                <select class="form-control select2" name="depreciation_account">
                                    <option value="">- Account -</option>
                                    <?php

                                    $sql2 = mysqli_query($mysqli, "SELECT depreciation_account_id, depreciation_account_name, depreciation_account_balance FROM depreciation_accounts WHERE depreciation_account_archived_at IS NULL ORDER BY depreciation_account_name ASC");
                                    while ($row2 = mysqli_fetch_array($sql2)) {
                                        $account_id = intval($row2['depreciation_account_id']);
                                        $account_name = htmlentities($row2['depreciation_account_name']);
                                        $opening_balance = floatval($row2['depreciation_account_balance']);
					
					/*
                                        $sql_payments = mysqli_query($mysqli, "SELECT SUM(payment_amount) AS total_payments FROM payments WHERE payment_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_payments);
                                        $total_payments = floatval($row['total_payments']);

                                        $sql_revenues = mysqli_query($mysqli, "SELECT SUM(revenue_amount) AS total_revenues FROM revenues WHERE revenue_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_revenues);
                                        $total_revenues = floatval($row['total_revenues']);

                                        $sql_expenses = mysqli_query($mysqli, "SELECT SUM(expense_amount) AS total_expenses FROM expenses WHERE expense_account_id = $account_id");
                                        $row = mysqli_fetch_array($sql_expenses);
                                        $total_expenses = floatval($row['total_expenses']);

                                        $balance = $opening_balance + $total_payments + $total_revenues - $total_expenses;*/

                                        ?>
                                        <option <?php if ($config_default_expense_account == $account_id) { echo "selected"; } ?> value="<?php echo $account_id; ?>"><div class="float-left"><?php echo $account_name; ?></div></option>

                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
		            
		        <div class="form-group col-md">
                            <label>Lifetime (in years)<strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-calendar"></i></span>
                                </div>
                                <input type="number" class="form-control" step="0.01" name="expense_lifetime" placeholder="Enter amount"  id ="lifetime" autofocus required>
                            </div>
                        </div>
                    </div>
                    

                    <div class="form-row">

                        <div class="form-group col-md">
                            <label>Category <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-list"></i></span>
                                </div>
                                <select class="form-control select2" name="category" required>
                                    <option value="">- Category -</option>
                                    <?php

                                    $sql = mysqli_query($mysqli, "SELECT category_id, category_name FROM categories WHERE category_type = 'Expense' AND category_archived_at IS NULL ORDER BY category_name ASC");
                                    while ($row = mysqli_fetch_array($sql)) {
                                        $category_id = intval($row['category_id']);
                                        $category_name = htmlentities($row['category_name']);
                                        ?>
                                        <option value="<?php echo $category_id; ?>"><?php echo $category_name; ?></option>

                                        <?php
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#addQuickCategoryExpenseModal"><i class="fas fa-fw fa-plus"></i></button>
                                </div>
                            </div>


                        </div>

                        <?php if (isset($_GET['client_id'])) { ?>
                            <input type="hidden" name="client" value="<?php echo $client_id; ?>">
                        <?php }else{ ?>

                            <div class="form-group col-md">
                                <label>Client</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-fw fa-user"></i></span>
                                    </div>
                                    <select class="form-control select2" name="client" required>
                                        <option value="0">- Client (Optional) -</option>
                                        <?php

                                        $sql = mysqli_query($mysqli, "SELECT client_id, client_name FROM clients ORDER BY client_name ASC");
                                        while ($row = mysqli_fetch_array($sql)) {
                                            $client_id = intval($row['client_id']);
                                            $client_name = htmlentities($row['client_name']);
                                            ?>
                                            <option value="<?php echo $client_id; ?>"><?php echo $client_name; ?></option>

                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                        <?php } ?>

                    </div>

                    <div class="form-group col-md">
                        <label>Receipt</label>
                        <input type="file" class="form-control-file" name="file" id="fileInput" onchange="displayImage()">
                       	<button onclick="scanFile()">Autofill with uploaded receipt</button>
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="submit" name="add_expense" class="btn btn-primary text-bold"><i class="fa fa-fw fa-check mr-2"></i>Create</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fa fa-times mr-2"></i>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
//using Tesseract lib
const Tesseract = window.Tesseract;

// Function to perform OCR on the receipt image
async function performOCR(image) {
  const result = await Tesseract.recognize(image, 'eng', { logger: m => console.log(m) });
  return result.data.text;
}

async function convertPDFToImages(file) {
  const pdfData = await file.arrayBuffer();
  const pdf = await pdfjsLib.getDocument({ data: pdfData }).promise;
  const numPages = pdf.numPages;
  
  const images = [];
  for (let i = 1; i <= numPages; i++) {
    const page = await pdf.getPage(i);
    const viewport = page.getViewport({ scale: 1.5 });
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.height = viewport.height;
    canvas.width = viewport.width;
  
    const renderContext = {
      canvasContext: context,
      viewport: viewport
    };
  
    await page.render(renderContext).promise;
    const imageData = canvas.toDataURL('image/jpeg');
    images.push(imageData);
  }
  
  return images;
}

// Usage example
function scanFile() {
  const receiptImage = document.getElementById('fileInput');
  const file = receiptImage.files[0];
  if (file) {
      if (file.type === 'application/pdf') 
      {
      	convertPDFToImages(file)
	.then(async (images) => {
	  for (const image of images) {
	    const text = await performOCR(image);
	    console.log(text);
	    extractReceiptData(text);
	    var formattedText = text.replace(/\n/g, "<br>");
	    showResult(formattedText);
	  }
	})
	.catch(error => {
	  console.error('Error converting PDF to images:', error);
	  showResult('Error!');
	});
      }
      else {
	  const reader = new FileReader();
	  reader.onload = function(event) {
	  const imageData = event.target.result;
	  performOCR(imageData)
	  	.then(text => {
		    console.log(text);
		    extractReceiptData(text);
		    var formattedText = text.replace(/\n/g, "<br>");
		    showResult(formattedText);
	  	})
	  	.catch(error => {
		    console.error('Error performing OCR:', error);
		    showResult('Error!');
	  	});
      	  };
          reader.readAsDataURL(file);
      }
  } 
  else {
    console.log('No file selected.');
    showResult('No file selected.');
  }
}
function showResult(text){
  var resultSpace = document.getElementById("scanResult");
  resultSpace.innerHTML = text;
}

function extractReceiptData(text) {
  const dateRegexType1 = /(\d{2}\/\d{2}\/\d{4})/i;//dd/mm/yyyy
  const dateRegexType2 = /Date\s+(\d{2}\s+\w+\s+\d{4})/i;//Date dd monthInWord yyyy
  const dateRegexType3 = /(\d{2}\-\d{2}\-\d{4})/i; //dd-mm-yyyy
  const dateRegexType4 = /(\d{2}\-\w+\-\d{4})/;//dd-monthInWord-yyyy
  
  const totalAmountRegex = /(Amount\:?|Total\:?|Total\:?NZ|Total Amount Paid\:?)\s+\$([\d,]+(?:\.\d+)?)/i;
  const taxRegex = /(GST|Tax|GST paid)\s+\$([\d,]+(?:\.\d+)?)/i;
  const dateMatchType1 = text.match(dateRegexType1);
  const dateMatchType2 = text.match(dateRegexType2);
  const dateMatchType3 = text.match(dateRegexType3);
  const dateMatchType4 = text.match(dateRegexType4);
  const totalAmountMatch = text.match(totalAmountRegex);
  const taxMatch = text.match(taxRegex);
  //const form = document.getElementById("form");
  const amount = document.getElementById("amount");
  const date = document.getElementById("date");
  const tax = document.getElementById("tax_paid");
  if (dateMatchType1) {
    var i = 1;
    while(dateMatchType1[i] === undefined)
    {
      i++;
    }
    const dateString = dateMatchType1[i];
    var [day, month, year] = dateString.split('/');
    console.log('day', day);
    console.log('month', month);
    console.log('year', year);
    day++;
    const dateObject = new Date(year, month-1, day);
    date.value = dateObject.toISOString().split('T')[0];
    console.log('Date:', dateObject.toISOString().split('T')[0]);
    console.log('Date:', dateObject);
  }
  else if(dateMatchType2){
    var i = 1;
    while(dateMatchType2[i] === undefined)
    {
      i++;
    }
    const dateString = dateMatchType2[i];
    var [day, month, year] = dateString.split(' ');
    const monthNames = {
    January: 0,
    Jan: 0,
    February: 1,
    Feb: 1,
    March: 2,
    Mar: 2,
    April: 3,
    Apr: 3,
    May: 4,
    June: 5,
    Jun: 5,
    July: 6,
    Jul: 6,
    August: 7,
    Aug: 7,
    September: 8,
    Sep: 8,
    October: 9,
    Oct: 9,
    November: 10,
    Nov: 10,
    December: 11,
    Dec: 11
    };
    const monthValue = monthNames[month];
    day++;
    const dateObject = new Date(year, monthValue, day);
    date.value = dateObject.toISOString().split('T')[0];
    console.log(dateObject.toISOString().split('T')[0]);
  }
  else if(dateMatchType3)
  {
    var i = 1;
    while(dateMatchType3[i] === undefined)
    {
      i++;
    }
    const dateString = dateMatchType3[i];
    var [day, month, year] = dateString.split('-');
    day++;
    const dateObject = new Date(year, month-1, day);
    date.value = dateObject.toISOString().split('T')[0];
    console.log('Date:', dateObject);
    
  }
  else if (dateMatchType4)
  {
    var i = 1;
    while(dateMatchType4[i] === undefined)
    {
      i++;
    }
    const dateString = dateMatchType4[i];
    var [day, month, year] = dateString.split('-');
    const monthNames = {
    January: 0,
    Jan: 0,
    February: 1,
    Feb: 1,
    March: 2,
    Mar: 2,
    April: 3,
    Apr: 3,
    May: 4,
    June: 5,
    Jun: 5,
    July: 6,
    Jul: 6,
    August: 7,
    Aug: 7,
    September: 8,
    Sep: 8,
    October: 9,
    Oct: 9,
    November: 10,
    Nov: 10,
    December: 11,
    Dec: 11
    };

    day++;
    const monthValue = monthNames[month];
    console.log(year);
    console.log(monthValue);
    console.log(day);
    const dateObject = new Date(year, monthValue, day);
    console.log(dateObject);

    date.value = dateObject.toISOString().split('T')[0];
    console.log('Date:', dateObject.toISOString().split('T')[0]);
  }
  if (totalAmountMatch) {
    const totalAmount = totalAmountMatch[2].replace(",", "");
    console.log('Total Amount:', totalAmountMatch);
    amount.value = totalAmount;
  }
  else{amount.value = "";}
  if(taxMatch)
  {
    const taxAmount = taxMatch[2].replace(",", "");
    console.log('Tax:', taxAmount);
    tax.value = taxAmount;
  }
  else{tax.value = 0;}

}

function displayImage() {
  var input = document.getElementById('fileInput');
  var imageDisplay = document.getElementById('imageDisplay');

  if (input.files && input.files[0]) {
    var file = input.files[0];
    var reader = new FileReader();

    reader.onload = async function(e) { 
      if (file.type === 'application/pdf') {
        const images = await convertPDFToImages(file);
        if (images && images.length > 0) {
          imageDisplay.src = images[0]; // Display the first image
        }
      }
      
      else{
      	imageDisplay.src = e.target.result;
      }
    }

    reader.readAsDataURL(input.files[0]);
  }
}

</script>

