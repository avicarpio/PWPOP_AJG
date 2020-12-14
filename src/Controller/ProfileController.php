<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 14/07/19
 * Time: 18:11
 */

namespace PwExam\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use \PDO;
use PDOException;


class ProfileController
{
    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function formAction(Request $request, Response $response): Response
    {

        $errors = [];

        $logged = $_SESSION['logged'];
        if(isset($_COOKIE['userId'])) {
            $userId = $_COOKIE['userId'];
            $user = $this->searchProfile($userId);
        }else{
            $userId = 0;
        }

        if(empty($user)){
            if($userId != 0) {
                $errors['general'] = 'Error fetching the user';
            }
            return $this->container->get('view')->render($response, 'profile.twig', ['logged'=>$logged, 'errors' => $errors]);
        }else{
            $fecha = explode(" ", $user[0]['birthdate']);
            $user[0]['birthdate'] = $fecha[0];
            $user[0]['profile_image'] = "/assets/images/" .$user[0]['profile_image'];
            return $this->container->get('view')->render($response, 'profile.twig', ['logged'=>$logged, 'user' => $user[0]]);
        }
    }

    public function searchProfile($userId)
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

    public function deleteAccountAction(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $errors = $this->deleteAccount($id);

        if(empty($errors)){
            $errors['info'] = 'Account Deleted';
            $_SESSION['logged'] = false;
            if(isset($_COOKIE['logged'])){
                unset($_COOKIE['logged']);
            }
            unset($_COOKIE['userId']);
            return $this->container->get('view')->render($response, 'login.twig', ['errors'=>$errors]);
        }else{
            return $this->container->get('view')->render($response, 'login.twig', ['errors'=>$errors]);
        }


    }

    public function deleteAccount($id)
    {

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("UPDATE item SET is_active = 0 WHERE id_user = :userId");
            $statement->bindParam(':userId', $id, PDO::PARAM_STR);
            $statement->execute();
            $statement = $db->prepare("UPDATE user SET is_active = 0 WHERE id = :userId");
            $statement->bindParam(':userId', $id, PDO::PARAM_STR);
            $statement->execute();

        } catch (PDOException $e) {
            $errors['general'] = 'Failed to delete user';

            return $errors;
        }

    }


    public function logout() {



        if( isset($_COOKIE['userId']) ) {
            setcookie( 'userId', '', time()-3600);
            setcookie( 'logged', '', time()-3600);
            unset($_SESSION['logged']);

        }


        header('Location: /' );

    }
}