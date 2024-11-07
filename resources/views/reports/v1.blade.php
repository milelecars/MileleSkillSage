<!DOCTYPE html>
<html>
<head>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Candidate Report</title>
    </head>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
        }
        .header {
            text-align: center;
            color: #333;
            padding-bottom: 20px;
        }
        .content {
            margin: 20px;
            line-height: 1.5;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on: {{ $date }}</p>
    </div>
    
    <div class="content">
        {{ $content }}
    </div>
    
    <div class="footer">
        Page {{ '[page]' }} of {{ '[pages]' }}
    </div>
</body>
</html>