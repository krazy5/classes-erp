@extends('layouts.admin')

@section('title', 'Edit Enquiry | ' . config('app.name'))
@section('header', 'Edit Enquiry')

@section('content')
    <form action="{{ route('admin.enquiries.update', $enquiry) }}" method="POST" class="mx-auto max-w-4xl space-y-6">
        @method('PUT')
        @include('admin.enquiries._form', ['enquiry' => $enquiry])
    </form>
@endsection
