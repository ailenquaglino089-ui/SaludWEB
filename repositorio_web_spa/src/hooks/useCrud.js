// ============================================================
// hooks/useCrud.js - Hook genérico de operaciones CRUD
// ============================================================
// Centraliza TODA la lógica repetida que antes estaba duplicada
// en ListaMedicos, ListaPacientes y ListaPrescripciones:
//
//   - Carga de la lista (GET) con estado de carga y error
//   - Alta (POST) y actualización (PUT) con feedback de éxito
//   - Baja (DELETE) con confirmación y feedback de éxito/error
//   - Estado del formulario (abrir nuevo / abrir edición / cerrar)
//
// Aplica la guía "CRUD como flujo completo":
//   Create -> POST, persiste y devuelve el recurso con su ID
//   Read   -> GET al montar, con isLoading / resolución / error
//   Update -> PUT/PATCH con el recurso en su nuevo estado
//   Delete -> DELETE + confirmación + 204 + quitar del estado local
//
// Uso:
//   const crud = useCrud({ endpoint: '/api/medicos', nombre: 'Médico' });
//   crud.guardar(datos);        // crea o actualiza según 'editando'
//   setItemAEliminar(item);     // abre el modal de confirmación
//   crud.confirmarEliminar();   // ejecuta el DELETE confirmado
// ============================================================

import { useState, useEffect, useCallback } from 'react';
// Importa los hooks de estado y ciclo de vida
import client from '../api/client';
// Importa la instancia de Axios (inyecta el token y maneja 401)
import { handleApiError, getDatos } from '../utils/crudHelpers';
// Importa las utilidades: traducir errores de API y extraer el payload { data }
import { useToast } from '../context/ToastContext';
// Importa el contexto de toasts para mostrar confirmaciones de éxito/error

// Parámetros configurables de cada módulo CRUD:
//   endpoint: ruta base de la API (ej: '/api/medicos')
//   nombre:   nombre legible del recurso (ej: 'Médico')
export default function useCrud({ endpoint, nombre }) {
  // Lista de registros obtenidos de la API (estado principal)
  const [data, setData] = useState([]);
  // Flag de carga: controla la visibilidad del skeleton mientras llega la respuesta
  const [cargando, setCargando] = useState(true);
  // Mensaje de error global de la lista (null = sin error)
  const [error, setError] = useState(null);
  // Registro en edición (null = modo "nuevo"; objeto = modo "editar")
  const [editando, setEditando] = useState(null);
  // Alterna entre mostrar la lista o el formulario de alta/edición
  const [formularioAbierto, setFormularioAbierto] = useState(false);
  // Registro pendiente de eliminar (abre el modal de confirmación; null = cerrado)
  const [itemAEliminar, setItemAEliminar] = useState(null);
  // Flag de la petición DELETE en curso (desactiva botones del modal)
  const [eliminando, setEliminando] = useState(false);

  // Hook del sistema de notificaciones: dispara los toasts de éxito/error
  const { mostrarToast } = useToast();

  // ------------------------------------------------------------
  // READ: carga la lista desde la API
  // useCallback + [endpoint] => solo se recrea si cambia la ruta
  // ------------------------------------------------------------
  const cargar = useCallback(async () => {
    try {
      // Activa el estado de carga (aparece el skeleton)
      setCargando(true);
      // GET al endpoint (el interceptor de Axios adjunta el Bearer token)
      const response = await client.get(endpoint);
      // Guarda la lista real extrayendo ".data.data" de la envoltura de la API
      setData(getDatos(response, []));
      // Operación exitosa => sin errores previos
      setError(null);
    } catch (err) {
      // Traduce el error de Axios a un mensaje legible (red / servidor / validación)
      setError(handleApiError(err));
    } finally {
      // Finaliza siempre: oculta el skeleton haya éxito o error
      setCargando(false);
    }
  }, [endpoint]); // Si el endpoint cambia, la función de carga se reconstruye

  // Al montar el componente se dispara la primer carga (patrón: GET al montar)
  useEffect(() => {
    cargar();
  }, [cargar]); // Depende de 'cargar' (estable gracias a useCallback)

  // ------------------------------------------------------------
  // Handlers del formulario: alternar entre lista y formulario
  // ------------------------------------------------------------

  // Prepara el modo "alta nueva": formulario limpio (editando = null)
  const abrirNuevo = () => {
    setEditando(null);          // Sin registro previo: campos vacíos
    setFormularioAbierto(true); // Muestra el formulario en reemplazo de la lista
  };

  // Prepara el modo "edición": precarga el registro en el formulario
  const abrirEditar = (item) => {
    setEditando(item);          // Guarda el registro para inicializar el formulario
    setFormularioAbierto(true); // Muestra el formulario en reemplazo de la lista
  };

  // Vuelve de la vista de formulario a la lista
  const cerrarFormulario = () => {
    setFormularioAbierto(false); // Restaura la lista
    setEditando(null);           // Limpia la edición pendiente
  };

  // ------------------------------------------------------------
  // CREATE + UPDATE: guarda un registro (crea o actualiza según 'editando')
  // ------------------------------------------------------------
  const guardar = async (datos) => {
    // Inicio del bloque con captura de errores
    try {
      if (editando) {
        // ----- Caso UPDATE -----
        // PUT al endpoint con el ID del registro en edición
        const response = await client.put(`${endpoint}/${editando.id}`, datos);
        // Reemplaza en el estado local el registro editado con la respuesta de la API
        setData((prev) =>
          prev.map((i) =>
            i.id === editando.id ? (getDatos(response) ?? { ...datos, id: editando.id }) : i
          )
        );
        // Confirmación de éxito proporcional a la acción (toast de guardar)
        mostrarToast(`${nombre} actualizado correctamente`, 'success');
      } else {
        // ----- Caso CREATE -----
        // POST al endpoint con los datos nuevos (alta)
        const response = await client.post(endpoint, datos);
        // Agrega el recurso creado (con su ID) al final de la lista local
        setData((prev) => [...prev, getDatos(response)]);
        // Confirmación de éxito del alta
        mostrarToast(`${nombre} creado correctamente`, 'success');
      }
      // Tras guardar con éxito se vuelve a la lista
      cerrarFormulario();
      // Sin errores pendientes
      setError(null);
    } catch (err) {
      // El servidor rechazó (validaciones, conflictos...): se muestra el error traducido
      const mensaje = handleApiError(err);
      // Error visible en la parte superior de la vista
      setError(mensaje);
      // Se re-lanza para que el formulario pueda mostrar errores por campo también
      throw err;
    }
  };

  // ------------------------------------------------------------
  // DELETE: elimina el registro confirmado en el modal
  // Se ejecuta SOLO después de que el usuario confirme
  // ------------------------------------------------------------
  const confirmarEliminar = async () => {
    // Guardia: sin registro seleccionado no hay nada que eliminar
    if (!itemAEliminar) return;

    // Inicio del bloque con captura de errores
    try {
      // Activa el estado de eliminación (spinner en el botón del modal)
      setEliminando(true);
      // DELETE al endpoint con el ID del registro confirmado
      await client.delete(`${endpoint}/${itemAEliminar.id}`);
      // Elimina el registro del estado local (patrón de la guía: quitar del estado local)
      setData((prev) => prev.filter((i) => i.id !== itemAEliminar.id));
      // Confirmación de éxito de la eliminación (toast breve)
      mostrarToast(`${nombre} eliminado correctamente`, 'success');
      // Sin errores pendientes (operación exitosa limpia el estado de error)
      setError(null);
      // Cierra el modal de confirmación
      setItemAEliminar(null);
    } catch (err) {
      // Traduce y muestra el error (ej: recurso con dependencias que no se puede borrar)
      mostrarToast(handleApiError(err), 'error');
      // También lo deja en el estado de error de la vista
      setError(handleApiError(err));
      // Cierra el modal igualmente para desbloquear la interfaz
      setItemAEliminar(null);
    } finally {
      // Finaliza siempre: el botón del modal vuelve a habilitarse
      setEliminando(false);
    }
  };

  // Cierra el modal de confirmación sin eliminar nada
  const cancelarEliminar = () => {
    setItemAEliminar(null); // Descarta el registro pendiente
  };

  // ------------------------------------------------------------
  // API pública del hook: todo lo que necesitan los listados
  // ------------------------------------------------------------
  return {
    // Datos y control de la lista
    data,              // Lista de registros (para iterar y renderizar la tabla)
    setData,           // Setter por si un módulo necesita una actualización custom
    cargando,          // Flag de carga (muestra/oculta el skeleton)
    error,             // Mensaje de error de la vista (o null)
    cargar,            // Recarga la lista manualmente (ideal tras un refetch)
    // Formulario
    formularioAbierto, // true = mostrar formulario; false = mostrar lista
    editando,          // Registro en edición (null en modo "nuevo")
    abrirNuevo,        // Abre el formulario para un alta nueva
    abrirEditar,       // Abre el formulario con un registro precargado
    cerrarFormulario,  // Volver de formulario a lista
    guardar,           // Crea o actualiza según el modo actual
    // Eliminación con confirmación
    itemAEliminar,     // Registro pendiente en el modal (null = modal cerrado)
    setItemAEliminar,  // Setter para abrir el modal (asignar un registro)
    eliminando,        // Flag de DELETE en curso (desactiva botones del modal)
    confirmarEliminar, // Ejecuta el DELETE confirmado
    cancelarEliminar,  // Cierra el modal sin eliminar
  };
}