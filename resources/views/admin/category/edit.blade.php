@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <x-page-hero icon="icon-[tabler--pencil]" eyebrow="Editing" :title="$category->name" subtitle="নাম, গ্রুপ বা স্ট্যাটাস পরিবর্তন করুন।" />

    @include('admin.category._form', ['action' => route('category.update', $category->id), 'submitLabel' => 'Save Changes'])
@endsection
