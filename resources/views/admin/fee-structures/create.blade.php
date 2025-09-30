@extends('layouts.admin')

@section('title', 'Create Fee Structure | ' . config('app.name'))
@section('header', 'Create Fee Structure')

@section('content')
    <form action="{{ route('admin.fee-structures.store') }}" method="POST" class="mx-auto max-w-3xl space-y-6">
        @include('admin.fee-structures._form', ['feeStructure' => $feeStructure])
    </form>
@endsection
