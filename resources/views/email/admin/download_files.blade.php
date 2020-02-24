@component('email.components.layout')

    @slot('header')
        @component('email.components.header', ['p' => ''])
            <img src="{{ $logo }}" alt="Company Logo" style="display: block">
        @endcomponent
    @endslot

    @lang('texts.download_timeframe')

    @component('email.components.button', ['url' => $url])
        @lang('texts.download')
    @endcomponent

    @slot('signature')
        TamTam
    @endslot

    @slot('footer')
        @component('email.components.footer', ['url' => 'https://tamtam.com', 'url_text' => '&copy; TamTam'])
            For any info, please visit TamTam.
        @endcomponent
    @endslot

@endcomponent
