<?php
class Token
{
    private $conn;
    private $table_name = "tokens";
    public $id;
    public $user_id;
    public $refresh_token;
    public $expires_in;
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
                user_id = :user_id,
                refresh_token = :refresh_token,
                expires_in = :expires_in,
                updated_at = :updated_at,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->refresh_token=htmlspecialchars(strip_tags($this->refresh_token));
        $this->expires_in=htmlspecialchars(strip_tags($this->expires_in));
        $this->updated_at=htmlspecialchars(strip_tags($this->updated_at));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':refresh_token', $this->refresh_token);
        $stmt->bindParam(':expires_in', $this->expires_in);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':created_at', $this->created_at);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    function tokenExists()
    {
        $query = "SELECT id, user_id, refresh_token, expires_in
            FROM " . $this->table_name . "
            WHERE refresh_token = ?
            LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->refresh_token=htmlspecialchars(strip_tags($this->refresh_token));
        $stmt->bindParam(1, $this->refresh_token);
        $stmt->execute();
        $num = $stmt->rowCount();
        if($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->refresh_token = $row['refresh_token'];
            $this->expires_in = $row['expires_in'];
            return true;
        }
        return false;
    }

    function deleteOldToken()
    {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(1, $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>