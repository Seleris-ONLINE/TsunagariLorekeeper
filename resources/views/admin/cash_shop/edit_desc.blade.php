@extends('admin.layout')

@section('admin-title') Edit Shop @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Products' => 'admin/data/products', 'Edit' => 'admin/data/products/edit/shop']) !!}
<h1>Edit Shop Text</h1>
<br>
<hr>
<h3>Edit Item Area</h3>
{!! Form::open(['url' => 'admin/data/products/shop/edit']) !!}
<div class="form-group">
    {!! Form::label('Item Area Title (Optional)') !!}
    {!! Form::text('title', $shop->title, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Item Area Description (Optional)') !!}
    {!! Form::textarea('desc', $shop->desc, ['class' => 'form-control wysiwyg']) !!}
</div>
<br>
<hr>
<h3>Edit Bundle Area</h3>
<br>
<div class="form-group">
    {!! Form::label('Bundle Area Title (Optional)') !!}
    {!! Form::text('btitle', $shop->btitle, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('Bundle Area Description (Optional)') !!}
    {!! Form::textarea('bdesc', $shop->bdesc, ['class' => 'form-control wysiwyg']) !!}
</div>
{!! Form::submit('Submit', ['class' => 'btn btn-primary text-right']) !!}
{!! Form::close() !!}

@endsection