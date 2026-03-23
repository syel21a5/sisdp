<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SisDP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    @include('layouts.app')
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Dashboard - SisDP</h2>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i>
                            <strong>Login realizado com sucesso!</strong>
                            Bem-vindo, {{ Auth::user()->nome }}!
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Usuários</h5>
                                        <p class="card-text">Gerenciar usuários do sistema</p>
                                        <a href="{{ route('usuarios.index') }}" class="btn btn-light">Acessar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Relatórios</h5>
                                        <p class="card-text">Gerar relatórios do sistema</p>
                                        <a href="#" class="btn btn-light">Acessar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">Configurações</h5>
                                        <p class="card-text">Configurações do sistema</p>
                                        <a href="#" class="btn btn-light">Acessar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>