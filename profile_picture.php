<?php
require_once 'inc/functions.php';

$action = '';
$error = '';
$pictureEdit = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {

    switch ($_GET['action']) {
        case 'add_pic':
            $action = 'add_pic';
            break;
        case 'edit_pic':
            $action = 'edit_pic';
            $pictureEdit = (isset($_GET['id'])) ? getPicture($_GET['id']) : null;
            break;
        default:
            break;
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

    switch ($_POST['submit']) {
        case 'set_as_default':
            break;
        case 'add_pic':
            if (!checkPicExtension($_FILES['picture']['name'])) {
                $error = 'File extension not supported !';
            }
            if ($_FILES['picture']['size'] > 1024 * 1024) {
                $error = 'File too Big ! No More than 1Mo.';
            }
            if ($error === '') {
                addPicture($_FILES['picture']);
            }
            break;
        case 'edit_pic':
            if (isset($_POST['delete_pic'])) {
                deletePicture($_POST['id']);
            } else {
                if (isset($_POST['set_as_default'])) {
                    setDefaultPicture($_POST['id']);
                }
                $values = [
                    'name' => $_POST['name'],
                    'description' => $_POST['description'],
                ];
                updatePicture($_POST['id'], $values);
            }
            break;
        default:
            break;
    }
}

$defaultPicture = getDefaultPic();
$allPictures = getAllPictures();

?>
<!doctype html>
<html>
<head>
    <title>Profile Picture Manager</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
    <header>
        <h1>Profile Pic Manager
        <img src="images/<?= $defaultPicture['filename'] ?>"  id="profile_pic" />
        </h1>
    </header>

<?php if ($action === 'add_pic'): ?>
    <section>
        <form method="POST" id="add_pic" enctype="multipart/form-data">
            <label for="picture">Please, select your picture :</label>
            <input type="file" name="picture" id="picture" />
            <button type="submit" name="submit" value="add_pic" class="action_add">Add Picture</button>
        </form>
    </section>
<?php endif; ?>

<?php if ($action === 'edit_pic' && $pictureEdit !== null): ?>
    <section>
        <form id="edit_pic" method="POST">
            <img src="images/<?= $pictureEdit['filename'] ?>" class="img-big" />
            <input type="hidden" name="id" id="id" value="<?= $pictureEdit['id'] ?>" />
            <div>
                <label for="name">Title :</label>
            </div>
            <div>
                <input type="text" name="name" id="name" value="<?= $pictureEdit['name'] ?>" />
            </div>
            <label for="description">Description :</label>
            <div>
                <textarea name="description" id="description"><?= $pictureEdit['description'] ?></textarea>
            </div>
            <div>
                <label for="set_as_default">Set As Default :</label>
                <input type="checkbox" id="set_as_default" name="set_as_default" value="1" <?= ($pictureEdit['is_default']) ? 'checked' : '' ?>/>
            </div>
            <div>
                <label for="delete_pic" class="red">Delete :</label>
                <input type="checkbox" id="delete_pic" name="delete_pic" value="1" />
            </div>
            <button type="submit" name="submit" value="edit_pic" class="action_add">Edit</button>
        </form>
    </section>

<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert-error"><?= $error ?></div>
<?php endif; ?>
    <section id="profile_pic_list">
        <h2>Available Profile Pictures</h2>
<?php if ($allPictures === []): ?>
        <p>No Pictures Avaiable.</p>
<?php else:
    foreach ($allPictures as $picture) : ?>
        <a href="?action=edit_pic&id=<?= $picture['id'] ?>" class="container">
            <img class="thumb-image" src="images/<?= $picture['filename'] ?>"  />
            <div class="overlay">
                <div class="text">Edit</div>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
    </section>

    <section id="add_new_profile_pic">
        <a href="?action=add_pic" class="add_pic">
            Add New Picture
        </a>
    </section>

</body>
</html>
