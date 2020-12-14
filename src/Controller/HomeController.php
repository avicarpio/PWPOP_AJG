<?php

namespace PwExam\Controller;

use PwExam\Model\Item;

use DateTime;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use \PDO;
use PDOException;

final class HomeController
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;

        if(!isset($_SESSION['logged'])){
            $_SESSION['logged'] = false;
        }

        if(isset($_COOKIE['logged'])){
            $_SESSION['logged'] = $_COOKIE['logged'];
        }

    }

    public function indexAction(Request $request, Response $response)
    {
        $items = $this->items();

        $logged = $_SESSION['logged'];

        return $this->container->get('view')
            ->render($response, 'home.html.twig', [
                'logged' => $logged,
                'items' => $items,
                'mine' => false,
            ]);
    }

    public function myItemsAction(Request $request, Response $response)
    {
        $data = $request->getParsedBody();


        $logged = $_SESSION['logged'];

        $items = $this->searchItem($data['search'], true);

        return $this->container->get('view')
            ->render($response, 'home.html.twig', [
                'logged' => $logged,
                'items' => $items,
                'mine' => true,
            ]);

    }

    public function searchAction(Request $request, Response $response)
    {
        $data = $request->getParsedBody();


        $logged = $_SESSION['logged'];

        $items = $this->searchItem($data['search'], false);

        return $this->container->get('view')
            ->render($response, 'home.html.twig', [
                'logged' => $logged,
                'items' => $items,
                'mine' => false,
            ]);

    }


    public function items() {

        $products = [];

        if(isset($_COOKIE['userId'])){
            $userId = $_COOKIE['userId'];
        }else{
            $userId = 0;
        }

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $db->prepare("SELECT * FROM item WHERE NOT id_user = :id_user AND is_active = 1 ORDER BY created_at DESC LIMIT 5");
            $statement->bindParam(':id_user', $userId, PDO::PARAM_STR);
            $statement->execute();
            $items = $statement->fetchAll();

            foreach($items as $item){
                array_push($products, new Item(
                    $item['id'],
                    $item['title'],
                    $item['description'],
                    $item['category'],
                    $item['price'],
                    "/assets/images/" . $item['product_image'],
                    new DateTime($item['created_at']),
                    new DateTime($item['updated_at'])));
            }

            return $products;

        } catch (PDOException $e) {
            return "";
        }

    }

    public function searchItem($search, $mine) {

        $products = [];

        $search = "%" . $search . "%";

        if(isset($_COOKIE['userId'])){
            $userId = $_COOKIE['userId'];
        }else{
            $userId = 0;
        }

        try {
            $db = new PDO('mysql:host=localhost;dbname=homestead', 'homestead', 'secret', []);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if($mine) {
                $statement = $db->prepare("SELECT * FROM item WHERE id_user = :id_user AND is_active = 1 ORDER BY created_at DESC");
            }else{
                $statement = $db->prepare("SELECT * FROM item WHERE NOT id_user = :id_user AND title LIKE :search AND is_active = 1 ORDER BY created_at DESC LIMIT 5");
                $statement->bindParam(':search', $search, PDO::PARAM_STR);
            }

            $statement->bindParam(':id_user', $userId, PDO::PARAM_STR);
            $statement->execute();
            $items = $statement->fetchAll();

            foreach($items as $item){
                array_push($products, new Item($item['id'], $item['title'], $item['description'],$item['category'], $item['price'], "/assets/images/" . $item['product_image'], new DateTime($item['created_at']), new DateTime($item['updated_at'])));
            }


            return $products;

        } catch (PDOException $e) {
            return "";
        }

    }
}
