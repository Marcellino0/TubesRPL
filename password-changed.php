<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Diperbarui</title>
    <style>
        /* Styling for the success page */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .message-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            text-align: center;
        }

        .message-box h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .message-box p {
            color: #7f8c8d;
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message-box">
            <h1>Password Berhasil Diperbarui</h1>
            <p>Password Anda telah berhasil diperbarui. Silakan login dengan password baru Anda.</p>
            <a href="login.php"><button class="btn">Login</button></a>
        </div>
    </div>
</body>
</html>
