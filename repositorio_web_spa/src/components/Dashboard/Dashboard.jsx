// Importamos los hooks useState y useEffect para el estado y la carga de datos.
import { useState, useEffect } from 'react';
// Importamos Link de react-router: navegación interna SPA sin recargar la página.
import { Link } from 'react-router-dom';
// Importamos el hook useAuth para acceder a los datos del usuario autenticado.
import { useAuth } from '../../hooks/useAuth';
// Importamos la instancia de axios configurada (base URL + interceptores de token y 401).
import client from '../../api/client';
// Importamos la utilidad para extraer el payload de la API ({ ok, mensaje, data }).
import { getDatos } from '../../utils/crudHelpers';
// Importamos los estilos CSS del Dashboard.
import './Dashboard.css';

// Componente funcional Dashboard: pantalla de bienvenida con accesos y estadísticas.
export default function Dashboard() {
  // Extraemos el objeto usuario del contexto de autenticación (puede ser null si no hay sesión).
  const { usuario } = useAuth();

  // Estado con las estadísticas reales del servidor. Valores iniciales en 0 hasta que llegue la API.
  const [stats, setStats] = useState({
    medicos: 0,            // Total de médicos registrados
    medicos_activos: 0,    // Médicos con estado activo
    pacientes: 0,          // Total de pacientes registrados
    recetas_activas: 0,    // Recetas (prescripciones) vigentes
  });
  // Estado de carga de las estadísticas (controla la visibilidad del skeleton).
  const [cargando, setCargando] = useState(true);
  // Estado de error: si la petición falla se muestra un cartel con reintento.
  const [error, setError] = useState(null);

  // Función que consulta las estadísticas al backend.
  const cargarEstadisticas = async () => {
    // Inicio del bloque con captura de errores
    try {
      // Activa el estado de carga (aparece el skeleton en las tarjetas).
      setCargando(true);
      // GET al endpoint /api/estadisticas (el interceptor adjunta el token JWT).
      const response = await client.get('/api/estadisticas');
      // Guarda los contadores reales extraídos de la envoltura de la API.
      setStats(getDatos(response, { medicos: 0, medicos_activos: 0, pacientes: 0, recetas_activas: 0 }));
      // Éxito: se limpia cualquier error previo.
      setError(null);
    } catch (err) {
      // Si falla (red, servidor, 401), se muestra un mensaje de error amigable.
      setError('No se pudieron cargar las estadísticas. Intentá nuevamente.');
    } finally {
      // El bloque finally se ejecuta siempre: se apaga el skeleton.
      setCargando(false);
    }
  };

  // Al montar el Dashboard se cargan las estadísticas (patrón de la guía: GET al montar).
  useEffect(() => {
    // Llama a la función de carga de estadísticas.
    cargarEstadisticas();
  }, []); // Arreglo vacío: se ejecuta una sola vez al montar el componente

  return (
    // Contenedor principal del Dashboard.
    <div className="dashboard">
      {/* Cabecera de bienvenida con el nombre del usuario */}
      <div className="dashboard-header">
        {/* El operador ?. evita errores si 'usuario' es null */}
        <h1>👋 Bienvenido, {usuario?.nombre}!</h1>
        <p>Sistema de Gestión de Salud - SaludWEB</p>
      </div>

      {/* Grid de tarjetas de acceso rápido a los módulos */}
      <div className="dashboard-grid">
        {/* Tarjeta de acceso al módulo de Médicos */}
        <div className="dashboard-card">
          {/* Ícono representativo del módulo */}
          <div className="card-icon">👨‍⚕️</div>
          <h3>Médicos</h3>
          <p>Gestiona médicos y especialistas</p>
          {/* Enlace interno de la SPA a la ruta /medicos (sin recarga de página) */}
          <Link to="/medicos" className="card-link">Ver Médicos →</Link>
        </div>

        {/* Tarjeta de acceso al módulo de Pacientes */}
        <div className="dashboard-card">
          <div className="card-icon">👤</div>
          <h3>Pacientes</h3>
          <p>Administra la información de pacientes</p>
          {/* Enlace interno de la SPA a la ruta /pacientes (sin recarga de página) */}
          <Link to="/pacientes" className="card-link">Ver Pacientes →</Link>
        </div>

        {/* Tarjeta de acceso al módulo de Prescripciones */}
        <div className="dashboard-card">
          <div className="card-icon">💊</div>
          <h3>Prescripciones</h3>
          <p>Gestiona recetas médicas</p>
          {/* Enlace interno de la SPA a la ruta /prescripciones (sin recarga de página) */}
          <Link to="/prescripciones" className="card-link">Ver Prescripciones →</Link>
        </div>

        {/* Tarjeta de acceso a la Configuración */}
        <div className="dashboard-card">
          <div className="card-icon">⚙️</div>
          <h3>Configuración</h3>
          <p>Ajusta tus preferencias</p>
          {/* Enlace interno de la SPA a la ruta /configuracion (sin recarga de página) */}
          <Link to="/configuracion" className="card-link">Ir a Configuración →</Link>
        </div>
      </div>

      {/* Sección de estadísticas EN VIVO (datos reales provenientes de la API) */}
      <div className="dashboard-stats">
        {/* Si falla la carga, se muestra un mensaje de error con opción de reintentar */}
        {error ? (
          // Cartel de error con botón de reintentar (patrón de la guía: errores accionables)
          <div className="stat-box stat-error">
            <p className="stat-label">{error}</p>
            {/* Botón que reintenta la petición para recuperar las estadísticas */}
            <button className="btn btn-sm btn-info" onClick={cargarEstadisticas}>
              🔄 Reintentar
            </button>
          </div>
        ) : (
          <>
            {/* Indicador de médicos registrados */}
            <div className="stat-box">
              {/* Mientras carga se muestra un skeleton; al llegar la data, el número real */}
              <p className="stat-value">
                {cargando ? <span className="skeleton skeleton-barra stat-skeleton" /> : stats.medicos}
              </p>
              <p className="stat-label">Médicos Registrados</p>
            </div>

            {/* Indicador de pacientes activos */}
            <div className="stat-box">
              <p className="stat-value">
                {cargando ? <span className="skeleton skeleton-barra stat-skeleton" /> : stats.pacientes}
              </p>
              <p className="stat-label">Pacientes Activos</p>
            </div>

            {/* Indicador de prescripciones emitidas */}
            <div className="stat-box">
              <p className="stat-value">
                {cargando ? <span className="skeleton skeleton-barra stat-skeleton" /> : stats.recetas_activas}
              </p>
              <p className="stat-label">Recetas Vigentes</p>
            </div>
          </>
        )}
      </div>

      {/* Sección informativa sobre la aplicación */}
      <div className="dashboard-info">
        <h2>📚 Sobre SaludWEB</h2>
        <p>
          SaludWEB es una aplicación web profesional para la gestión integral de sistemas de salud.
          Construida siguiendo los patrones y arquitecturas modernas de Programación IV.
        </p>
        <p>
          {/* Solo el texto que está entre <strong> se resalta en negrita */}
          <strong>Características principales:</strong>
        </p>
        {/* Lista no ordenada con las características destacadas */}
        <ul>
          <li>API REST desacoplada (Backend + Frontend)</li>
          <li>Autenticación segura con JWT</li>
          <li>CRUD completo de Médicos, Pacientes y Prescripciones</li>
          <li>Interfaz responsiva y moderna</li>
          <li>Validaciones y manejo de errores robusto</li>
        </ul>
      </div>
    </div>
  );
}