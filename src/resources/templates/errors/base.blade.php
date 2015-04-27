<html>
    <head>
        <title>{{$title}}</title>
        <style>
            .error-template{
                margin: 50px auto;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="error-template">
            <h2>{{$title}}</h2>
            <div class="error-details">{{$body}}</div>
        </div>
    </body>
</html>