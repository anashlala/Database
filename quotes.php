<?php
session_start();

//this is for the database connection 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "authors";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}



//handles the post request 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'login') {
        $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);

        $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = :username");
        $stmt->execute([':username', $user]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($pass, $row['password'])) {
                $_SESSION['validated'] = $row['user_id'];
                echo "Login successful!";
            } else {
                echo "Invalid username or password.";
            }


        } else {
            echo "Invalid username or password.";
        }
    } elseif ($action === 'logout') {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($action === 'favourite') {
        $quote_id = filter_input(INPUT_POST, 'quote', FILTER_SANITIZE_NUMBER_INT);
        $checked = filter_input(INPUT_POST, 'check', FILTER_VALIDATE_BOOLEAN);
        $user_id = $_SESSION['validated'];

        if ($checked) {

            //add to the favorites

            $stmt = $conn->prepare("INSERT INTO favourites (user_id, quote_id) VALUES (:user_id, :quote_id)");
            $stmt->execute([':user_id', $user_id]);
            $stmt->execute([':quote_id', $quote_id]);

            $stmt->execute();
        } else {

            //removes from the favorites
            $stmt = $conn->prepare("DELETE FROM favourites WHERE user_id = :user_id AND quote_id = :quote_id");
            $stmt->execute([':user_id', $user_id]);
            $stmt->execute([':quote_id', $quote_id]);

            $stmt->execute();
        }
    }
}



//handles the get request for the quotes

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    

    //validate
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    if ($page === false || $page < 1) {
        $page = 1;
    }


    //a max of 20 pages
    $limit = 20; 
    $offset = ($page - 1) * $limit;


    //fetch the quotes and favorites from the database

    $user_id = isset($_SESSION['validated']) ? $_SESSION['validated'] : 0;

    $stmt = $conn->prepare("SELECT q.quote_id, q.author_id, q.quote_text, IF(f.user_id IS NOT NULL, 1, 0) AS is_favorites
                            FROM quotes q
                        LEFT JOIN favorites f ON q.quote_id = f.quote_id AND f.user_id = :user_id
                        LIMIT :limit OFFSET :offset");



    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();

    $quotes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $author = htmlspecialchars($row['author_id']);
        $quote = htmlspecialchars($row['quote_text']);
        $quote_id = $row['quote_id'];
        $is_favourite = $row['is_favorites'] ? 'checked' : '';


        
        $fav_button = '';
        if (isset($_SESSION['validated'])) {
            $fav_button = "<div class=\"form-check form-switch\">
                             <input type=\"checkbox\" class=\"form-check-input\" id=\"c$quote_id\" 
                             onclick=\"buttonFav('c$quote_id', document.getElementById('c$quote_id').checked);\" $is_favourite>
                           </div>";
        }

        $quotes[] = "<div class=\"card mb-3 a4card w-100\">
                         <div class=\"card-header\">$author</div>
                         <div class=\"card-body d-flex align-items-center\">
                              <p class=\"card-text w-100\">$quote</p>
                              $fav_button
                         </div>
                     </div>";
    }


    //return the quotes as a json
    header('Content-Type: application/json');
    echo json_encode($quotes);
}

$conn = null;

?>

