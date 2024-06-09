<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <p>Hello {{ $userName }},</p>

    <p>Your order has been successfully created with the following details:</p>

    <p>
        <strong>Client Name:</strong> {{ $clientName }}<br>
        <strong>Order Number:</strong> {{ $orderNumber }}
    </p>

    <p>Thank you for your order!</p>

    <p>Best regards,<br>Your Company</p>
</body>
</html>