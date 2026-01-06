@extends('system.layouts.app')

@section('content')

    <div class="row">
        <!--<div class="col-lg-6 col-md-12 pt-2 pt-md-0">
            <system-companies-form></system-companies-form>
        </div> -->
        <div class="col-lg-6 col-md-12">
            <system-whatsapp-configuration
            :name="{{json_encode($name)}}"
           :api_whatsapp="{{json_encode($api_whatsapp)}}">
       </system-whatsapp-configuration>
            <system-certificate-index></system-certificate-index>
            {{-- <system-login-settings :configuration='@json($configuration)'></system-login-settings> --}}

            <system-login-other-configuration></system-login-other-configuration>
           
        </div>
        <div class="col-lg-6 col-md-12">
            <system-configuration-bg-image class="mb-2"></system-configuration-bg-image>
            <system-configuration-culqi  class="mb-2"></system-configuration-culqi>
            <system-configuration-token  class="mb-2"></system-configuration-token>
            <system-configuration-apk-url  class="mb-2"></system-configuration-apk-url>
            <system-configuration-digemid  class="mb-2"></system-configuration-digemid>
        </div>
    </div>

@endsection
