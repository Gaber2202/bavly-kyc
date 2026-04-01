@extends('layouts.app')

@section('title', 'تعديل KYC')

@section('heading', 'تعديل ملف KYC')

@section('content')
    <form method="post" action="{{ route('kyc.update', $record) }}">
        @csrf
        @method('PUT')
        @include('kyc._form', ['record' => $record])
    </form>
@endsection
