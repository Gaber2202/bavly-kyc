@extends('layouts.app')

@section('title', 'إضافة KYC')

@section('heading', 'نموذج KYC جديد')

@section('content')
    <form method="post" action="{{ route('kyc.store') }}">
        @csrf
        @include('kyc._form', ['record' => $record])
    </form>
@endsection
