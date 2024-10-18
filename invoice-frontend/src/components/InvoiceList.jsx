// src/components/InvoiceList.jsx
import React, { useEffect, useState } from 'react';
import axios from '../axios'; // Asegúrate de que esto apunta correctamente a tu instancia de Axios
import '../App.css';
import { ToastContainer, toast } from 'react-toastify'; // Importar toast
import 'react-toastify/dist/ReactToastify.css';

const InvoiceList = () => {
    const [invoices, setInvoices] = useState([]); // Inicializar como un array vacío
    const [loading, setLoading] = useState(true); // Para manejar el estado de carga
    const [error, setError] = useState(null); // Para manejar errores

    useEffect(() => {
        const fetchInvoices = async () => {
            try {
                const response = await axios.get('/invoices'); // Asegúrate de que esta URL sea correcta
                const invoicesWithEmails = await Promise.all(
                    response.data.map(async (invoice) => {
                        const email = await fetchUserEmail(invoice.user_id); // Obtener email por user_id
                        return { ...invoice, email }; // Añadir el email al objeto de la factura
                    })
                );
                setInvoices(invoicesWithEmails); // Guardar las facturas con los emails
            } catch (err) {
                setError(err);
            } finally {
                setLoading(false);
            }
        };

        fetchInvoices();
    }, []);

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

    const handleDelete = async (id) => {
        if (window.confirm('¿Estás seguro de que deseas eliminar esta factura?')) {
            try {
                await axios.delete(`/invoices/${id}`); // Llamada para eliminar la factura
                setInvoices(invoices.filter(invoice => invoice.id !== id)); // Actualizar el estado para eliminar la factura
                toast.success('Factura eliminada con éxito'); // Mensaje de éxito
            } catch (error) {
                console.error('Error al eliminar la factura:', error);
                toast.error('Error al eliminar la factura'); // Mensaje de error
            }
        }
    };

    if (loading) {
        return <div className="loading-message">Cargando...</div>;
    }

    if (error) {
        return <div className="error-message">Error al cargar las facturas: {error.message}</div>;
    }   

    return (
        <div className="invoice-list-container">
            <h2 className="text-center">Listado de Facturas</h2>
            <ul className="invoice-list" style={{ textAlign: 'left' }}>
            {invoices.map((invoice) => (
                <li key={invoice.id} className="invoice-item d-flex justify-content-between align-items-center">
                    <div className="invoice-details" style={{ flex: 1 }}>
                        <strong>Factura ID:</strong> {invoice.id}, 
                        <strong> Usuario:</strong> {invoice.email},
                        <strong> Total:</strong> {invoice.total_invoice} €,
                        <strong> Creado:</strong> {new Date(invoice.created_at).toLocaleDateString('es-ES')}
                    </div>
                    <div>
                        <button 
                            className="btn btn-danger btn-sm" // Clase Bootstrap para botón pequeño
                            onClick={() => handleDelete(invoice.id)} // Eliminar factura
                            aria-label="Eliminar factura" // Atributo accesible
                            style={{ padding: '0.2rem 0.5rem', fontSize: '0.75rem' }} // Estilos adicionales si es necesario
                        >
                            <i className="bi bi-trash"></i> {/* Ícono de papelera de Bootstrap */}
                        </button>
                    </div>
                </li>
            ))}
            </ul>
            <ToastContainer position="top-center" autoClose={5000} hideProgressBar={false} newestOnTop={false} closeOnClick rtl={false} pauseOnFocusLoss draggable pauseOnHover />
        </div>
    );
};

export default InvoiceList;
