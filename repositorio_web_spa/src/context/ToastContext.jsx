// ============================================================
// context/ToastContext.jsx - Sistema global de notificaciones
// ============================================================
// Aplica el patrón de la guía "Confirmaciones y UX":
//
//   Confirmaciones de éxito:
//   - Toast/Snackbar para operaciones frecuentes (guardar,
//     actualizar, eliminar). Aparece brevemente y desaparece solo.
//   - No interrumpe el flujo del usuario: es no bloqueante.
//
// Cómo se usa:
//   const { mostrarToast } = useToast();
//   mostrarToast('Médico guardado correctamente', 'success');
//
// Tipos soportados:
//   - 'success' (verde):  operaciones exitosas
//   - 'error' (rojo):     operaciones fallidas con mensaje claro
//   - 'info' (azul):      avisos neutros
//   - 'warning' (naranja): advertencias
// ============================================================

import React, { createContext, useContext, useState, useRef, useCallback } from 'react';
// Importa React y los hooks del contexto global de toasts
import '../components/UI/Toast.css';
// Importa los estilos de los toasts (los estilos viajan con el proveedor)

// Contexto de toasts: cualquier componente puede leerlo con useToast()
export const ToastContext = createContext();

// Hook público: expone mostrarToast() a toda la aplicación
export const useToast = () => {
  // Lee el valor inyectado por <ToastProvider>
  const context = useContext(ToastContext);
  // Guardia: si se usa fuera del Provider se avisa con un error claro
  if (!context) {
    throw new Error('useToast debe ser usado dentro de ToastProvider');
  }
  // Devuelve el objeto con la función para disparar toasts
  return context;
};

// Proveedor global: envuelve la aplicación y gestiona la cola de toasts
export const ToastProvider = ({ children }) => {
  // Estado con la lista de toasts activos (cada uno con id, tipo y mensaje)
  const [toasts, setToasts] = useState([]);
  // Contador de toasts: genera IDs únicos de forma incremental
  const contadorId = useRef(0);

  // Diccionario (clave -> valor) de duración por tipo en milisegundos
  const DURACIONES = {
    success: 3000, // 3 segundos para confirmaciones de éxito (las más frecuentes)
    error: 5000,   // 5 segundos para errores (el usuario necesita leer el mensaje)
    info: 3500,    // 3.5 segundos para avisos neutros
    warning: 4000, // 4 segundos para advertencias
  };

  // Crea un nuevo toast y lo agrega a la cola.
  // useCallback memoriza la función para que su referencia sea estable
  // (evita re-renders innecesarios de los componentes que la consumen).
  const mostrarToast = useCallback((mensaje, tipo = 'success') => {
    // Genera un ID único usando el contador ref (++ incrementa antes de usar)
    const id = ++contadorId.current;
    // Agrega el nuevo toast al final de la lista (spread ...prev conserva los anteriores)
    setToasts((prev) => [...prev, { id, mensaje, tipo }]);

    // Auto-descarte: pasado el tiempo según el tipo, el toast se elimina solo
    setTimeout(() => {
      // Filtra la lista quedándose solo con los toasts de ID distinto al actual
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, DURACIONES[tipo] || 3500); // Fallback de 3.5s si el tipo no está en el mapa
  }, []);

  // Cierra un toast manualmente (al tocar su botón de cierre "×")
  const cerrarToast = useCallback((id) => {
    // Elimina de la lista el toast con el ID recibido
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  // Valor expuesto por el contexto: la lista de toasts y las dos funciones
  const value = {
    toasts,          // Lista de toasts activos (la consume ToastContainer para renderizar)
    mostrarToast,    // Función pública: dispara un toast nuevo (success/error/info/warning)
    cerrarToast,     // Función para cerrar un toast específico prematuramente
  };

  // Inyecta el valor a todos los descendientes del árbol de componentes
  return (
    <ToastContext.Provider value={value}>
      {/* children: toda la aplicación (rutas, páginas, formularios) */}
      {children}

      {/* --- Contenedor visual de toasts (renderizado dentro del Provider) --- */}
      <div className="toast-container">
        {/* Recorre la lista de toasts activos y dibuja uno por cada elemento */}
        {toasts.map((toast) => (
          // Cada toast con su key única (el id generado con el ref)
          <div 
            key={toast.id} 
            className={`toast toast-${toast.tipo}`}
            role="status" // Rol ARIA: los lectores de pantalla lo anuncian como status
            aria-live="polite" // Anuncia cambios sin interrumpir la lectura actual
          >
            <span className="toast-mensaje">{toast.mensaje}</span>
            {/* Botón de cierre manual (×) para descartar el toast antes del timeout */}
            <button
              className="toast-cerrar"
              onClick={() => cerrarToast(toast.id)}
              aria-label="Cerrar notificación"
            >
              ×
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
};