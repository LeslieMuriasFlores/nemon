import React, { useState } from 'react';
import axios from '../axios';

const InvoiceCalculator = () => {
  const [consumption, setConsumption] = useState({ p1: '', p2: '', p3: '' });
  const [contractedPower, setContractedPower] = useState({ p1: '', p2: '', p3: '' });
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [username, setUsername] = useState('');
  const [invoiceResult, setInvoiceResult] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();

    // Verificar que todos los campos requeridos estén llenos
    if (!contractedPower.p1 || !contractedPower.p2 || !contractedPower.p3) {
      alert("Todos los campos de potencia son obligatorios.");
      return;
    }

    if (!consumption.p1 || !consumption.p2 || !consumption.p3) {
      alert("Todos los campos de consumo son obligatorios.");
      return;
    }

    if (!startDate || !endDate || !username) {
      alert("Por favor, complete todos los campos.");
      return;
    }

    try {
      const response = await axios.post('/generatebill', {
        consumption,
        contractedpower: contractedPower,  // Cambiar aquí a 'contractedpower'
        start_date: startDate,
        end_date: endDate,
        username,
      });

      // Asignar el resultado de la factura
      setInvoiceResult(response.data);
    } catch (error) {
      console.error('Error al generar la factura:', error);
      alert('Error al generar la factura');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Calcular Factura</h2>
      <input 
        type="date" 
        value={startDate} 
        onChange={(e) => setStartDate(e.target.value)} 
        required 
      />
      <input 
        type="date" 
        value={endDate} 
        onChange={(e) => setEndDate(e.target.value)} 
        required 
      />
      <h3>Consumos (P1-P3)</h3>
      <input 
        type="number" 
        value={consumption.p1} 
        onChange={(e) => setConsumption({ ...consumption, p1: e.target.value })} 
        placeholder="Consumo P1" 
        required 
      />
      <input 
        type="number" 
        value={consumption.p2} 
        onChange={(e) => setConsumption({ ...consumption, p2: e.target.value })} 
        placeholder="Consumo P2" 
        required 
      />
      <input 
        type="number" 
        value={consumption.p3} 
        onChange={(e) => setConsumption({ ...consumption, p3: e.target.value })} 
        placeholder="Consumo P3" 
        required 
      />
      <h3>Potencias (P1-P3)</h3>
      <input 
        type="number" 
        value={contractedPower.p1} 
        onChange={(e) => setContractedPower({ ...contractedPower, p1: e.target.value })} 
        placeholder="Potencia P1" 
        required 
      />
      <input 
        type="number" 
        value={contractedPower.p2} 
        onChange={(e) => setContractedPower({ ...contractedPower, p2: e.target.value })} 
        placeholder="Potencia P2" 
        required 
      />
      <input 
        type="number" 
        value={contractedPower.p3} 
        onChange={(e) => setContractedPower({ ...contractedPower, p3: e.target.value })} 
        placeholder="Potencia P3" 
        required 
      />
      <input 
        type="email" 
        value={username} 
        onChange={(e) => setUsername(e.target.value)} 
        placeholder="Email" 
        required 
      />
      <button type="submit">Calcular</button>
      {invoiceResult && (
        <div>
          <h3>Resultados de la Factura:</h3>
          <p>Costo de Energía: {invoiceResult.total_energy_cost}</p>
          <p>Costo de Potencia: {invoiceResult.total_power_cost}</p>
          <p>Total de Factura: {invoiceResult.total_invoice}</p>
          <p>ID de Factura: {invoiceResult.invoice_id}</p>
        </div>
      )}
    </form>
  );
};

export default InvoiceCalculator;
