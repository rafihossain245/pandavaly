@extends('frontEnd.layouts.master')

@section('page-title', $page->title)

@section('css')
<style>
    .cms-page { background: #f5f5f5; min-height: 60vh; padding: 22px 0 50px; }
    .cms-breadcrumb { font-size: 13px; color: #8a8a8a; margin-bottom: 14px; }
    .cms-breadcrumb a { color: var(--primary); }
    .cms-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 6px; padding: 30px 34px; }
    .cms-card h1.cms-title { font-size: 26px; font-weight: 700; color: #111; margin: 0 0 6px; }
    .cms-updated { font-size: 12.5px; color: #9aa8b8; margin-bottom: 20px; }
    .cms-body { color: #3f4550; font-size: 15px; line-height: 1.8; }
    .cms-body h2 { font-size: 20px; font-weight: 700; color: #111; margin: 26px 0 10px; }
    .cms-body h3 { font-size: 17px; font-weight: 700; color: #111; margin: 22px 0 8px; }
    .cms-body p { margin-bottom: 14px; }
    .cms-body ul, .cms-body ol { padding-left: 22px; margin-bottom: 14px; }
    .cms-body li { margin-bottom: 7px; }
    .cms-body a { color: var(--primary); }
    .cms-body img { max-width: 100%; height: auto; border-radius: 4px; }
    .cms-body table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .cms-body table td, .cms-body table th { border: 1px solid #e5e5e5; padding: 9px 11px; }
    .cms-empty { text-align: center; padding: 40px 16px; color: #8a8a8a; }
    .cms-empty i { font-size: 2.2rem; color: #d8dde3; margin-bottom: 14px; display: block; }
    .cms-empty a { color: var(--primary); }

    @media (max-width: 575px) {
        .cms-card { padding: 20px 18px; }
        .cms-card h1.cms-title { font-size: 22px; }
    }
</style>
@endsection

@section('content')
<section class="cms-page">
    <div class="container">
        <div class="cms-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            @if($page->category)
                / <span>{{ $page->category->name }}</span>
            @endif
            / <span>{{ $page->title }}</span>
        </div>

        <div class="cms-card">
            <h1 class="cms-title">{{ $page->title }}</h1>
            <p class="cms-updated">Last updated {{ $page->updated_at->format('d M Y') }}</p>

            @if($page->hasContent())
                <div class="cms-body">{!! $page->content !!}</div>
            @else
                {{-- The page exists in the footer but nobody has written it yet;
                     say so plainly instead of showing a blank card. --}}
                <div class="cms-empty">
                    <i class="far fa-file-lines"></i>
                    <p class="mb-1"><strong>This page is being written.</strong></p>
                    <p class="mb-0">Please check back soon, or
                        <a href="{{ route('home') }}">continue shopping</a>.</p>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
