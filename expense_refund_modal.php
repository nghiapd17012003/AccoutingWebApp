<div class="modal" id="addExpenseRefundModal<?php echo $expense_id; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-fw fa-undo-alt mr-2"></i>Refunding expense</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="post.php" method="post" autocomplete="off">
                <div class="modal-body bg-white">
                    <input type="hidden" name="account" value="<?php echo $expense_account_id; ?>">
                    <input type="hidden" name="vendor" value="<?php echo $expense_vendor_id; ?>">
                    <input type="hidden" name="category" value="<?php echo $expense_category_id; ?>">

                    <div class="form-row">

                        <div class="form-group col-md">
                            <label>Refund Date</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-calendar"></i></span>
                                </div>
                                <input type="date" class="form-control" name="date" max="2999-12-31" value="<?php echo date("Y-m-d"); ?>" required>
                            </div>
                        </div>

                        <div class="form-group col-md">
                            <label>Refund Amount (inc tax) </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control" step="0.01" name="amount" value="-<?php echo $expense_amount; ?>" required>
                            </div>
                        </div>
                    </div>
		    <div class="form-row">
                    	<div class="form-group col-md">
                            <label>Tax Type<strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <select class="form-control select2" name="expense_tax_id" required>
					<?php

                                    $sql_taxes = mysqli_query($mysqli, "SELECT tax_id, tax_name, tax_percent FROM taxes WHERE (tax_archived_at > '$expense_created_at' OR tax_archived_at IS NULL) ORDER BY tax_name ASC");
                                    while ($row = mysqli_fetch_array($sql_taxes)) {
                                        $tax_id_select = intval($row['tax_id']);
                                        $tax_name_select = htmlentities($row['tax_name']);
                                        $tax_percent_select = intval($row['tax_percent']);
                                        
                                        ?>
                                        <option <?php if ($expense_tax_id == $tax_id_select) { ?> selected <?php } ?> value="<?php echo $tax_id_select; ?>"><?php echo $tax_name_select.' '. $tax_percent_select.'%'; ?></option>
                                        <?php
                                    }

                                    ?>
          
                                </select>
                            </div>
                        </div>
                        
                    	<div class="form-group col-md">
                            <label>Tax Paid <strong class="text-danger">*</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-fw fa-dollar-sign"></i></span>
                                </div>
                                <input type="number" class="form-control" step="0.01" name="expense_tax_paid" value="-<?php echo $expense_tax; ?>" autofocus required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" rows="6" name="description" placeholder="Enter a description" required>Refund: <?php echo $expense_description; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Reference</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-fw fa-file-alt"></i></span>
                            </div>
                            <input type="text" class="form-control" name="reference" placeholder="Enter a reference" value="<?php echo $expense_reference; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Receipt</label>
                        <input type="file" class="form-control-file" name="file">
                    </div>

                </div>
                <div class="modal-footer bg-white">
                    <button type="submit" name="add_expense" class="btn btn-primary text-bold"><i class="fas fa-check mr-2"></i>Refund</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fas fa-times mr-2"></i>Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
