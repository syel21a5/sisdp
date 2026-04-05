@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-people"></i> Gerenciar Usuários</h4>
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Novo Usuário
                    </a>
                </div>

                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Usuário</th>
                                    <th>Nível de Acesso</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usuarios as $usuario)
                                <tr>
                                    <td>{{ $usuario->id }}</td>
                                    <td>{{ $usuario->nome }}</td>
                                    <td>{{ $usuario->username }}</td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->nivel_acesso == 'administrador' ? 'danger' : 'primary' }}">
                                            {{ $usuario->nivel_acesso }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $usuario->ativo ? 'success' : 'secondary' }}">
                                            {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                            
                                            <form action="{{ route('usuarios.toggle', $usuario->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $usuario->ativo ? 'secondary' : 'success' }}">
                                                    <i class="bi bi-power"></i> {{ $usuario->ativo ? 'Desativar' : 'Ativar' }}
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ✅ SEÇÃO DE BACKUP DO BANCO DE DADOS --}}
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #1a1a2e, #16213e); border: none;">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bi bi-database-down me-2"></i> Backup do Banco de Dados
                    </h5>
                </div>
                <div class="card-body p-4" style="background: #f8f9fa;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="mb-1 text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Gera um dump completo do banco de dados MySQL, compacta em <strong>.ZIP</strong> e faz o download direto no seu computador.
                            </p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> O processo pode levar alguns segundos dependendo do tamanho do banco.
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('backup.download') }}" 
                               id="btnBackup"
                               class="btn btn-lg fw-bold px-4 py-2 shadow-sm" 
                               style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: #fff; border: none; border-radius: 10px;"
                               onclick="var b=this; b.innerHTML='<i class=\'bi bi-hourglass-split me-2\'></i> Gerando backup...'; b.style.opacity='0.7'; b.style.pointerEvents='none'; setTimeout(function(){ b.innerHTML='<i class=\'bi bi-download me-2\'></i> Baixar Backup'; b.style.opacity='1'; b.style.pointerEvents='auto'; }, 15000);">
                                <i class="bi bi-download me-2"></i> Baixar Backup
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection