@extends('errors.layout')

@section('title', 'انتهت صلاحية الصفحة')
@section('code', '419')
@section('heading', 'انتهت صلاحية الجلسة')
@section('message', 'صارت الصفحة قديمة أو انتهت مهلة الحماية. حدّث الصفحة وحاول مرة ثانية.')

@section('actions')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="btn btn-primary">الرجوع والمحاولة</a>
    <a href="{{ url('/') }}" class="btn btn-ghost">الرئيسية</a>
@endsection
