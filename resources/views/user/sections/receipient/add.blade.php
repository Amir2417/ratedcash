@extends('user.layouts.master')

@push('css')

@endpush
@php
    $token = (object)session()->get('remittance_token');
@endphp

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="row justify-content-center mb-30-none">
        <div class="col-xl-12 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ @$page_title }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('user.receipient.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group transaction-type">
                                    <label for="bank_name">{{ __("Select Bank") }} <span class="text-danger">*</span></label>
                                    <select name="bank_name" class="form--control select2-auto-tokenize" id="receipent_select" required data-placeholder="Select Bank" >
                                        <option disabled selected value="">{{ __("Select Bank") }}</option>
                                        @foreach ($banks ?? [] as $bank)
                                            <option value="{{ $bank['code'] }}|{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label for="account_number">{{ __("Account Number") }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form--control " id="account_number"  name="account_number" value="{{ old('account_number') }}" placeholder="Account Number" required>
                                    <label class="exist text-start"></label>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading transfer">{{ __("Add Receipient") }} <i class="fas fa-plus-circle ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')


@endpush
