@extends('layouts.app')

@section('content')

<div class="container">
<h4 class="mb-4 mt-4">Projects
    <span class="float-right">
        <a href="{{ url('projects/create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Project
        </a>
    </span>
</h4>

@if(session()->has('success'))
    <div class="alert alert-success" role="alert">
        {{ session()->get('success') }}
    </div>
@endif

<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Branch</th>
      <th scope="col">Status</th>
      <th scope="col">Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($projects as $project)
    <tr>
      <th scope="row">{{ $project->id }}</th>
      <td>{{ $project->name }}</td>
      <td>{{ $project->branch }}</td>
      <td><i class="fa fa-check"></i></td>
      <td>
        @if(file_exists(public_path() . '/' . $project->name .'_deploy.log'))
        <a href="{{ url($project->name .'_deploy.log') }}" class="btn btn-success">
          <i class="fa fa-eye-open"></i>  View Log
        </a>
        @endif
        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-primary">
          <i class="fa fa-edit"></i>  Edit
        </a>
        <button class="ml-2 btn btn-danger delete-project" data-project-id="{{ $project->id }}" type="button">
            <i class="fa fa-trash"></i> Delete
        </button>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<form id="delete-form" action="{{ url('/projects') }}" method="POST" style="display: none;">
    @csrf
    {{ method_field('DELETE') }}
</form>

</div>


<script>
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var aElems = document.getElementsByClassName('delete-project');
        for(var i=0; i<aElems.length; i++){
            aElems[i].onclick = function(){
                var check = confirm("Are you sure you want to delete this project?");
                if(check){
                    let projectID = this.getAttribute('data-project-id');
                    let deleteForm = document.getElementById('delete-form');
                    deleteForm.action += '/' + projectID;
                    deleteForm.submit();
                    return true;
                }else{
                    return false;
                }
            }
        }
    }, false);
})();
</script>

@endsection