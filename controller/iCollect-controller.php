<?php

class ICollectController {
    private $_f3;
    private $_validator;
    private $_cnxn;
    private $_user;

    public function __construct($f3)
    {
        $this->_f3 = $f3;
        $this->_validator = new Validate();
        $this->_cnxn = new Database();
    }

    public function home()
    {
        $_SESSION['page']="iCollect";
        $view = new Template();
        echo $view->render("views/home.html");
    }

    public function login()
    {
        $_SESSION['page']="iCollect Login";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isValid = true;
            $this->_f3->set("username", $_POST["username"]);
            $this->_f3->set("password", $_POST["password"]);

            if ($this->_cnxn) {
                if ($this->_validator->validLogin($_POST["username"]) AND
                    $this->_cnxn->checkCredentials($_POST["username"], $_POST['password'])) {
                    $_SESSION["username"] = $_POST["username"];
                } else {
                    $this->_f3->set("errors['login']", "Try again.");
                    $isValid = false;
                }
            } else {
                $this->_f3->set("errors['connection']", "No Connection.");
                $isValid = false;
            }

            if ($isValid) {
                $this->_f3->reroute('/welcome');
            }
        }
        $view = new Template();
        echo $view->render("views/login.html");
    }

    public function signup() {
        require ("../../../connection.php");
        $_SESSION['page']="iCollect Signup";
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isValid = true;
            $this->_f3->set("username", $_POST["username"]);
            $this->_f3->set("password", $_POST["password"]);
            $this->_f3->set("password2", $_POST["password2"]);
            $this->_f3->set("email", $_POST["email"]);
            $this->_f3->set("accountType", $_POST["accountType"]);

            $this->_user = new User();

            if ($this->_f3->exists("errors['username']")) {
                $this->_f3->clear("errors['username']");
            }

            if ($this->_f3->exists("errors['email']")) {
                $this->_f3->clear("errors['email']");
            }

            if ($this->_cnxn) {
                if ($this->_validator->validLogin($_POST["username"]) AND !$this->_cnxn->containsUsername($_POST["username"])) {
                    $this->_user->setUsername($_POST["username"]);
                } else {
                    $this->_f3->set("errors['username']", "Please choose another name.");
                    $isValid = false;
                }
                //add db function
                if ($this->_validator->validEmail($_POST["email"]) AND !$this->_cnxn->containsEmail($_POST["email"]) ) {
                    $this->_user->setUserEmail($_POST["email"]);
                } else {
                    $this->_f3->set("errors['email']", "Please choose another email.");
                    $isValid = false;
                }

                if ($this->_validator->validPassword($_POST["password"])) {
                    if ($this->_validator->passwordMatch($_POST["password"], $_POST["password2"])) {
                        $this->_user->setPassword($_POST["password"]);
                    } else {
                        $this->_f3->set("errors['passwordMatch']", "*not a match");
                        $isValid = false;
                    }

                } else {
                    $this->_f3->set("errors['password']", "*required");
                    $isValid = false;
                }

                if ($this->_validator->validateAcctType($_POST["accountType"])) {
                    $this->_user->setPremium($_POST["accountType"]);
                } else {
                    $this->_f3->set("errors['acctType']", "I don't think so");
                    $isValid = false;
                }
            } else {
                $this->_f3->set("errors['connection']", "No Connection.");
                $isValid = false;
            }

            //all inputs valid and user is added to the database, go to next page
            if ($isValid) {
                $id = $this->_cnxn->addNewUser($this->_user);
                if($id != null) {
                    $this->_user->setUserID($id);
                    $_SESSION["user"] = $this->_user;
                    $this->_f3->reroute('/success');
                } else {
                    $this->_f3->set("errors['addNewUser']", "Something went wrong try again.");
                }
            }
        }

        $view = new Template();
        echo $view->render("views/signup.html");
    }

    public function welcome() {
        $_SESSION['page']="Welcome";
        $view = new Template();
        echo $view->render("views/welcome.html");
    }

    public function createCollection() {
        $_SESSION['page']="Create Collection";
        $view = new Template();
        echo $view->render("views/create-collection.html");
    }

    public function success() {
        $_SESSION['page']="Sign Up Success";
        $view = new Template();
        echo $view->render("views/success.html");
    }
}