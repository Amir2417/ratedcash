@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Virtual Account Service")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Virtual Account Service") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.virtual.account.service.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group configForm" id="flutterwave">
                        <div class="row" >
                            <div class="col-xl-12 col-lg-12 form-group">
                                <label>{{ __("Name*") }}</label>
                                <select class="form--control nice-select" name="name">
                                    <option disabled>{{ __("Select Platfrom") }}</option>
                                    <option value="9psb" @if(@$service->config->name == '9psb') selected @endif>@lang('9PSB')</option>
                                </select>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="row">
                                    <div class="custom-inner-card">
                                        <div class="card-inner-header">
                                            <h5>{{ __("Wallet as a Service") }}</h5>
                                        </div>
                                        <div class="card-inner-body">
                                            <div class="row">
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("User Name*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="username" value="{{ @$service->config->username }}">
                                                    </div>
                                                </div>
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Password*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-hashtag"></i></span>
                                                        <input type="text" class="form--control" name="password" value="{{ @$service->config->password }}">
                                                    </div>
                                                </div>
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Client ID*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="clientId" value="{{ @$service->config->clientId }}">
                                                    </div>
                                                </div>
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Client Secret*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="clientSecret" value="{{ @$service->config->clientSecret }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                            <div class="col-xl-12 col-lg-12 col-md-12 mt-2">
                                <div class="row">
                                    <div class="custom-inner-card">
                                        <div class="card-inner-header">
                                            <h5>{{ __("Virtual Account & Payout") }}</h5>
                                        </div>
                                        <div class="card-inner-body">
                                            <div class="row">
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Public Key*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="publickey" value="{{ @$service->config->publickey }}">
                                                    </div>
                                                </div>
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Private Key*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="privatekey" value="{{ @$service->config->privatekey }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                            <div class="col-xl-12 col-lg-12 col-md-12 mt-2">
                                <div class="row">
                                    <div class="custom-inner-card">
                                        <div class="card-inner-header">
                                            <h5>{{ __("Virtual Account Service") }}</h5>
                                        </div>
                                        <div class="card-inner-body">
                                            <div class="row">
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Api Key*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="apiKey" value="{{ @$service->config->apiKey }}">
                                                    </div>
                                                </div>
                                                <div class="col-xxl-6 col-xl-6 col-lg-6 form-group">
                                                    <label>{{ __("Secret Key*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-key"></i></span>
                                                        <input type="text" class="form--control" name="secretKey" value="{{ @$service->config->secretKey }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group mt-2">
                                <div class="row">
                                    <div class="custom-inner-card">
                                        <div class="card-inner-header">
                                            <h5>{{ __("Service Base Url") }}</h5>
                                        </div>
                                        <div class="card-inner-body">
                                            <div class="row">
                                                <div class="col-12 form-group">
                                                    <label>{{ __("Service Url*") }}</label>
                                                    <div class="input-group append">
                                                        <span class="input-group-text"><i class="las la-link"></i></span>
                                                        <input type="text" class="form--control" name="service_url" value="{{ @$service->config->service_url }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>   
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input-text-rich',[
                            'label'         => 'Card Details*',
                            'name'          => 'card_details',
                            'value'         => old('card_details',@$service->card_details),
                            'placeholder'   => "Write Here...",
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="card-image">{{ __("Background Image") }}</label>
                        <div class="col-12 col-sm-6 m-auto">
                            @include('admin.components.form.input-file',[
                                'label'             => false,
                                'class'             => "file-holder m-auto",
                                'old_files_path'    => files_asset_path('virtual-account-service'),
                                'name'              => "image",
                                'old_files'         => old('image',@$service->image)
                            ])
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => "Update",
                            'permission'    => "admin.virtual.account.service.update"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    
@endpush
