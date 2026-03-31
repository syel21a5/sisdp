<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SisDP - Administração</title>

    <!-- Favicon Online - Emoji de Policial -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👮</text></svg>">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    @stack('styles')

    <style>
        .navbar-brand {
            font-weight: 600;
        }
        .nav-link {
            font-weight: 500;
        }
        .btn-link.nav-link {
            padding: 0.5rem 0;
            margin: 0;
            border: none;
            background: none;
            color: rgba(255, 255, 255, 0.75) !important;
        }
        .btn-link.nav-link:hover {
            color: white !important;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">

        <div class="container-fluid">

            <a class="navbar-brand" href="{{ url('/wf-geral') }}">

                <i class="bi bi-shield-lock"></i> SisDP

            </a>



            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>

            </button>



            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">

                        <a class="nav-link" href="{{ url('/wf-geral') }}">

                            <i class="bi bi-house"></i> Início

                        </a>

                    </li>



                    <!-- Mostrar link de Usuários apenas para administradores -->

                    @auth

                        @if(Auth::user()->nivel_acesso === 'administrador')

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('usuarios.index') }}">
                                    <i class="bi bi-people"></i> Usuários
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('administrativo.auditoria') }}">
                                    <i class="bi bi-list-ul"></i> Auditoria
                                </a>
                            </li>

                        @endif

                    @endauth

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('consulta.pessoa.index') }}">
                            <i class="bi bi-person-bounding-box"></i> Antecedentes
                        </a>
                    </li>



                    <li class="nav-item">

                        <form action="{{ route('logout') }}" method="POST" class="d-inline">

                            @csrf

                            <button type="submit" class="btn btn-link nav-link">

                                <i class="bi bi-box-arrow-right"></i> Sair

                            </button>

                        </form>

                    </li>

                </ul>

            </div>

        </div>

    </nav>



    <main class="container">

        <!-- Mensagens de Alerta -->

        @if(session('success'))

            <div class="alert alert-success alert-dismissible fade show">

                <i class="bi bi-check-circle"></i> {{ session('success') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

        @endif



        @if(session('error'))

            <div class="alert alert-danger alert-dismissible fade show">

                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

        @endif



        @if($errors->any())

            <div class="alert alert-danger alert-dismissible fade show">

                <i class="bi bi-exclamation-triangle"></i>

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

        @endif



        @yield('content')

    </main>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>


    <!-- JS Central do Sistema -->
    <script src="{{ asset('js/core.js') }}"></script>

    @stack('scripts')

    <script>

        // Fechar alertas automaticamente após 5 segundos

        document.addEventListener('DOMContentLoaded', function() {

            const alerts = document.querySelectorAll('.alert');

            alerts.forEach(function(alert) {

                setTimeout(function() {

                    const bsAlert = new bootstrap.Alert(alert);

                    bsAlert.close();

                }, 5000);

            });

        });

    </script>

</body>

</html>
