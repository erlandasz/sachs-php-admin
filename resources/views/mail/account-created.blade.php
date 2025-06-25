<x-mail::message>
# Welcome to {{ config('app.name') }}

Your account has been created successfully!

**Your temporary password is:**
**{{ $password }}**

Please log in and change your password as soon as possible.

<x-mail::button :url="route('login')">
    Log In Now
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>
