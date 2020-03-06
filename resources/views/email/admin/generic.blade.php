@component('email.template.master', ['design' => 'light'])

@slot('header')
@component('email.components.header', ['p' => $title, 'logo' => $logo])
@endcomponent
@endslot

@slot('greeting')
@lang($test)
@endslot

@component('email.components.button', ['url' => $url])
@lang($button)
@endcomponent

@slot('signature')
<img style="display:block; width:100px;height:100px;" id="base64image" src="{{$signature}}"/>
@endslot

@slot('footer')
@component('email.components.footer', ['url' => 'https://tamtam.com', 'url_text' => '&copy; TamTam'])
For any info, please visit TamTam.
@endcomponent
@endslot

@endcomponent
