@extends('layouts.app')

@section('content')

<div class="container">
    <h4 class="mb-4 mt-4">Create Project</h4>
    <hr>

    <form class="needs-validation" novalidate action="{{ url('/projects') }}" method="POST">
        {!! csrf_field() !!}
        <div class="form">

            <div class="col mb-3">
                <label for="validationCustom01">Repository Name</label>
                <input type="text" name="name" class="form-control" id="validationCustom01" placeholder="Ex: atplearning/test" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                @endif
            </div>

            <div class="col mb-3">
                <label for="validationCustom02">Branch</label>
                <input type="text" name="branch" class="form-control" id="validationCustom02" placeholder="Ex: master" required>
                @if($errors->has('branch'))
                    <div class="invalid-feedback">{{ $errors->first('branch') }}</div>
                @endif
            </div>

            <div class="col mb-3">
                <label for="validationCustomUsername">Secret</label>
                <input type="text" name="secret" class="form-control" id="validationCustomUsername" placeholder="Enter Secret Key" aria-describedby="inputGroupPrepend" required>
                @if($errors->has('secret'))
                    <div class="invalid-feedback">{{ $errors->first('secret') }}</div>
                @endif
            </div>
        
            <div class="col mb-3">
                <label for="validationCustom03">Local Deploy Path</label>
                <input type="text" name="path" class="form-control" id="validationCustom03" placeholder="Ex: /var/www/html/repo (local path where to deploy)" required>
                @if($errors->has('path'))
                    <div class="invalid-feedback">{{ $errors->first('path') }}</div>
                @endif
            </div>

            <div class="col mb-3">
                <label for="validationCustom04">Pre Hook Script</label>
                <input type="text" name="pre_hook" class="form-control" id="validationCustom04" placeholder="script that you want to run before pull">
                @if($errors->has('pre_hook'))
                    <div class="invalid-feedback">{{ $errors->first('pre_hook') }}</div>
                @endif
            </div>

            <div class="col mb-3">
                <label for="validationCustom05">Post Hook Script</label>
                <input type="text" name="post_hook" class="form-control" id="validationCustom05" placeholder="script that you want to run after pull">
                @if($errors->has('post_hook'))
                    <div class="invalid-feedback">{{ $errors->first('post_hook') }}</div>
                @endif
            </div>

        </div>
        <div class="form">
            <div class="col mb-3">
                <p><label for="inlineRadio">Status</label></p>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inlineRadio1" value="1" checked>
                    <label class="form-check-label" for="inlineRadio1">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inlineRadio2" value="0">
                    <label class="form-check-label" for="inlineRadio2">Inactive</label>
                </div>
            </div>
        </div>
        <div class="form">
            <div class="col">
                <button class="btn btn-primary" type="submit">Submit</button>
                <a href="{{ url('/projects') }}" class="ml-2 btn btn-danger">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function() {
  'use strict';
  window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script>

@endsection


