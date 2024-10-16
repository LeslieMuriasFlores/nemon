import React, { useState } from 'react';
import axios from '../axios';

const PowerPriceForm = () => {
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [p1, setP1] = useState('');
  const [p2, setP2] = useState('');
  const [p3, setP3] = useState('');
  const [username, setUsername] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await axios.post('/prices/power', {
        power: { p1, p2, p3 },
        start_date: startDate,
        end_date: endDate,
        username,
      });
      alert('Precios de potencia guardados con éxito');
    } catch (error) {
      console.error('Error al guardar los precios de potencia:', error);
      alert('Error al guardar los precios de potencia');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Introducir Precios de Potencia</h2>
      <input type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} required />
      <input type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} required />
      <input type="number" value={p1} onChange={(e) => setP1(e.target.value)} placeholder="Precio P1" required />
      <input type="number" value={p2} onChange={(e) => setP2(e.target.value)} placeholder="Precio P2" required />
      <input type="number" value={p3} onChange={(e) => setP3(e.target.value)} placeholder="Precio P3" required />
      <input type="email" value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Email" required /> {/* Campo para el email */}
      <button type="submit">Guardar</button>
    </form>
  );
};

export default PowerPriceForm;
