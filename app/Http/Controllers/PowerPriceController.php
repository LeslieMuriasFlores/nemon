<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PowerPrice;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PowerPriceController extends Controller
{
    // Insertar los precios de potencia
    public function store(Request $request)
    {
        try{
            // Validar los datos del request
            $validated = $this->validateRequest($request);
            $user = User::firstOrCreate(['email' => $validated['username']], [
                'name' => explode('@', $validated['username'])[0], // Nombre antes de '@'
                'password' => Hash::make('12345678'), // Contraseña por defecto
            ]);
            // Crear o actualizar los precios de potencia
            PowerPrice::updateOrCreate(
                [
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'user_id' => $user->id
                ],
                [
                    'p1' => $validated['power']['p1'],
                    'p2' => $validated['power']['p2'],
                    'p3' => $validated['power']['p3']
                ]
            );
            return response()->json(['message' => 'Power prices updated successfully']);

        }catch (Exception $e) {
            // Respuesta de error
            return response()->json(['error' => 'Error updating power prices: ' . $e->getMessage()], 500);
        }
    }

    // Obtener los precios de potencia
    public function index()
    {
        try {
            $prices = PowerPrice::all();
            return response()->json($prices);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error retrieving power prices: ' . $e->getMessage()], 500);
        }
    }

    private function validateRequest(Request $request)
    {
        return $request->validate([
            'power.p1' => 'required|numeric',
            'power.p2' => 'required|numeric',
            'power.p3' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'username' => 'required|email'
        ]);
    }

    // Método para eliminar precios de potencia (DELETE)
    public function destroy($id)
    {
        try {
            // Buscar el precio de potencia por su ID y eliminarlo
            $powerPrice = PowerPrice::findOrFail($id);
            $powerPrice->delete();
            return response()->json(['message' => 'Power price deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting power price: ' . $e->getMessage()], 500);
        }
    }

    // Método para actualizar precios de potencia (PUT)
    public function update(Request $request, $id)
    {
        try {
            // Validar los datos del request
            $validated = $this->validateRequest($request);
            $user = User::firstOrCreate(['email' => $validated['username']], [
                'name' => explode('@', $validated['username'])[0], // Nombre antes de '@'
                'password' => Hash::make('12345678'), // Contraseña por defecto
            ]);
            // Buscar el precio de potencia por su ID y actualizarlo
            $powerPrice = PowerPrice::findOrFail($id);
            $powerPrice->update([
                'p1' => $validated['power']['p1'],
                'p2' => $validated['power']['p2'],
                'p3' => $validated['power']['p3'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'user_id' => $user->id
            ]);
            return response()->json(['message' => 'Power price updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error updating power price: ' . $e->getMessage()], 500);
        }
    }
}

