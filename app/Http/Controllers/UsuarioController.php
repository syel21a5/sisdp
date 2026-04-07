<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::all();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuario',
            'password' => 'required|string|min:6|confirmed',
            'nivel_acesso' => 'required|in:administrador,usuario'
        ]);

        $usuarioData = [
            'nome' => $request->nome,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'nivel_acesso' => $request->nivel_acesso,
            'ativo' => $request->has('ativo'),
            'permissions' => [
                'menu_lateral' => $request->has('menu_lateral'),
                'apreensao' => $request->has('perm_apreensao'),
                'administrativo' => $request->has('perm_administrativo'),
                'celular' => $request->has('perm_celular'),
                'veiculo' => $request->has('perm_veiculo'),
                'apreensao_outros' => $request->has('perm_apreensao_outros'),
                'intimacao' => $request->has('perm_intimacao'),
                'apfd' => $request->has('perm_apfd'),
                'oficios' => $request->has('perm_oficios'),
                'oitivas' => $request->has('perm_oitivas'),
                'pericias' => $request->has('perm_pericias'),
                'pecas' => $request->has('perm_pecas'),
                'preliminares' => $request->has('perm_preliminares'),
                'infopol' => $request->has('perm_infopol'),
                'antecedentes' => $request->has('perm_antecedentes'),
                'auditoria_chips' => $request->has('perm_auditoria_chips'),
                'verificar_sei' => $request->has('perm_verificar_sei')
            ]
        ];

        Usuario::create($usuarioData);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuario,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'nivel_acesso' => 'required|in:administrador,usuario'
        ]);

        $data = [
            'nome' => $request->nome,
            'username' => $request->username,
            'nivel_acesso' => $request->nivel_acesso,
            'ativo' => $request->has('ativo'),
            'permissions' => [
                'menu_lateral' => $request->has('menu_lateral'),
                'apreensao' => $request->has('perm_apreensao'),
                'administrativo' => $request->has('perm_administrativo'),
                'celular' => $request->has('perm_celular'),
                'veiculo' => $request->has('perm_veiculo'),
                'apreensao_outros' => $request->has('perm_apreensao_outros'),
                'intimacao' => $request->has('perm_intimacao'),
                'apfd' => $request->has('perm_apfd'),
                'oficios' => $request->has('perm_oficios'),
                'oitivas' => $request->has('perm_oitivas'),
                'pericias' => $request->has('perm_pericias'),
                'pecas' => $request->has('perm_pecas'),
                'preliminares' => $request->has('perm_preliminares'),
                'infopol' => $request->has('perm_infopol'),
                'antecedentes' => $request->has('perm_antecedentes'),
                'auditoria_chips' => $request->has('perm_auditoria_chips'),
                'verificar_sei' => $request->has('perm_verificar_sei')
            ]
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        // CORREÇÃO: usando Auth::id() que é o método correto
        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode excluir seu próprio usuário!');
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }

    public function toggleAtivo($id)
    {
        $usuario = Usuario::findOrFail($id);

        // CORREÇÃO: usando Auth::id() que é o método correto
        if ($usuario->id === Auth::id()) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode desativar seu próprio usuário!');
        }

        $usuario->update(['ativo' => !$usuario->ativo]);

        $status = $usuario->ativo ? 'ativado' : 'desativado';
        return redirect()->route('usuarios.index')->with('success', "Usuário {$status} com sucesso!");
    }
}
