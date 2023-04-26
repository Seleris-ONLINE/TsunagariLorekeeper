@extends('layouts.app')
@section('title') Thank you! @endsection
@section('content')
 <div class="container">

    @if (Session::has('message'))
     <div class="alert alert-{{ Session::get('code') }}">
      {{ Session::get('message') }}
     </div>
    @endif

    <div class="text-center">
        <h4>Thank you for buying from our store!</h4>
        <br>
        If you encounter any errors, please let us know ASAP and we will try our best to fix it!
    </div>

    @endsection