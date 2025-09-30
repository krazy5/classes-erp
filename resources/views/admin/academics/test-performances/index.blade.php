@extends('layouts.admin')

@section('title', 'Test Performance | ' . config('app.name'))
@section('header', 'Test Performance')

@section('content')
    @include('academics.test-performances.index-content')
@endsection
