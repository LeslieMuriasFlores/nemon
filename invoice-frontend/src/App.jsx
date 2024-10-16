// src/App.jsx
import React from 'react';
import EnergyPriceForm from './components/EnergyPriceForm';
import PowerPriceForm from './components/PowerPriceForm';
import InvoiceCalculator from './components/InvoiceCalculator';
import InvoiceList from './components/InvoiceList';

const App = () => {
  return (
    <div>
      <h1>Gestión de Facturas de Energía</h1>
      <EnergyPriceForm />
      <PowerPriceForm />
      <InvoiceCalculator />
      <InvoiceList />
    </div>
  );
};

export default App;