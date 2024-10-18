import React, { useState, useEffect, useRef } from 'react';
import axios from '../axios';
import '../App.css';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const EnergyPriceForm = () => {
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [p1, setP1] = useState('');
  const [p2, setP2] = useState('');
  const [p3, setP3] = useState('');
  const [username, setUsername] = useState('');
  const [energyPrices, setEnergyPrices] = useState([]); // Estado para almacenar los precios
  const [editingPriceId, setEditingPriceId] = useState(null); // Estado para saber si estamos editando

  const formRef = useRef(null); // Referencia al formulario
  const startDateRef = useRef(null); // Referencia al campo startDate

  useEffect(() => {
    fetchEnergyPrices(); // Cargar los precios de energía cuando se monte el componente
  }, []);

  // Obtener precios de energía y los emails de los usuarios relacionados
  const fetchEnergyPrices = async () => {
    try {
      const response = await axios.get('/prices/energy');
      const pricesWithEmails = await Promise.all(
        response.data.map(async (price) => {
          const email = await fetchUserEmail(price.user_id); // Obtener email por user_id
          return { ...price, email }; // Añadir el email al objeto del precio
        })
      );
      setEnergyPrices(pricesWithEmails); // Guardar los precios con los emails
    } catch (error) {
      console.error('Error al obtener los precios de energía:', error);
      toast.error('Error al cargar los precios de energía');
    }
  };

  // Obtener el email de un usuario por su ID
  const fetchUserEmail = async (userId) => {
    try {
      const response = await axios.get(`/users/${userId}/email`);
      return response.data.email; // Retornar el email
    } catch (error) {
      console.error('Error al obtener el email del usuario:', error);
      return 'Email no disponible'; // En caso de error, retornar un mensaje
    }
  };

  // Manejar el envío del formulario para crear o editar un precio de energía
  const handleSubmit = async (e) => {
    e.preventDefault();

    const energyData = {
      energy: { p1: parseFloat(p1), p2: parseFloat(p2), p3: parseFloat(p3) },
      start_date: startDate,
      end_date: endDate,
      username,
    };

    try {
      if (editingPriceId) {
        // Si estamos editando, usar PUT para actualizar el registro
        await axios.put(`/prices/energy/${editingPriceId}`, energyData);
        toast.success('Precios de energía actualizados con éxito');
      } else {
        // Si no estamos editando, usar POST para crear un nuevo registro
        await axios.post('/prices/energy', energyData);
        toast.success('Precios de energía guardados con éxito');
      }

      // Limpiar el formulario después de guardar
      resetForm();
      fetchEnergyPrices(); // Volver a cargar los precios después de guardar
    } catch (error) {
      console.error('Error al guardar los precios de energía:', error);
      toast.error('Error al guardar los precios de energía');
    }
  };

  // Manejar la edición de un precio de energía
  const handleEdit = (price) => {
    // Cargar los datos del precio en el formulario para editar
    setStartDate(price.start_date);
    setEndDate(price.end_date);
    setP1(price.p1);
    setP2(price.p2);
    setP3(price.p3);
    setUsername(price.email); // Cargar el email en el campo de edición
    setEditingPriceId(price.id); // Establecer el ID del precio que estamos editando

    // Desplazar y enfocar el formulario
    if (formRef.current) {
      formRef.current.scrollIntoView({ behavior: 'smooth' });
    }

    // Enfocar el primer campo del formulario
    if (startDateRef.current) {
      startDateRef.current.focus();
    }
  };

  // Manejar la eliminación de un precio de energía
  const handleDelete = async (id) => {
    if (!window.confirm('¿Estás seguro de que quieres eliminar este precio de energía?')) {
      return;
    }

    try {
      await axios.delete(`/prices/energy/${id}`);
      toast.success('Precio de energía eliminado con éxito');
      fetchEnergyPrices(); // Volver a cargar los precios después de eliminar
    } catch (error) {
      console.error('Error al eliminar el precio de energía:', error);
      toast.error('Error al eliminar el precio de energía');
    }
  };

  // Resetear el formulario
  const resetForm = () => {
    setStartDate('');
    setEndDate('');
    setP1('');
    setP2('');
    setP3('');
    setUsername('');
    setEditingPriceId(null); // Restablecer el estado de edición
  };

  return (
    <div className="container mt-4">
      {/* Tabla de Precios de Energía */}
      <h3 className="mb-3 text-center" style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>Lista de Precios de Energía</h3>
      <ul className="list-group mt-3">
        {energyPrices.map((price) => (
          <li key={price.id} className="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <strong>P1:</strong> {price.p1}, <strong>P2:</strong> {price.p2}, <strong>P3:</strong> {price.p3}, 
              <strong> Desde:</strong> {price.start_date}, <strong>Hasta:</strong> {price.end_date}, 
              <strong> Usuario:</strong> {price.email} {/* Mostrar el email del usuario */}
            </div>
            <div>
              <button
                className="btn btn-warning btn-sm me-2"
                onClick={() => handleEdit(price)} // Iniciar edición
              >
                <i className="bi bi-pencil-square"></i>
              </button>
              <button
                className="btn btn-danger btn-sm"
                onClick={() => handleDelete(price.id)} // Eliminar
              >
                <i className="bi bi-trash"></i>
              </button>
            </div>
          </li>
        ))}
      </ul>

      {/* Formulario de Precios de Energía */}
      <form
        ref={formRef} // Referencia al formulario
        onSubmit={handleSubmit}
        className="border p-4 rounded bg-light shadow mt-4"
      >
        <h2 className="mb-3 text-center" style={{ fontSize: '1.5rem', fontWeight: 'bold' }}>
          {editingPriceId ? 'Editar Precios de Energía' : 'Precios de Energía'}
        </h2>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="startDate" className="form-label me-3" style={{ width: '150px' }}>Fecha de Inicio:</label>
          <input
            type="date"
            id="startDate"
            className="form-control"
            value={startDate}
            onChange={(e) => setStartDate(e.target.value)}
            required
            ref={startDateRef} // Asignar la referencia
          />
        </div>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="endDate" className="form-label me-3" style={{ width: '150px' }}>Fecha de Fin:</label>
          <input
            type="date"
            id="endDate"
            className="form-control"
            value={endDate}
            onChange={(e) => setEndDate(e.target.value)}
            required
          />
        </div>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="p1" className="form-label me-3" style={{ width: '150px' }}>Precio P1:</label>
          <input
            type="number"
            id="p1"
            className="form-control"
            value={p1}
            onChange={(e) => setP1(e.target.value)}
            placeholder="Precio P1"
            required
          />
        </div>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="p2" className="form-label me-3" style={{ width: '150px' }}>Precio P2:</label>
          <input
            type="number"
            id="p2"
            className="form-control"
            value={p2}
            onChange={(e) => setP2(e.target.value)}
            placeholder="Precio P2"
            required
          />
        </div>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="p3" className="form-label me-3" style={{ width: '150px' }}>Precio P3:</label>
          <input
            type="number"
            id="p3"
            className="form-control"
            value={p3}
            onChange={(e) => setP3(e.target.value)}
            placeholder="Precio P3"
            required
          />
        </div>

        <div className="mb-3 form-group d-flex align-items-center">
          <label htmlFor="username" className="form-label me-3" style={{ width: '150px' }}>Email:</label>
          <input
            type="email"
            id="username"
            className="form-control"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            placeholder="Email"
            required
          />
        </div>

        <button type="submit" className="btn btn-primary btn-sm">
          {editingPriceId ? 'Actualizar' : 'Guardar'}
        </button>
        {editingPriceId && (
          <button
            type="button"
            className="btn btn-secondary btn-sm ms-3"
            onClick={resetForm}
          >
            Cancelar Edición
          </button>
        )}
      </form>

      <ToastContainer
        position="top-center"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
      />
    </div>
  );
};

export default EnergyPriceForm;
