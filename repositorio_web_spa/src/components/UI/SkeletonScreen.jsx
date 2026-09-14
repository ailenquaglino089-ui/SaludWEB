// ============================================================
// components/UI/SkeletonScreen.jsx - Esqueleto de carga
// ============================================================
// Aplica el patrón de la guía "Estados de carga":
//
//   Skeleton screens:
//   - Reemplazan los placeholders vacíos por formas grises que
//     imitan el layout del contenido real.
//   - Reducen la percepción de espera porque el usuario ve
//     estructura inmediatamente.
//   - Ideales para listas y tablas.
//
// Este componente simula las filas y columnas de la tabla que
// está por cargar, con barras grises animadas (skeleton).
// ============================================================

// Definición de las columnas que imita el skeleton. Cada objeto
// representa una celda de la cabecera/tabla con su ancho (width)
// para que el esqueleto respete la proporción de la tabla real.
const columnas = [
  { key: 'col1', width: '35%' }, // Columna ancha (ej: Nombre)
  { key: 'col2', width: '20%' }, // Columna mediana (ej: Matrícula/DNI)
  { key: 'col3', width: '25%' }, // Columna mediana (ej: Especialidad)
  { key: 'col4', width: '10%' }, // Columna corta (ej: Estado)
  { key: 'col5', width: '10%' }, // Columna corta (ej: Acciones)
];

// Importa los estilos del esqueleto de carga (barras animadas)
import './Skeleton.css';

// SkeletonScreen: simula la tabla de listado mientras llegan los datos
export default function SkeletonScreen({ cargando }) {
  // Si no está cargando no se muestra nada (el contenido real ya está listo)
  if (!cargando) return null;

  return (
    // Contenedor con la misma clase de tabla real para heredar el layout
    <div className="tabla-container">
      <table className="tabla" aria-hidden="true">
        <thead>
          <tr>
            {/* Cabecera del esqueleto: un bloque gris por columna con su ancho */}
            {columnas.map((col) => (
              // key único por columna (col.key)
              <th key={col.key}>
                {/* Barra gris animada con el ancho de la columna */}
                <div className="skeleton skeleton-barra" style={{ width: col.width }} />
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {/* Genera de forma dinámica 5 filas de esqueleto (número fijo de ejemplo) */}
          {Array.from({ length: 5 }).map((_, filaIndex) => (
            // key compuesto: "fila-" + índice (nunca usar solo el índice en listas reales)
            <tr key={`fila-${filaIndex}`}>
              {/* Una celda de esqueleto por cada columna definida */}
              {columnas.map((col) => (
                // key compuesto: fila + columna (único entre todas las celdas)
                <td key={`${filaIndex}-${col.key}`}>
                  {/* Barra gris animada con el ancho de la columna */}
                  <div className="skeleton skeleton-barra" style={{ width: col.width }} />
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}