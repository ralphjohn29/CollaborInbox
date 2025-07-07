<?php
// This is a fallback home page in case Laravel routing isn't working properly
?>
<!DOCTYPE html>
<html>
<head>
    <title>CollaborInbox - Home Fallback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px;
            background-color: #f7fafc;
            color: #4a5568;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2d3748;
        }
        p {
            line-height: 1.6;
        }
        .note {
            margin-top: 20px;
            padding: 15px;
            background-color: #ebf8ff;
            border-left: 4px solid #4299e1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to CollaborInbox - Fallback Page</h1>
        <p>This is a fallback home page that is shown when the Laravel routing system isn't able to serve the welcome page correctly.</p>
        
        <div class="note">
            <strong>Note:</strong> If you're seeing this page, it means there might be an issue with the Laravel routing configuration.
            Try accessing the following URLs to test:
            <ul>
                <li><a href="/hello">/hello</a> - Should display a simple message from WelcomeController</li>
                <li><a href="/test">/test</a> - A direct route test in web.php</li>
            </ul>
        </div>
    </div>
</body>
</html> 