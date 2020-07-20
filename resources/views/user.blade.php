<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User</title>
    <style>
        *{
            margin: 0;
            padding: 0;
        }
        ul li{
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div>
        <ul>
            @foreach($usuario as $section => $value)
                <li>{{ $section }} = {{ $value }}</li>
            @endforeach
        </ul>
        <a href="/">Home</a>
    </div>
</body>
</html>
