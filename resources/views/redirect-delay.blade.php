<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="3;url={{ $url }}">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: sans-serif;
            text-align: center;
        }
    </style>
</head>
<body>
    <div>
        <h2>Redirecting to App Store...</h2>
        <p>You’ll be redirected in <span id="countdown">3</span> seconds.</p>
    </div>

    <script>
        let countdown = 3;
        const interval = setInterval(() => {
            countdown--;
            document.getElementById('countdown').textContent = countdown;
            if (countdown <= 0) clearInterval(interval);
        }, 1000);
    </script>
</body>
</html>
