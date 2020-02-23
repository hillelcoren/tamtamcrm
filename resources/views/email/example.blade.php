@if()
@component('email.components.layout-dark')
@else
@component('email.components.layout')
@endif

@slot('header')
    @component('email.components.header', ['p' => 'Your upgrade has completed!'])
        Upgrade!
    @endcomponent
@endslot

@slot('greeting')
    Hello, David
@endslot

Hello, this is really tiny template. We just want to inform you that upgrade has been completed.

@component('email.components.button', ['url' => 'https://tamtam.com'])
    Visit TamTam
@endcomponent

@component('email.components.table')
| Laravel       | Table         | Example  |
| ------------- |:-------------:| --------:|
| Col 2 is      | Centered      | $10      |
| Col 3 is      | Right-Aligned | $20      |
@endcomponent

@slot('signature')
    Benjamin, InvoiceNinja (michael@tamtam.com)
@endslot

@slot('footer')
    @component('email.components.footer', ['url' => 'https://tamtam.com', 'url_text' => '&copy; TamTam'])
        For any info, please visit TamTam.
    @endcomponent
@endslot

@slot('below_card')
    Lorem ipsum dolor sit amet. I love TamTam CRM.
@endslot    

@endcomponent
