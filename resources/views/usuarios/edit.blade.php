@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-11">
            <x-card title="Editar Usuário: {{ $usuario->nome }}" icon="bi-person-gear" headerClass="bg-dark text-white border-0 py-3">
                
                <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Header da Pessoa e Senhas -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nome" class="form-label fw-bold">Nome Completo</label>
                            <input type="text" class="form-control form-control-lg bg-light" id="nome" name="nome" value="{{ old('nome', $usuario->nome) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold">Nome de Usuário</label>
                            <input type="text" class="form-control form-control-lg bg-light" id="username" name="username" value="{{ old('username', $usuario->username) }}" required>
                        </div>
                        
                        <div class="col-md-4 mt-4">
                            <label for="nivel_acesso" class="form-label fw-bold text-primary">Nível de Acesso Especial</label>
                            <select class="form-select border-primary cursor-pointer border-2 fw-semibold" id="nivel_acesso" name="nivel_acesso" required>
                                <option value="administrador" {{ $usuario->nivel_acesso == 'administrador' ? 'selected' : '' }}>👑 Administrador Máximo</option>
                                <option value="usuario" {{ $usuario->nivel_acesso == 'usuario' ? 'selected' : '' }}>👤 Usuário Operador</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-4">
                            <label for="password" class="form-label fw-bold text-muted">Redefinir Senha</label>
                            <input type="password" class="form-control placeholder-sm" id="password" name="password" placeholder="Em branco para manter atual">
                        </div>
                        <div class="col-md-4 mt-4">
                            <label for="password_confirmation" class="form-label fw-bold text-muted">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repetir a nova senha">
                        </div>
                    </div>

                    @php
                        $permissions = $usuario->permissions ?? [];
                        // defaults are true for older logic, meaning if not set they inherit access
                        $menuLateral = isset($permissions['menu_lateral']) ? $permissions['menu_lateral'] : true;
                        $permApreensao = isset($permissions['apreensao']) ? $permissions['apreensao'] : true;
                        $permAdministrativo = isset($permissions['administrativo']) ? $permissions['administrativo'] : true;
                        $permCelular = isset($permissions['celular']) ? $permissions['celular'] : true;
                        $permVeiculo = isset($permissions['veiculo']) ? $permissions['veiculo'] : true;
                        $permApreensaoOutros = isset($permissions['apreensao_outros']) ? $permissions['apreensao_outros'] : true;
                        $permIntimacao = isset($permissions['intimacao']) ? $permissions['intimacao'] : true;
                        $permApfd = isset($permissions['apfd']) ? $permissions['apfd'] : true;
                        $permOficios = isset($permissions['oficios']) ? $permissions['oficios'] : true;
                        $permOitivas = isset($permissions['oitivas']) ? $permissions['oitivas'] : true;
                        $permPericias = isset($permissions['pericias']) ? $permissions['pericias'] : true;
                        $permPecas = isset($permissions['pecas']) ? $permissions['pecas'] : true;
                        $permPreliminares = isset($permissions['preliminares']) ? $permissions['preliminares'] : true;
                        
                        // NOVAS PERMISSÕES RECENTES: Infopol, Antecedentes, Sem Chips
                        $permInfopol = isset($permissions['infopol']) ? $permissions['infopol'] : true;
                        $permAntecedentes = isset($permissions['antecedentes']) ? $permissions['antecedentes'] : true;
                        $permBoeSemChip = isset($permissions['boe_sem_chip']) ? $permissions['boe_sem_chip'] : true;
                    @endphp

                    <h5 class="fw-bold mt-5 mb-3 border-bottom border-2 border-primary pb-2 d-flex align-items-center"><i class="bi bi-shield-lock-fill text-primary me-2 fs-4"></i> Controle de Permissões</h5>

                    <div class="row g-4 mb-4">
                        
                        <!-- MÓDULOS GERAIS E MODERNOS -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100" style="background-color: #f8f9fa;">
                                <div class="card-header bg-dark text-white fw-bold py-2"><i class="bi bi-rocket me-1"></i> Módulos Principais & IA</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_apfd" name="perm_apfd" {{ old('perm_apfd', $permApfd) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_apfd">Acesso ao Módulo APFD | IP</label>
                                    </div>
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_administrativo" name="perm_administrativo" {{ old('perm_administrativo', $permAdministrativo) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_administrativo">Acesso Administrativo Base</label>
                                    </div>
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_intimacao" name="perm_intimacao" {{ old('perm_intimacao', $permIntimacao) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_intimacao">Módulo Intimações</label>
                                    </div>
                                    
                                    <hr class="my-3 opacity-25">
                                    <h6 class="text-muted fw-bold small text-uppercase mb-2">Novos Operacionais</h6>
                                    
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_infopol" name="perm_infopol" {{ old('perm_infopol', $permInfopol) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_infopol"><i class="bi bi-cloud-arrow-down text-primary"></i> Sincronizador INFOPOL (Automação BOE)</label>
                                    </div>
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_antecedentes" name="perm_antecedentes" {{ old('perm_antecedentes', $permAntecedentes) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_antecedentes"><i class="bi bi-robot text-danger"></i> Consultor Antecedentes (IA)</label>
                                    </div>
                                    <div class="form-check form-switch mb-2 fs-6">
                                        <input type="checkbox" class="form-check-input cursor-pointer" id="perm_boe_sem_chip" name="perm_boe_sem_chip" {{ old('perm_boe_sem_chip', $permBoeSemChip) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold cursor-pointer" for="perm_boe_sem_chip"><i class="bi bi-search text-warning"></i> Monitor de Ocorrências Sem Envolvidos</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GRUPO APREENSÃO -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 h-100" style="background-color: #f8f9fa;">
                                <div class="card-header bg-dark text-white fw-bold py-2"><i class="bi bi-box-seam me-1"></i> Controle de Apreensões</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3 p-3 bg-white border rounded">
                                        <input type="checkbox" class="form-check-input ms-0 me-2 mt-1 cursor-pointer" id="perm_apreensao" name="perm_apreensao" {{ old('perm_apreensao', $permApreensao) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer fs-6" for="perm_apreensao">
                                            Habilitar Módulo Raiz de Apreensão
                                        </label>
                                    </div>
                                    
                                    <div class="ms-3 border-start border-3 border-secondary ps-3">
                                        <div class="form-check form-switch mb-2 fs-6">
                                            <input type="checkbox" class="form-check-input cursor-pointer" id="perm_celular" name="perm_celular" {{ old('perm_celular', $permCelular) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-secondary cursor-pointer" for="perm_celular"><i class="bi bi-phone"></i> Celulares</label>
                                        </div>
                                        <div class="form-check form-switch mb-2 fs-6">
                                            <input type="checkbox" class="form-check-input cursor-pointer" id="perm_veiculo" name="perm_veiculo" {{ old('perm_veiculo', $permVeiculo) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-secondary cursor-pointer" for="perm_veiculo"><i class="bi bi-car-front"></i> Veículos</label>
                                        </div>
                                        <div class="form-check form-switch mb-2 fs-6">
                                            <input type="checkbox" class="form-check-input cursor-pointer" id="perm_apreensao_outros" name="perm_apreensao_outros" {{ old('perm_apreensao_outros', $permApreensaoOutros) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-secondary cursor-pointer" for="perm_apreensao_outros"><i class="bi bi-boxes"></i> Outros Itens</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GRUPO PEÇAS ACESSORIAS -->
                        <div class="col-md-12">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-header bg-dark text-white fw-bold py-2"><i class="bi bi-file-earmark-text me-1"></i> Documentos & Peças Judiciais</div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-6">
                                            <div class="form-check form-switch mb-2 fs-6">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="perm_oficios" name="perm_oficios" {{ old('perm_oficios', $permOficios) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold cursor-pointer" for="perm_oficios">Ofícios</label>
                                            </div>
                                            <div class="form-check form-switch mb-2 fs-6">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="perm_oitivas" name="perm_oitivas" {{ old('perm_oitivas', $permOitivas) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold cursor-pointer" for="perm_oitivas">Oitivas</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="form-check form-switch mb-2 fs-6">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="perm_pericias" name="perm_pericias" {{ old('perm_pericias', $permPericias) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold cursor-pointer" for="perm_pericias">Perícias</label>
                                            </div>
                                            <div class="form-check form-switch mb-2 fs-6">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="perm_pecas" name="perm_pecas" {{ old('perm_pecas', $permPecas) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold cursor-pointer" for="perm_pecas">Peças Diversas</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6">
                                            <div class="form-check form-switch mb-2 fs-6">
                                                <input type="checkbox" class="form-check-input cursor-pointer" id="perm_preliminares" name="perm_preliminares" {{ old('perm_preliminares', $permPreliminares) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold cursor-pointer" for="perm_preliminares">Preliminares</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ESTADO GERAL DO USUÁRIO -->
                    <div class="p-3 bg-secondary bg-opacity-10 rounded border border-secondary border-opacity-25 d-flex gap-5 mb-4 align-items-center">
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input fs-5 cursor-pointer mt-1" id="menu_lateral" name="menu_lateral" {{ old('menu_lateral', $menuLateral) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark fs-6" for="menu_lateral">Fixar Menu Lateral</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input fs-5 cursor-pointer mt-1" id="ativo" name="ativo" {{ old('ativo', $usuario->ativo) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold {{ $usuario->ativo ? 'text-success' : 'text-danger' }} fs-6" for="ativo">Membro Ativo no Banco de Dados</label>
                        </div>
                    </div>

                    <!-- AÇÕES E SALVAR -->
                    <div class="d-flex justify-content-between align-items-center pt-3 mt-4 border-top">
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary px-4 fw-bold shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Cancelar Fechar
                        </a>
                        <button type="submit" class="btn btn-success px-5 fw-bold shadow-sm" style="font-size: 1.1rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Confirmar Salvar Usuário
                        </button>
                    </div>

                </form>
            </x-card>
        </div>
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
            if (el) { 
                el.disabled = !enabled; 
                if(!enabled) el.checked = false; // reset UI
            }
        });
    }
    if (grp) {
        grp.addEventListener('change', syncSubs);
        syncSubs();
    }
    
    // Atualizar cor de ativo dinamico
    const ativoCheckbox = document.getElementById('ativo');
    if(ativoCheckbox){
        ativoCheckbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if(this.checked) {
                label.classList.remove('text-danger');
                label.classList.add('text-success');
            } else {
                label.classList.remove('text-success');
                label.classList.add('text-danger');
            }
        });
    }
});
</script>
@endsection

