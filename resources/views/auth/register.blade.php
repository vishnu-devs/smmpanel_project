@extends('layouts.guest')

@section('title', 'Sign Up - ' . App\Models\Setting::get('site_name', 'RishiSMM'))

@section('content')
    @include('auth.auth_landing_component', ['activeTab' => 'register'])
@endsection
