<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>RPG Sim</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body >
        <!-- Image and text -->
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand" href="#">
                RPG Simulation
            </a>
        </nav>

        <div class="container mt-10" id='app'>
            <game-component></game-component>
        </div>
        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>
        
        <!-- jQuery -->
        <!-- <script src="js/jquery.js"></script> -->

        <!-- Bootstrap Core JavaScript -->
        <!-- <script src="js/bootstrap.min.js"></script> -->
    </body>
</html>
