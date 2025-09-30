@extends('layouts.admin')

@section('title', 'Edit Fee Structure | ' . config('app.name'))
@section('header', 'Edit Fee Structure')

@section('content')
    <form action="{{ route('admin.fee-structures.update', $feeStructure) }}" method="POST" class="mx-auto max-w-3xl space-y-6">
        @method('PUT')
        @include('admin.fee-structures._form', ['feeStructure' => $feeStructure])
    </form>
@endsection
