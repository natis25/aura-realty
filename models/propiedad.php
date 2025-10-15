<?php
class Propiedad {
    private $conn;
    private $table_name = "propiedades";

    public $id;
    public $titulo;
    public $descripcion;
    public $tipo;
    public $direccion;
    public $ciudad;
    public $precio;
    public $habitaciones;
    public $banos;
    public $metros_cuadrados;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . "
                SET titulo=:titulo, descripcion=:descripcion, tipo=:tipo, 
                    direccion=:direccion, ciudad=:ciudad, precio=:precio,
                    habitaciones=:habitaciones, banos=:banos, metros_cuadrados=:metros_cuadrados";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->ciudad = htmlspecialchars(strip_tags($this->ciudad));

        // Vincular parámetros
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":ciudad", $this->ciudad);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":habitaciones", $this->habitaciones);
        $stmt->bindParam(":banos", $this->banos);
        $stmt->bindParam(":metros_cuadrados", $this->metros_cuadrados);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function leer() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fecha_creacion DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function leerUno() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->titulo = $row['titulo'];
            $this->descripcion = $row['descripcion'];
            $this->tipo = $row['tipo'];
            $this->direccion = $row['direccion'];
            $this->ciudad = $row['ciudad'];
            $this->precio = $row['precio'];
            $this->habitaciones = $row['habitaciones'];
            $this->banos = $row['banos'];
            $this->metros_cuadrados = $row['metros_cuadrados'];
            return true;
        }
        return false;
    }

    public function actualizar() {
        $query = "UPDATE " . $this->table_name . "
                SET titulo=:titulo, descripcion=:descripcion, tipo=:tipo,
                    direccion=:direccion, ciudad=:ciudad, precio=:precio,
                    habitaciones=:habitaciones, banos=:banos, metros_cuadrados=:metros_cuadrados
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->ciudad = htmlspecialchars(strip_tags($this->ciudad));

        // Vincular parámetros
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":ciudad", $this->ciudad);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":habitaciones", $this->habitaciones);
        $stmt->bindParam(":banos", $this->banos);
        $stmt->bindParam(":metros_cuadrados", $this->metros_cuadrados);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>