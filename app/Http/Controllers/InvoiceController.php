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

            // Buscar el usuario
            $user = $this->findUserByEmail($validated['username']);

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
            $totalEnergyCost = $this->calculateEnergyCost($validated['consumption'], $energyPrices, $validated['start_date'], $validated['end_date']);
            $totalPowerCost = $this->calculatePowerCost($validated['contractedpower'], $powerPrices, $days, $validated['start_date'], $validated['end_date']);

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

    // Buscar usuario basado en el email
    private function findUserByEmail($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new Exception('User does not exist.');
        }
        return $user;
    }

    // Obtener los precios de energía para un periodo y usuario específico
    private function getEnergyPrices($userId, $startDate, $endDate)
    {
        return EnergyPrice::where('user_id', $userId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();
    }

    // Obtener los precios de potencia para un periodo y usuario específico
    private function getPowerPrices($userId, $startDate, $endDate)
    {
        return PowerPrice::where('user_id', $userId)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get();
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
    private function calculateEnergyCost($consumption, $energyPrices, $startDate, $endDate)
    {
        $totalCost = 0;
        foreach ($energyPrices as $price) {
            $daysInPeriod = $this->calculateDays($startDate, $endDate); // O ajustar para calcular días en el rango del precio
            $totalCost += ($consumption['p1'] * $price->p1 * $daysInPeriod) +
                          ($consumption['p2'] * $price->p2 * $daysInPeriod) +
                          ($consumption['p3'] * $price->p3 * $daysInPeriod);
        }
        return $totalCost;
    }

    // Calcular el costo de potencia
    private function calculatePowerCost($contractedPower, $powerPrices, $days, $startDate, $endDate)
    {
        $totalCost = 0;
        foreach ($powerPrices as $price) {
            $totalCost += ($contractedPower['p1'] * $price->p1 * $days) +
                          ($contractedPower['p2'] * $price->p2 * $days) +
                          ($contractedPower['p3'] * $price->p3 * $days);
        }
        return $totalCost;
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
