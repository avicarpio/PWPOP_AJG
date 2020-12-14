<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 13/07/19
 * Time: 16:39
 */

namespace PwExam\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use PwExam\Model\Item;
use DateTime;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \PDO;
use PDOException;

require ('PHPMailer/src/PHPMailer.php');
require ('PHPMailer/src/SMTP.php');



class ItemController
{

    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function formAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'addItem.twig', []);
    }


    public function addItemAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $data['updating'] = false;
        $errors = $this->validate($data);

        if (!empty($errors) > 0) {
            return $this->container->get('view')->render($response, 'addItem.twig', ['errors' => $errors], 200);
        }

        if (empty($errors)) {
            $_SESSION['logged'] = true;
            $target_Path = __DIR__ . "/../../public/assets/images/";
            if (!file_exists($target_Path)) {
                mkdir($target_Path, 0777, true);
            }
            $target_Path = $target_Path . basename($_FILES['item_image']['name']);
            move_uploaded_file($_FILES['item_image']['tmp_name'], $target_Path);
            $fp = $_FILES['item_image']['name'];
            $errors_db = $this->addItem($data['title'], $data['description'], $data['price'], $fp, $data['category']);
            if (!empty($errors_db)) {
                return $this->container->get('view')->render($response, 'addItem.twig', ['errors' => $errors_db], 200);
            }
            return $this->container->get('view')->render($response, 'addItem.twig', [], 200);
        }
        return $this->container->get('view')->render($response, 'addItem.twig', ['errors' => $errors], 200);
    }


    public function itemAction(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $result = $this->getItem($id);

        $logged = $_SESSION['logged'];

        if (empty($result)) {
            return $this->container->get('view')->render($response, 'itemOverview.twig', ['okay' => false], 200);
        } else {
            $item = new Item(
                $result[0]['id'],
                $result[0]['title'],
                $result[0]['description'],
                $result[0]['category'],
                $result[0]['price'],
                "/assets/images/" . $result[0]['product_image'],
                new DateTime($result[0]['created_at']),
                new DateTime($result[0]['updated_at'])
            );

            return $this->container->get('view')->render($response, 'itemOverview.twig',
                ['item' => $item, 'okay' => true, 'logged' => $logged], 200);
        }


    }

    public function itemDeleteAction(Request $request, Response $response, array $args)
    {

        $id = $args['id'];

        $errors = $this->deleteItem($id);

        header('Location: ' . '../../');
    }


    public function buyItemAction(Request $request, Response $response, array $args)
    {

        $id = $args['id'];

        $errors = $this->buyItem($id);

        if (empty($errors)) {
            $errors['info'] = 'Mail sent';
        }

        return $this->container->get('view')->render($response, 'info.twig', ['errors' => $errors], 200);

    }


    public function buyItem($id)
    {

        $errors = [];

        $item = $this->getItem($id);

        $item = $item[0];

        $userId = $_COOKIE['userId'];

        $user = $this->searchUser($userId);
        $user = $user[0];
        $sellerUser = $this->searchUser($item['id_user']);
        $sellerUser = $sellerUser[0];

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = '465';
        $mail->isHTML(true);
        $mail->Username = 'noreply.pwpop@gmail.com';
        $mail->Password = 'YWxleCB2aWNlbnRl';
        $mail->SetFrom('no-reply@pwpop.cat');
        $mail->Subject = 'Someone is interested in your ' . $item['title'];
        $mail->Body = "Hi " . $sellerUser['name'] . "! I'm interested in buying your " . $item['title'] . ", please contact me asap.\n\n Name: " . $user['name'] . " \n/ Phone:  " . $user['phone'];
        $mail->AddAddress($sellerUser['email']);

        if (!$mail->Send()) {
            $errors['general'] = "Error Sending mail";
            return $errors;
        } else {
            $errors = $this->deleteItem($id);
        }

        return $errors;
    }


    public function validate($data)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = 'Title cannot be empty';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description cannot be empty';
        } elseif (strlen($data['description']) < 20) {
            $errors['description'] = 'Write at least 20 characters';
        }

        if (filter_var($data['price'], FILTER_VALIDATE_FLOAT) === false) {
            $errors['price'] = 'Price must be a float.';
        }

        if(!$data['updating']) {
            $path = $_FILES['item_image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            if ($ext != "jpg" && $ext != "png") {
                $errors['image'] = 'Only JPG and PNG accepted.';
            } elseif ($_FILES['item_image']['size'] > 1000000) {
                $errors['image'] = 'Max size is 1Mb.';
            }
        }

        if ($data['category'] != 'Computers and electronic'
            && $data['category'] != 'Cars'
            && $data['category'] != 'Sports'
            && $data['category'] != 'Games'
            && $data['category'] != 'Fashion'
            && $data['category'] != 'Home'
            && $data['category'] != 'Other') {
            $errors['category'] = 'Select one option of the list.';
        }

        return $errors;

    }

    public function addItem($title, $description, $price, $pic, $category)
    {
        $errors = [];

        $userId = $_COOKIE['userId'];

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $currentDate = date("Y-m-d H:i:s");
            $statement = $db->prepare("INSERT INTO item (id_user, title, description, price, product_image, category, created_at, updated_at) 
                                                                  VALUES (:id_user, :title, :description, :price, :product_image, :category, :created_at, :updated_at)");
            $statement->bindParam(':id_user', $userId, PDO::PARAM_STR);
            $statement->bindParam(':title', $title, PDO::PARAM_STR);
            $statement->bindParam(':description', $description, PDO::PARAM_STR);
            $statement->bindParam(':price', $price, PDO::PARAM_STR);
            $statement->bindParam(':product_image', $pic, PDO::PARAM_STR);
            $statement->bindParam(':category', $category, PDO::PARAM_STR);
            $statement->bindParam(':created_at', $currentDate, PDO::PARAM_STR);
            $statement->bindParam(':updated_at', $currentDate, PDO::PARAM_STR);
            $statement->execute();
            $errors['info'] = 'Item updated!';


        } catch (PDOException $e) {
            $errors['general'] = 'Fatal Error';

            return $errors;
        }

        return $errors;

    }


    public function getItem($id)
    {

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("SELECT * FROM item WHERE id = :id AND is_active = 1");
            $statement->bindParam(':id', $id, PDO::PARAM_STR);
            $statement->execute();
            $item = $statement->fetchAll();

            return $item;

        } catch (PDOException $e) {
            return [];
        }

    }


    public function deleteItem($id)
    {

        $errors = [];

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("UPDATE item SET is_active = 0 WHERE id = :id");
            $statement->bindParam(':id', $id, PDO::PARAM_STR);
            $statement->execute();

            return $errors;

        } catch (PDOException $e) {

            $errors['general'] = 'Failed deleting the item';
            return $errors;
        }

    }


    public function searchUser($userId)
    {
        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("SELECT * FROM user WHERE id = :id ORDER BY id DESC");
            $statement->bindParam(':id', $userId, PDO::PARAM_STR);
            $statement->execute();
            $users = $statement->fetchAll();

            return $users;

        } catch (PDOException $e) {
            return [];
        }
    }




    public function updateItemForm( Request $request, Response $response, array $args)
    {

        $id = $args['id'];


        $errors = [];

        $logged = $_SESSION['logged'];

        if(isset($_COOKIE['userId'])) {
            $userId = $_COOKIE['userId'];
            $item = $this->getItem($id);
        }else{
            $userId = 0;
        }

        if( empty($item) || $userId != $item[0]['id_user'] ){
           $errors['general'] = 'Error updating item';

            return $this->container->get('view')->render($response, ' editItem.twig', ['logged'=>$logged, 'errors' => $errors]);

        }else{
            return $this->container->get('view')->render($response, 'editItem.twig', ['logged'=>$logged, 'item' => $item[0]]);
        }
    }


    public function updateItem(Request $request, Response $response)
    {

        $data = $request -> getParsedBody();
        $data['updating'] = true;
        $id = $data['id'];
        $errors = $this -> validate($data);
        $logged = $_SESSION['logged'];

        if (count($errors) > 0) {
            return $this->container->get('view')->render($response,'editItem.twig', ['logged'=>$logged, 'errors'=>$errors ], 200);
        }

        if (empty($errors)) {

            try {
                $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $currentDate = date("Y-m-d H:i:s");

                $statement = $db->prepare("UPDATE item SET title = :title,
                                                                    description = :description,
                                                                    price = :price,category = :category , 
                                                                    updated_at = :updated_at 
                                                                WHERE id = :id"
                );


                $statement->bindParam(':title', $data['title'], PDO::PARAM_STR);
                $statement->bindParam(':description', $data['description'], PDO::PARAM_STR);
                $statement->bindParam(':price', $data['price'], PDO::PARAM_STR);
                $statement->bindParam(':category', $data['category'], PDO::PARAM_STR);
                $statement->bindParam(':updated_at', $currentDate, PDO::PARAM_STR);
                $statement->bindParam(':id', $id, PDO::PARAM_STR);
                $statement->execute();

            } catch (PDOException $e) {
                $errors['general'] = $e;

                return $errors;
            }
        }

        return $this->container->get('view')->render($response,'editItem.twig', ['logged'=>$logged, 'errors'=>$errors], 200);

    }



}