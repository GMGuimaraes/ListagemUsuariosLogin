<?php 

require 'Controlador.php';
require 'app/modelos/Usuario.php';
include_once('app/modelos/Banco.php');

/**
* Controlador do login.
*/
class LoginController extends Controller  {
    
    /**
    * @var Usuario armazena o usuário logado no momento.
    */
    private $loggedUser;
    
    /**
    *  Construtor da classe. 
    *  Inicia/recupera a sessão do usuário e recupera o usuário logado.
    */
    function __construct() {
        session_start();
        if (isset($_SESSION['user'])) $this->loggedUser = $_SESSION['user'];
    }
    
    /**
    *  Método que trata as requisições:
    *  POST - busca pelo email no banco e confere se a senha é igual. Se sim, usuário logado!
    *  GET  - se não logado, abre a página de login, senão mostra as informações do usuário
    */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario = Usuario::buscar($_POST['email']);

            if (!is_null($usuario) && $usuario->igual($_POST['email'], $_POST['senha'])) {
                $_SESSION['user'] = $this->loggedUser = $usuario;
            }
            

            if ($this->loggedUser) {
                header('Location: index.php?acao=info');
            } else {
                header('Location: index.php?email=' . $_POST['email'] . '&mensagem=Usuário e/ou senha incorreta!');
            }
        } else {
            if (!$this->loggedUser) {
                $this->view('users/login');
            } else {
                header('Location: index.php?acao=info');
            }
        }
    }

    /**
    *  Método que trata as requisições:
    *  POST - cadastra o usuário com os dados informados. Se cadastrado, informa a indisponibilidade.
    *  GET  - mostra a página de cadastro.
    */
    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user = new Usuario($_POST['email'], $_POST['senha'], $_POST['nome']);
            
            try {
                $user->salvar();
                header('Location: index.php?email=' . $_POST['email'] . '&mensagem=Usuário cadastrado com sucesso!');
            } catch(PDOException $erro) {
                header('Location: index.php?acao=cadastrar&mensagem=Email já cadastrado!');
            }
        }
        $this->view('users/cadastrar');
    }

    /**
    *  Se o usuário estiver logado, motra as informações do mesmo.
    *  Senão, redireciona para a página de login.
    */
    public function info() {
        if (!$this->loggedUser) {
            header('Location: index.php?acao=entrar&mensagem=Você precisa se identificar primeiro');    
            return;
        }
        $this->view('users/info', $this->loggedUser);        
    }

    /**
    *  Se o usuário estiver logado, destroi a sessão e redireciona para a página de login.
    *  Senão, redireciona para a página de login.
    */
    public function sair() {
        if (!$this->loggedUser) {
            header('Location: index.php?acao=entrar&mensagem=Você precisa se identificar primeiro');
            return;
        }
        session_destroy();
        header('Location: index.php?mensagem=Usuário deslogado com sucesso!');
    }


    /**
     *  ============  EXERCÍCIO AVALIATIVO  ============
     * 
     *  Nesta atividade você deverá buscar no banco de dados todos
     *  os registros de usuários e passar um array com todos eles
     *  para a visão (última linha, onde $usuários é um array). 
     * 
     *  SE HOUVER UM USUÁRIO COM EMAIL "suporte@login.com" VOCÊ DEVERÁ 
     *  OMITIR ESSE REGISTRO NA LISTAGEM (remova-o da lista).
     * 
     *  Lembre-se de não deteriorar a arquitetura do sistema acessando
     *  módulos indevidos. Em outras palavras, o controlador não pode 
     *  acessar os dados diretamente. Portanto, você deverá criar uma 
     *  função na classe Usuário para fazer a busca dos dados.
     *  
     */
    public function listar() {
        $db = Banco::getInstance();
        $stmt = $db -> prepare('SELECT * FROM Usuarios WHERE email != "suporte@login.com"');
        $stmt->execute();
        $resultado = $stmt->fetch();
        $usuarios = array($resultado);
        
        $this->view('users/listar', $usuarios);   
    }
}

?>