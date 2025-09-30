# {{ $exception->class() }} - {!! $exception->title() !!}
{!! $exception->message() !!}

PHP {{ PHP_VERSION }}
Laravel {{ app()->version() }}
{{ $exception->request()->httpHost() }}

## Stack Trace

@foreach($exception->frames() as $index => $frame)
{{ $index }} - {{ $frame->file() }}:{{ $frame->line() }}
@endforeach

## Request

{{ $exception->request()->method() }} {{ \Illuminate\Support\Str::start($exception->request()->path(), '/') }}

## Headers

@php
    $headers = $exception->requestHeaders();
@endphp
@if(!empty($headers))
    @foreach($headers as $key => $value)
* **{{ $key }}**: {!! $value !!}
    @endforeach
@else
No header data available.
@endif

## Route Context

@php
    $context = $exception->applicationRouteContext();
@endphp
@if(!empty($context))
    @foreach($context as $name => $value)
{{ $name }}: {!! $value !!}
    @endforeach
@else
No routing data available.
@endif

## Route Parameters

@if ($routeParametersContext = $exception->applicationRouteParametersContext())
{!! $routeParametersContext !!}
@else
No route parameter data available.
@endif

## Database Queries

@php
    $queries = $exception->applicationQueries();
@endphp
@if(!empty($queries))
    @foreach($queries as $query)
        @php
            $connectionName = $query['connectionName'] ?? 'default';
            $sql = $query['sql'] ?? '';
            $time = $query['time'] ?? '';
        @endphp
* {{ $connectionName }} - {!! $sql !!} ({{ $time }} ms)
    @endforeach
@else
No database queries detected.
@endif
