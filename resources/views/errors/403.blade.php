@extends('errors.layout')

@section('title', 'غير مصرح')
@section('code', '403')
@section('heading', 'ما عندك صلاحية لعرض هذه الصفحة')
@section('message', 'إما إن الرابط خاص، أو حسابك ما يسمح بالوصول لهذا المحتوى. لو تعتقد إن فيه خطأ، تواصل معنا.')

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">العودة للرئيسية</a>
    @auth
        <a href="{{ url('/dashboard') }}" class="btn btn-ghost">لوحة التحكم</a>
    @else
        <a href="{{ url('/login') }}" class="btn btn-ghost">تسجيل الدخول</a>
    @endauth
@endsection
