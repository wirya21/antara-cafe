@php $css = \App\Support\Theme::css(); @endphp
@if ($css)
    <style>{!! $css !!}</style>
    <meta name="theme-color" content="{{ \App\Support\Theme::color() }}">
@endif
