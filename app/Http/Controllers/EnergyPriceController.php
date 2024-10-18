<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnergyPrice;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EnergyPriceController extends Controller
{
    // Insertar o modificar precios de energía
    public function store(Request $request)
    {
        try{
            // Validar los datos del request
            $validated = $this->validateRequest($request);
            $user = User::firstOrCreate(['email' => $validated['username']], [
                'name' => explode('@', $validated['username'])[0], // Nombre antes de '@'
                'password' => Hash::make('12345678'), // Contraseña por defecto
            ]);

            // Crear o actualizar los precios de energía
            EnergyPrice::updateOrCreate(
                [
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'user_id' => $user->id
                ],
                [
                    'p1' => $validated['energy']['p1'],
                    'p2' => $validated['energy']['p2'],
                    'p3' => $validated['energy']['p3']
                ]
            );

            return response()->json(['message' => 'Energy prices updated successfully'], 201);

        }catch (Exception $e) {
            return response()->json(['error' => 'Error updating energy prices: ' . $e->getMessage()], 500);
        }
        
    }

    // Obtener los precios de energía
    public function index()
    {
        try {
            // Obtener los precios de energía
            $prices = EnergyPrice::all();
            return response()->json($prices);

        } catch (Exception $e) {
            return response()->json(['error' => 'Error retrieving energy prices: ' . $e->getMessage()], 500);
        }
    }

    // Método privado para validar la solicitud
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'energy.p1' => 'required|numeric',
            'energy.p2' => 'required|numeric',
            'energy.p3' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'username' => 'required|email'
        ]);
    }

    // Actualizar precios de energía
    public function update(Request $request, $id)
    {
        try {
            // Validar los datos del request
            $validated = $this->validateRequest($request);

            // Encontrar el precio de energía por ID
            $energyPrice = EnergyPrice::findOrFail($id);

            // Encontrar el usuario o crearlo si no existe
            $user = User::firstOrCreate(['email' => $validated['username']], [
                'name' => explode('@', $validated['username'])[0],
                'password' => Hash::make('12345678'), 
            ]);

            // Actualizar los datos del precio de energía
            $energyPrice->update([
                'user_id' => $user->id,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'p1' => $validated['energy']['p1'],
                'p2' => $validated['energy']['p2'],
                'p3' => $validated['energy']['p3'],
            ]);

            return response()->json(['message' => 'Energy prices updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error updating energy prices: ' . $e->getMessage()], 500);
        }
    }

    // Eliminar precios de energía
    public function destroy($id)
    {
        try {
            // Buscar el precio de energía por ID y eliminarlo
            $energyPrice = EnergyPrice::findOrFail($id);
            $energyPrice->delete();
            return response()->json(['message' => 'Energy price deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting energy price: ' . $e->getMessage()], 500);
        }
    }

}
