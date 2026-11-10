
<?php

require_once "config.php";

header("Content-Type: application/json; charset=UTF-8");

$metodo = $_SERVER["REQUEST_METHOD"];

$tabela = $_GET["tabela"] ?? "";

$id = $_GET["id"] ?? null;

$dados = json_decode(file_get_contents("php://input"), true) ?? [];

function resposta($mensagem, $status = 200) {
    http_response_code($status);
    echo json_encode(["mensagem" => $mensagem],
        JSON_UNESCAPED_UNICODE);
    exit;
}



if ($tabela == "usuarios") {

    if ($metodo == "POST") {

        if (empty($dados["nome"]) ||
            empty($dados["username"]) ||
            empty($dados["email"]) ||
            empty($dados["senha"])) {

            resposta("Preencha todos os campos obrigatorios!", 400);
        }

        $sql = "INSERT INTO usuarios
                (nome, username, email, senha)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados["nome"],
            $dados["username"],
            $dados["email"],
            password_hash($dados["senha"], PASSWORD_DEFAULT)
        ]);

        resposta("Usuario cadastrado com sucesso!", 201);
    }

    if ($metodo == "GET") {

        $sql = "SELECT id_usuario, nome, username, email,
                data_cadastro FROM usuarios";

        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ($metodo == "PUT") {

        if (empty($id)) {
            resposta("Informe o ID do usuario!", 400);
        }

        $campos = [];
        $valores = [];

        foreach (["nome", "username", "email"] as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = ?";
                $valores[] = $dados[$campo];
            }
        }

        if (!empty($dados["senha"])) {
            $campos[] = "senha = ?";
            $valores[] = password_hash(
                $dados["senha"], PASSWORD_DEFAULT
            );
        }

        if (empty($campos)) {
            resposta("Nenhum campo para atualizar!", 400);
        }

        $valores[] = $id;

        $sql = "UPDATE usuarios SET " .
               implode(", ", $campos) .
               " WHERE id_usuario = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        resposta("Usuario atualizado com sucesso!");
    }

    if ($metodo == "DELETE") {

        if (empty($id)) {
            resposta("Informe o ID do usuario!", 400);
        }

        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        resposta(
            $stmt->rowCount() > 0
                ? "Usuario excluido com sucesso!"
                : "Usuario nao encontrado!",
            $stmt->rowCount() > 0 ? 200 : 404
        );
    }
}



if ($tabela == "publicacoes") {

    if ($metodo == "POST") {

        if (empty($dados["id_usuario"])) {
            resposta("Informe o ID do usuario!", 400);
        }

        $sql = "INSERT INTO publicacoes
                (id_usuario, legenda, imagem_url)
                VALUES (?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados["id_usuario"],
            $dados["legenda"] ?? null,
            $dados["imagem_url"] ?? null
        ]);

        resposta("Publicacao cadastrada com sucesso!", 201);
    }

    if ($metodo == "GET") {

        $sql = "SELECT * FROM publicacoes";

        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ($metodo == "PUT") {

        if (empty($id)) {
            resposta("Informe o ID da publicacao!", 400);
        }

        $campos = [];
        $valores = [];

        foreach (["id_usuario", "legenda", "imagem_url"] as $campo) {
            if (array_key_exists($campo, $dados)) {
                $campos[] = "$campo = ?";
                $valores[] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            resposta("Nenhum campo para atualizar!", 400);
        }

        $valores[] = $id;

        $sql = "UPDATE publicacoes SET " .
               implode(", ", $campos) .
               " WHERE id_publicacao = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        resposta("Publicacao atualizada com sucesso!");
    }

    if ($metodo == "DELETE") {

        if (empty($id)) {
            resposta("Informe o ID da publicacao!", 400);
        }

        $sql = "DELETE FROM publicacoes WHERE id_publicacao = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        resposta(
            $stmt->rowCount() > 0
                ? "Publicacao excluida com sucesso!"
                : "Publicacao nao encontrada!",
            $stmt->rowCount() > 0 ? 200 : 404
        );
    }
}





if ($tabela == "curtidas") {

    if ($metodo == "POST") {

        if (empty($dados["id_usuario"]) ||
            empty($dados["id_publicacao"])) {

            resposta("Informe o usuario e a publicacao!", 400);
        }

        $sql = "INSERT INTO curtidas
                (id_usuario, id_publicacao)
                VALUES (?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados["id_usuario"],
            $dados["id_publicacao"]
        ]);

        resposta("Curtida registrada com sucesso!", 201);
    }

    if ($metodo == "GET") {

        $sql = "SELECT * FROM curtidas";

        $stmt = $pdo->query($sql);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE);

        exit;
    }

    if ($metodo == "PUT") {

        if (empty($id)) {
            resposta("Informe o ID da curtida!", 400);
        }

        if (empty($dados["id_usuario"]) ||
            empty($dados["id_publicacao"])) {

            resposta("Informe o usuario e a publicacao!", 400);
        }

        $sql = "UPDATE curtidas
                SET id_usuario = ?, id_publicacao = ?
                WHERE id_curtida = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $dados["id_usuario"],
            $dados["id_publicacao"],
            $id
        ]);

        resposta("Curtida atualizada com sucesso!");
    }

    if ($metodo == "DELETE") {

        if (empty($id)) {
            resposta("Informe o ID da curtida!", 400);
        }

        $sql = "DELETE FROM curtidas WHERE id_curtida = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        resposta(
            $stmt->rowCount() > 0
                ? "Curtida removida com sucesso!"
                : "Curtida nao encontrada!",
            $stmt->rowCount() > 0 ? 200 : 404
        );
    }

}

resposta("Tabela ou metodo nao encontrado!", 404);

?>
