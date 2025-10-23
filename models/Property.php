<?php
class Property {
    private $conn;
    private $table_name = "propiedades";

    public $id;
    public $nombre;
    public $precio;
    public $zona;
    public $tipo_vivienda;
    public $tipo_contrato;
    public $direccion;
    public $imagen;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Verificar si nombre y dirección ya existen
    public function checkUnique($nombre, $direccion, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE nombre = :nombre OR direccion = :direccion";
        
        if($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        $nombre = htmlspecialchars(strip_tags($nombre));
        $direccion = htmlspecialchars(strip_tags($direccion));
        
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":direccion", $direccion);
        
        if($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Crear propiedad
    public function create() {
        // Verificar si nombre o dirección ya existen
        if($this->checkUnique($this->nombre, $this->direccion)) {
            return "duplicate";
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre=:nombre, precio=:precio, zona=:zona, 
                  tipo_vivienda=:tipo_vivienda, tipo_contrato=:tipo_contrato, 
                  direccion=:direccion, imagen=:imagen";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->zona = htmlspecialchars(strip_tags($this->zona));
        $this->tipo_vivienda = htmlspecialchars(strip_tags($this->tipo_vivienda));
        $this->tipo_contrato = htmlspecialchars(strip_tags($this->tipo_contrato));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->imagen = htmlspecialchars(strip_tags($this->imagen));

        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":zona", $this->zona);
        $stmt->bindParam(":tipo_vivienda", $this->tipo_vivienda);
        $stmt->bindParam(":tipo_contrato", $this->tipo_contrato);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":imagen", $this->imagen);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer propiedades con filtros
    public function read($filters = []) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        
        // Aplicar filtros
        if(!empty($filters['zona'])) {
            $query .= " AND zona = :zona";
        }
        if(!empty($filters['tipo_contrato'])) {
            $query .= " AND tipo_contrato = :tipo_contrato";
        }
        if(!empty($filters['tipo_vivienda'])) {
            $query .= " AND tipo_vivienda = :tipo_vivienda";
        }
        if(!empty($filters['precio_min'])) {
            $query .= " AND precio >= :precio_min";
        }
        if(!empty($filters['precio_max'])) {
            $query .= " AND precio <= :precio_max";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Vincular parámetros de filtros
        if(!empty($filters['zona'])) {
            $stmt->bindParam(":zona", $filters['zona']);
        }
        if(!empty($filters['tipo_contrato'])) {
            $stmt->bindParam(":tipo_contrato", $filters['tipo_contrato']);
        }
        if(!empty($filters['tipo_vivienda'])) {
            $stmt->bindParam(":tipo_vivienda", $filters['tipo_vivienda']);
        }
        if(!empty($filters['precio_min'])) {
            $stmt->bindParam(":precio_min", $filters['precio_min']);
        }
        if(!empty($filters['precio_max'])) {
            $stmt->bindParam(":precio_max", $filters['precio_max']);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Leer una sola propiedad
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nombre = $row['nombre'];
            $this->precio = $row['precio'];
            $this->zona = $row['zona'];
            $this->tipo_vivienda = $row['tipo_vivienda'];
            $this->tipo_contrato = $row['tipo_contrato'];
            $this->direccion = $row['direccion'];
            $this->imagen = $row['imagen'];
            return true;
        }
        return false;
    }

    // Actualizar propiedad
    public function update() {
        // Verificar si nombre o dirección ya existen (excluyendo el actual)
        if($this->checkUnique($this->nombre, $this->direccion, $this->id)) {
            return "duplicate";
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET nombre=:nombre, precio=:precio, zona=:zona, 
                  tipo_vivienda=:tipo_vivienda, tipo_contrato=:tipo_contrato, 
                  direccion=:direccion, imagen=:imagen
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->zona = htmlspecialchars(strip_tags($this->zona));
        $this->tipo_vivienda = htmlspecialchars(strip_tags($this->tipo_vivienda));
        $this->tipo_contrato = htmlspecialchars(strip_tags($this->tipo_contrato));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        $this->imagen = htmlspecialchars(strip_tags($this->imagen));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vincular parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":zona", $this->zona);
        $stmt->bindParam(":tipo_vivienda", $this->tipo_vivienda);
        $stmt->bindParam(":tipo_contrato", $this->tipo_contrato);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":imagen", $this->imagen);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar propiedad
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener zonas únicas
    public function getZonas() {
        $query = "SELECT DISTINCT zona FROM " . $this->table_name . " ORDER BY zona";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener tipos de contrato únicos
    public function getTiposContrato() {
        $query = "SELECT DISTINCT tipo_contrato FROM " . $this->table_name . " ORDER BY tipo_contrato";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener tipos de vivienda únicos
    public function getTiposVivienda() {
        $query = "SELECT DISTINCT tipo_vivienda FROM " . $this->table_name . " ORDER BY tipo_vivienda";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener precio máximo
    public function getPrecioMaximo() {
        $query = "SELECT MAX(precio) as max_precio FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['max_precio'] ? $row['max_precio'] : 100000;
    }
}
?>