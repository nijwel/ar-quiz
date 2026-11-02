@extends('layouts.app')

@section('title', 'New Category')

@section('content')
    <x-page-hero icon="icon-[tabler--category-plus]" title="New Category" subtitle="নতুন বিষয় বা কোনো গ্রুপের সাব-ক্যাটেগরি তৈরি করুন।" />

    @include('admin.category._form', ['action' => route('category.store'), 'submitLabel' => 'Create Category'])
@endsection
