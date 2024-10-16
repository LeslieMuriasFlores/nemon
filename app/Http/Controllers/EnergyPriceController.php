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

            Log::info('ID de Usuario para EnergyPrice:', ['user_id' => $user->id]); // Verifica el ID del usuario

            Log::info('Datos a insertar en energy_prices:', [
                'user_id' => $user->id,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'p1' => $validated['energy']['p1'],
                'p2' => $validated['energy']['p2'],
                'p3' => $validated['energy']['p3'],
            ]);

            // Crear o actualizar los precios de energía
            $energyPrice = EnergyPrice::updateOrCreate(
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

            Log::info('Precios de energía actualizados:', $energyPrice->toArray()); // Log de precios de energía

            return response()->json(['message' => 'Energy prices updated successfully'], 201);

        }catch (Exception $e) {
            // Logging de error
            Log::error("Error al actualizar los precios de energía para el usuario {$request->username}", [
                'error_message' => $e->getMessage()
            ]);

            // Respuesta de error
            return response()->json(['error' => 'Error updating energy prices: ' . $e->getMessage()], 500);
        }
        
    }

    // Obtener los precios de energía
    public function index()
    {
        try {
            // Obtener los precios de energía
            $prices = EnergyPrice::all();

            // Log de éxito
            Log::info("Precios de energía obtenidos correctamente.");

            return response()->json($prices);

        } catch (Exception $e) {
            // Logging de error
            Log::error("Error al obtener los precios de energía", [
                'error_message' => $e->getMessage()
            ]);

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
}
