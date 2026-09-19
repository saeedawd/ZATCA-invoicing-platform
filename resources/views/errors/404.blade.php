@extends('errors.layout')

@section('title', 'الصفحة غير موجودة')
@section('code', '404')
@section('heading', 'الصفحة اللي تدور عليها غير موجودة')
@section('message', 'يمكن الرابط تغيّر أو انكتب بالغلط. تقدر ترجع للرئيسية أو تتصفح صفحات الفوترة الإلكترونية وعروض الأسعار.')

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">العودة للرئيسية</a>
    <a href="{{ url('/electronic-invoicing') }}" class="btn btn-ghost">الفوترة الإلكترونية</a>
    <a href="{{ url('/contact') }}" class="btn btn-ghost">تواصل معنا</a>
@endsection
