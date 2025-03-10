<?php
// Información de conexión a la base de datos
$servername = "54.165.133.156";  // Cambiar si tu servidor MySQL está en otro host
$username = "adminback";         // Cambiar por tu usuario de MySQL
$password = "Fixeba93.*";             // Cambiar por tu contraseña de MySQL
$dbname = "IngesedApp";          // Nombre de la base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}



// Verificar si se enviaron los datos del formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $Id = $_POST['Id'];
    $fldName = $_POST['fldName'];
    $fldEmail = $_POST['fldEmail'];
    $fldPhone = $_POST['fldPhone'];

    // Actualizar los datos en la base de datos
    $sql = "UPDATE tbl_contact SET fldName='$fldName', fldEmail='$fldEmail', fldPhone='$fldPhone' WHERE Id=$Id";
    if ($conn->query($sql) === TRUE) {
        echo "Registro actualizado correctamente";
    } else {
        echo "Error al actualizar: " . $conn->error;
    }

}
$sql = "SELECT Id, fldName, fldEmail, fldPhone FROM tbl_contact";
$result = $conn->query($sql);



?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Empleados</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>

    <h2>Listado de Empleados</h2>

    <?php
    // Verificar si hay resultados
    if ($result->num_rows > 0) {
        // Crear la tabla HTML
        echo "<form method='POST' action=''>
               <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>";

        // Mostrar los datos de cada fila
           while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["Id"] . "</td>
                    <td><input type='text' name='fldName' value='" . $row["fldName"] . "' required></td>
                    <td><input type='text' name='fldEmail' value='" . $row["fldEmail"] . "' required></td>
                    <td><input type='number' name='fldPhone' value='" . $row["fldPhone"] . "' step='0.01' required></td>
                    <td>
                        <button type='submit' name='Id' value='" . $row["Id"] . "'>Actualizar</button>
                    </td>
                  </tr>";
        }
        echo "</table></form>";
    } else {
        echo "No se encontraron resultados.";
    }

    // Cerrar la conexión
    $conn->close();
    ?>

</body>
</html>
