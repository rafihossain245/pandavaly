@extends('layout.app')

@section('meta-information')
    <title>Edit {{ $user->name }}</title>
@endsection

@section('main-content')
    @include('panel.users._form')
@endsection
