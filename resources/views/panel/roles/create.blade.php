@extends('layout.app')

@section('meta-information')
    <title>Add Role</title>
@endsection

@section('main-content')
    @include('panel.roles._form', ['target' => null])
@endsection
