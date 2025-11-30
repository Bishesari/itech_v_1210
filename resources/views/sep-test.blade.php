<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>تست درگاه SEP</title>
</head>
<body>

<h2>تست درگاه پرداخت سامان (SEP)</h2>

<form method="POST" action="{{ route('sep.pay') }}">
    @csrf
    <button type="submit">پرداخت تستی 15,000 ریال</button>
</form>

</body>
</html>
