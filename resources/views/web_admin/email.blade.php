@extends('layouts.web_admin')

@section('css')
    
@endsection

@section('content')
  <div class="card rounded-0">
    <div class="card-header">
        <h3 class="font-weight-bold">Emails</h3>
     </div>
    <div class="card-body">
        <div class="tableFixHead">
        <table class="table table-bordered table-hover"> 
        <thead class="table-success">
          <th>Datetime</th>
          <th>Email Address</th>
          <th>Email Type</th>
        </thead>
          <tbody>

          </tbody>
      </table>
        </div>
    </div>
  </div>


  @endsection