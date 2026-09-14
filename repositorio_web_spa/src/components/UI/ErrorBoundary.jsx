// ============================================================
// components/UI/ErrorBoundary.jsx - Límite de errores de React
// ============================================================
// Aplica el patrón de la guía "Manejo de errores visibles":
//
//   Error boundaries:
//   - Capturan errores de renderizado inesperados y muestran un
//     fallback en lugar de una pantalla blanca.
//   - Permiten loguear el error con contexto suficiente (URL,
//     timestamp) para poder reproducirlo en depuración.
//
// Nota: los Error Boundaries SOLO existen como componentes de
// clase (los hooks no pueden capturar errores de renderizado).
// ============================================================

import React from 'react';
// Importa React (necesario para la sintaxis JSX y el componente de clase)

// Componente de clase que actúa como "límite de error" de la aplicación.
// Envuelve al árbol de componentes: si cualquier hijo lanza un error
// durante el render, este componente lo captura en vez de fallar la app.
export default class ErrorBoundary extends React.Component {
  // Constructor: inicializa el estado del componente
  constructor(props) {
    // Llama al constructor de React.Component (obligatorio en clases)
    super(props);
    // Estado inicial: todavía no hay ningún error capturado (null)
    this.state = { error: null };
  }

  // Método del ciclo de vida de React: se ejecuta cuando un hijo
  // lanza un error de renderizado. Devuelve el nuevo estado.
  static getDerivedStateFromError(error) {
    // Devuelve el error capturado para que render() muestre el fallback
    return { error };
  }

  // Método del ciclo de vida: se ejecuta DESPUÉS de capturar el error.
  // Es el lugar ideal para loguear con contexto (patrón de la guía:
  // "Siempre loguea los errores con contexto suficiente").
  componentDidCatch(error, info) {
    // Registra cuándo sucedió (año-mes-día hora:minuto:segundo)
    const timestamp = new Date().toLocaleString('es-ES');
    // Loguea el error y el fragmento (component) que lo causó
    console.error(`[ErrorBoundary] ${timestamp} - URL: ${window.location.href}`, error, info);
  }

  // Método que reinicia el límite de errores (borra el error actual).
  // Permite intentar de nuevo sin recargar toda la página.
  resetear = () => {
    // Vuelve a estado inicial: sin error capturado, se re-renderiza el árbol
    this.setState({ error: null });
  };

  // Método de renderizado obligatorio de los componentes de clase
  render() {
    // Si se capturó un error, se muestra una pantalla de fallback
    if (this.state.error) {
      return (
        // Contenedor centrado del fallback con estilo de error
        <div className="loading-container">
          {/* Título claro del fallback */}
          <h2>⚠️ Algo salió mal</h2>
          {/* Mensaje amigable: NO se expone el detalle técnico al usuario */}
          <p>Ocurrió un error inesperado. Intentá recargar la página o volver al inicio.</p>
          {/* Botón que reintenta el render (reinicia el límite de errores) */}
          <button className="btn btn-primary" onClick={this.resetear}>
            🔄 Reintentar
          </button>
        </div>
      );
    }

    // Si no hay error, se renderizan normalmente los componentes hijos
    return this.props.children;
  }
}