<div class="modal" id="editTaxYearModal" tabindex="-1" role="dialog"> 
  <div class="modal-dialog">
    <div class="modal-content bg-dark">
      <div class="modal-header text-white">
        <h5 class="modal-title"><i class="fas fa-fw fa-balance-scale mr-2"></i>Edit Tax Year</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="post.php" method="post" autocomplete="off">
        <div class="modal-body bg-white">
          <div class="form-group">
            <label>Date From<strong class="text-danger">*</strong></label>
            <br>
            <tr>
            	<td>
            	    <Lable>Day<input type="number" class="form-control" name="df" placeholder="dd" min = "1" max = "31" required></label>
            	</td>
            	<td>
            	    <Lable>Month<input type="number" class="form-control" name="mf" placeholder="mm" min = "1" max = "12" required></label>
            	</td>
            </tr>
          </div>
          <div class="form-group">
            <label>Date To<strong class="text-danger">*</strong></label>
            <br>
            <tr>
            	<td>
            	    <Lable>Day<input type="number" class="form-control" name="dt" placeholder="dd" min = "1" max = "31" required></label>
            	</td>
            	<td>
            	    <Lable>Month<input type="number" class="form-control" name="mt" placeholder="mm" min = "1" max = "12" required></label>
            	</td>
            </tr>
          </div>
        </div>
        <div class="modal-footer bg-white">
          <button type="submit" name="edit_tax_year" class="btn btn-primary text-bold"><i class="fa fa-check mr- 2"></i>Save</button>
          <button type="button" class="btn btn-light" data-dismiss="modal"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


