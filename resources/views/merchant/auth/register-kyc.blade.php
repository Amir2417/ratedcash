@extends('merchant.layouts.user_auth')

@php
    $allCountries = get_all_countries() ?? [];
    $dialCode = getDialCode();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();
@endphp

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account kyc">
    <div id="body-overlay" class="body-overlay"></div>
    <div class="account-area">
        <div class="account-wrapper kyc">
            <div class="account-logo text-center">
                <a href="{{ setRoute('index') }}" class="site-logo">
                    <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                    data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                        alt="site-logo">
                </a>
            </div>
            <h5 class="title">{{ __("KYC Form") }}</h5>
            <p>{{ __("Please input all the fild for login to your account to get access to your dashboard.") }}</p>
            <form class="account-form" action="{{ setRoute('merchant.register.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                            'name'          => "firstname",
                            'placeholder'   => "First Name",
                            'value'         => old("firstname"),
                        ])
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                                    'name'          => "lastname",
                                    'placeholder'   => "Last Name",
                                    'value'         => old("lastname"),
                        ])
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                                    'name'          => "business_name",
                                    'placeholder'   => "Business Name",
                                    'value'         => old("business_name"),
                        ])
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        <select name="country" class="form--control select2-auto-tokenize country-select" data-old="{{ old('country') }}">
                            @foreach ($allCountries as $country)
                                @if ($dialCode == $country->mobile_code)
                                <option  value="{{ $country->name }}"
                                    {{ getDialCode() ==  $country->mobile_code?'selected':''}}
                                    data-data-mobile-code="{{ $country->mobile_code }}"
                                    >{{ $country->name }}</option>
                                @endif
                            @endforeach

                        </select>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext">@</span>
                            </div>
                            <input type="email" name="email" class="form--control" placeholder="Email" value="{{ old('email',@$email) }}">

                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                            'name'          => "city",
                            'placeholder'   => "City ",
                            'value'         => old("city"),
                        ])
                    </select>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                        @include('admin.components.form.input',[
                                    'name'          => "zip_code",
                                    'placeholder'   => "Enter Zip",
                                    'value'         => old('zip_code')
                                ])
                    </div>
                    <div class="col-xl-8 col-lg-8 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext">+{{ getDialCode() }}</span>
                            </div>
                            <input class="phone-code" type="hidden" name="phone_code" value="{{ getDialCode() }}" />
                            <input type="number" name="phone" class="form--control" placeholder="Enter Number" value="{{ old('mobile',@$mobile) }}" readonly>
                        </div>
                    </div>

                    @include('merchant.components.register-kyc',compact("kyc_fields"))
                    <div class="col-lg-6 col-md-4 form-group show_hide_password" id="">
                        <input type="password" class="form--control" name="password" placeholder="Password" required>
                        <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>


                    </div>
                    <div class="col-lg-6 col-md-4 form-group show_hide_password-2" id="">
                        <input type="password" class="form--control" name="password_confirmation" placeholder="Confirm Password" required>
                        <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>

                    </div>
                    @if($basic_settings->agree_policy)
                    <div class="col-lg-12 form-group">
                        <div class="custom-check-group">
                            <div class="custom-check-group mb-0">
                                <input type="checkbox" id="level-1" name="agree">
                                <label for="level-1" class="mb-0">{{ __("I have read agreed with the") }} <a href=" {{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                            </div>

                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit" class="btn--base w-100 btn-loading">Register <i class="fas fa-arrow-alt-circle-right ms-1"></i></button>
                    </div>
                    <div class="or-area">
                        <span class="or-line"></span>
                        <span class="or-title">Or</span>
                        <span class="or-line"></span>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label>{{ __("Already Have An Account?") }} <a href="{{ setRoute('merchant.login') }}" class="account-control-btn">{{ __("Login Now") }}</a></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>
@endsection

@push('script')
<script>
      $(document).ready(function(){
        $("select[name=country]").change(function(){
            var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
            placePhoneCode(phoneCode);
        });

        setTimeout(() => {
            var phoneCodeOnload = $("select[name=country] :selected").attr("data-mobile-code");
            placePhoneCode(phoneCodeOnload);
        }, 400);
    });
</script>

@endpush
