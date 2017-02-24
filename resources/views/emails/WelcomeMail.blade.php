@component('mail::message')

<div style="text-align:center; font-weight:bold;"> Welcome to RiftBets, {{ $name }}!</div>
<br>
Thank you for signing up for RiftBets!

Thanks,<br>
The {{ config('app.name') }} Team

@component('mail::button', ['url' => ''])
Go to RiftBets
@endcomponent

@endcomponent
