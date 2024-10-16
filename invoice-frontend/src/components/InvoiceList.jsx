// InvoiceList.jsx
import React, { useEffect, useState } from 'react';
import axios from '../axios'; // Asegúrate de que esto apunta correctamente a tu instancia de Axios

const InvoiceList = () => {
    const [invoices, setInvoices] = useState([]); // Inicializar como un array vacío
    const [loading, setLoading] = useState(true); // Para manejar el estado de carga
    const [error, setError] = useState(null); // Para manejar errores

    useEffect(() => {
        const fetchInvoices = async () => {
            try {
                const response = await axios.get('/invoices'); // Asegúrate de que esta URL sea correcta
                setInvoices(response.data); // Asegúrate de que response.data sea un array
            } catch (err) {
                setError(err);
            } finally {
                setLoading(false);
            }
        };

        fetchInvoices();
    }, []);

    if (loading) {
        return <div>Cargando...</div>;
    }

    if (error) {
        return <div>Error al cargar las facturas: {error.message}</div>;
    }

    return (
        <div>
            <h2>Listado de Facturas</h2>
            <ul>
                {invoices.map((invoice) => (
                    <li key={invoice.id}>
                        Factura ID: {invoice.id}, Total: {invoice.total_invoice}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default InvoiceList;
