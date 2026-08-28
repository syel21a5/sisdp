<?php

namespace App\Http\Controllers;

use App\Models\AlbumSuspeito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AlbumSuspeitosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = AlbumSuspeito::query()->orderBy('created_at', 'desc');
            
            // Filtros da Datatable ou da tela de busca
            if ($request->has('cor_pele') && $request->cor_pele) {
                $data->where('cor_pele', $request->cor_pele);
            }
            if ($request->has('cabelo') && $request->cabelo) {
                $data->where('cabelo', $request->cabelo);
            }
            if ($request->has('idade_aparente') && $request->idade_aparente) {
                $data->where('idade_aparente', $request->idade_aparente);
            }
            if ($request->has('marcas_peculiares') && $request->marcas_peculiares) {
                $data->where('marcas_peculiares', 'LIKE', '%' . $request->marcas_peculiares . '%');
            }

            return Datatables::of($data)
                ->addColumn('foto_url', function ($row) {
                    return Storage::url($row->caminho_foto);
                })
                ->addColumn('acoes', function($row){
                    $btn = '<a href="javascript:void(0)" onclick="visualizarSuspeito('.$row->id.')" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
                    $btn .= ' <a href="javascript:void(0)" onclick="editarSuspeito('.$row->id.')" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= ' <a href="javascript:void(0)" onclick="excluirSuspeito('.$row->id.')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>';
                    return $btn;
                })
                ->rawColumns(['acoes'])
                ->make(true);
        }

        return view('album_suspeitos.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $caminho = null;
        if ($request->hasFile('foto')) {
            $caminho = $request->file('foto')->store('album_suspeitos', 'public');
        }

        AlbumSuspeito::create([
            'nome' => $request->nome,
            'alcunha' => $request->alcunha,
            'sexo' => $request->sexo,
            'cor_pele' => $request->cor_pele,
            'cabelo' => $request->cabelo,
            'olhos' => $request->olhos,
            'idade_aparente' => $request->idade_aparente,
            'estatura' => $request->estatura,
            'marcas_peculiares' => $request->marcas_peculiares,
            'caminho_foto' => $caminho,
            'observacoes' => $request->observacoes,
            'usuario_id' => Auth::id() ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Suspeito cadastrado com sucesso no álbum!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $suspeito = AlbumSuspeito::findOrFail($id);
        $suspeito->foto_url = Storage::url($suspeito->caminho_foto);
        return response()->json(['success' => true, 'data' => $suspeito]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $suspeito = AlbumSuspeito::findOrFail($id);
        
        $caminho = $suspeito->caminho_foto;
        if ($request->hasFile('foto')) {
            // Remove a foto antiga
            if (Storage::disk('public')->exists($caminho)) {
                Storage::disk('public')->delete($caminho);
            }
            // Salva a nova foto
            $caminho = $request->file('foto')->store('album_suspeitos', 'public');
        }

        $suspeito->update([
            'nome' => $request->nome,
            'alcunha' => $request->alcunha,
            'sexo' => $request->sexo,
            'cor_pele' => $request->cor_pele,
            'cabelo' => $request->cabelo,
            'olhos' => $request->olhos,
            'idade_aparente' => $request->idade_aparente,
            'estatura' => $request->estatura,
            'marcas_peculiares' => $request->marcas_peculiares,
            'caminho_foto' => $caminho,
            'observacoes' => $request->observacoes,
        ]);

        return response()->json(['success' => true, 'message' => 'Suspeito atualizado com sucesso!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $suspeito = AlbumSuspeito::findOrFail($id);
        
        if (Storage::disk('public')->exists($suspeito->caminho_foto)) {
            Storage::disk('public')->delete($suspeito->caminho_foto);
        }
        
        $suspeito->delete();

        return response()->json(['success' => true, 'message' => 'Registro excluído com sucesso!']);
    }

    /**
     * API for the Auto de Reconhecimento Editor
     */
    public function pesquisarParaEditor(Request $request)
    {
        $query = AlbumSuspeito::query();

        if ($request->has('sexo') && $request->sexo) {
            $query->where('sexo', $request->sexo);
        }
        if ($request->has('cor_pele') && $request->cor_pele) {
            $query->where('cor_pele', $request->cor_pele);
        }
        if ($request->has('cabelo') && $request->cabelo) {
            $query->where('cabelo', $request->cabelo);
        }
        if ($request->has('idade_aparente') && $request->idade_aparente) {
            $query->where('idade_aparente', $request->idade_aparente);
        }
        if ($request->has('estatura') && $request->estatura) {
            $query->where('estatura', $request->estatura);
        }
        if ($request->has('termo') && $request->termo) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'LIKE', '%' . $request->termo . '%')
                  ->orWhere('alcunha', 'LIKE', '%' . $request->termo . '%')
                  ->orWhere('marcas_peculiares', 'LIKE', '%' . $request->termo . '%');
            });
        }

        // Limita a busca para não travar o navegador
        $resultados = $query->orderBy('created_at', 'desc')->take(50)->get();

        foreach ($resultados as $resultado) {
            $resultado->foto_url = $resultado->url();
        }

        return response()->json(['success' => true, 'data' => $resultados]);
    }
}
