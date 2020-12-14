<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 4/06/19
 * Time: 15:45
 */

namespace PwExam\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use \PDO;
use PDOException;

class LoginController
{
    private const COOKIES_ADVICE = 'cookies_advice';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function formAction(Request $request, Response $response): Response{
        return $this->container->get('view')->render($response, 'login.twig', []);
    }

    public function loginAction(Request $request, Response $response): Response{

        $data = $request->getParsedBody();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['logged'] = false;
            $_SESSION['userId'] = "0";
            return $this->container->get('view')->render($response, 'login.twig',['errors'=>$errors, 'logged'=>false], 200);
        }else{
            $_SESSION['logged'] = true;
            if(isset($data['remember'])){
                setcookie('logged', true, time() + 3600*3);
            }
            return $this->container->get('view')->render($response, 'login.twig', ['logged'=>true], 200);
        }

    }

    private function validate(array $data): array
    {
        $errors = [];
        if(empty($data['email'])) {
            $errors['email'] = 'The email cannot be empty.';
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'The password must contain, at least, 6 characters.';
        }


        if (empty($errors)){
            $errors = $this->dataMatch($data['email'],$data['password']);

            return $errors;

        }

        return $errors;
    }

    public function dataMatch($user, $password){
        $errors = [];
        $success = false;

        $password = md5($password);

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("SELECT * FROM user WHERE email = :email AND is_active = 1 ORDER BY id DESC");
            $statement->bindParam(':email', $user, PDO::PARAM_STR);
            $statement->execute();
            $users = $statement->fetchAll();
            if(!empty($users)) {
                foreach ($users as $userMatch) {
                    if ($password == $userMatch['password']) {
                        $success = true;
                        setcookie("userId", $userMatch['id'], time()+3600*3);

                    }
                }
                if(!$success){
                    $errors['email'] = 'Incorrect email or password';
                    return $errors;
                }elseif ($success){
                    return $errors;
                }
            }

            $errors['email'] = 'Incorrect email or password';
            return $errors;

        } catch (PDOException $e) {
            $errors['general'] = 'An unexpected error has occurred fetching the users, please try it again later';
            return $errors;
        }

    }

}