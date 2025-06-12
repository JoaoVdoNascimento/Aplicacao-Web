<?php
// Define o cabeçalho para permitir requisições de diferentes origens (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Lida com a requisição pre-flight do CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petshop_db";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["message" => "Conexão falhou: " . $conn->connect_error]));
}

// --- Roteamento Compatível ---
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$request_uri = strtok($_SERVER['REQUEST_URI'], '?');
$path = substr($request_uri, strlen($base_path));
$path_parts = explode('/', trim($path, '/'));

// Remove 'api.php' se ele for o primeiro elemento
if (isset($path_parts[0]) && $path_parts[0] == 'api.php') {
    array_shift($path_parts);
}

$entity = $path_parts[0] ?? '';
$id = isset($path_parts[1]) && is_numeric($path_parts[1]) ? (int)$path_parts[1] : null;
// Obtém o método da requisição HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Processa a requisição com base no método
switch ($method) {
    case 'GET':
        if ($entity == 'clientes') {
            $sql = "SELECT * FROM clientes ORDER BY nome ASC";
            $result = $conn->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($data);

        } elseif ($entity == 'atendentes') {
            $sql = "SELECT * FROM atendentes ORDER BY nome ASC";
            $result = $conn->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($data);

        } elseif ($entity == 'atendimentos') {
            $sql = "SELECT a.id, c.nome AS cliente_nome, c.nomePet AS cliente_nome_pet, t.nome AS atendente_nome, a.tipoServico, a.valor, a.dataAtendimento, a.horaAtendimento
                    FROM atendimentos a
                    JOIN clientes c ON a.cliente_id = c.id
                    JOIN atendentes t ON a.atendente_id = t.id
                    ORDER BY a.dataAtendimento DESC, a.horaAtendimento DESC";
            $result = $conn->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($data);

        } else {
            http_response_code(404);
            echo json_encode(["message" => "Recurso GET não encontrado"]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if ($entity == 'clientes') {
            $stmt = $conn->prepare("INSERT INTO clientes (nome, telefone, email, nomePet, tipoPet) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $input['nome'], $input['telefone'], $input['email'], $input['nomePet'], $input['tipoPet']);
        
        } elseif ($entity == 'atendentes') {
            $stmt = $conn->prepare("INSERT INTO atendentes (nome, telefone, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $input['nome'], $input['telefone'], $input['email']);
        
        } elseif ($entity == 'atendimentos') {
            $data_recebida = $input['dataAtendimento'] ?? null;
            $data_para_banco = null;

            if (!empty($data_recebida)) {
                $dateObject = DateTime::createFromFormat('Y-m-d', $data_recebida);
                if ($dateObject && $dateObject->format('Y-m-d') === $data_recebida) {
                    $data_para_banco = $dateObject->format('Y-m-d');
                }
            }

            $stmt = $conn->prepare("INSERT INTO atendimentos (cliente_id, atendente_id, tipoServico, valor, dataAtendimento, horaAtendimento) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdss", $input['clienteId'], $input['atendenteId'], $input['tipoServico'], $input['valor'], $data_para_banco, $input['horaAtendimento']);
        
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Recurso POST não encontrado"]);
            exit();
        }

        if ($stmt->execute()) {
            echo json_encode(["message" => "Registro adicionado com sucesso!", "id" => $conn->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erro ao adicionar registro: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'DELETE':
        if ($id === null) {
            http_response_code(400);
            echo json_encode(["message" => "ID não fornecido para exclusão"]);
            break;
        }

        if ($entity == 'clientes' || $entity == 'atendentes' || $entity == 'atendimentos') {
            $stmt = $conn->prepare("DELETE FROM $entity WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["message" => ucfirst($entity) . " excluído(a) com sucesso!"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Erro ao excluir: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Recurso DELETE não encontrado"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método não suportado"]);
        break;
}

$conn->close();
?>