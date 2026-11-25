<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;



class CrudController extends Controller
{
  

    public function index()
    {
        return view('crud.index');
    }

    public function create()
    {
        return view('crud.create');
    }

    public function store(Request $request)
    {
        
        return redirect()->route('crud.index')->with('success', 'Registro creado exitosamente');
    }

    public function edit($id)
    {
        return view('crud.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
      
        return redirect()->route('crud.index')->with('success', 'Registro actualizado exitosamente');
    }

    public function destroy($id)
    {
        
        return redirect()->route('crud.index')->with('success', 'Registro eliminado exitosamente');
    }
}
