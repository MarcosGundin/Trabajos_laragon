<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="{{ asset('bootstrap/bootstrap.min.css') }}" rel="stylesheet">    
    <script src="{{ asset('bootstrap/bootstrap.bundle.min.js') }}"></script></head>
<body>
    @if(Auth::user()->rol->nombre==='controlador')
    <nav class="navbar navbar-expand-lg navbar-controlador bg-controlador controlador">
    @elseif(Auth::user()->rol->nombre==='administrativo')
    <nav class="navbar navbar-expand-lg navbar-administrativo bg-administrativo administrativo">
    @endif
        <div class="container">
            <a class="navbar-brand" href="#">Mi Aplicación</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    @if(Auth::user()->rol->nombre==='controlador')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('crud.index') }}">CRUD</a>
                    </li>
                    @endif
                </ul>
                <span class="navbar-text me-3">
                    Bienvenido, {{ Auth::user()->name }} ({{ Auth::user()->rol->nombre }})
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Dashboard</div>
                    <div class="card-body">
                        <h5>Bienvenido a tu panel de control</h5>
                        <p>Tu rol es: <strong></strong></p>
                        
                        @if(Auth::user()->rol->nombre==='controlador')
                            <div class="alert alert-info">
                                Tienes acceso completo al sistema, incluyendo el CRUD.
                                <a href="{{ route('crud.index') }}" class="btn btn-primary mt-2">Ir al CRUD</a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Tu rol actual no tiene acceso a las funcionalidades de CRUD.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

  
</body>
</html>

