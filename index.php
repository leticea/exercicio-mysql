<?php

    $utilizador = "root";
    $password = "1234";
    $novaBD = "negocios";
    $host = "localhost";

    //adicionarEncomendas(array(1,'Avião', 1, '2022-08-29'));

    buscarEncomendasCliente(0);


    function buscarEncomendasCliente($id_cliente)
    {
        //apresenta todas as encomendas de um determinado cliente
        global $utilizador, $password, $novaBD, $host;

        try {
            
            $ligacao = new PDO("mysql:dbname=$novaBD;host=$host", $utilizador, $password);

            $sql = "SELECT * FROM clientes 
                    INNER JOIN encomendas ON clientes.id_cliente = encomendas.id_cliente";

            $resultados = $ligacao->prepare($sql);
            $resultados->execute();

            foreach ($resultados as $linha) {

                echo $linha['nome'].' - '.$linha['produto'].' - '.$linha['quantidade'].'<br>';
            }


            //fechar a ligação
            $ligacao = null;
        }
        catch(PDOException $erro) {

            echo $erro->getMessage();
        }
    }

    function adicionarClientes($nome)
    {
        global $utilizador, $password, $novaBD, $host;

        try {

            $ligacao = new PDO("mysql:dbname=$novaBD;host=$host", $utilizador, $password);
            
            //verificar o id_cliente disponível
            $resultado = $ligacao->prepare("SELECT MAX(id_cliente) AS MaxID FROM clientes");
            $resultado->execute();
            $id_temp = $resultado->fetch(PDO::FETCH_ASSOC) ['MaxID'];

            if ($id_temp == null)
                $id_temp = 0;
            else    
                $id_temp++;

            //adicionar o novo cliente
            $resultado = $ligacao->prepare("INSERT INTO clientes VALUES(?,?)");
            $resultado->execute(array($id_temp, $nome));

            echo '<p>Novo cliente adicionado com sucesso.</p>';

            //fechar a ligação
            $ligacao = null;

        }
        catch(PDOException $erro) {

            echo $erro->getMessage();
        }
    }

    function adicionarEncomendas($elementos)
    {
        global $utilizador, $password, $novaBD, $host;

        try {

            $id_cliente = $elementos[0];
            $produto = $elementos[1];
            $quantidade = $elementos[2];
            $data_encomenda = $elementos[3];

            //verificar se o id_cliente é válido
            $ligacao = new PDO("mysql:dbname=$novaBD;host=$host", $utilizador, $password);
            $sql = "SELECT id_cliente FROM clientes WHERE id_cliente = :id_cliente";
            $resultado = $ligacao->prepare($sql);
            $resultado->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $final = $resultado->execute();

            $numLinhas = $resultado->rowCount();

            //verificar se o id existe
            if ($numLinhas == 0) {

                echo '<p>Não existe nenhum cliente com o id_cliente = '.$id_cliente.'</p>';
                exit;
            }

            //ir buscar o id_encomenda disponível
            $resultado = $ligacao->prepare("SELECT MAX(id_encomenda) AS MaxID FROM encomendas");
            $resultado->execute();
            $id_temp = $resultado->fetch(PDO::FETCH_ASSOC) ['MaxID'];

            if ($id_temp == null)
                $id_temp = 0;
            else    
                $id_temp++;


            //inserção da encomenda
            $sql = "INSERT INTO encomendas 
                    VALUES(:id_temp, :id_cliente, :produto, :quantidade, :data_encomenda)";
            $resultado = $ligacao->prepare($sql);
            $resultado->bindParam(":id_temp", $id_temp, PDO::PARAM_INT);
            $resultado->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $resultado->bindParam(":produto", $produto, PDO::PARAM_STR);
            $resultado->bindParam(":quantidade", $quantidade, PDO::PARAM_INT);
            $resultado->bindParam(":data_encomenda", $data_encomenda, PDO::PARAM_STR);
            $resultado->execute();

            echo '<p>Encomenda inserida com sucesso.</p>';

            //fecha a ligação
            $ligacao = null;

        }
        catch(PDOException $erro) {

            echo $erro->getMessage();
        }

    }

    function criarBD()
    {

        global $utilizador, $password, $novaBD, $host;

        try{

        //criar a nova base de dados
        $ligacao = new PDO("mysql:host=$host", $utilizador, $password);
        $ligacao->prepare("CREATE DATABASE $novaBD")->execute();
        $ligacao = new PDO("mysql:dbname=$novaBD;host=$host", $utilizador, $password);

        //adicionar tabela clientes
        $sql="CREATE TABLE clientes(

            id_cliente      INT NOT NULL PRIMARY KEY,
            nome            VARCHAR(50)

            )";

        $ligacao->prepare($sql)->execute();

        //adicionar tabela encomendas
        $sql="CREATE TABLE encomendas(

            id_encomenda    INT NOT NULL PRIMARY KEY,
            id_cliente      INT NOT NULL,
            produto         VARCHAR(100),
            quantidade      INT,
            data_encomenda  DATE,
            FOREIGN KEY(id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE NO ACTION

            )";

        $ligacao->prepare($sql)->execute();

        //fechar a ligação
        $ligacao = null;

        }
        catch(PDOException $erro) {

            echo $erro->getMessage();
        }
    }
 
?>
