@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('Access Forbidden'))
@section('message2', __('You do not have permission to access this page'))
@section('messageurl', __('Back to dashboard'))
