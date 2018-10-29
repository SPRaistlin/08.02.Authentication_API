<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;

use App\Service\Helpers;
use App\Service\JwtAuth;

use App\Entity\User;

class DefaultController extends Controller

{
    public function tests(Request $request, Helpers $helpers)
    {
        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository(User::class);

        $userList = $user_repo->findAll();
        return $helpers->json($userList);
    }
    public function signup(Request $request, Helpers $helpers){
        $em = $this->getDoctrine()->getManager();
        $email = "admin@admin.com";
        $name = "Pepe";
        $surname = "Botilo";
        $password ="1234A";
        $user = new User();
        // $user->setCreatedAt($createdAt);
        $user->setRole("ROLE_ADMIN");
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        // Encrypt the password
        $pwd = hash('sha256', $password);
        $user->setPassword($pwd);
        $em->persist($user);
        $em->flush();
        return $helpers->json("new user");
    }

    public function login(Request $request, Helpers $helpers, JwtAuth $jwt_auth){
        // Receive json by POST
        $json = $request->get('json', null);
        // Array to return by default
        $data = array(
            'status' => 'error',
            'data' => 'Send json via post !!'
        );
        if($json != null){
            // you make the login
            // We convert a json to a php object
            $params = json_decode($json);
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            // Encrypt the password
            $pwd = hash('sha256', $password);
            if($email != null && count($validate_email) == 0 && $password != null){
                if($getHash === null || $getHash === 'null' || $getHash === false || $getHash === 'false'){
                    $signup = $jwt_auth->signup($email, $pwd);
                }elseif($getHash === true || $getHash === 'true') {
                    $signup = $jwt_auth->signup($email, $pwd, true);
                }
                return new JsonResponse($signup);
                /*
                return $this->json($signup);
                */
            }else{
                $data = array(
                    'status' => 'error',
                    'data' => 'Email or password incorrect'
                );
            }
        }
        return $helpers->json($data);
    }
}
