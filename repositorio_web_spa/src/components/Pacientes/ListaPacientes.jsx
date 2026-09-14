// Importamos React y el hook useState para el estado local (useEffect lo maneja el hook useCrud).
import React, { useState } from 'react';
// Importamos el componente de formulario que se muestra al crear/editar.
import FormularioPaciente from './FormularioPaciente';
// Importamos el hook useAuth para conocer el rol del usuario (RBAC en la UI).
import { useAuth } from '../../hooks/useAuth';
// Importamos el hook genérico de CRUD: centraliza carga, alta, edición y baja con toasts.
import useCrud from '../../hooks/useCrud';
// Importamos el modal de confirmación que reemplaza al window.confirm() nativo.
import ConfirmModal from '../UI/ConfirmModal';
// Importamos el esqueleto de carga (skeleton screen) que imita la tabla real.
import SkeletonScreen from '../UI/SkeletonScreen';
// Importamos utilidades CRUD: extracción de datos y helpers de badges de estado.
import { getStatusBadge, getStatusColor } from '../../utils/crudHelpers';
// Importamos los estilos CSS de Pacientes.
import './Pacientes.css';

// Componente principal: lista de pacientes con operaciones CRUD completas.
export default function ListaPacientes() {
  // Obtenemos el usuario logueado desde el contexto de autenticación.
  const { usuario } = useAuth();
  // RBAC en UI: 'esAdmin' es true solo si el tipo_usuario del usuario es 'admin'.
  const esAdmin = usuario?.tipo_usuario === 'admin';

  // El hook useCrud reemplaza TODO el estado y handlers repetidos:
  // data (pacientes), cargando, error, cargar, guardar, eliminar,
  // y el control del formulario (abrirNuevo, abrirEditar, cerrarFormulario).
  const crud = useCrud({ endpoint: '/api/pacientes', nombre: 'Paciente' });
  // Desestructuramos del hook lo que usa la vista de la lista.
  const {
    data: pacientes,          // Lista de pacientes (renombrada para el render)
    cargando,                 // Flag de carga (controla el skeleton)
    error,                    // Mensaje de error de la vista
    formularioAbierto,        // true => mostrar formulario; false => mostrar lista
    editando,                 // Paciente en edición (null en modo alta nueva)
    abrirNuevo,               // Abre el formulario vacío (alta)
    abrirEditar,              // Abre el formulario con el paciente precargado
    cerrarFormulario,         // Vuelve de formulario a lista
    guardar,                  // Crea o actualiza el paciente según el modo
    itemAEliminar,            // Paciente pendiente de confirmar eliminación
    setItemAEliminar,         // Abre el modal de confirmación con un paciente
    eliminando,               // Flag de DELETE en curso (desactiva botones del modal)
    confirmarEliminar,        // Ejecuta el DELETE confirmado
    cancelarEliminar,         // Cierra el modal sin eliminar
  } = crud;

  // Estado del texto de búsqueda para filtrar la tabla.
  const [busqueda, setBusqueda] = useState('');

  // Filtrado por búsqueda: filtra los pacientes por nombre, DNI u obra social.
  // (Se mantiene en cliente por ser un dataset pequeño ya cargado en memoria).
  const pacientesFiltr = pacientes.filter(p =>
    // Compara nombre (en minúsculas) contra el texto de búsqueda (en minúsculas)
    p.nombre?.toLowerCase().includes(busqueda.toLowerCase()) ||
    // Compara DNI (numérico, sin cambios de mayúsculas porque son dígitos)
    p.dni?.includes(busqueda) ||
    // Compara obra social contra el texto de búsqueda
    p.obra_social?.toLowerCase().includes(busqueda.toLowerCase())
  );

  // Si 'formularioAbierto' es true, en lugar de la lista renderizamos el formulario.
  if (formularioAbierto) {
    return (
      <FormularioPaciente
        // La prop 'paciente' es el objeto a editar o null para alta nueva
        paciente={editando}
        // La prop 'onGuardar' recibe los datos; useCrud decide entre POST o PUT
        onGuardar={guardar}
        // La prop 'onCancelar' restaura la vista de la lista
        onCancelar={cerrarFormulario}
      />
    );
  }

  return (
    // Contenedor principal de la lista.
    <div className="lista-container">
      {/* Cabecera con título y botón de nuevo paciente */}
      <div className="lista-header">
        <h1>👥 Gestión de Pacientes</h1>
        {/* Botón que dispara el modo alta nueva (formulario vacío) */}
        <button className="btn btn-primary" onClick={abrirNuevo}>
          ➕ Nuevo Paciente
        </button>
      </div>

      {/* Renderizado condicional: solo mostramos el alert si hay error */}
      {error && <div className="alert alert-error">{error}</div>}

      {/* Barra de búsqueda */}
      <div className="lista-filtro">
        {/* Input controlado por el estado 'busqueda': cada tecla actualiza la búsqueda */}
        <input
          type="text"
          placeholder="🔍 Buscar por nombre, DNI u obra social..."
          value={busqueda}
          onChange={(e) => setBusqueda(e.target.value)}
          className="input-busqueda"
        />
      </div>

      {/* Estado 1 - CARGANDO: skeleton que imita la tabla real (en vez del spinner) */}
      {cargando ? (
        <SkeletonScreen cargando={cargando} />
      ) : pacientesFiltr.length === 0 ? (
        // Estado 2 - VACÍO: el filtro no devolvió resultados
        <div className="lista-vacia">
          <p>No hay pacientes registrados</p>
        </div>
      ) : (
        // Estado 3 - CON DATOS: hay pacientes para mostrar en la tabla
        <div className="tabla-container">
          <table className="tabla">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Obra Social</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {/* Iteramos sobre la lista filtrada para generar una fila por paciente */}
              {pacientesFiltr.map(paciente => (
                // La key única por fila debe ser el id del paciente
                <tr key={paciente.id}>
                  <td>{paciente.nombre}</td>
                  <td>{paciente.dni}</td>
                  <td>{paciente.obra_social}</td>
                  <td>
                    {/* Badge con color de fondo según el estado (getStatusColor) y texto con ícono (getStatusBadge) */}
                    <span 
                      className="status-badge"
                      style={{ backgroundColor: getStatusColor(paciente.activo ? 'activo' : 'inactivo') }}
                    >
                      {/* getStatusBadge devuelve el texto con ícono del estado */}
                      {getStatusBadge(paciente.activo ? 'activo' : 'inactivo')}
                    </span>
                  </td>
                  <td className="acciones">
                    {/* Botón de editar: pasa el paciente completo al handler */}
                    <button 
                      className="btn btn-sm btn-info"
                      onClick={() => abrirEditar(paciente)}
                    >
                      ✏️ Editar
                    </button>
                    {/* RBAC: el botón Eliminar solo se muestra si el usuario es admin */}
                    {esAdmin && (
                      <button 
                        className="btn btn-sm btn-danger"
                        // Abre el modal de confirmación con el paciente como objetivo
                        onClick={() => setItemAEliminar(paciente)}
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
        // 'abierto' es true solo si hay un paciente pendiente de eliminar
        abierto={!!itemAEliminar}
        // Título específico de la acción destructiva
        titulo="Eliminar paciente"
        // Descripción concreta de lo que se va a eliminar (guía: mensaje específico)
        mensaje={`¿Estás seguro de eliminar a "${itemAEliminar?.nombre}"? Esta acción no se puede deshacer.`}
        // Botón rojo de confirmación con texto demarcado
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