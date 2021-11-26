<?php

$data = ['title' => $title];
if (isset($userName)) {
    $data['userName'] = $userName;
}
$this->layout('layout', $data);

?>

<div class="container mx-auto text-center">
</div>
