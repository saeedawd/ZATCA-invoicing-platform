@extends('errors.layout')

@section('title', 'خطأ في الخادم')
@section('code', '500')
@section('heading', 'صار خطأ غير متوقع')
@section('message', 'نشتغل على إصلاحه. جرّب بعد قليل، ولو استمرت المشكلة راسلنا وبنساعدك.')

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">العودة للرئيسية</a>
    <a href="{{ url('/contact') }}" class="btn btn-ghost">بلّغنا بالمشكلة</a>
@endsection
