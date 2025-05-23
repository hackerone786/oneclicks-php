<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        h1 {
            font-size: 2.5rem;
            color: #e74c3c;
        }

        p {
            font-size: 1.2rem;
            color: #555;
        }

        .message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 30px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .footer {
            font-size: 0.9rem;
            margin-top: 40px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Session Expired</h1>
    <p>Your session has expired, and its no longer valid.</p>
    <div class="message">
        <strong
