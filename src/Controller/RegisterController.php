<?php


namespace PwExam\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \PDO;
use PDOException;


final class RegisterController
{

    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function formAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'register.twig', []);
    }

    public function registerAction(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $data['updating'] = false;
        $errors = $this->validate($data);

        if (count($errors) > 0) {
            return $this->container->get('view')->render($response,'register.twig', ['errors'=>$errors], 200);
        }

        if (empty($errors)) {
            if(!empty($_FILES['profile_image']['tmp_name'])) {
                $target_Path = __DIR__ . "/../../public/assets/images/";
                if (!file_exists($target_Path)) {
                    mkdir($target_Path, 0777, true);
                }
                $target_Path = $target_Path.basename( $_FILES['profile_image']['name'] );
                move_uploaded_file( $_FILES['profile_image']['tmp_name'], $target_Path );
                $fp = $_FILES['profile_image']['name'];
            }else{
                $fp = null;
            }
            $errors_db = $this->addProfileData($data['name'], $data['username'], $data['email'],  $data['birthdate'], $data['phone'], $data['password'], $fp);
            if (!empty($errors_db)){
                return $this->container->get('view')->render($response,'register.twig', ['errors'=>$errors_db], 200);
            }
            $errors['info'] = 'Registration Successfull';
            return $this->container->get('view')->render($response, 'login.twig',['errors'=>$errors], 200);
        }

        return $this->container->get('view')->render($response,'register.twig', ['errors'=>$errors], 200);

    }



    public function updateUser(Request $request, Response $response): Response
    {
        $data = $request -> getParsedBody();
        $data['updating'] = true;
        $errors = $this -> validate($data);
        $logged = $_SESSION['logged'];

        if (count($errors) > 0) {
            return $this->container->get('view')->render($response,'profile.twig', ['logged'=>$logged, 'errors'=>$errors], 200);
        }

        if (empty($errors)) {


            if( !empty($_FILES['profile_image']['tmp_name']) ) {
                $target_Path = __DIR__ . "/../../public/assets/images/";

                if (!file_exists($target_Path)) {
                    mkdir($target_Path, 0777, true);
                }

                $target_Path = $target_Path.basename( $_FILES['profile_image']['name'] );
                move_uploaded_file( $_FILES['profile_image']['tmp_name'], $target_Path );
                $fp = $_FILES['profile_image']['name'];

            }else{
                $fp = null;
            }
            $data['profile_image'] = __DIR__ . "/../../public/assets/images/".$fp;

            $errors_db = $this->doUpdate($data['name'], $data['username'], $data['email'],  $data['birthdate'], $data['phone'], $data['password'], $fp);

            if (!empty($errors_db)){
                return $this->container->get('view')->render($response,'profile.twig', ['logged'=>$logged, 'errors'=>$errors_db, 'user' => $data], 200);
            }
            $errors['info'] = 'Update Successfull';
            return $this->container->get('view')->render($response, 'profile.twig',['logged'=>$logged, 'errors'=>$errors, 'user' => $data], 200);
        }

        return $this->container->get('view')->render($response,'profile.twig', ['logged'=>$logged, 'errors'=>$errors, 'user' => $data], 200);

    }







    public function doUpdate($name, $user, $email, $birth, $phone, $password, $fp)
    {
        $errors = [];

        $userId = $_COOKIE['userId'];

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $currentDate = date("Y-m-d H:i:s");

            if(empty($password) && $fp == null){
                $statement = $db->prepare("UPDATE user SET name = :name, email = :email, birthdate = :birthdate, phone = :phone, updated_at = :updated_at WHERE id = :userId");
            }

            if(empty($password) && $fp != null){
                $statement = $db->prepare("UPDATE user SET name = :name, email = :email, birthdate = :birthdate, profile_image = :pic, phone = :phone, updated_at = :updated_at WHERE id = :userId");
                $statement->bindParam(':pic', $fp, PDO::PARAM_STR);
            }
            if(!empty($password) &&  $fp == null){
                $statement = $db->prepare("UPDATE user SET name = :name, email = :email, birthdate = :birthdate, phone = :phone, password = :password, updated_at = :updated_at WHERE id = :userId");
                $password = md5($password);
                $statement->bindParam(':password', $password, PDO::PARAM_STR);
            }

            if(!empty($password) &&  $fp != null){
                $statement = $db->prepare("UPDATE user SET name = :name, email = :email, birthdate = :birthdate, profile_image = :pic, phone = :phone, password = :password, updated_at = :updated_at WHERE id = :userId");
                $password = md5($password);
                $statement->bindParam(':password', $password, PDO::PARAM_STR);
                $statement->bindParam(':pic', $fp, PDO::PARAM_STR);
            }


            if(empty($birth)){
                $birth = null;
            }

            $statement->bindParam(':name', $name, PDO::PARAM_STR);
            $statement->bindParam(':email', $email, PDO::PARAM_STR);
            $statement->bindParam(':birthdate', $birth, PDO::PARAM_STR);
            $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
            $statement->bindParam(':updated_at', $currentDate, PDO::PARAM_STR);
            $statement->bindParam(':userId', $userId, PDO::PARAM_STR);
            $statement->execute();

        } catch (PDOException $e) {
            $errors['general'] = $e;

            return $errors;
        }

        return $errors;

    }

    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'The name cannot be empty.';
        } elseif (!ctype_alnum($data['name'])) {
            $errors['name'] = 'The name can contain only alphanumeric characters.';
        }

        if (empty($data['username'])) {
            $errors['username'] = 'The username cannot be empty.';
        } elseif (strlen($data['username']) > 21) {
            $errors['username'] = 'The username cannot exceed the 20 characters.';
        } elseif (!ctype_alnum($data['username'])) {
            $errors['username'] = 'The username can contain only alphanumeric characters.';
        }

        if(!$data['updating']) {
            try {
                $db = new PDO('mysql:host=localhost; dbname=homestead', 'homestead', 'secret');
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $statement = $db->prepare("SELECT * FROM user WHERE username = :username AND is_active = 1 ORDER BY id DESC");
                $statement->bindParam(':username', $data['username'], PDO::PARAM_STR);
                $statement->execute();
                $users = $statement->fetchAll();
                if (!empty($users)) {
                    $errors['username'] = 'That username is already used';
                }
            } catch (PDOException $e) {
                $errors['general'] = 'An unexpected error has occurred fetching the users, please try it again later';
                return $errors;
            }
        }


        if(empty($data['email'])) {
            $errors['email'] = 'The email cannot be empty.';
        }elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if(empty($data['phone'])) {
            $errors['phone'] = 'The phone number cannot be empty.';
        }elseif (!preg_match("/^[0-9]{9}$/", $data['phone'])) {
            $errors['phone'] = 'Invalid phone format';
        }

        if ((empty($data['password']) || strlen($data['password']) < 6) && !$data['updating']) {
            $errors['password'] = 'The password must contain, at least, 6 characters.';
        }elseif (!$data['updating']){
            if($data['password'] != $data['passwordConfirm']){
                $errors['password'] = 'Passwords are not equals.';
            }
        }

        if(!$data['updating']) {
            $path = $_FILES['profile_image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            if ($ext != "jpg" && $ext != "png" && $ext != "") {
                $errors['image'] = 'Only JPG and PNG accepted.';
            } elseif ($_FILES['profile_image']['size'] > 500000) {
                $errors['image'] = 'Max size is 500kb.';
            }
        }

        return $errors;
    }

    public function addProfileData($name, $user, $email, $birth, $phone, $password, $prof_pic){
        $errors = [];

        $password = md5($password);

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $currentDate = date("Y-m-d H:i:s");
            $statement = $db->prepare("INSERT INTO user (name, username, email, birthdate, phone, password, profile_image, created_at, updated_at) 
                                                                  VALUES (:name, :username, :email, :birthdate, :phone, :password, :profile_image, :created_at, :updated_at)");
            $statement->bindParam(':name', $name, PDO::PARAM_STR);
            $statement->bindParam(':username', $user, PDO::PARAM_STR);
            $statement->bindParam(':email', $email, PDO::PARAM_STR);
            if(empty($birth)){
                $birth = null;
            }
            $statement->bindParam(':birthdate', $birth, PDO::PARAM_STR);
            $statement->bindParam(':phone', $phone, PDO::PARAM_STR);
            $statement->bindParam(':password', $password, PDO::PARAM_STR);
            $statement->bindParam(':profile_image', $prof_pic, PDO::PARAM_STR);
            $statement->bindParam(':created_at', $currentDate, PDO::PARAM_STR);
            $statement->bindParam(':updated_at', $currentDate, PDO::PARAM_STR);
            $statement->execute();

        } catch (PDOException $e) {
            $errors['general'] = $e;

            return $errors;
        }

        return $errors;

    }

}