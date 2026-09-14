// Importamos React y el hook useState para el estado local (useEffect lo maneja el hook useCrud).
import React, { useState } from 'react';
// Importamos el componente de formulario que se muestra al crear/editar.
import FormularioPrescripcion from './FormularioPrescripcion';
// Importamos el componente de cambio de estado de prescripción.
import ChangeStateForm from './ChangeStateForm';
// Importamos el hook useAuth para conocer el rol del usuario (RBAC en la UI).
import { useAuth } from '../../hooks/useAuth';
// Importamos el hook genérico de CRUD: centraliza carga, alta, edición y baja con toasts.
import useCrud from '../../hooks/useCrud';
// Importamos el sistema de toasts para confirmar el cambio de estado con feedback visual.
import { useToast } from '../../context/ToastContext';
// Importamos el modal de confirmación que reemplaza al window.confirm() nativo.
import ConfirmModal from '../UI/ConfirmModal';
// Importamos el esqueleto de carga (skeleton screen) que imita la tabla real.
import SkeletonScreen from '../UI/SkeletonScreen';
// Importamos la instancia de axios configurada (base URL + interceptores de token y 401).
import client from '../../api/client';
// Importamos utilidades CRUD: manejo de errores, extracción de datos, badges de estado y formato de fecha.
import { handleApiError, getStatusBadge, getStatusColor, formatDate } from '../../utils/crudHelpers';
// Importamos los estilos CSS de Prescripciones.
import './Prescripciones.css';

// Componente principal: lista de prescripciones con operaciones CRUD y cambio de estado.
export default function ListaPrescripciones() {
  // Obtenemos el usuario logueado desde el contexto de autenticación.
  const { usuario } = useAuth();
  // RBAC en UI: 'esAdmin' es true solo si el tipo_usuario del usuario es 'admin'.
  const esAdmin = usuario?.tipo_usuario === 'admin';

  // El hook useCrud reemplaza TODO el estado y handlers del CRUD típico:
  // data (prescripciones), cargando, error, cargar, guardar, eliminar,
  // y el control del formulario de alta/edición.
  const crud = useCrud({ endpoint: '/api/prescripciones', nombre: 'Prescripción' });
  // Desestructuramos del hook lo que usa la vista de la lista.
  const {
    data: prescripciones,     // Lista de prescripciones (renombrada para el render)
    cargando,                 // Flag de carga (controla el skeleton)
    error,                    // Mensaje de error de la vista
    formularioAbierto,        // true => mostrar formulario de alta/edición
    editando,                 // Prescripción en edición (null en modo alta nueva)
    abrirNuevo,               // Abre el formulario vacío (alta)
    abrirEditar,              // Abre el formulario con la prescripción precargada
    cerrarFormulario,         // Vuelve de formulario a lista
    guardar,                  // Crea o actualiza la prescripción según el modo
    itemAEliminar,            // Prescripción pendiente de confirmar eliminación
    setItemAEliminar,         // Abre el modal de confirmación con una prescripción
    eliminando,               // Flag de DELETE en curso (desactiva botones del modal)
    confirmarEliminar,        // Ejecuta el DELETE confirmado
    cancelarEliminar,         // Cierra el modal sin eliminar
  } = crud;

  // Estado que alterna entre mostrar la lista o el formulario de cambio de estado.
  const [mostrarCambioEstado, setMostrarCambioEstado] = useState(false);
  // Estado con la prescripción de la cual se está cambiando el estado.
  const [prescripcionParaCambioEstado, setPrescripcionParaCambioEstado] = useState(null);
  // Estado del texto de búsqueda para filtrar la tabla.
  const [busqueda, setBusqueda] = useState('');
  // Estado del filtro por estado (cadena vacía = todos los estados).
  const [filtroEstado, setFiltroEstado] = useState('');

  // Hook del sistema de toasts: lo usa el cambio de estado para confirmar éxito/error.
  const { mostrarToast } = useToast();

  // Handler del botón "Cambiar Estado": prepara el formulario de cambio de estado.
  const handleCambiarEstado = (prescripcion) => {
    // Guardamos la prescripción seleccionada para pasarla al formulario.
    setPrescripcionParaCambioEstado(prescripcion);
    // Mostramos el formulario de cambio de estado.
    setMostrarCambioEstado(true);
  };

  // Handler del guardado de cambio de estado: recibe el nuevo estado desde ChangeStateForm.
  const handleGuardarCambioEstado = async (nuevoEstado) => {
    // Inicio del bloque con captura de errores
    try {
      // PATCH a /api/prescripciones/{id}/estado con el nuevo estado en el body.
      await client.patch(`/api/prescripciones/${prescripcionParaCambioEstado.id}/estado`, {
        estado: nuevoEstado
      });
      // Actualizamos la prescripción correspondiente en el estado local con el nuevo estado.
      // (Uso del estado funcional "prev" para trabajar sobre la versión más reciente).
      crud.setData(prev => prev.map(p =>
        // Buscamos la prescripción que se estaba editando
        p.id === prescripcionParaCambioEstado.id
          // Solo a esa prescripción le actualizamos el campo 'estado'
          ? { ...p, estado: nuevoEstado }
          // Al resto las dejamos intactas
          : p
      ));
      // Confirmación de éxito proporcional a la acción (toast de actualización).
      mostrarToast('Estado actualizado correctamente', 'success');
      // Ocultamos el formulario de cambio de estado.
      setMostrarCambioEstado(false);
      // Limpiamos la prescripción seleccionada.
      setPrescripcionParaCambioEstado(null);
    } catch (err) {
      // Traduce el error de Axios a un mensaje legible para el usuario.
      mostrarToast(handleApiError(err), 'error');
    }
  };

  // Función auxiliar: convierte la lista de medicamentos de una prescripción en un texto separado por comas.
  const nombresMedicamentos = (p) =>
    // El || [] evita errores si el campo 'medicamentos' viene null o indefinido
    (p.medicamentos || []).map(m => m.nombre).join(', ');

  // Filtrado combinado: por búsqueda (texto) y por estado (select).
  const prescripcionesFiltr = prescripciones.filter(p => {
    // Coincide si el texto de búsqueda aparece en medicamentos, paciente o médico.
    const matchBusqueda = nombresMedicamentos(p).toLowerCase().includes(busqueda.toLowerCase()) ||
                         p.nombre_paciente?.toLowerCase().includes(busqueda.toLowerCase()) ||
                         p.nombre_medico?.toLowerCase().includes(busqueda.toLowerCase());
    // Coincide si no hay filtro de estado ('') o si el estado coincide exactamente.
    const matchEstado = !filtroEstado || p.estado === filtroEstado;
    // La prescripción se muestra solo si cumple AMBAS condiciones.
    return matchBusqueda && matchEstado;
  });

  // Si 'formularioAbierto' es true, en lugar de la lista renderizamos el formulario de alta/edición.
  if (formularioAbierto) {
    return (
      <FormularioPrescripcion
        // La prop 'prescripcion' es el objeto a editar o null para alta nueva
        prescripcion={editando}
        // La prop 'onGuardar' recibe los datos; useCrud decide entre POST o PUT
        onGuardar={guardar}
        // La prop 'onCancelar' restaura la vista de la lista
        onCancelar={cerrarFormulario}
      />
    );
  }

  // Si 'mostrarCambioEstado' está activo y hay prescripción, renderizamos el formulario de cambio de estado.
  if (mostrarCambioEstado && prescripcionParaCambioEstado) {
    return (
      <ChangeStateForm
        // La prop 'prescripcion' es la que será modificada
        prescripcion={prescripcionParaCambioEstado}
        // La prop 'onGuardar' recibe el nuevo estado elegido
        onGuardar={handleGuardarCambioEstado}
        // La prop 'onCancelar' restaura la vista de la lista
        onCancelar={() => {
          setMostrarCambioEstado(false);
          setPrescripcionParaCambioEstado(null);
        }}
      />
    );
  }

  return (
    // Contenedor principal de la lista.
    <div className="lista-container">
      {/* Cabecera con título y botón de nueva prescripción */}
      <div className="lista-header">
        <h1>💊 Gestión de Prescripciones</h1>
        {/* Botón que dispara el modo alta nueva (formulario vacío) */}
        <button className="btn btn-primary" onClick={abrirNuevo}>
          ➕ Nueva Prescripción
        </button>
      </div>

      {/* Renderizado condicional: solo mostramos el alert si hay error */}
      {error && <div className="alert alert-error">{error}</div>}

      {/* Barra de búsqueda + filtro de estado */}
      <div className="lista-filtros">
        {/* Input controlado por el estado 'busqueda': cada tecla actualiza la búsqueda */}
        <input
          type="text"
          placeholder="🔍 Buscar por medicamento, paciente o médico..."
          value={busqueda}
          onChange={(e) => setBusqueda(e.target.value)}
          className="input-busqueda"
        />
        {/* Select controlado por el estado 'filtroEstado': cada cambio filtra por estado */}
        <select 
          value={filtroEstado} 
          onChange={(e) => setFiltroEstado(e.target.value)}
          className="select-filtro"
        >
          {/* Opción por defecto: todos los estados */}
          <option value="">Todos los estados</option>
          <option value="activa">✓ Activa</option>
          <option value="vencida">⚠ Vencida</option>
          <option value="dispensada">✓ Dispensada</option>
          <option value="cancelada">✗ Cancelada</option>
        </select>
      </div>

      {/* Estado 1 - CARGANDO: skeleton que imita la tabla real (en vez del spinner) */}
      {cargando ? (
        <SkeletonScreen cargando={cargando} />
      ) : prescripcionesFiltr.length === 0 ? (
        // Estado 2 - VACÍO: el filtro no devolvió resultados
        <div className="lista-vacia">
          <p>No hay prescripciones registradas</p>
        </div>
      ) : (
        // Estado 3 - CON DATOS: hay prescripciones para mostrar en la tabla
        <div className="tabla-container">
          <table className="tabla">
            <thead>
              <tr>
                <th>Medicamentos</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Fecha Emisión</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {/* Iteramos sobre la lista filtrada para generar una fila por prescripción */}
              {prescripcionesFiltr.map(prescripcion => (
                // La key única por fila debe ser el id de la prescripción
                <tr key={prescripcion.id}>
                  {/* Medicamentos formateados: "Nombre (Dosis)" separados por comas */}
                  <td>{(prescripcion.medicamentos || [])
                      .map(m => `${m.nombre}${m.dosis ? ` (${m.dosis})` : ''}`)
                      .join(', ')}</td>
                  <td>{prescripcion.nombre_paciente}</td>
                  <td>{prescripcion.nombre_medico}</td>
                  {/* formatDate convierte la fecha ISO del backend al formato local dd/mm/aaaa */}
                  <td>{formatDate(prescripcion.fecha_emision)}</td>
                  <td>
                    {/* Badge con color de fondo según el estado (getStatusColor) y texto con ícono (getStatusBadge) */}
                    <span 
                      className="status-badge"
                      style={{ backgroundColor: getStatusColor(prescripcion.estado) }}
                    >
                      {/* getStatusBadge devuelve el texto con ícono del estado */}
                      {getStatusBadge(prescripcion.estado)}
                    </span>
                  </td>
                  <td className="acciones">
                    {/* Botón de editar: pasa la prescripción completa al handler */}
                    <button 
                      className="btn btn-sm btn-info"
                      onClick={() => abrirEditar(prescripcion)}
                    >
                      ✏️ Editar
                    </button>
                    {/* Botón para cambiar el estado (disponible para todos los usuarios) */}
                    <button 
                      className="btn btn-sm btn-warning"
                      onClick={() => handleCambiarEstado(prescripcion)}
                    >
                      🔄 Cambiar Estado
                    </button>
                    {/* RBAC: el botón Eliminar solo se muestra si el usuario es admin */}
                    {esAdmin && (
                    <button 
                      className="btn btn-sm btn-danger"
                      // Abre el modal de confirmación con la prescripción como objetivo
                      onClick={() => setItemAEliminar(prescripcion)}
                    >
                      🗑️ Eliminar
                    </button>
                  )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Modal de confirmación para acciones destructivas (reemplaza window.confirm) */}
      <ConfirmModal
        // 'abierto' es true solo si hay una prescripción pendiente de eliminar
        abierto={!!itemAEliminar}
        // Título específico de la acción destructiva
        titulo="Eliminar prescripción"
        // Descripción concreta de lo que se va a eliminar (guía: mensaje específico para el usuario)
        mensaje={`¿Estás seguro de eliminar la prescripción del paciente "${itemAEliminar?.nombre_paciente}"? Esta acción no se puede deshacer.`}
        // Mensaje de confirmación específico
        textoConfirmar="Eliminar"
        // Botón neutro de cancelación
        textoCancelar="Cancelar"
        // Desactiva ambos botones mientras corre el DELETE (evita envíos duplicados)
        cargando={eliminando}
        // Ejecuta el borrado solo cuando el usuario confirma
        onConfirmar={confirmarEliminar}
        // Cancela sin borrar nada
        onCancelar={cancelarEliminar}
      />
    </div>
  );
}