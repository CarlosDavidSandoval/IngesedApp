
<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Información de conexión a la base de datos
$servername = "54.165.133.156";  // Cambia esto a tu servidor
$username = "adminback";         // Cambia esto a tu usuario de base de datos
$password = "Fixeba93.*";         // Cambia esto a tu contraseña
$dbname = "IngesedApp";          // Cambia esto al nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Variable para almacenar los errores
$error_message = '';

// Obtener el filtro del combo box si se ha enviado (por GET)
$filtro_proyecto = isset($_GET['filtro_proyecto']) ? $_GET['filtro_proyecto'] : '';
$filtro_nropedido = isset($_GET['filtro_nropedido']) ? $_GET['filtro_nropedido'] : '';

// Consulta para obtener los datos filtrados
$sql = "SELECT Id,NroPedido,Producto,CantidadSolicitada,Categoria,Tipo,Cantidad,Medida,UnidadMedicion,ValorUnitario,Proyecto,Total 
from IngesedApp.TransacObras";

// Si hay un filtro, se aplica
//if ($filtro != '') {
    //$sql .= " WHERE Proyecto LIKE '%$filtro%'";  // Filtrado por nombre
//}

$where_clauses = [];
if ($filtro_proyecto != '') {
    $where_clauses[] = "Proyecto LIKE '%$filtro_proyecto%'";
}
if ($filtro_nropedido != '') {
    $where_clauses[] = "NroPedido = '$filtro_nropedido'";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$result = $conn->query($sql);

// Obtener las opciones para el combo box desde la base de datos
$sql_combo = "SELECT DISTINCT Proyecto FROM IngesedApp.TransacObras";  // Puedes ajustar esta consulta para que sea más general si lo prefieres
$result_combo = $conn->query($sql_combo);

// Verificar si se enviaron los datos del formulario de inserción o actualización
if (isset($_POST['procesar'])) {
    echo 'Pedidos procesados';
    $ids = isset($_POST['ids']) ? json_decode($_POST['ids'], true) : [];
    $cantidades_solicitadas = isset($_POST['cantidades_solicitadas']) ? json_decode($_POST['cantidades_solicitadas'], true) : [];
        echo "<pre>"; // Esto formatea la salida para mejor legibilidad
         print_r($ids);
         print_r($cantidades_solicitadas);
        echo "</pre>";

       

    // Array para almacenar los IDs con cantidades solicitadas no vacías o distintas de 0
    if (count($ids) == count($cantidades_solicitadas)) {
    // Procesar los valores no vacíos y no 0
    foreach ($ids as $index => $id) {
        $cantidad = $cantidades_solicitadas[$index];

        // Si la cantidad es diferente de 0 y no está vacía, almacenamos el ID
        if ( $cantidad != 0) {
            $ids_con_cantidades_validas[] = $id;
            echo "Procesando ID: $id con CantidadSolicitada: $cantidad<br>";
        }
    }

    }
}elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updaterow'])) {
    // Recibir los datos del formulario
    $NroPedido = isset($_POST['NroPedido']) ? $_POST['NroPedido'] : '';
    $Categoria = isset($_POST['Categoria']) ? $_POST['Categoria'] : '';
    $Tipo = isset($_POST['Tipo']) ? $_POST['Tipo'] : '';
    $Producto = isset($_POST['Producto']) ? $_POST['Producto'] : '';
    $Cantidad = isset($_POST['Cantidad']) ? $_POST['Cantidad'] : '';
    $UnidadMedicion = isset($_POST['UnidadMedicion']) ? $_POST['UnidadMedicion'] : '';
    $Proyecto = isset($_POST['Proyecto']) ? $_POST['Proyecto'] : '';

    $consulta = "SELECT Tipo FROM IngesedApp.TransacObras";  // Aquí agregamos un filtro para obtener el tipo basado en NroPedido
    $resultado = $conn->query($consulta);

    //echo "El valor de la variable Id es: " . $resultado;
    if ($resultado) {
        $row= $resultado->fetch_assoc();
        $Tipo = $row['Tipo'];  // Aquí obtienes el valor de la columna 'Tipo'
    }

    echo "El valor de Nro pedido maximo es: " . $Tipo;

    // Verificar si es una actualización o una inserción
}elseif (isset($_POST['update']) && isset($_POST['Id'])) {
        // Es una actualización
        $Id = $_POST['Id'];

    

        //$Proyecto = $filtro
        echo "El valor de la variable Id es: " . $Id;

        $MaxNroPedido= "select max(NroPedido) NroPedidoMax from IngesedApp.TransacObras";
        $MaxNroPedidoGetResult=$conn->query($MaxNroPedido);

        $MaxNroPedidoGetRow = $MaxNroPedidoGetResult->fetch_assoc();
        $MaxNroPedidoGet = $MaxNroPedidoGetRow['NroPedidoMax']; // Obtener el valor del máximo NroPedido

        echo "El valor de Nro pedido maximo es: " . $MaxNroPedidoGet;


        
        
        if(($MaxNroPedidoGet-1)>$NroPedido )
        {
        
            $sql_insert_history = "INSERT INTO IngesedApp.TransacObras (NroPedido, CantdiadSolicitada,Producto,Categoria,Tipo,Cantidad,  UnidadMedicion,Proyecto)
                               VALUES ('$MaxNroPedidoGet', '$Categoria',SUBSTRING_INDEX('$Producto', ' ', 1), '$Producto', '$Cantidad', '$UnidadMedicion','$Proyecto')";
                   echo "la consulta ejecutada es: " .$sql_insert_history ;
                   $exec_sql_insert_history= $conn->query($sql_insert_history);              
        

        } else
        
        {
            $NroPedido2=$NroPedido+1;
        
            // Primero, insertamos el registro editado en la tabla de historial (TransacObras)
            $sql_insert_history = "INSERT INTO IngesedApp.TransacObras (NroPedido, Producto,CantidadSolicitada, Categoria,Tipo,  Cantidad, UnidadMedicion,Proyecto)
                                   VALUES ('$NroPedido2','$Producto', '$Categoria',SUBSTRING_INDEX('$Producto', ' ', 1), '$Producto', '$Cantidad', '$UnidadMedicion','$Proyecto')";
                                
            echo "la consulta ejecutada es: " .$sql_insert_history ;
            $exec_sql_insert_history= $conn->query($sql_insert_history);



        }


        
       
    } elseif (isset($_POST['insertar'])) 
    {
       
        
         

        
            // Es una inserción
            // Verificar si ya existe un pedido con el mismo NroPedido
            $sql_check = "SELECT distinct NroPedido FROM IngesedApp.TransacObras WHERE NroPedido = '$NroPedido'";
             echo "El valor del pedido es: " . $NroPedido;
            $result_check = $conn->query($sql_check);

             if ($result_check->num_rows > 0) 
             {
                 $sql_insert = "INSERT INTO IngesedApp.TransacObras (NroPedido, Categoria,Tipo, Producto, Cantidad, UnidadMedicion,Proyecto) 
                                VALUES ('$NroPedido', '$Categoria',SUBSTRING_INDEX('$Producto', ' ', 1), '$Producto', '$Cantidad', '$UnidadMedicion','$Proyecto')";
                $Result_insert=$conn->query($sql_insert);
                echo "la consulta ejecutada es: " .$sql_insert ;
            

            } else 
               {
            // Insertar un nuevo registro en la base de datos
            $error_message = "Debe estar creado el pedido y el proyecto";
       
            
            }
        
    } elseif (isset($_POST['updaterow']) && isset($_POST['Id'])) 
    {
        $Id = $_POST['Id'];
        
        // Actualizar el registro en la base de datos
        $sql_update = "UPDATE IngesedApp.TransacObras 
                       SET NroPedido = '$NroPedido', Categoria = '$Categoria',Tipo='$Tipo', Producto = '$Producto', 
                           Cantidad = '$Cantidad',  UnidadMedicion = '$UnidadMedicion', Proyecto = '$Proyecto'
                       WHERE Id = $Id";
        
        if ($conn->query($sql_update) === TRUE) {
            // Agregar el saludo después de actualizar
            $saludo_message = "¡Registro actualizado correctamente! Gracias por actualizar los datos.";
        } else {
            $error_message = "Error al actualizar el registro: " . $conn->error;
        }
    }


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Productos</title>
    <style>
    table {
            width: 100%;
            border-collapse: collapse; /* Eliminar espacio entre celdas */
        }

        /* Estilo para los encabezados */
        th {
            text-align: center;            /* Centrar el texto en las celdas del encabezado */
            padding: 15px;                 /* Añadir espacio dentro de las celdas */
            background-color: #4CAF50;     /* Fondo verde para el encabezado */
            color: white;                  /* Texto blanco */
            font-size: 18px;               /* Tamaño de la fuente */
            font-weight: bold;             /* Hacer el texto negrita */
            line-height: 1.5;              /* Ajustar la altura de la celda */
            border: 1px solid #ddd;        /* Añadir borde delgado */
        }

        /* Estilo para las celdas */
        td {
            padding: 10px;                 /* Espaciado en las celdas */
            text-align: left;              /* Alinear texto a la izquierda */
            border: 1px solid #ddd;        /* Borde para las celdas */
        }

        /* Ajuste específico para cada columna */
        th:nth-child(1), td:nth-child(1) { width: 50px; }   /* Ajusta la columna ID */
        th:nth-child(2), td:nth-child(2) { width: 50px; }  /* Ajusta la columna NroPedido */
        th:nth-child(3), td:nth-child(3) { width: 200px; }  /* Ajusta la columna Categoria */
        th:nth-child(4), td:nth-child(4) { width: 50px; }  /* Ajusta la columna CantidadSolicitada */
        th:nth-child(7), td:nth-child(7) { width: 50px; }  /* Ajusta la columna CantidadSolicitada */
        th:nth-child(8), td:nth-child(8) { width: 50px; }  /* Ajusta la columna CantidadSolicitada */
       
        /* Estilo para el formulario de inserción en una sola fila */
        .form-insert {
            display: grid;
            grid-template-columns: repeat(6, 1fr) auto; /* 6 columnas para los campos + 1 para el botón */
            gap: 10px;
            align-items: center;
            width: 100%;
        }

        .form-insert input {
            padding: 8px;
            width: 100%;
        }

        .input-nropedido {
        width: 30px; /* Ajusta el ancho del NroPedido */
        }

        .input-CantidadSolicitada {
        width: 30px; /* Ajusta el ancho del NroPedido */
        }

        th {
        text-align: center; /* Esto alineará el texto al centro dentro de las celdas del encabezado */
        }


        td input[name="Producto"] {
            width: 300px; /* Ajustamos el ancho del input "Producto" */
        }

        td input[name="NroPedido"] {
            width: 20px; /* Ajustamos el ancho del input "Producto" */
        }

        td input[name="CantidadSolicitada"] {
            width: 30px; /* Ajustamos el ancho del input "Producto" */
        }

        td input[name="UnidadMedicion"] {
            width: 20px; /* Ajustamos el ancho del input "Producto" */
        }

        td input[name="Cantidad"] {
            width: 30px; /* Ajustamos el ancho del input "Producto" */
        }

        .form-insert button {
            padding: 8px 16px;
            width: auto;
        }


    </style>
</head>
<body>

    <h2>Ingesed</h2>

    <!-- Formulario de filtro con combo box -->
    <!-- Formulario de filtro con combo box y filtro por NroPedido -->
    <form method="GET" action="">
        <label for="filtro_proyecto">Seleccionar filtro (Proyecto):</label>
        <select id="filtro_proyecto" name="filtro_proyecto" onchange="this.form.submit()">
            <option value="">-- Todos --</option>
            <?php
            // Generar las opciones del combo box
            if ($result_combo->num_rows > 0) {
                while ($row = $result_combo->fetch_assoc()) {
                    echo "<option value='" . $row['Proyecto'] . "' " . ($filtro_proyecto == $row['Proyecto'] ? 'selected' : '') . ">" . $row['Proyecto'] . "</option>";
                }
            }
            ?>
        </select>

        <label for="filtro_nropedido">Filtrar por NroPedido:</label>
        <input type="number" id="filtro_nropedido" name="filtro_nropedido" value="<?php echo htmlspecialchars($filtro_nropedido); ?>" placeholder="Número de Pedido" onchange="this.form.submit()">
    </form>

    <br><br>


<script>
    // Función que recoge los Ids y Cantidades solicitadas
    function procesarPedidos(event) {
        event.preventDefault();
        // Crear un arreglo para almacenar los Ids y las Cantidades solicitadas
        let ids = [];
        let cantidadesSolicitadas = [];

        // Obtener todas las filas de la tabla
        let rows = document.querySelectorAll('table tr');

        rows.forEach((row, index) => {
            // Evitar la primera fila de encabezados
            if (index === 0) return;

            let idInput = row.querySelector('input[name="Id[]"]');
            let cantidadInput = row.querySelector('input[name="CantidadSolicitada[]"]');
            console.log(`Row ${index}:`, idInput, cantidadInput);

            let id = idInput ? idInput.value : null;
            let cantidadSolicitada = cantidadInput ? cantidadInput.value : null;

            if (id && cantidadSolicitada && cantidadSolicitada != 0) {
                ids.push(id);
                cantidadesSolicitadas.push(cantidadSolicitada);
            }
        });

        console.log('IDs:', ids);
        console.log('Cantidades Solicitadas:', cantidadesSolicitadas);
         
        if (ids.length > 0) {
            document.querySelector('input[name="ids"]').value = JSON.stringify(ids);
            document.querySelector('input[name="cantidades_solicitadas"]').value = JSON.stringify(cantidadesSolicitadas);
            var form = document.getElementById('miFormulario');
            alert('No se han editado cantidades o no son válidas.'+ form);
            
            document.getElementById('miFormulario').submit();
            
            } else {
                  alert('No se han editado cantidades o no son válidas.');
            }
             
      
    }
    

</script>
    
    <form method="POST" action="" id="miFormulario">
    <button type="button" onclick="procesarPedidos(event)" name="procesar">Realizar Pedidos</button>
        
        <h3>Lista de productos</h3>
        
    <?php
    if ($result->num_rows > 0) {

     
        echo "<table>
                <tr>
                    <th>ID</th>
                    <th>Nro Pedido</th>
                    <th>Producto</th>
                    <th>Cantidad Solicitada </th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Cantidad Inicial</th>
                    <th>Unidad Medicion</th>
                    <th>Acción</th>
                </tr>";
    
    
        // Mostrar los datos de cada fila
        while ($row = $result->fetch_assoc()) {
            // Iniciar un formulario para cada fila
            $Tipo = isset($row["Tipo"]) ? $row["Tipo"] : ''; // Si 'Categoria' es null, asignamos una cadena vacía
            echo "<form method='POST' action=''>"; 
            echo "<tr>
                    <td>" . $row["Id"] . "</td>
                    <td><input type='number' name='NroPedido' value='" . $row["NroPedido"] . "' readonly required></td> <!-- No editable -->
                    <td><input type='text' name='Producto' value='" . $row["Producto"] . "' required></td>
                    <td><input type='number' name='CantidadSolicitada[]' value='" . $row["CantidadSolicitada"] . "' required></td>
                    <td><input type='text' name='Categoria' value='" . $row["Categoria"] . "' required></td>
                    <td><input type='text' name='Tipo' value='" . htmlspecialchars($Tipo) . "' required></td>
                    <td><input type='text' name='Cantidad' value='" . $row["Cantidad"] . "' required></td>
                    <td><input type='text' name='UnidadMedicion' value='" . $row["UnidadMedicion"] . "' required></td>
                    <td>
                        <!-- Campo hidden para el ID -->
                        <input type='hidden' name='Id[]' value='" . $row["Id"] . "'> 
                        <button type='submit' name='updaterow'>Actualizar</button>
                        <button type='submit' name='update'>Realizar Pedido</button>
                    </td>
                  </tr>";
            echo "</form>"; // Cerrar el formulario de cada fila
        }
    
        echo "</table>";
    } else {
        echo "No se encontraron resultados.";
    }
      
    ?>
    <br><br>
    

    <input type="hidden" name="ids" value="">
    <input type="hidden" name="cantidades_solicitadas" value="">
</form>

    <!-- Formulario para insertar un nuevo registro en formato de tabla -->
    <h3>Insertar nuevo producto</h3>
    <form method="POST" action="" class="form-insert">
        <!-- Fila de inserción de datos -->
        <input type="number" name="NroPedido" placeholder="NroPedido" required>
        <input type="text" name="Categoria" placeholder="Categoria" required>
        <input type="text" name="Producto" placeholder="Producto" required>
        <input type="number" name="Cantidad" placeholder="Cantidad" step="0.01" required>
        <input type="text" name="UnidadMedicion" placeholder="UnidadMedicion" required>
        <input type="text" name="Proyecto" placeholder="Proyecto" required>
        <button type="submit" name="insertar">Insertar Producto</button>
    </form>

</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
