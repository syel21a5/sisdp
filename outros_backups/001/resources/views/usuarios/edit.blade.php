@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-person-gear"></i> Editar Usuário</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Usuário</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $usuario->username) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <div class="mb-3">
                            <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
                            <select class="form-select" id="nivel_acesso" name="nivel_acesso" required>
                                <option value="administrador" {{ $usuario->nivel_acesso == 'administrador' ? 'selected' : '' }}>Administrador</option>
                                <option value="usuario" {{ $usuario->nivel_acesso == 'usuario' ? 'selected' : '' }}>Usuário</option>
                            </select>
                        </div>

                        @php
                            $permissions = $usuario->permissions ?? [];
                            $menuLateral = isset($permissions['menu_lateral']) ? $permissions['menu_lateral'] : true;
                            $permApreensao = isset($permissions['apreensao']) ? $permissions['apreensao'] : true;
                            $permAdministrativo = isset($permissions['administrativo']) ? $permissions['administrativo'] : true;
                            $permCelular = isset($permissions['celular']) ? $permissions['celular'] : true;
                            $permVeiculo = isset($permissions['veiculo']) ? $permissions['veiculo'] : true;
                            $permApreensaoOutros = isset($permissions['apreensao_outros']) ? $permissions['apreensao_outros'] : true;
                            $permIntimacao = isset($permissions['intimacao']) ? $permissions['intimacao'] : true;
                            $permApfd = isset($permissions['apfd']) ? $permissions['apfd'] : true;
                        @endphp

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card mb-3">
                                    <div class="card-header">Permissões de Acesso</div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-header">Grupo Apreensão</div>
                                                    <div class="card-body">
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="perm_apreensao" name="perm_apreensao" {{ old('perm_apreensao', $permApreensao) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_apreensao">Acesso ao grupo Apreensão</label>
                                                        </div>
                                                        <div class="ms-3">
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox" class="form-check-input" id="perm_celular" name="perm_celular" {{ old('perm_celular', $permCelular) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="perm_celular">Acesso a Celulares</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox" class="form-check-input" id="perm_veiculo" name="perm_veiculo" {{ old('perm_veiculo', $permVeiculo) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="perm_veiculo">Acesso a Veículos</label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input type="checkbox" class="form-check-input" id="perm_apreensao_outros" name="perm_apreensao_outros" {{ old('perm_apreensao_outros', $permApreensaoOutros) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="perm_apreensao_outros">Acesso a Outros Itens</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-header">Módulos Gerais</div>
                                                    <div class="card-body">
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="perm_apfd" name="perm_apfd" {{ old('perm_apfd', $permApfd) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_apfd">Acesso ao Módulo APFD | IP</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="perm_administrativo" name="perm_administrativo" {{ old('perm_administrativo', $permAdministrativo) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_administrativo">Acesso ao Administrativo</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="perm_intimacao" name="perm_intimacao" {{ old('perm_intimacao', $permIntimacao) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_intimacao">Acesso a Intimação</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-4 mb-3">
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="menu_lateral" name="menu_lateral" {{ old('menu_lateral', $menuLateral) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="menu_lateral">Acesso ao Menu Lateral</label>
                            </div>
                            <div class="form-check mb-0">
                                <input type="checkbox" class="form-check-input" id="ativo" name="ativo" {{ old('ativo', $usuario->ativo) ? 'checked' : '' }}>
                                <label class="form-check-label ms-1" for="ativo">Usuário Ativo</label>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var grp = document.getElementById('perm_apreensao');
                            var subs = ['perm_celular','perm_veiculo','perm_apreensao_outros'];
                            function syncSubs() {
                                var enabled = grp.checked;
                                subs.forEach(function(id){
                                    var el = document.getElementById(id);
                                    if (el) { el.disabled = !enabled; }
                                });
                            }
                            if (grp) {
                                grp.addEventListener('change', syncSubs);
                                syncSubs();
                            }
                        });
                        </script>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Atualizar Usuário
                            </button>
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
