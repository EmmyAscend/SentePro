<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Registration</title>
</head>
<body>
    <h1>Business Registration</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <form action="{{ route('business.register.store') }}" method="POST">
        @csrf

        <input type="text" name="business_name" placeholder="Business Name" required>
        <input type="text" name="trading_name" placeholder="Trading Name" required>
        <input type="text" name="registration_number" placeholder="Registration Number" required>
        <input type="text" name="country" placeholder="Country" required>
        <input type="text" name="phone" placeholder="Phone" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="industry" placeholder="Industry" required>
        <input type="text" name="expected_monthly_volume" placeholder="Expected Monthly Volume" required>
        <textarea name="business_description" placeholder="Business Description"></textarea>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
