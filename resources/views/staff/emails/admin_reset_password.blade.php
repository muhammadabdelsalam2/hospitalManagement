@component('mail::message')
# Reset Account
Welcome {{ $data['data']->name }} <br>
The body of your message.

@component('mail::button', ['url' => aurl('staff/reset/password/'.$data['token'])])
Click Here To Reset Your Password
@endcomponent
Or <br>
copy this link
<a href="{{ aurl('staff/reset/password/'.$data['token']) }}">{{ aurl('staff/reset/password/'.$data['token']) }}</a>

Thanks,<br>
{{ config('app.name') }}
@endcomponent
