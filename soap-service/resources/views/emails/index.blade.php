<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Purchase Confirmation</title>
</head>

<body>
    <p>Hi {{$purchase["name"]}}.</p>
    <p>To confirm the purchase with the price {{$purchase["price"]}}$, Please click <a href="{{$purchase["confirm_url"]}}">here</a></p>
</body>

</html>