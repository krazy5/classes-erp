@extends('layouts.admin')

@section('title', 'Create Enquiry | ' . config('app.name'))
@section('header', 'Create Enquiry')

@section('content')
    <form action="{{ route('admin.enquiries.store') }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @include('admin.enquiries._form', ['enquiry' => $enquiry])
    </form>
@endsection
