<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnvolvidoFoto;
use Illuminate\Support\Facades\Storage;

class EnvolvidoFotoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'envolvido_id' => 'required|integer',
        ]);

        // Busca por PESSOA (envolvido_id = IdCad da cadpessoa), independente do papel
        $fotos = EnvolvidoFoto::where('envolvido_id', $request->envolvido_id)
            ->orderBy('is_principal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach($fotos as $f) {
            $f->url = asset('storage/' . $f->caminho_foto);
        }

        return response()->json(['success' => true, 'data' => $fotos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_envolvido' => 'required|string',
            'envolvido_id' => 'required|integer',
            'foto' => 'required|image|max:5120',
        ]);

        $caminho = $request->file('foto')->store('envolvidos_fotos', 'public');

        // Se for a primeira foto desta PESSOA, marca como principal
        $isFirst = EnvolvidoFoto::where('envolvido_id', $request->envolvido_id)
                    ->count() === 0;

        $foto = EnvolvidoFoto::create([
            'tipo_envolvido' => $request->tipo_envolvido,
            'envolvido_id' => $request->envolvido_id,
            'caminho_foto' => $caminho,
            'is_principal' => $isFirst
        ]);

        $foto->url = asset('storage/' . $foto->caminho_foto);

        return response()->json(['success' => true, 'data' => $foto, 'message' => 'Foto salva com sucesso!']);
    }

    public function destroy($id)
    {
        $foto = EnvolvidoFoto::findOrFail($id);
        
        if (Storage::disk('public')->exists($foto->caminho_foto)) {
            Storage::disk('public')->delete($foto->caminho_foto);
        }
        
        $foto->delete();

        return response()->json(['success' => true, 'message' => 'Foto excluída!']);
    }

    public function setPrincipal($id)
    {
        $foto = EnvolvidoFoto::findOrFail($id);
        
        // Remove principal de todas as outras fotos desta PESSOA
        EnvolvidoFoto::where('envolvido_id', $foto->envolvido_id)
            ->update(['is_principal' => false]);

        $foto->is_principal = true;
        $foto->save();

        return response()->json(['success' => true, 'message' => 'Foto definida como principal!']);
    }
}
