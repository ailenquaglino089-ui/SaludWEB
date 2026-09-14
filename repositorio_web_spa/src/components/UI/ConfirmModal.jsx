// ============================================================
// components/UI/ConfirmModal.jsx - Modal de confirmación
// ============================================================
// Importa los estilos específicos del modal (overlay + tarjeta)
import './Modal.css';
// ============================================================
// Aplica el patrón de la guía "Confirmaciones y UX":
//   - Las acciones destructivas (eliminar, cancelar, descartar)
//     requieren confirmación explícita antes de ejecutarse.
//   - Reemplaza a window.confirm() nativo (que rompe el diseño
//     y no permite personalización) por un modal accesible.
//   - El botón de confirmar es rojo y con acción descriptiva.
//
// Props:
//   - abierto:      bool que controla si el modal se ve o no
//   - titulo:       texto del título (ej: "Eliminar médico")
//   - mensaje:      descripción específica de lo que se eliminará
//   - textoConfirmar: texto del botón rojo (ej: "Eliminar")
//   - textoCancelar:  texto del botón de cancelar (ej: "Cancelar")
//   - cargando:     deshabilita ambos botones mientras se procesa
//   - onConfirmar:  callback que se ejecuta al confirmar
//   - onCancelar:   callback que se ejecuta al cancelar / cerrar
// ============================================================

// Expone el componente confirmable con la API declarativa
export default function ConfirmModal({
  abierto = false,      // Por defecto cerrado: no se ve nada
  titulo = 'Confirmar acción', // Título genérico si no se pasa uno
  mensaje = '',         // Descripción específica de la acción destructiva
  textoConfirmar = 'Confirmar', // Texto del botón principal (rojo)
  textoCancelar = 'Cancelar',   // Texto del botón secundario
  cargando = false,     // Flag que desactiva botones durante la petición al servidor
  onConfirmar,          // Función ejecutada cuando el usuario confirma la acción
  onCancelar,           // Función ejecutada cuando el usuario cancela o cierra
}) {
  // Si el modal está cerrado no se renderiza nada (ahorro de DOM)
  if (!abierto) return null;

  return (
    // Capa oscura detrás del modal: cubre toda la pantalla y captura el clic de cierre
    // onClick en el overlay cierra el modal solo si el usuario toca FUERA de la tarjeta
    <div className="modal-overlay" onClick={onCancelar}>
      {/* 
        La tarjeta central del modal.
        onClick con stopPropagation: evita que el clic dentro de la tarjeta
        "suba" hasta el overlay y cierre el modal por error.
      */}
      <div className="modal-card" onClick={(e) => e.stopPropagation()}>
        {/* Encabezado del modal: título coherente con la acción destructiva */}
        <div className="modal-header">
          {/* Título dinámico del modal (ej: "Eliminar médico") */}
          <h3>{titulo}</h3>
        </div>

        {/* Cuerpo del modal: describe QUÉ se va a eliminar (acción irreversible) */}
        <div className="modal-body">
          {/* Mensaje específico que informa al usuario qué perderá */}
          <p>{mensaje}</p>
        </div>

        {/* Pie del modal: botones de decisión (cancelar / confirmar) */}
        <div className="modal-actions">
          {/* Botón de cancelar: estilo neutro; deshabilitado mientras se procesa */}
          <button
            type="button"
            className="btn btn-secondary"
            onClick={onCancelar}   // Cierra el modal sin hacer nada
            disabled={cargando}    // Previene doble clic mientras la petición corre
          >
            {textoCancelar}
          </button>

          {/* Botón de confirmar: SIEMPRE rojo (btn-danger) para señalar que es destructivo */}
          <button
            type="button"
            className="btn btn-danger"
            onClick={onConfirmar}  // Ejecuta la acción destructiva
            disabled={cargando}    // Evita envíos duplicados durante el procesamiento
          >
            {/* Muestra estado de carga en el propio botón (spinner contextual) */}
            {cargando ? 'Procesando...' : textoConfirmar}
          </button>
        </div>
      </div>
    </div>
  );
}