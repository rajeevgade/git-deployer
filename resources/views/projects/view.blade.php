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
        <a href="{{ url('projects/edit/'. $project->id) }}" class="btn btn-primary">
          <i class="fa fa-edit"></i>  Edit
        </a>
        <a href="{{ url('projects/delete/'. $project->id) }}" class="ml-2 btn btn-danger">
            <i class="fa fa-trash"></i> Delete
        </a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

</div>

@endsection