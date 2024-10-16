<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\EnergyPrice;
use App\Models\PowerPrice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceController extends Controller
{
    // Generar y almacenar una factura
    public function generateBill(Request $request)
    {
        try {
            // Validar los datos del request
            $validated = $this->validateRequest($request);

            // Buscar o crear el usuario
            $user = $this->findOrCreateUser($validated['username']);

            // Calcular el número de días
            $days = $this->calculateDays($validated['start_date'], $validated['end_date']);

            // Obtener los precios de energía y potencia para el usuario y el periodo
            $energyPrices = $this->getEnergyPrices($user->id, $validated['start_date'], $validated['end_date']);
            $powerPrices = $this->getPowerPrices($user->id, $validated['start_date'], $validated['end_date']);

            // Si no se encuentran los precios, lanzar excepción
            if (!$energyPrices || !$powerPrices) {
                throw new Exception('Energy or power prices not found for the given dates.');
            }

            // Calcular el costo total de energía y potencia
            $totalEnergyCost = $this->calculateEnergyCost($validated['consumption'], $energyPrices);
            $totalPowerCost = $this->calculatePowerCost($validated['contractedpower'], $powerPrices, $days);

            // Calcular el total de la factura
            $totalInvoice = $totalEnergyCost + $totalPowerCost;

            // Almacenar la factura
            $invoice = $this->storeInvoice($user->id, $totalEnergyCost, $totalPowerCost, $totalInvoice);

            // Logging de éxito
            Log::info("Factura generada con éxito para el usuario {$user->email}", [
                'invoice_id' => $invoice->id,
                'total_invoice' => $totalInvoice
            ]);

            // Respuesta exitosa
            return response()->json([
                'total_energy_cost' => $totalEnergyCost,
                'total_power_cost' => $totalPowerCost,
                'total_invoice' => $totalInvoice,
                'invoice_id' => $invoice->id
            ], 201);

        } catch (Exception $e) {
            // Logging de error
            Log::error("Error generando factura para el usuario: {$request->username}", [
                'error_message' => $e->getMessage()
            ]);

            // Respuesta de error
            return response()->json([
                'error' => 'Error generating invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    // Buscar o crear usuario basado en el email
    private function findOrCreateUser($email)
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => explode('@', $email)[0],
                'password' => bcrypt('12345678') // Contraseña por defecto
            ]
        );
    }

    // Obtener los precios de energía para un periodo y usuario específico
    private function getEnergyPrices($userId, $startDate, $endDate)
    {
        return EnergyPrice::where('user_id', $userId)
            ->where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            ->first();
    }

    // Obtener los precios de potencia para un periodo y usuario específico
    private function getPowerPrices($userId, $startDate, $endDate)
    {
        return PowerPrice::where('user_id', $userId)
            ->where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            ->first();
    }

    // Almacenar la factura en la base de datos
    private function storeInvoice($userId, $totalEnergyCost, $totalPowerCost, $totalInvoice)
    {
        return Invoice::create([
            'total_energy_cost' => $totalEnergyCost,
            'total_power_cost' => $totalPowerCost,
            'total_invoice' => $totalInvoice,
            'user_id' => $userId
        ]);
    }

    // Calcular el costo de energía
    private function calculateEnergyCost($consumption, $energyPrices)
    {
        return ($consumption['p1'] * $energyPrices->p1) +
               ($consumption['p2'] * $energyPrices->p2) +
               ($consumption['p3'] * $energyPrices->p3);
    }

    // Calcular el costo de potencia
    private function calculatePowerCost($contractedPower, $powerPrices, $days)
    {
        return ($contractedPower['p1'] * $powerPrices->p1 * $days) +
               ($contractedPower['p2'] * $powerPrices->p2 * $days) +
               ($contractedPower['p3'] * $powerPrices->p3 * $days);
    }

    // Calcular el número de días entre dos fechas
    private function calculateDays($startDate, $endDate)
    {
        return (new \DateTime($endDate))->diff(new \DateTime($startDate))->days + 1;
    }

    // Validar la solicitud
    private function validateRequest(Request $request)
    {
        return $request->validate([
            'consumption.p1' => 'required|numeric',
            'consumption.p2' => 'required|numeric',
            'consumption.p3' => 'required|numeric',
            'contractedpower.p1' => 'required|numeric',
            'contractedpower.p2' => 'required|numeric',
            'contractedpower.p3' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'username' => 'required|email'
        ]);
    }

    // Listar las facturas
    public function index()
    {
        $invoices = Invoice::all();
        return response()->json($invoices);
    }
}
