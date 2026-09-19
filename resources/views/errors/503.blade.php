@extends('errors.layout')

@section('title', 'الصيانة')
@section('code', '503')
@section('heading', 'المنصة تحت الصيانة مؤقتًا')
@section('message', 'نحدّث الخدمة عشان تشتغل أفضل. نرجع خلال وقت قصير — شكرًا لصبرك.')

@section('actions')
    <a href="{{ url('/') }}" class="btn btn-primary">حاول مرة أخرى</a>
    <a href="mailto:{{ config('seo.contact_to', 'info@zatca.app') }}" class="btn btn-ghost" dir="ltr">{{ config('seo.contact_to', 'info@zatca.app') }}</a>
@endsection
