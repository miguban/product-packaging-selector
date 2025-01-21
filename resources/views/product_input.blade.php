@extends('templates.template')

@section('title', "Product Input | Product Packaging Selector")

@section('content')

@if (Session::has('error'))
<div class="alert alert-danger mb-4" role="alert">
    <h3 class="mb-3">ERROR</h3>
  <ul>
  {!! Session::get('error') !!}
  </ul>
</div>
@endif


@if (Session::has('success'))
<div class="alert alert-success mb-4" role="alert">
    <h3 class="mb-3">RESULTS</h3>
  <ul>
  {!! Session::get('success') !!}
  </ul>
</div>
@endif


<h3>PRODUCT PACKAGING SELECTOR</h3>
<h5>Product Input</h5>
<hr>
 <form action="/submitProductInput" method="post" id="productInputForm">
  @csrf
<div id="dProductDiv" class="my-4" style="max-width: 30em;">
 

<label class="fw-bold mb-3">PRODUCT 1</label>
    <div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Product Name</span>
  <input type="text" class="form-control" name="productName[]" required>
</div>

    <div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Length (cm)</span>
  <input type="number" class="form-control" name="productLength[]" required onkeypress="return isNumberKey(this,event);">
</div>

<div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Width (cm)</span>
  <input type="number" class="form-control" name="productWidth[]" required onkeypress="return isNumberKey(this,event);">
</div>


<div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Height (cm)</span>
  <input type="number" class="form-control" name="productHeight[]" required onkeypress="return isNumberKey(this,event);">
</div>


<div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Weight (kg)</span>
  <input type="number" class="form-control" name="productWeight[]" required onkeypress="return isNumberKey(this,event);">
</div>

<div class="input-group mb-3">
  <span class="input-group-text bg-secondary text-white w-9em">Quantity (pcs)</span>
  <input type="number" class="form-control" name="productQuantity[]" required onkeypress="return isNumberKey(this,event);">
</div>


</div>


<div style="text-align: end; max-width: 30em;">
  <button type="button" class="btn btn-outline-dark" id="addProductButton" onclick="addAnotherProduct();">+ Add another product</button>
  <button type="submit" class="btn btn-success">Submit</button>
</div>


</form>



@endsection

<script type="text/javascript">
  
  var productNumber = 1;

  function addAnotherProduct () {
    
    console.log(productNumber+1);

    if (productNumber <= 9) {

    var productNumberIncrement = ++productNumber;

    var newProductEntry = '<div id="newProductDiv'+productNumberIncrement+'"><label class="fw-bold mb-3 mt-3">PRODUCT <span>'+productNumberIncrement+'</span></label><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Product Name</span><input type="text" class="form-control" name="productName[]" required></div><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Length (cm)</span><input type="number" class="form-control" name="productLength[]" required onkeypress="return isNumberKey(this,event);"></div><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Width (cm)</span><input type="number" class="form-control" name="productWidth[]" required onkeypress="return isNumberKey(this,event);"></div><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Height (cm)</span><input type="number" class="form-control" name="productHeight[]" required onkeypress="return isNumberKey(this,event);"></div><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Weight (kg)</span><input type="number" class="form-control" name="productWeight[]" required onkeypress="return isNumberKey(this,event);"></div><div class="input-group mb-3"><span class="input-group-text bg-secondary text-white w-9em">Quantity (pcs)</span><input type="number" class="form-control" name="productQuantity[]" required onkeypress="return isNumberKey(this,event);"></div></div>\r\n';

    document.getElementById('dProductDiv').insertAdjacentHTML('beforeend', newProductEntry);;    


    }

    if (productNumber == 10) {
      $('#addProductButton').hide();
    }

  
    $('html, body').animate({
        scrollTop: $("#newProductDiv"+productNumberIncrement).offset().top
    });


  }


  function isNumberKey(txt, evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode == 46) {
        if (txt.value.indexOf('.') === -1) {
          return true;
        } else {
          return false;
        }
      } else {
        if (charCode > 31 &&
          (charCode < 48 || charCode > 57))
          return false;
      }
      return true;
    }


</script>

<style>
  .w-9em {
    width: 9em;
  }
</style>