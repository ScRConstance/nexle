<?php
class User
{
    private $conn;
    private $table_name = "users";
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $updated_at;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }
    function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                first_name = :first_name,
                last_name = :last_name,
                email = :email,
                password = :password,
                updated_at = :updated_at,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->first_name=htmlspecialchars(strip_tags($this->first_name));
        $this->last_name=htmlspecialchars(strip_tags($this->last_name));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->password=htmlspecialchars(strip_tags($this->password));
        $this->updated_at=htmlspecialchars(strip_tags($this->updated_at));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':created_at', $this->created_at);

        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);

        if($stmt->execute()) {
            //return true;
            $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, CONCAT(first_name , ' ', last_name) as displayname FROM " . $this->table_name . " WHERE id = ".$this->conn->lastInsertId());
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }
        return false;
    }
    function emailExists()
    {
        $query = "SELECT id, first_name, last_name, password
            FROM " . $this->table_name . "
            WHERE email = ?
            LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->email=htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        $num = $stmt->rowCount();
        if($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->password = $row['password'];
            return true;
        }
        return false;
    }
}
?>