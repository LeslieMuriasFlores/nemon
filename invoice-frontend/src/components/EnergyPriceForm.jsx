import React, { useState } from 'react';
import axios from '../axios';

const EnergyPriceForm = () => {
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [p1, setP1] = useState('');
  const [p2, setP2] = useState('');
  const [p3, setP3] = useState('');
  const [username, setUsername] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      // Asegúrate de que p1, p2 y p3 sean números
      await axios.post('/prices/energy', {
        energy: { p1: parseFloat(p1), p2: parseFloat(p2), p3: parseFloat(p3) },
        start_date: startDate,
        end_date: endDate,
        username,
      });
      alert('Precios de energía guardados con éxito');
    } catch (error) {
      // Manejo mejorado del error
      if (error.response) {
        console.error('Error al guardar los precios de energía:', error.response.data);
        alert(`Error: ${error.response.data.message}`);
      } else {
        console.error('Error al guardar los precios de energía:', error.message);
        alert('Error de conexión con el servidor');
      }
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Introducir Precios de Energía</h2>
      <input type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} required />
      <input type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} required />
      <input type="number" value={p1} onChange={(e) => setP1(e.target.value)} placeholder="Precio P1" required />
      <input type="number" value={p2} onChange={(e) => setP2(e.target.value)} placeholder="Precio P2" required />
      <input type="number" value={p3} onChange={(e) => setP3(e.target.value)} placeholder="Precio P3" required />
      <input type="email" value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Email" required />
      <button type="submit">Guardar</button>
    </form>
  );
};

export default EnergyPriceForm;
