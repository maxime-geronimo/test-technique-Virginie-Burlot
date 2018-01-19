<?php

/**
 * DB Functions
 */
function dbConnect() {
    try {
        $pdo = new PDO('sqlite:data/data.sqlite');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(Exception $e) {
        echo 'Impossible d\'accéder à la base de données SQLite : ' .
            $e->getMessage();
        die();
    }
}

function prepareQuery(PDO $pdo, string $query, array $params = []): PDOStatement {
    $stmt = $pdo->prepare($query);
    foreach($params as $param) {
        $stmt->bindParam($param['name'], $param['value']);
    }
    return $stmt;
}

function executeQuery(PDOStatement $stmt): PDOStatement {
    $stmt->execute();
    return $stmt;
}

function prepareAndExecute($query, $params = []) {
    $pdo = dbConnect();
    $stmt = prepareQuery($pdo, $query, $params);
    executeQuery($stmt);
}

function fetchResult(PDOStatement $stmt): array {
    return $stmt->fetchAll();
}

function getResults(string $query, array $params = []) {
    $pdo = dbConnect();
    $stmt = prepareQuery($pdo, $query, $params);
    $stmt = executeQuery($stmt);
    return fetchResult($stmt);
}

/**
 * Profile Pic Functions
 */
function getDefaultPic(): array {
    $query = 'SELECT filename FROM profile_pics WHERE is_default = 1';
    $result = getResults($query);
    if ($result !== []) {
        return $result[0];
    }
    return ['filename' => 'default.jpg'];
}

function getAllPictures(): array {
    $query = 'SELECT id, filename, is_default FROM profile_pics';
    $results = getResults($query);
    return $results;
}

function getPicture(int $id): array {
    $query = 'SELECT id, name, filename, description, is_default FROM profile_pics WHERE id = :id';
    $params = [
        ['name' => ':id', 'value' => $id]
    ];
    $results = getResults($query, $params);
    return $results[0];
}

function addPicture($picture) {

    $query = 'SELECT max(id) AS max_id FROM profile_pics';
    $result = getResults($query);
    $maxId = ($result[0]['max_id'] === null) ? 1 : $result[0]['max_id'];

    $ext = strrchr($picture['name'], '.');
    $ext = substr($ext, 1);

    $filename = $maxId . '.' . $ext;
    move_uploaded_file(
        $picture['tmp_name'],
        'images/' . $filename
    );

    $query = 'INSERT INTO profile_pics(filename) VALUES (:filename)';
    $params = [
        ['name' => ':filename', 'value' => $filename],
    ];

    prepareAndExecute($query, $params);
}

function updatePicture(int $id, array $values) {

    $query = 'UPDATE profile_pics SET name =  :name, description = :description WHERE id = :id';
    $params = [
        ['name' => ':id', 'value' => $id ],
        ['name' => ':name', 'value'  => $values['name']],
        ['name' => ':description', 'value'  => $values['description']]
    ];
    prepareAndExecute($query, $params);

}

function setDefaultPicture(int $id) {

    $query = 'UPDATE profile_pics SET is_default = 0  WHERE is_default = 1';
    prepareAndExecute($query);

    $query = 'UPDATE profile_pics SET is_default = 1 WHERE id = :id';
    $params = [
        ['name' => ':id', 'value' => $id ],
    ];
    prepareAndExecute($query, $params);

}

function deletePicture(int $id) {
    $picture = getPicture($id);
    $filename = $picture['filename'];

    $query = 'DELETE FROM profile_pics WHERE id = :id';
    $params = [
        ['name' => ':id', 'value' => $id]
    ];
    prepareAndExecute($query, $params);
    unlink('images/' . $filename);
}

function checkPicExtension($picture) {
    $ext = strrchr($picture, '.');
    $ext = substr($ext, 1);
    $ext = strtolower($ext);
    $validExt = array('gif', 'jpg', 'jpeg', 'png');
    return in_array($ext, $validExt);
}