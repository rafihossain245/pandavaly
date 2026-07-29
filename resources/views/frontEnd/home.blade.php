@extends('frontEnd.layouts.master')

@section('content')

    @forelse ($sections as $section)
        @includeIf('frontEnd.sections.' . $section->type, ['section' => $section])
    @empty
        <div class="container py-5 text-center text-muted">
            <p>No homepage sections have been configured yet. Add sections from the admin dashboard under Website Management &rarr; Homepage Sections.</p>
        </div>
    @endforelse

@endsection
