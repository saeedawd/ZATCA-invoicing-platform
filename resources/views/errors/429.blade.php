@extends('errors.layout')

@section('title', 'طلبات كثيرة')
@section('code', '429')
@section('heading', 'كثرّت المحاولات شوي')
@section('message', 'وصلنا حد الطلبات المسموح في الوقت الحالي. انتظر لحظات ثم حاول مرة أخرى.')

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">العودة للرئيسية</a>
    <a href="{{ url('/contact') }}" class="btn btn-ghost">تواصل معنا</a>
@endsection
