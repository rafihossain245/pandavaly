@extends('layout.app')

@section('meta-information')
    <title>Edit {{ $target->name }}</title>
@endsection

@section('main-content')
    @include('panel.roles._form')
@endsection
