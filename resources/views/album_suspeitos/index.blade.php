@extends('layouts.app')

@section('title', 'Álbum de Suspeitos')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3 mb-0 text-gray-800"><i class="fas fa-camera"></i> Álbum de Suspeitos</h2>
        <button class="btn btn-primary" onclick="abrirModalSuspeito()"><i class="fas fa-plus"></i> Novo Suspeito</button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tabelaSuspeitos" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome/Alcunha</th>
                            <th>Características</th>
                            <th>Estatura/Idade</th>
                            <th>Marcas/Tatuagens</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Suspeito -->
<div class="modal fade" id="modalSuspeito" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formSuspeito" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="suspeito_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSuspeitoTitle">Cadastrar Suspeito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Coluna Foto -->
                        <div class="col-md-4 text-center mb-3">
                            <img id="previewFoto" src="{{ asset('images/b_PCPE.png') }}" alt="Foto" class="img-thumbnail mb-2" style="max-height: 200px; width: 100%; object-fit: cover;">
                            <input type="file" class="form-control" name="foto" id="foto" accept="image/*">
                            <small class="text-muted">Selecione uma imagem (Máx: 5MB)</small>
                        </div>
                        
                        <!-- Coluna Dados -->
                        <div class="col-md-8">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nome</label>
                                    <input type="text" class="form-control" name="nome" id="nome">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Alcunha / Apelido</label>
                                    <input type="text" class="form-control" name="alcunha" id="alcunha">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gênero</label>
                                    <select class="form-select" name="sexo" id="sexo">
                                        <option value="">Selecione...</option>
                                        <option value="MASCULINO">MASCULINO</option>
                                        <option value="FEMININO">FEMININO</option>
                                        <option value="OUTRO">OUTRO/LGBTQIA+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Cor da Pele</label>
                                    <select class="form-select" name="cor_pele" id="cor_pele">
                                        <option value="">Selecione...</option>
                                        <option value="BRANCO">BRANCO</option>
                                        <option value="PARDO">PARDO</option>
                                        <option value="NEGRO">NEGRO</option>
                                        <option value="AMARELO">AMARELO</option>
                                        <option value="INDIGENA">INDÍGENA</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Cabelo</label>
                                    <select class="form-select" name="cabelo" id="cabelo">
                                        <option value="">Selecione...</option>
                                        <option value="LISO">LISO</option>
                                        <option value="ONDULADO">ONDULADO</option>
                                        <option value="CACHEADO">CACHEADO</option>
                                        <option value="CRESPO">CRESPO</option>
                                        <option value="CARECA/CALVO">CARECA/CALVO</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Olhos</label>
                                    <select class="form-select" name="olhos" id="olhos">
                                        <option value="">Selecione...</option>
                                        <option value="CASTANHOS">CASTANHOS</option>
                                        <option value="PRETOS">PRETOS</option>
                                        <option value="VERDES">VERDES</option>
                                        <option value="AZUIS">AZUIS</option>
                                        <option value="MEL">MEL</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Idade Aparente</label>
                                    <select class="form-select" name="idade_aparente" id="idade_aparente">
                                        <option value="">Selecione...</option>
                                        <option value="JOVEM">JOVEM (até 25)</option>
                                        <option value="ADULTO">ADULTO (26 a 50)</option>
                                        <option value="MADURO/IDOSO">MADURO/IDOSO (+50)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Estatura</label>
                                    <select class="form-select" name="estatura" id="estatura">
                                        <option value="">Selecione...</option>
                                        <option value="BAIXO">BAIXO</option>
                                        <option value="MEDIO">MÉDIO</option>
                                        <option value="ALTO">ALTO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-12">
                            <label class="form-label">Marcas Peculiares, Tatuagens, Cicatrizes</label>
                            <input type="text" class="form-control" name="marcas_peculiares" id="marcas_peculiares" placeholder="Ex: Tatuagem no pescoço, cicatriz no rosto...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Observações Adicionais</label>
                            <textarea class="form-control" name="observacoes" id="observacoes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarSuspeito">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- DataTables -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const table = $('#tabelaSuspeitos').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('album-suspeitos.index') }}",
            columns: [
                { 
                    data: 'foto_url', 
                    name: 'foto_url',
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return '<img src="'+data+'" style="height: 60px; width: 60px; object-fit: cover; border-radius: 5px;">';
                    }
                },
                { 
                    data: null, 
                    render: function(data, type, row) {
                        return '<strong>' + (row.nome || 'N/I') + '</strong><br><small>Alcunha: ' + (row.alcunha || 'N/I') + '</small>';
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return '<small>Cor: '+(row.cor_pele||'N/I')+' | Cabelo: '+(row.cabelo||'N/I')+' | Olhos: '+(row.olhos||'N/I')+'</small>';
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return '<small>Alt: '+(row.estatura||'N/I')+' | Idade: '+(row.idade_aparente||'N/I')+'</small>';
                    }
                },
                { data: 'marcas_peculiares', name: 'marcas_peculiares' },
                { data: 'acoes', name: 'acoes', orderable: false, searchable: false }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            }
        });

        // Preview da imagem ao selecionar arquivo
        $('#foto').change(function(){
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event){
                    $('#previewFoto').attr('src', event.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Submissão do form
        $('#formSuspeito').submit(function(e) {
            e.preventDefault();
            
            const id = $('#suspeito_id').val();
            const url = id ? '/album-suspeitos/' + id : '/album-suspeitos';
            const method = id ? 'POST' : 'POST'; // Laravel aceita _method=PUT em forms multpart
            
            let formData = new FormData(this);
            if (id) {
                formData.append('_method', 'PUT');
            }

            $('#btnSalvarSuspeito').prop('disabled', true).text('Salvando...');

            $.ajax({
                url: url,
                type: 'POST', // O _method vai fazer o Laravel entender como PUT
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#modalSuspeito').modal('hide');
                        table.ajax.reload();
                        // Aqui você poderia usar o mostrarSucesso do SisDP
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert('Erro ao salvar. Verifique os dados e tente novamente.');
                    console.error(xhr.responseJSON);
                },
                complete: function() {
                    $('#btnSalvarSuspeito').prop('disabled', false).text('Salvar');
                }
            });
        });
    });

    function abrirModalSuspeito() {
        $('#formSuspeito')[0].reset();
        $('#suspeito_id').val('');
        $('#sexo').val('');
        $('#previewFoto').attr('src', '{{ asset("images/b_PCPE.png") }}');
        $('#modalSuspeitoTitle').text('Cadastrar Suspeito');
        $('#modalSuspeito').modal('show');
    }

    function editarSuspeito(id) {
        $.get('/album-suspeitos/' + id, function(response) {
            if (response.success) {
                const s = response.data;
                $('#formSuspeito')[0].reset();
                $('#suspeito_id').val(s.id);
                $('#nome').val(s.nome);
                $('#alcunha').val(s.alcunha);
                $('#sexo').val(s.sexo);
                $('#cor_pele').val(s.cor_pele);
                $('#cabelo').val(s.cabelo);
                $('#olhos').val(s.olhos);
                $('#idade_aparente').val(s.idade_aparente);
                $('#estatura').val(s.estatura);
                $('#marcas_peculiares').val(s.marcas_peculiares);
                $('#observacoes').val(s.observacoes);
                $('#previewFoto').attr('src', s.foto_url);
                $('#modalSuspeitoTitle').text('Editar Suspeito');
                $('#modalSuspeito').modal('show');
            }
        });
    }

    function excluirSuspeito(id) {
        if (confirm('Deseja realmente excluir este suspeito? Esta ação não pode ser desfeita.')) {
            $.ajax({
                url: '/album-suspeitos/' + id,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        $('#tabelaSuspeitos').DataTable().ajax.reload();
                    }
                }
            });
        }
    }
</script>
@endsection
