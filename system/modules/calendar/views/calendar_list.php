<?php
$op = $_POST['op'];
$id = $_POST['id'];
switch ($op) {
    case 'complete':
        $result = array('isSuccess' => true);
        echo json_encode($result);
        break;

    case 'delete':
        $result = array('isSuccess' => true);
        echo json_encode($result);
        break;

    case 'getdetail':
        // 根据 Id 返回对应条目详情
        $content = file_get_contents("detail.php");

        $result = array('isSuccess' => true, 'content' => $content);
        echo json_encode($result);
        break;

    default:
        # code...
        break;
}
?>